<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<br/>
<a class="btn btn-success pull-right" id="bt_saveConfiguration"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
<a class="btn btn-default pull-right" id="bt_displayDevice"><i class="fa fa-eye"></i> {{Voir la configuration}}</a>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
	<li role="presentation"><a href="#scenariotab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Scénario}}</a></li>
</ul>

<div class="tab-content" id="div_configuration" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
	<div role="tabpanel" class="tab-pane active" id="eqlogictab">
		<br/>
		<table class="table table-bordered tablesorter">
			<thead>
				<tr>
					<th>{{Equipement}}</th>
					<th>{{Transmettre}}</th>
					<th>{{Type}}</th>
				</tr>
			</thead>
			<tbody>
				<?php
foreach (eqLogic::all() as $eqLogic) {
	echo '<tr class="device" data-link_id="' . $eqLogic->getId() . '" data-link_type="eqLogic">';
	echo '<td>' . $eqLogic->getHumanName() . '</td>';
	echo '<td>';
	echo '<input style="display:none;" class="deviceAttr" data-l1key="id" />';
	echo '<input style="display:none;" class="deviceAttr" data-l1key="link_type" value="eqLogic" />';
	echo '<input style="display:none;" class="deviceAttr" data-l1key="link_id" value="' . $eqLogic->getId() . '" />';
	echo '<input type="checkbox" class="deviceAttr" data-l1key="enable" />';
	echo '</td>';
	echo '<td>';
	echo '<select class="deviceAttr form-control" data-l1key="type">';
	echo '<option value="">{{Aucun}}</option>';
	foreach (gsh::$_supportedType as $key => $value) {
		echo '<option value="' . $key . '">{{' . $value['name'] . '}}</option>';
	}
	echo '<select>';
	echo '</td>';
	echo '</tr>';
}
?>
			</tbody>
		</table>
	</div>


	<div role="tabpanel" class="tab-pane" id="scenariotab">
		<a class="btn btn-success pull-right btn-xs" id="bt_addScene" style="margin-top:5px;"><i class="fa fa-plus"></i> {{Ajouter scène}}</a>
		<br/><br/>
		<div id="div_scenes"></div>
	</div>
</div>


<?php include_file('desktop', 'gsh', 'js', 'gsh');?>
