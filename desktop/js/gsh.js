
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
 	var devices = $('#div_configuration .device').getValues('.deviceAttr');
 	$.ajax({
 		type: "POST", 
 		url: "plugins/gsh/core/ajax/gsh.ajax.php", 
 		data: {
 			action: "saveDevices",
 			devices : json_encode(devices),
 		},
 		dataType: 'json',
 		error: function (request, status, error) {
 			handleAjaxError(request, status, error);
 		},
 		success: function (data) { 
 			if (data.state != 'ok') {
 				$('#div_alert').showAlert({message: data.result, level: 'danger'});
 				return;
 			}
 			sendDevices()
 		},
 	});
 });

 function sendDevices(){
 	$.ajax({
 		type: "POST", 
 		url: "plugins/gsh/core/ajax/gsh.ajax.php", 
 		data: {
 			action: "sendDevices",
 		},
 		dataType: 'json',
 		error: function (request, status, error) {
 			handleAjaxError(request, status, error);
 		},
 		success: function (data) { 
 			if (data.state != 'ok') {
 				$('#div_alert').showAlert({message: data.result, level: 'danger'});
 				return;
 			}
 			$('#div_alert').showAlert({message: '{{Synchronisation réussie, n\'oubliez pas de dire à Google : Synchroniser tous mes appareils}}', level: 'success'});
 		},
 	});
 }

 function loadData(){
 	$.ajax({
 		type: "POST", 
 		url: "plugins/gsh/core/ajax/gsh.ajax.php", 
 		data: {
 			action: "allDevices"
 		},
 		dataType: 'json',
 		error: function (request, status, error) {
 			handleAjaxError(request, status, error);
 		},
 		success: function (data) { 
 			if (data.state != 'ok') {
 				$('#div_alert').showAlert({message: data.result, level: 'danger'});
 				return;
 			}
 			for(var i in data.result){
 				var el = $('.device[data-link_id='+data.result[i]['link_id']+'][data-link_type=eqLogic]');
 				if(!el){
 					continue;
 				}
 				el.setValues(data.result[i], '.deviceAttr');
 			}
 		},
 	});
 }

 loadData();