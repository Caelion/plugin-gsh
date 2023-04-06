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

class gsh_HumiditySetting {
  
  /*     * *************************Attributs****************************** */
  
  private static $_SET_SETPOINT = array('HUMIDITY_SET_SETPOINT');
  private static $_SETPOINT = array('HUMIDITY_SETPOINT');
  private static $_HUMIDITY = array('HUMIDITY','THERMOSTAT_HUMIDITY');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array(
      'queryOnlyHumiditySetting' => true,
      'commandOnlyHumiditySetting'=>true
    ));
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_SET_SETPOINT)) {
        if (!in_array('action.devices.traits.HumiditySetting', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.HumiditySetting';
        }
        $return['customData']['HumiditySetting_cmdSetSetpoint'] = $cmd->getId();
        $return['attributes']['queryOnlyHumiditySetting'] = false;
        $return['attributes']['humiditySetpointRange'] = array(
          'minPercent' => intval($cmd->getConfiguration('minValue',0)),
          'maxPercent' => intval($cmd->getConfiguration('maxValue',100))
        );
      }
      if (in_array($cmd->getGeneric_type(), self::$_SETPOINT)) {
        if (!in_array('action.devices.traits.HumiditySetting', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.HumiditySetting';
        }
        $return['customData']['HumiditySetting_cmdGetSetpoint'] = $cmd->getId();
        $return['attributes']['commandOnlyHumiditySetting'] = false;
      }
      if (in_array($cmd->getGeneric_type(), self::$_HUMIDITY)) {
        if (!in_array('action.devices.traits.HumiditySetting', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.HumiditySetting';
        }
        $return['customData']['HumiditySetting_cmdGetHumidity'] = $cmd->getId();
        $return['attributes']['commandOnlyHumiditySetting'] = false;
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Contrôle humidité',__FILE__) => self::$_SET_SETPOINT,
      __('Etat contrôle humidité',__FILE__) => self::$_SETPOINT,
      __('Humidité',__FILE__) => self::$_HUMIDITY,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.SetHumidity':
          if (isset($_infos['customData']['HumiditySetting_cmdSetSetpoint'])) {
            $cmd = cmd::byId($_infos['customData']['HumiditySetting_cmdSetSetpoint']);
          }
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider' => $execution['params']['humidity']));
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
    if (isset($_infos['customData']['HumiditySetting_cmdGetSetpoint'])) {
      $cmd = cmd::byId($_infos['customData']['HumiditySetting_cmdGetSetpoint']);
      $return['humiditySetpointPercent'] = $cmd->execCmd();
    }
    if (isset($_infos['customData']['HumiditySetting_cmdGetHumidity'])) {
      $cmd = cmd::byId($_infos['customData']['HumiditySetting_cmdGetHumidity']);
      $return['humidityAmbientPercent'] = $cmd->execCmd();
    }
    return $return;
  }
  
}
