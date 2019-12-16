var App = smarthome.App;
var DataFlow = smarthome.DataFlow;
var Intents = smarthome.Intents;
var Constants = smarthome.Constants;
var Execute = smarthome.Execute;
var IntentFlow = smarthome.IntentFlow;

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

var executeHandler = function (request) {
  console.log('executeHandler : '+(new Date().toLocaleString())+' request : ',request);
  console.log('response : {}')
  const command = request.inputs[0].payload.commands[0];
  const execution = command.execution[0];
  const customData = command.devices[0].customData
  
  const response = new Execute.Response.Builder().setRequestId(request.requestId);
  
  for(var i in command.devices){
    var device = command.devices[i]
    const deviceId = device.id;
    const deviceCommand = new DataFlow.HttpRequestData();
    deviceCommand.requestId = request.requestId;
    deviceCommand.method = Constants.HttpOperation.POST;
    deviceCommand.port = 80;
    deviceCommand.deviceId = deviceId;
    deviceCommand.path = '/plugins/gsh/core/php/jeeGSH.php';
    deviceCommand.dataType = 'application/json';
    deviceCommand.data = JSON.stringify(device);
    console.log(localHomeApp.getDeviceManager());
    return localHomeApp.getDeviceManager().send(deviceCommand)
    .then((result) => {
      console.log(result)
    })
    .catch((err) => {
      console.log(err)
      err.errorCode = err.errorCode || IntentFlow.ErrorCode.INVALID_REQUEST;
      response.setErrorState(device.id, err.errorCode);
      return response.build()
    });
  }
  
  
};


localHomeApp.onExecute(executeHandler)
.onIdentify(identifyHandler)
.onReachableDevices(reachablesDeviceHandler)
.onExecute(executeHandler)
.listen()
.then(function () {
  console.log('Ready : '+(new Date().toLocaleString()))
});
