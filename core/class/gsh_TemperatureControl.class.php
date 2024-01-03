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

class gsh_TemperatureControl {
  
  /*     * *************************Attributs****************************** */
  
  private static $_SET_SETPOINT = array('THERMOSTAT_SET_SETPOINT');
  private static $_SETPOINT = array('THERMOSTAT_SETPOINT');
  private static $_TEMPERATURE = array('TEMPERATURE','THERMOSTAT_TEMPERATURE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array(
      'temperatureUnitForUX' => 'C',
      'queryOnlyTemperatureControl' => true,
      'commandOnlyTemperatureControl'=>true
    ));
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_SET_SETPOINT)) {
        if (!in_array('action.devices.traits.TemperatureControl', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.TemperatureControl';
        }
        $return['customData']['TemperatureControl_cmdSetSetpoint'] = $cmd->getId();
        $return['attributes']['queryOnlyTemperatureControl'] = false;
        $return['attributes']['temperatureRange'] = array(
          'minThresholdCelsius' => intval($cmd->getConfiguration('minValue',0)),
          'maxThresholdCelsius' => intval($cmd->getConfiguration('maxValue',40))
        );
      }
      if (in_array($cmd->getGeneric_type(), self::$_SETPOINT)) {
        if (!in_array('action.devices.traits.TemperatureControl', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.TemperatureControl';
        }
        $return['customData']['TemperatureControl_cmdGetSetpoint'] = $cmd->getId();
        $return['attributes']['commandOnlyTemperatureControl'] = false;
      }
      if (in_array($cmd->getGeneric_type(), self::$_TEMPERATURE)) {
        if (!in_array('action.devices.traits.TemperatureControl', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.TemperatureControl';
        }
        $return['customData']['TemperatureControl_cmdGetTemperature'] = $cmd->getId();
        $return['attributes']['commandOnlyTemperatureControl'] = false;
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Thermostat',__FILE__) => self::$_SET_SETPOINT,
      __('Etat thermostat/Température',__FILE__) => self::$_SETPOINT,
      __('Température',__FILE__) => self::$_TEMPERATURE,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.SetTemperature':
          if (isset($_infos['customData']['TemperatureControl_cmdSetSetpoint'])) {
            $cmd = cmd::byId($_infos['customData']['TemperatureControl_cmdSetSetpoint']);
          }
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider' => $execution['params']['temperature']));
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
    $cmd = null;
    if (isset($_infos['customData']['TemperatureControl_cmdGetTemperature'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureControl_cmdGetTemperature']);
      $return['temperatureAmbientCelsius'] = $cmd->execCmd();
    }
    if (isset($_infos['customData']['TemperatureControl_cmdGetSetpoint'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureControl_cmdGetSetpoint']);
      $return['temperatureSetpointCelsius'] = $cmd->execCmd();
    } else if (isset($_infos['customData']['TemperatureControl_cmdGetTemperature'])) {
      $cmd = cmd::byId($_infos['customData']['TemperatureControl_cmdGetTemperature']);
      $return['temperatureSetpointCelsius'] = $cmd->execCmd();
    }
    return $return;
  }
  
}
