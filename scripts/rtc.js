/**
 * Socket.io socket
 */
 let socket;
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
 
 /////////////////////////////////////////////////////////
 
 // enabling the camera at startup
 navigator.mediaDevices.getUserMedia(constraints).then(stream => {
     console.log('Received local stream');
 
     localStream = stream;
 
     init()
 
 }).catch(e => alert(`getusermedia error ${e.name}`))
 
 /**
  * initialize the socket connections
  */
 function init() {
   o_xhr("joinChatVoc.php", "", function(res) {
    if (res && (res >= 0)) {
     peerId = +res;
     pollVocChat();
     return true;
    }
   });
 
     /*socket.on('initReceive', socket_id => {
         console.log('INIT RECEIVE ' + socket_id)
         addPeer(socket_id, false)
 
         socket.emit('initSend', socket_id)
     })
 
     socket.on('initSend', socket_id => {
         console.log('INIT SEND ' + socket_id)
         addPeer(socket_id, true)
     })
 
     socket.on('removePeer', socket_id => {
         console.log('removing peer ' + socket_id)
         removePeer(socket_id)
     })
 
     socket.on('disconnect', () => {
         console.log('GOT DISCONNECTED')
         for (let socket_id in peers) {
             removePeer(socket_id)
         }
     })
 
     socket.on('signal', data => {
         peers[data.socket_id].signal(data.signal)
     })*/
 }

 var lastSignalId = 0;
 function pollVocChat() {
  o_xhr("chat.php", "", function(res) {
    setTimeout(pollVocChat, 1000);
    var data = JSON.parse(res);
    var members = data[0];
    for (var i=0;i<members.length;i++) {
        var member = members[i];
        if (member.peer) {
            if (!peers[member.peer])
                addPeer(member.peer, (member.peer < peerId));
        }
    }
    return true;
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
    var interval = 100;
    o_xhr("getChatSignals.php", "sender="+memberPeerId+"&receiver="+peerId+"&lastsignalid="+peers[memberPeerId].lastSignalId, function(res) {
        var signals;
        try {
            signals = JSON.parse(res);
        }
        catch (e) {
            return false;
        }
        for (var i=0;i<signals.length;i++) {
            var signal = signals[i];
            peers[memberPeerId].lastSignalId = signal.id;
            peers[memberPeerId].conn.signal(signal.data);
        }
        setTimeout(function() {
            doPollPeerSignal(memberPeerId);
        }, interval);
        return true;
     });
 }
 
 /**
  * Remove a peer with given socket_id. 
  * Removes the audio element and deletes the connection
  * @param {String} socket_id 
  */
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
 
 /**
  * Creates a new peer connection and sets the event listeners
  * @param {String} socket_id 
  *                 ID of the peer
  * @param {Boolean} am_initiator 
  *                  Set to true if the peer initiates the connection process.
  *                  Set to false if the peer receives the connection. 
  */
 function addPeer(socket_id, am_initiator) {
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
        /*socket.emit('signal', {
            signal: data,
            socket_id: socket_id
        })*/
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
 }
 var sendingSignalHandlers = {};
 function sendSignalDebounced(socket_id, data, delay=300) {
    if (!sendingSignalHandlers[socket_id]) {
        sendingSignalHandlers[socket_id] = {
            data: []
        };
    }
    sendingSignalHandlers[socket_id].data.push(data);
    clearTimeout(sendingSignalHandlers[socket_id].timer);
    sendingSignalHandlers[socket_id].timer = setTimeout(function() {
        o_xhr("registerChatSignals.php", "sender="+peerId+"&receiver="+socket_id+"&signals="+encodeURIComponent(JSON.stringify(sendingSignalHandlers[socket_id].data)), function(res) {
            return (res == 1);
        });
        delete sendingSignalHandlers[socket_id];
    }, delay);
 }
 
 /**
  * Enable/disable microphone
  */
 function toggleMute() {
     for (let index in localStream.getAudioTracks()) {
         localStream.getAudioTracks()[index].enabled = !localStream.getAudioTracks()[index].enabled
         muteButton.innerText = localStream.getAudioTracks()[index].enabled ? "Unmuted" : "Muted"
     }
 }
 
 /**
  * updating text of buttons
  */
 function updateButtons() {
     for (let index in localStream.getAudioTracks()) {
         muteButton.innerText = localStream.getAudioTracks()[index].enabled ? "Unmuted" : "Muted"
     }
 }
 