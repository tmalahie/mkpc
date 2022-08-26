function RTCService(opts = {}) {
    /**
    * The stream object used to send media
    */
    let localStream = null;
    /**
    * All peer connections
    */
    let peers = {}

    let peerId;

    let spectatorId = opts.spectatorId;
    function setSpectatorId(id) {
        spectatorId = id;
    }

    //////////// CONFIGURATION //////////////////

    /**
    * RTCPeerConnection configuration 
    */

    const configuration = {
        // Using From https://www.metered.ca/tools/openrelay/
        "iceServers": [
            {
                urls: [
                    'stun:stun.l.google.com:19302',
                    'stun:stun1.l.google.com:19302',
                    'stun:stun2.l.google.com:19302',
                    'stun:stun3.l.google.com:19302',
                    'stun:stun4.l.google.com:19302'
                ]
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
    function getVocChat(options) {
        if (!peerId) return options.callback(null);
        xhr("getChatVoc.php", "peer="+peerId+(spectatorId ? "&spectator="+spectatorId : ""), function(res) {
            try {
                res = JSON.parse(res);
            }
            catch(e) {
                options.callback(null);
                return true;
            }
            if (res) {
                if (options.callback)
                    options.callback(res);
                return true;
            }
            quitVocChat({
                callback: function() {
                    options.callback(null);
                }
            });
            return true;
        });
    }

    function quitVocChat(options) {
        xhr("quitChatVoc.php", "peer="+peerId, function(res) {
            if (res == 1) {
                if (options && options.callback)
                    options.callback();
                return true;
            }
        });
        peerId = null;
        for (var socket_id in peers) {
            var peer = peers[socket_id];
            delete peers[socket_id];
            peer.conn.destroy();
        }
        if (!localStream) return;
        localStream.getTracks().forEach(function(track) {
            track.stop();
        });
    }

    function init(options) {
        var xhrParams = [];
        if (options.muted)
            xhrParams.push("muted=1");
        if (spectatorId)
            xhrParams.push("spectator="+spectatorId);
        xhr("joinChatVoc.php", xhrParams.join("&"), function(res) {
            if (res && (res >= 0)) {
                peerId = +res;
                if (options.success)
                    options.success();
                return true;
            }
        });
    }

    function pollPeerSignal(memberPeerId, options) {
        if (peers[memberPeerId].isPolling) return;
        peers[memberPeerId].isPolling = true;
        doPollPeerSignal(memberPeerId, 100, options);
    }
    function doPollPeerSignal(memberPeerId, triedSince, options) {
        if (!peers[memberPeerId]) return;
        if (peers[memberPeerId].hasStreamed) {
            peers[memberPeerId].isPolling = false;
            return;
        }
        if (triedSince > 10000) {
            removePeer(memberPeerId, {
                disconnectReceiver: true
            });
            if (options && options.error)
                options.error();
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
                doPollPeerSignal(memberPeerId, triedSince+interval, options);
            }, interval);
            return true;
        });
    }

    function removePeer(socket_id, options) {
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
        xhr("unregisterChatSignals.php", "sender="+peerId+"&receiver="+socket_id + (options && options.disconnectReceiver ? "&disconnect" : "") + (spectatorId ? "&spectator="+spectatorId : ""), function(res) {
            if (res == 1) {
                return true;
            }
        });
    }

    function addPeer(socket_id, options) {
        if (!peerId) return false;
        if (peers[socket_id]) return false;
        if (options.loading)
            options.loading();
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
            pollPeerSignal(socket_id, options);
        })
        
        peers[socket_id].conn.on('stream', stream => {
            let newAudio = document.createElement('audio');
            newAudio.srcObject = stream;
            newAudio.autoplay = true;
            newAudio.className = "audio";
            document.body.appendChild(newAudio);
            peers[socket_id].audio = newAudio;
            peers[socket_id].hasStreamed = true;
            if (options && options.success)
                options.success();
        })
        
        peers[socket_id].conn.on('error', () => {
        });
        
        peers[socket_id].conn.on('close', () => {
            if (!peers[socket_id] && options.error) {
                options.error();
                return;
            }
            removePeer(socket_id, {
                disconnectReceiver: true
            });
        })
        
        if (!am_initiator)
            pollPeerSignal(socket_id, options);
        
        return true;
    }
    function getPeer(socket_id) {
        return peers[socket_id];
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
        getVocChat,
        addPeer,
        getPeer,
        removePeer,
        quitVocChat,
        toggleMute,
        setSpectatorId,
    }
}