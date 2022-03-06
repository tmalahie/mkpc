function RTCService() {
    /**
    * The stream object used to send media
    */
    let localStream = null;
    /**
    * All peer connections
    */
    let peers = {}

    let peerId;

    //////////// CONFIGURATION //////////////////

    /**
    * RTCPeerConnection configuration 
    */

    const configuration = {
        // Using From https://www.metered.ca/tools/openrelay/
        "iceServers": [
            {
                urls: "stun:openrelay.metered.ca:80"
            },
            {
                urls: "turn:openrelay.metered.ca:80",
                username: "openrelayproject",
                credential: "openrelayproject"
            },
            {
                urls: "turn:openrelay.metered.ca:443",
                username: "openrelayproject",
                credential: "openrelayproject"
            },
            {
                urls: "turn:openrelay.metered.ca:443?transport=tcp",
                username: "openrelayproject",
                credential: "openrelayproject"
            }
        ]
    }

    /**
    * UserMedia constraints
    */
    let constraints = {
        audio: true
    }

    function joinVocChat(successCallback, errorCallback) {
        navigator.mediaDevices.getUserMedia(constraints).then(stream => {
            localStream = stream;
            
            init(successCallback);
        }).catch(errorCallback);
    }

    function init(successCallback) {
        xhr("joinChatVoc.php", "", function(res) {
            if (res && (res >= 0)) {
                peerId = +res;
                if (successCallback)
                    successCallback();
                return true;
            }
        });
    }

    function pollPeerSignal(memberPeerId) {
        if (peers[memberPeerId].isPolling) return;
        peers[memberPeerId].isPolling = true;
        doPollPeerSignal(memberPeerId);
    }
    function doPollPeerSignal(memberPeerId) {
        if (peers[memberPeerId].hasStreamed) {
            peers[memberPeerId].isPolling = false;
            return;
        }
        let interval = 100;
        xhr("getChatSignals.php", "sender="+memberPeerId+"&receiver="+peerId+"&lastsignalid="+peers[memberPeerId].lastSignalId, function(res) {
            let signals;
            try {
                signals = JSON.parse(res);
            }
            catch (e) {
                return false;
            }
            for (const signal of signals) {
                peers[memberPeerId].lastSignalId = signal.id;
                peers[memberPeerId].conn.signal(signal.data);
            }
            setTimeout(function() {
                doPollPeerSignal(memberPeerId);
            }, interval);
            return true;
        });
    }

    function removePeer(socket_id) {
        
        let audioEl = document.getElementById(socket_id)
        if (audioEl) {
            
            const tracks = audioEl.srcObject.getTracks();
            
            tracks.forEach(function (track) {
                track.stop()
            })
            
            audioEl.srcObject = null
            audioEl.parentNode.removeChild(audioEl)
        }
        if (peers[socket_id]) peers[socket_id].conn.destroy()
        delete peers[socket_id]
    }

    function addPeer(socket_id) {
        if (!peerId) return false;
        if (peers[socket_id]) return false;
        var am_initiator = (socket_id < peerId);
        peers[socket_id] = {
            conn: new SimplePeer({
                initiator: am_initiator,
                stream: localStream,
                config: configuration
            }),
            lastSignalId: 0
        };
        
        peers[socket_id].conn.on('signal', data => {
            sendSignalDebounced(socket_id, data);
            pollPeerSignal(socket_id);
        })
        
        peers[socket_id].conn.on('stream', stream => {
            let newAudio = document.createElement('audio');
            newAudio.srcObject = stream;
            newAudio.id = socket_id;
            newAudio.autoplay = true;
            newAudio.className = "audio";
            audios.appendChild(newAudio);
            peers[socket_id].hasStreamed = true;
        })
        
        if (!am_initiator)
            pollPeerSignal(socket_id);
        
        return true;
    }
    let sendingSignalHandlers = {};
    function sendSignalDebounced(socket_id, data, delay=300) {
        if (!sendingSignalHandlers[socket_id]) {
            sendingSignalHandlers[socket_id] = {
                data: []
            };
        }
        sendingSignalHandlers[socket_id].data.push(data);
        clearTimeout(sendingSignalHandlers[socket_id].timer);
        sendingSignalHandlers[socket_id].timer = setTimeout(function() {
            xhr("registerChatSignals.php", "sender="+peerId+"&receiver="+socket_id+"&signals="+encodeURIComponent(JSON.stringify(sendingSignalHandlers[socket_id].data)), function(res) {
                return (res == 1);
            });
            delete sendingSignalHandlers[socket_id];
        }, delay);
    }

    function toggleMute() {
        for (let index in localStream.getAudioTracks()) {
            localStream.getAudioTracks()[index].enabled = !localStream.getAudioTracks()[index].enabled;
        }
    }

    return {
        joinVocChat,
        addPeer,
        removePeer,
        toggleMute
    }
}