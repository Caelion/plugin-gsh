var App = smarthome.App;
var localHomeApp = new App("1.0.1");

var identifyHandler = function (request) {
  console.log('identifyHandler : '+(new Date().toLocaleString())+' request : ',request);
  var response = {
    intent: 'action.devices.IDENTIFY',
    requestId: request.requestId,
    payload: {
      device: {
        id: "fake-jeedom-local",
        isProxy: true,
        isLocalOnly: true
      },
    }
  };
  console.log('response :',response);
  return response;
};

var reachablesDeviceHandler = function (request) {
  console.log('reachablesDeviceHandler : '+(new Date().toLocaleString())+' request : ',request);
  var reachables_devices = [];
  for(var i in request.devices){
    if(request.devices[i].id == 'fake-jeedom-local'){
      continue;
    }
    reachables_devices.push({
      verificationId: request.devices[i].id
    });
  }
  var response = {
    intent: 'action.devices.REACHABLE_DEVICES',
    requestId: request.requestId,
    payload: {
      devices: reachables_devices
    }
  };
  console.log('response :',response)
  return response;
};

var proxyHandler = function (request) {
  console.log('proxyHandler : '+(new Date().toLocaleString())+' request : ',request);
  console.log('response : {}')
  return {};
};

var executeHandler = function (request) {
  console.log('executeHandler : '+(new Date().toLocaleString())+' request : ',request);
  console.log('response : {}')
};


localHomeApp.onExecute(executeHandler)
.onIdentify(identifyHandler)
.onReachableDevices(reachablesDeviceHandler)
.onProxySelected(proxyHandler)
.onExecute(executeHandler)
.listen()
.then(function () {
  console.log('Ready : '+(new Date().toLocaleString()))
});
