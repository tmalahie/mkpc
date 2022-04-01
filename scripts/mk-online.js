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

    function joinVocChat(options) {
        if (!navigator.mediaDevices) options.error(new Error("Your browser does not support the WebRTC API"));
        navigator.mediaDevices.getUserMedia(constraints).then(stream => {
            localStream = stream;
            muteStream(options.muted);
            
            init(options);
        }).catch(options.error);
    }

    function quitVocChat() {
        xhr("quitChatVoc.php", "peer="+peerId, function(res) {
            return (res == 1);
        });
        peerId = null;
        for (var socket_id in peers) {
            var peer = peers[socket_id];
            delete peers[socket_id];
            peer.conn.destroy();
        }
        localStream.getTracks().forEach(function(track) {
            track.stop();
        });
    }

    function init(options) {
        xhr("joinChatVoc.php", options.muted ? "muted=1" : "", function(res) {
            if (res && (res >= 0)) {
                peerId = +res;
                if (options.success)
                    options.success();
                return true;
            }
        });
    }

    function pollPeerSignal(memberPeerId) {
        if (peers[memberPeerId].isPolling) return;
        peers[memberPeerId].isPolling = true;
        doPollPeerSignal(memberPeerId, 100,0);
    }
    function doPollPeerSignal(memberPeerId, triedSince) {
        if (peers[memberPeerId].hasStreamed) {
            peers[memberPeerId].isPolling = false;
            return;
        }
        if (triedSince > 10000) {
            removePeer(memberPeerId);
            return;
        }
        const interval = Math.max(100, triedSince/10-100);
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
                doPollPeerSignal(memberPeerId, triedSince+interval);
            }, interval);
            return true;
        });
    }

    function removePeer(socket_id) {
        var peer = peers[socket_id];
        if (!peer) return;
        const audioEl = peer.audio;
        if (audioEl) {
            const tracks = audioEl.srcObject.getTracks();
            
            tracks.forEach(function (track) {
                track.stop()
            });
            audioEl.srcObject = null;
            document.body.removeChild(audioEl);
        }
        delete peers[socket_id];
        peer.conn.destroy();
        xhr("unregisterChatSignals.php", "sender="+peerId+"&receiver="+socket_id, function(res) {
            return (res == 1);
        });
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
            newAudio.autoplay = true;
            newAudio.className = "audio";
            document.body.appendChild(newAudio);
            peers[socket_id].audio = newAudio;
            peers[socket_id].hasStreamed = true;
        })
        
        peers[socket_id].conn.on('error', () => {
        });
        
        peers[socket_id].conn.on('close', () => {
            removePeer(socket_id);
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

    function muteStream(muted) {
        if (!localStream) return;
        
        for (let index in localStream.getAudioTracks()) {
            localStream.getAudioTracks()[index].enabled = !muted;
        }
    }

    function toggleMute(muted, callback) {
        muteStream(muted);
        xhr("updateChatVoc.php", "peer="+peerId+"&muted="+ (muted ? 1:0), function() {
            if (callback) callback();
            return true;
        });
    }

    return {
        joinVocChat,
        addPeer,
        removePeer,
        quitVocChat,
        toggleMute
    }
}