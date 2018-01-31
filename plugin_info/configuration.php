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
		<div class="form-group">
			<label class="col-lg-3 control-label">{{J'utilise mon propre serveur}}</label>
			<div class="col-lg-2">
				<input type="checkbox" class="configKey" data-l1key="useMyOwnServer" value="1" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{DNS ou IP du serveur}}</label>
			<div class="col-lg-2">
				<input class="configKey form-control" data-l1key="gshs::ip" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Clef maitre}}</label>
			<div class="col-lg-3">
				<input class="configKey form-control" data-l1key="gshs::masterkey" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Clef API Google}}</label>
			<div class="col-lg-3">
				<input class="configKey form-control" data-l1key="gshs::googleapikey" />
			</div>
		</div>
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
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Port}}</label>
			<div class="col-lg-3">
				<input class="configKey form-control" data-l1key="gshs::port" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Timeout}}</label>
			<div class="col-lg-3">
				<input class="configKey form-control" data-l1key="gshs::timeout" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{URL}}</label>
			<div class="col-lg-3">
				<input class="configKey form-control" data-l1key="gshs::url" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-3 control-label">{{Configuration}}</label>
			<div class="col-lg-3">
				<a class="btn btn-success" id="bt_viewConf">{{Voir}}</a>
			</div>
		</div>
	</fieldset>
</form>

<script type="text/javascript">
	$('#bt_viewConf').on('click',function(){
		$('#md_modal2').dialog({title: "{{Configuration général}}"});
		$('#md_modal2').load('index.php?v=d&plugin=gsh&modal=showConf').dialog('open');
	});
</script>