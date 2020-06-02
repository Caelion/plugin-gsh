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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	throw new Exception(__('Eqlogic ID non valide : ', __FILE__) . init('eqLogic_id'));
}
$device = gsh_devices::byLinkTypeLinkId('eqLogic', $eqLogic->getId());
if (!is_object($device)) {
	throw new Exception(__('Device non trouvé', __FILE__));
}
if ($device->getType() == '') {
	throw new Exception(__('Aucun type configuré pour ce périphérique', __FILE__));
}
$supportedType = gsh::getSupportedType();
sendVarToJs('device', utils::o2a($device));
?>
<div id="div_alertAdvanceConfigure"></div>
<div id="div_advanceConfigForm">
	<a class="btn btn-success pull-right bt_advanceConfigSaveDevice"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
	<input type="text" class="deviceAttr form-control" data-l1key="id" style="display : none;" />
	<form class="form-horizontal">
		<fieldset>
			<div class="form-group">
				<label class="col-sm-3 control-label">{{Groupe objet (option nécéssitant un compte market spécifique)}}</label>
				<div class="col-sm-3">
					<input type="number" class="deviceAttr" data-l1key="options" data-l2key="group"></input>
				</div>
			</div>
			<div class="form-group has-error">
				<label class="col-sm-3 control-label">{{Pousser l'état}}</label>
				<div class="col-sm-1">
					<input type="checkbox" class="deviceAttr" data-l1key="options" data-l2key="reportState::enable" />
				</div>
				<div class="alert alert-danger col-sm-8">
					{{Attention cela ne change absolument pas le fonctionnement du plugin}} (<?php echo config::byKey('product_name'); ?> {{pousse l'information de l'état des modules au lieu d'attendre la demande de Google) par contre ca peut ralentir votre}} <?php echo config::byKey('product_name'); ?>
				</div>
			</div>
		</fieldset>
	</form>
	
	<form class="form-horizontal">
		<fieldset>
			<?php
			foreach ($supportedType[$device->getType()]['traits'] as $traits) {
				$class = 'gsh_'.$traits;
				if (!class_exists($class) || !method_exists($class,'getHtmlConfiguration')) {
					continue;
				}
				echo '<legend>{{Configuration}} '.$traits.'</legend>';
				$class::getHtmlConfiguration($eqLogic);
			}
			?>
		</fieldset>
	</form>
</div>

<script>
initTooltips($("#div_advanceConfigForm"))
$('#div_advanceConfigForm').setValues(device, '.deviceAttr');
$('.bt_advanceConfigSaveDevice').on('click',function(){
	var device = $('#div_advanceConfigForm').getValues('.deviceAttr')[0];
	$.ajax({
		type: "POST",
		url: "plugins/gsh/core/ajax/gsh.ajax.php",
		data: {
			action: "saveDevice",
			device : json_encode(device),
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alertAdvanceConfigure').showAlert({message: data.result, level: 'danger'});
				return;
			}
			$('#div_alertAdvanceConfigure').showAlert({message: '{{Sauvegarde réussi, pensez à relancer une synchronisation}}', level: 'success'});
		},
	});
});

</script>
