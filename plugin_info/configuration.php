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
		<?php
		if (strpos(network::getNetworkAccess('external'), 'https://') == -1) {
			echo '<div class="alert alert-danger">{{Attention votre connexion externe ne semble pas etre en https, ce plugin nécessite ABSOLUMENT une connexion https. Si vous ne savez pas comment faire vous pouvez souscrire à un service pack power pour utiliser le service de DNS Jeedom}}</div>';
		}
		?>
		<legend>{{Serveur Google smarthome}}</legend>
		<div class="form-group">
			<label class="col-sm-3 control-label">{{Mode}}</label>
			<div class="col-sm-2">
				<select class="form-control configKey" data-l1key="mode">
					<option value="jeedom">{{Cloud}}</option>
					<option value="internal">{{Standalone}}</option>
				</select>
			</div>
		</div>
		<div class="form-group gshmode jeedom">
			<?php
			try {
				$info =	gsh::voiceAssistantInfo();
				echo '<label class="col-sm-3 control-label">{{Abonnement aux services assistants vocaux}}</label>';
				echo '<div class="col-sm-9">';
				if (isset($info['limit']) && $info['limit'] != -1 && $info['limit'] != '') {
					echo '<div>{{Votre abonnement aux services assistants vocaux finit le }}' . $info['limit'] . '.';
					echo ' {{Pour le prolonger, allez}} <a href="https://www.jeedom.com/market/index.php?v=d&p=profils#services" target="_blank">{{ici}}</a>';
				} else if ($info['limit'] == -1) {
					echo '<div>{{Votre abonnement aux services assistants vocaux est illimité.}}';
				} else {
					echo '<div class="alert alert-warning">{{Votre abonnement aux services assistants vocaux est finit.}}';
					echo ' {{Pour vous réabonner, allez}} <a href="https://www.jeedom.com/market/index.php?v=d&p=profils#services" target="_blank">{{ici}}</a>';
				}
				echo '</div>';
				echo '</div>';
			} catch (\Exception $e) {
				echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
			}
			?>
		</div>
		<div class="form-group gshmode jeedom">
			<label class="col-sm-3 control-label">{{Envoyer configuration au market}}</label>
			<div class="col-sm-2">
				<a class="btn btn-default" id="bt_sendConfigToMarket"><i class="fa fa-paper-plane" aria-hidden="true"></i> {{Envoyer}}</a>
			</div>
		</div>
		<div class="form-group gshmode jeedom">
			<label class="col-sm-3 control-label">{{Activer l'éxecution local}}</label>
			<div class="col-sm-2">
				<input type="checkbox" class="configKey" data-l1key="gshs::allowLocalApi" />
			</div>
		</div>
		<div class="form-group gshmode jeedom">
			<label class="col-sm-3 control-label">{{Activer la rotation de la clef api}}</label>
			<div class="col-sm-2">
				<input type="checkbox" class="configKey" data-l1key="gshs::enableApikeyRotate" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">{{Interdire toute requête si}}</label>
			<div class="col-sm-2">
				<div class="input-group">
					<input class="configKey form-control" data-concat="1" data-l1key="gshs::disableRequestIf" />
					<span class="input-group-btn">
						<a class="btn btn-default roundedRight bt_gshListCmdInfo"><i class="fas fa-list-alt"></i></a>
					</span>
				</div>
			</div>
		</div>
	</fieldset>
</form>
<div class='row gshmode internal'>
	<div class='col-md-12'>
		<form class="form-horizontal">
			<fieldset>
				<legend>{{Oauth}}</legend>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Cient ID}}</label>
					<div class="col-sm-3">
						<input class="configKey form-control" data-l1key="gshs::clientId" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Cient Secret}}</label>
					<div class="col-sm-3">
						<input class="configKey form-control" data-l1key="gshs::clientSecret" />
					</div>
				</div>
				<legend>{{Smarthome}}</legend>
				<div class="alert alert-info">
					{{Fulfillment URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGsh.php' ?><br />
					{{Authorization URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGshOauth.php?type=sh' ?><br />
					{{Token URL : }}<?php echo network::getNetworkAccess('external') . '/plugins/gsh/core/php/jeeGshOauth.php?type=sh' ?>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{ID du projet Smarthome}}</label>
					<div class="col-sm-4">
						<input class="configKey form-control" data-l1key="googleSmarthomeProjectId" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Homegraph API Google}}</label>
					<div class="col-sm-3">
						<input class="configKey form-control" data-l1key="gshs::googleapikey" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Homegraph User Agent}}</label>
					<div class="col-sm-3">
						<input class="configKey form-control" data-l1key="gshs::useragent" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Mail client (JWT)}}</label>
					<div class="col-sm-3">
						<input class="configKey form-control" data-l1key="gshs::jwtclientmail" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">{{Clef privé (JWT)}}</label>
					<div class="col-sm-5">
						<textarea rows="10" class="configKey form-control" data-l1key="gshs::jwtprivkey"></textarea>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<script type="text/javascript">
	$('.bt_gshListCmdInfo').off('click').on('click', function() {
		var el = $(this).closest('.form-group').find('input.configKey:first');
		jeedom.cmd.getSelectModal({
			cmd: {
				type: 'info'
			}
		}, function(result) {
			if (el.attr('data-concat') == 1) {
				el.atCaret('insert', result.human);
			} else {
				el.value(result.human);
			}
		});
	});

	$('.configKey[data-l1key=mode]').off('change').on('change', function() {
		$('.gshmode').hide();
		$('.gshmode.' + $(this).value()).show();
	});

	$('#bt_sendConfigToMarket').on('click', function() {
		$.ajax({
			type: "POST",
			url: "plugins/gsh/core/ajax/gsh.ajax.php",
			data: {
				action: "sendConfig",
			},
			dataType: 'json',
			error: function(request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function(data) {
				if (data.state != 'ok') {
					$('#div_alert').showAlert({
						message: data.result,
						level: 'danger'
					});
					return;
				}
				$('#div_alert').showAlert({
					message: '{{Configuration envoyée avec succès}}',
					level: 'success'
				});
			}
		});
	});
</script>