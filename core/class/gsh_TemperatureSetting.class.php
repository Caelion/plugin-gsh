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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class gsh_TemperatureSetting {
  
  /*     * *************************Attributs****************************** */
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array());
    $modes = 'auto,';
    if ($_device->getOptions('TemperatureSetting::heat') != '') {
      $modes .= 'heat,';
    }
    if ($_device->getOptions('TemperatureSetting::cool') != '') {
      $modes .= 'cool,';
    }
    if ($_device->getOptions('TemperatureSetting::off') != '') {
      $modes .= 'off,';
    }
    if ($_device->getOptions('TemperatureSetting::eco') != '') {
      $modes .= 'eco,';
    }
    if ($_device->getOptions('TemperatureSetting::auto') != '') {
      #$modes .= 'auto,';
    }
    if ($_device->getOptions('TemperatureSetting::heatcool') != '') {
      $modes .= 'heatcool,';
    }
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_SET_SETPOINT'))) {
        if (!in_array('action.devices.traits.TemperatureSetting', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.TemperatureSetting';
        }
        if (!isset($return['attributes'])) {
          $return['attributes'] = array();
        }
        $return['attributes']['availableThermostatModes'] = 'heat';
        $return['attributes']['thermostatTemperatureUnit'] = 'C';
        $return['customData']['TemperatureSetting_cmdSetThermostat'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_STATE_NAME'))) {
        $return['customData']['TemperatureSetting_cmdGetState'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_MODE'))) {
        $return['customData']['TemperatureSetting_cmdGetMode'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_SET_MODE'))) {
        if ($cmd->getLogicalId() == 'off' && strpos($modes,'off') === false) {
          $modes .= 'off,';
        }
      }
      if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_SETPOINT'))) {
        $return['customData']['TemperatureSetting_cmdGetSetpoint'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_TEMPERATURE', 'TEMPERATURE'))) {
        $return['customData']['TemperatureSetting_cmdGetTemperature'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), array('HUMIDITY'))) {
        $return['customData']['TemperatureSetting_cmdGetHumidity'] = $cmd->getId();
      }
    }
    if (isset($return['customData']['TemperatureSetting_cmdGetTemperature']) && count($return['traits']) == 0) {
      $return['traits'][] = 'action.devices.traits.TemperatureSetting';
      if (!isset($return['attributes'])) {
        $return['attributes'] = array();
      }
      $return['attributes']['thermostatTemperatureUnit'] = 'C';
      $modes = 'heat';
    }
    if (isset($return['attributes']['availableThermostatModes'])) {
      $return['attributes']['availableThermostatModes'] = trim($modes, ',');
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Thermostat',__FILE__) => array('THERMOSTAT_SET_SETPOINT'),
      __('Etat themostat ',__FILE__) => array('THERMOSTAT_TEMPERATURE','TEMPERATURE'),
      __('Mode',__FILE__) => array('THERMOSTAT_SET_MODE','THERMOSTAT_MODE'),
      __('Etat',__FILE__) => array('THERMOSTAT_STATE_NAME'),
      __('Humidité',__FILE__) => array('HUMIDITY')
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    $eqLogic = $_device->getLink();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.ThermostatTemperatureSetpoint':
          if (isset($_infos['customData']['TemperatureSetting_cmdSetThermostat'])) {
            $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdSetThermostat']);
          }
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider' => $execution['params']['thermostatTemperatureSetpoint']));
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.ThermostatTemperatureSetRange':
          if (isset($_infos['customData']['TemperatureSetting_cmdSetThermostat'])) {
            $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdSetThermostat']);
          }
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider' => ($execution['params']['thermostatTemperatureSetpointLow'] + $execution['params']['thermostatTemperatureSetpointHigh'])/2));
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.SetTemperature':
          if (isset($_infos['customData']['TemperatureSetting_cmdSetThermostat'])) {
            $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdSetThermostat']);
          }
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider' => $execution['params']['temperature']));
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.ThermostatSetMode':
          $cmds = cmd::byGenericType('THERMOSTAT_SET_MODE', $_device->getLink_id(), true);
          if ($cmds == null) {
            break;
          }
          if (is_array($cmds)) {
            $cmds = array($cmds);
          }
          if ($execution['params']['thermostatMode'] == 'off') {
            $cmd = cmd::byId($_device->getOptions('TemperatureSetting::off'));
            if(!is_object($cmd)){
              $cmd = $eqLogic->getCmd('action', 'off');
            }
          } elseif ($execution['params']['thermostatMode'] == 'heat') {
            $cmd = cmd::byId($_device->getOptions('TemperatureSetting::heat'));
          } elseif ($execution['params']['thermostatMode'] == 'cool') {
            $cmd = cmd::byId($_device->getOptions('TemperatureSetting::cool'));
          } elseif ($execution['params']['thermostatMode'] == 'eco') {
            $cmd = cmd::byId($_device->getOptions('TemperatureSetting::eco'));
          } elseif ($execution['params']['thermostatMode'] == 'auto') {
            $cmd = cmd::byId($_device->getOptions('TemperatureSetting::auto'));
          } elseif ($execution['params']['thermostatMode'] == 'heatcool') {
            $cmd = cmd::byId($_device->getOptions('TemperatureSetting::heatcool'));
          }
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
          break;
        }
      } catch (Exception $e) {
        log::add('gsh', 'error', $e->getMessage());
        $return = array('status' => 'ERROR');
      }
    }
    return $return;
  }
  
  public static function query($_device, $_infos){
    $return = array();
    $return['online'] = true;
    $eqLogic = $_device->getLink();
    if (isset($_infos['customData']['TemperatureSetting_cmdGetMode'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdGetMode']);
      if (is_object($cmd)) {
        $mode = $cmd->execCmd();
        $cmd_off = $eqLogic->getCmd('action', 'off');
        if(!is_object($cmd_off)){
          $cmd_off = cmd::byId($_device->getOptions('TemperatureSetting::off'));
        }
        if (is_object($cmd_off) && $mode == $cmd_off->getName()) {
          $return['thermostatMode'] = 'off';
        }
        $cmds_action = $eqLogic->getCmd(null, 'modeAction', null, true);
        if(count($cmds_action) == 0){
          $cmds_action = $eqLogic->getCmd('action');
        }
        foreach ($cmds_action as $cmd_found) {
          if ($mode == $cmd_found->getName()) {
            switch ($cmd_found->getId()) {
              case $_device->getOptions('TemperatureSetting::heat'):
              $return['thermostatMode'] = 'heat';
              break;
              case $_device->getOptions('TemperatureSetting::cool'):
              $return['thermostatMode'] = 'cool';
              break;
              case $_device->getOptions('TemperatureSetting::eco'):
              $return['thermostatMode'] = 'eco';
              break;
              case $_device->getOptions('TemperatureSetting::auto'):
              $return['thermostatMode'] = 'auto';
              break;
              case $_device->getOptions('TemperatureSetting::heatcool'):
              $return['thermostatMode'] = 'heatcool';
              break;
              case $_device->getOptions('TemperatureSetting::off'):
              $return['thermostatMode'] = 'off';
              break;
            }
          }
        }
      }
    }
    
    if (!isset($return['thermostatMode']) && isset($_infos['customData']['TemperatureSetting_cmdGetState'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdGetState']);
      if (is_object($cmd)) {
        $state = $cmd->execCmd();
        switch ($state) {
          case __('Chauffage', __FILE__);
          $return['thermostatMode'] = 'heat';
          break;
          case __('Climatisation', __FILE__);
          $return['thermostatMode'] = 'cool';
          break;
          case __('Suspendu', __FILE__):
          $return['thermostatMode'] = 'auto';
          break;
          case __('Arrêté', __FILE__):
          #$return['thermostatMode'] = 'auto';
          break;
          case __('Off', __FILE__):
          $return['thermostatMode'] = 'off';
          break;
        }
      }
    }
    if (isset($_infos['customData']['TemperatureSetting_cmdGetSetpoint'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdGetSetpoint']);
      if (is_object($cmd)) {
        $value = $cmd->execCmd();
        if (isset($return['thermostatMode']) && $return['thermostatMode'] == 'heatcool') {
          $return['thermostatTemperatureSetpointHigh'] = $value + 2;
          $return['thermostatTemperatureSetpointLow'] = $value - 2;
        } else {
          $return['thermostatTemperatureSetpoint'] = $value;
        }
      }
    }
    if (isset($_infos['customData']['TemperatureSetting_cmdGetTemperature'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdGetTemperature']);
      if (is_object($cmd)) {
        $return['thermostatTemperatureAmbient'] = $cmd->execCmd();
      }
    }
    if (isset($_infos['customData']['TemperatureSetting_cmdGetHumidity'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureSetting_cmdGetHumidity']);
      if (is_object($cmd)) {
        $return['thermostatHumidityAmbient'] = $cmd->execCmd();
      }
    }
    if (isset($return['thermostatHumidityAmbient']) && $return['thermostatHumidityAmbient'] == '') {
      $return['thermostatHumidityAmbient'] = 0;
    }
    if (isset($return['thermostatTemperatureAmbient']) && $return['thermostatTemperatureAmbient'] == '') {
      $return['thermostatTemperatureAmbient'] = 0;
    }
    if ((!isset($return['thermostatTemperatureSetpoint'])) && (!isset($return['thermostatTemperatureSetpointHigh']))) {
      $return['thermostatTemperatureSetpoint'] = $return['thermostatTemperatureAmbient'];
      $return['thermostatMode'] = 'heat';
    }
    if (isset($return['thermostatTemperatureSetpoint']) && $return['thermostatTemperatureSetpoint'] == '') {
      $return['thermostatTemperatureSetpoint'] = 0;
    }
    if (!isset($return['thermostatMode'])) {
      $return['thermostatMode'] = ($_device->getOptions('TemperatureSetting::heat') == '') ? 'cool' : 'heat';
    }
    $return['activeThermostatMode'] = $return['thermostatMode'];
    if($return['activeThermostatMode'] == 'off'){
      $return['activeThermostatMode'] = 'none';
    }
    return $return;
  }
  
  
  public static function getHtmlConfiguration($_eqLogic){
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Action pour le mode chaud}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="TemperatureSetting::heat">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Action pour le mode froid}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="TemperatureSetting::cool">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Action pour le mode off}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="TemperatureSetting::off">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Action pour le mode Auto(chaud et froid)}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="TemperatureSetting::heatcool">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Action pour le mode Eco}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="TemperatureSetting::eco">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Action pour le mode Autre (non sélectionnable)}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="TemperatureSetting::auto">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
  }
  
}
