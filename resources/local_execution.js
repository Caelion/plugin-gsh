var App = smarthome.App;
var localHomeApp = new App("1.0.1");
var connexionInfo = null;

var identifyHandler = function (request) {
  console.log('identifyHandler : '+(new Date().toLocaleString())+' request : ',request);
  for(var i in request.devices){
    if(request.devices[i].id == 'fake-jeedom-local'){
      connexionInfo = request.devices[i].customData
      console.log('Found fake-jeedom-local : ',connexionInfo);
    }
  }
  var response = {
    intent: 'IDENTIFY',
    requestId: request.requestId,
    payload: {
      device: {
        id: "fake-jeedom-local",
        isProxy: true,
        isLocalOnly: true,
        verificationId : 'fake-jeedom-local'
      },
    }
  };
  console.log('response :',response);
  return response;
};

var devicesHandler = function (request) {
  console.log('devicesHandler : '+(new Date().toLocaleString())+' request : ',request);
  var proxyDevice = request.inputs[0].payload.device.proxyDevice;
  var reachables = [];
  for(var i in request.devices){
    reachables.push({verificationId: request.devices[i].id})
  }
  var response = {
    intent: 'REACHABLE_DEVICES',
    requestId: request.requestId,
    payload: {
      devices: reachables
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
.onReachableDevices(devicesHandler)
.onProxySelected(proxyHandler)
.listen()
.then(function () {
  console.log('Ready : '+(new Date().toLocaleString()))
});
