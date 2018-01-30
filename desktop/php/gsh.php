<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<br/>
<a class="btn btn-success pull-right" id="bt_saveConfiguration"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
<ul class="nav nav-tabs" role="tablist">
 <li role="presentation" class="active"><a href="#generaltab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Générale}}</a></li>
 <li role="presentation"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
 <li role="presentation"><a href="#scenariotab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Scénario}}</a></li>
</ul>

<div class="tab-content" id="div_configuration" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
  <div role="tabpanel" class="tab-pane active" id="generaltab">
    <br/>
    <form class="form-horizontal">
      <fieldset>
        <div class="form-group">
          <label class="col-sm-2 control-label">{{Clef API}}</label>
          <div class="col-sm-3">
            <input disabled type="text" class="eqLogicAttr form-control" value="<?php echo jeedom::getApiKey('gsh') ?>" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>

  <div role="tabpanel" class="tab-pane" id="eqlogictab">
    <br/>
    <table class="table table-bordered">
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
	echo '<tr class="syncEqLogic" data-id="' . $eqLogic->getId() . '">';
	echo '<td>' . $eqLogic->getHumanName() . '</td>';
	echo '<td>';
	echo '<input style="display:none;" class="syncAttr" data-l1key="id" value="' . $eqLogic->getId() . '" />';
	echo '<input type="checkbox" class="syncAttr" data-l1key="enable" />';
	echo '</td>';
	echo '<td>';
	echo '<select class="syncAttr form-control" data-l1key="type">';
	echo '<option value="">{{Aucun}}</option>';
	echo '<option value="action.devices.types.CAMERA">{{Camera}}</option>';
	echo '<option value="action.devices.types.DISHWASHER">{{Dishwasher}}</option>';
	echo '<option value="action.devices.types.DRYER">{{Dryers}}</option>';
	echo '<option value="action.devices.types.LIGHT">{{Light}}</option>';
	echo '<option value="action.devices.types.OUTLET">{{Outlet}}</option>';
	echo '<option value="action.devices.types.REFRIGERATOR">{{Refrigerator}}</option>';
	echo '<option value="action.devices.types.THERMOSTAT">{{Thermostat}}</option>';
	echo '<option value="action.devices.types.VACUUM">{{Vacuum}}</option>';
	echo '<option value="action.devices.types.WASHER">{{Washer}}</option>';
	echo '<option value="action.devices.types.SWITCH">{{Switch}}</option>';
	echo '<select>';
	echo '</td>';
	echo '</tr>';
}
?>
      </tbody>
    </table>
  </div>


  <div role="tabpanel" class="tab-pane" id="scenariotab">
    <br/>


  </div>
</div>


<?php include_file('desktop', 'gsh', 'js', 'gsh');?>
