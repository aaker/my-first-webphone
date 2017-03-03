
var URL = window.URL || window.webkitURL;

var myUA = null;
var mySession = null;



document.addEventListener('DOMContentLoaded', function() {
  myUA = createUA(device,displayName,server,password);
  var remoteRender = document.getElementById("remoteVideo");
  var localRender = document.getElementById("localVideo");

  myUA.on('invite', function (incomingSession) {
       onCall = true;
       mySession = incomingSession;
       var options = mediaOptions(remoteRender, localRender);
       remoteRender.style.visibility = 'visible';
       mySession.accept(options);
       mySession.on('bye', function () {
           onCall = false;
           remoteRender.style.visibility = 'hidden';
           session = null;
       });
   });
}, false);








// Function: mediaOptions
//   A shortcut function to construct the media options for an SIP session.
//
// Arguments:
//   audio: whether or not to send audio in a SIP WebRTC session
//   audio: whether or not to send audio in a SIP WebRTC session
//   remoteRender: the video tag to render the callee's remote video in. Can be null
//   localRender: the video tag to render the caller's local video in. Can be null
function mediaOptions(remoteRender, localRender) {
    var audio = document.getElementById('isAudio').checked;
    var video = document.getElementById('isVideo').checked;

    return {
        media: {
            constraints: {
                audio: audio,
                video: video
            },
            render: {
                remote: remoteRender,
                local: localRender
            }
        }
    };
}

// Function: createUA
//   creates a user agent with the given arguments plugged into the UA
//   configuration. This is a standard user agent for WebRTC calls.
//   For a user agent for data transfer, see createDataUA
//
// Arguments:
//   callerURI: the URI of the caller, aka, the URI that belongs to this user.
//   displayName: what name we should display the user as
function createUA(callerURI,displayName,server,password) {
    var configuration = {
        traceSip: true,
        uri: callerURI,
        displayName: displayName,
        wsServers: ['wss://'+server],
        password: password,
        register:true,
        userAgentString: "My first WebRTC App",

    };
    var userAgent = new SIP.UA(configuration);
    return userAgent;
}

// Function: makeCall
//   Makes a call from a user agent to a target URI
//
// Arguments:
//   userAgent: the user agent to make the call from
//   target: the URI to call
//   audio: whether or not to send audio in a SIP WebRTC session
//   audio: whether or not to send audio in a SIP WebRTC session
//   remoteRender: the video tag to render the callee's remote video in. Can be null
//   localRender: the video tag to render the caller's local video in. Can be null
function makeCall(userAgent, target) {

    var remoteRender = document.getElementById("remoteVideo");
    var localRender = document.getElementById("localVideo");
    var options = mediaOptions(remoteRender, localRender);

    // makes the call
    mySession = userAgent.invite('sip:' + target, options);
    return mySession;
}

function endCall()
{
  if (mySession)
  {
    mySession.bye();
  }
}
