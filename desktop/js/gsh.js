
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

 $('#bt_saveConfiguration').on('click',function(){
 	var syncEqLogic = {}
 	var syncData =  $('#div_configuration .syncEqLogic').getValues('.syncAttr');
 	for(var i in syncData){
 		syncEqLogic[syncData[i].id] = syncData[i];
 	}
 	jeedom.config.save({
 		configuration: {syncEqLogic : syncEqLogic},
 		plugin : 'gsh',
 		error: function (error) {
 			$('#div_alert').showAlert({message: error.message, level: 'danger'});
 		},
 		success: function () {
 			sendDevices();
 		}
 	});
 });


 function sendDevices(){
 	$.ajax({
 		type: "POST", 
 		url: "plugins/gsh/core/ajax/gsh.ajax.php", 
 		data: {
 			action: "sendDevices",
 		},
 		global:false,
 		dataType: 'json',
 		error: function (request, status, error) {
 			handleAjaxError(request, status, error);
 		},
 		success: function (data) { 
 			if (data.state != 'ok') {
 				$('#div_alert').showAlert({message: data.result, level: 'danger'});
 				return;
 			}
 			$('#div_alert').showAlert({message: '{{Synchronisation réussie}}', level: 'success'});
 		},
 	});
 }

 function loadData(){
 	jeedom.config.load({
 		configuration:'syncEqLogic',
 		plugin : 'gsh',
 		error: function (error) {
 			$('#div_alert').showAlert({message: error.message, level: 'danger'});
 		},
 		success: function (data) {
 			for(var i in data){
 				var el = $('.syncEqLogic[data-id='+i+']');
 				if(!el){
 					continue;
 				}
 				el.setValues(data[i], '.syncAttr');
 			}
 		}
 	});
 }

 loadData();