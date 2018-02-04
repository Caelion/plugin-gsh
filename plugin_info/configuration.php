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
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
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
		<div class="form-group gshmode internal">
			<label class="col-lg-3 control-label">{{DNS ou IP du serveur}}</label>
			<div class="col-lg-2">
				<input class="configKey form-control" data-l1key="gshs::ip" />
			</div>
		</div>
	</fieldset>
</form>
<div class='row gshmode internal'>
	<div class='col-md-6'>
		<form class="form-horizontal">
			<fieldset>
				<legend>{{Configuration général}}</legend>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Clef maitre}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::masterkey" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Clef API Google}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::googleapikey" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Cient ID}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::clientId" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Cient Secret}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::clientSecret" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Port}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::port" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Timeout}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::timeout" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{URL}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::url" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Configuration}}</label>
					<div class="col-lg-6">
						<a class="btn btn-success" id="bt_viewConf"><i class="fa fa-eye" aria-hidden="true"></i> {{Voir}}</a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<div class='col-md-6'>
		<form class="form-horizontal">
			<fieldset>
				<legend>{{Utilisateur}}</legend>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{ID utilisateur}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::userid" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Nom d'utilisateur}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::username" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Mot de passe}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::password" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Token}}</label>
					<div class="col-lg-6">
						<input class="configKey form-control" data-l1key="gshs::token" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-6 control-label">{{Configuration}}</label>
					<div class="col-lg-6">
						<a class="btn btn-success" id="bt_viewUserConf"><i class="fa fa-eye" aria-hidden="true"></i> {{Voir}}</a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<script type="text/javascript">
	$('.configKey[data-l1key=mode]').on('change',function(){
		$('.gshmode').hide();
		$('.gshmode.'+$(this).value()).show();
	});
	$('#bt_viewConf').on('click',function(){
		$('#md_modal2').dialog({title: "{{Configuration général}}"});
		$('#md_modal2').load('index.php?v=d&plugin=gsh&modal=showConf').dialog('open');
	});

	$('#bt_viewUserConf').on('click',function(){
		$('#md_modal2').dialog({title: "{{Configuration utilisateur}}"});
		$('#md_modal2').load('index.php?v=d&plugin=gsh&modal=showUserConf').dialog('open');
	});
</script>