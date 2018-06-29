<?php
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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	include_file('desktop', '404', 'php');
	die();
}
if (init('result_code') == 'FAILURE') {
	echo '<div class="alert alert-danger">' . init('result_message') . '</div>';
}
?>
<form class="form-horizontal">
	<fieldset>
		<legend>{{Serveur Google smarthome}}</legend>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Mode}}</label>
			<div class="col-lg-2">
				<select class="form-control configKey" data-l1key="mode">
					<option value="jeedom">{{Service Jeedom Cloud}}</option>
					<option value="internal">{{Service Interne}}</option>
				</select>
			</div>
		</div>
		<div class="form-group gshmode jeedom">
			<label class="col-lg-3 control-label">{{Envoyer configuration au market}}</label>
			<div class="col-lg-2">
				<a class="btn btn-default" id="bt_sendConfigToMarket"><i class="fa fa-paper-plane" aria-hidden="true"></i> {{Envoyer}}</a>
			</div>
		</div>
	</fieldset>
</form>
<div class='row gshmode internal'>
	<div class='col-md-12'>
		<form class="form-horizontal">
			<fieldset>
				<legend>{{Information}}</legend>
				<legend>{{Oauth}}</legend>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Cient ID}}</label>
					<div class="col-lg-3">
						<input class="configKey form-control" data-l1key="gshs::clientId" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Cient Secret}}</label>
					<div class="col-lg-3">
						<input class="configKey form-control" data-l1key="gshs::clientSecret" />
					</div>
				</div>
				<legend>{{Smarthome}}</legend>
				<div class="alert alert-info">
					{{Fulfillment URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGsh.php' ?><br/>
					{{Authorization URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGshOauth.php?type=sh' ?><br/>
					{{Token URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGshOauth.php?type=sh' ?>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{ID du projet Smarthome}}</label>
					<div class="col-lg-4">
						<input class="configKey form-control" data-l1key="googleSmarthomeProjectId" />
					</div>
					<div class="col-lg-2">
						<a class="btn btn-sm btn-success" id="bt_connectGoogleSmarthome"><i class="fa fa-plug" aria-hidden="true"></i> {{Connection}}</a>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Homegraph API Google}}</label>
					<div class="col-lg-3">
						<input class="configKey form-control" data-l1key="gshs::googleapikey" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Homegraph User Agent}}</label>
					<div class="col-lg-3">
						<input class="configKey form-control" data-l1key="gshs::useragent" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Clef d'accès sécurisé}}</label>
					<div class="col-lg-3">
						<input class="configKey form-control" data-l1key="gshs::authkey" />
					</div>
				</div>
				<legend>{{Interaction}}</legend>
				<div class="alert alert-info">
					{{Fulfillment URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGsh.php&secure=' . config::byKey('gshs::authkey', 'gsh') ?><br/>
					{{Authorization URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGshOauth.php?type=df' ?><br/>
					{{Token URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGshOauth.php?type=df' ?>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{ID du projet Dialogflow}}</label>
					<div class="col-lg-4">
						<input class="configKey form-control" data-l1key="googleDialogflowProjectId" />
					</div>
					<div class="col-lg-2">
						<a class="btn btn-sm btn-success" id="bt_connectGoogleDialogFlow"><i class="fa fa-plug" aria-hidden="true"></i> {{Connection}}</a>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Clef authentification header (authkey)}}</label>
					<div class="col-lg-4">
						<input class="configKey form-control" data-l1key="dialogflow::authkey" />
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<script type="text/javascript">
	var return_url = window.location.href
	if(getUrlVars('result_code') == 'FAILURE'){
		return_url = window.location.href.replace('result_code='+getUrlVars('result_code'),'').replace('result_message='+getUrlVars('result_message'),'').replace('id=gsh','').replace('#','');
		$('#div_alert').showAlert({message: getUrlVars('result_message').replace(/\+/g, ' '), level: 'danger'});
	}
	if(getUrlVars('result_code') == 'SUCCESS'){
		return_url = window.location.href.replace('result_code='+getUrlVars('result_code'),'').replace('id=gsh','').replace('#','');
		$('#div_alert').showAlert({message: getUrlVars('result_message').replace(/\+/g, ' '), level: 'success'});
	}
	$('#bt_connectGoogleDialogFlow').off('click').on('click',function(){
		window.location = 'https://assistant.google.com/services/auth/handoffs/auth/start?provider='+$('.configKey[data-l1key=googleDialogflowProjectId]').value()+'_dev&return_url='+encodeURIComponent(return_url+'&id=gsh');
	});
	$('#bt_connectGoogleSmarthome').off('click').on('click',function(){
		window.location = 'https://assistant.google.com/services/auth/handoffs/auth/start?provider='+$('.configKey[data-l1key=googleSmarthomeProjectId]').value()+'_dev&return_url='+encodeURIComponent(return_url+'&id=gsh');
	});
	$('.configKey[data-l1key=mode]').on('change',function(){
		$('.gshmode').hide();
		$('.gshmode.'+$(this).value()).show();
	});

	$('#bt_sendConfigToMarket').on('click', function () {
		$.ajax({
			type: "POST",
			url: "plugins/gsh/core/ajax/gsh.ajax.php",
			data: {
				action: "sendConfig",
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
				$('#div_alert').showAlert({message: '{{Configuration envoyée avec succès}}', level: 'success'});
			}
		});
	});
</script>