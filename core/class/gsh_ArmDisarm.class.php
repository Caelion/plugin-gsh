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

class gsh_ArmDisarm {
  
  /*     * *************************Attributs****************************** */
  
  private static $_STATE = array('ALARM_ENABLE_STATE');
  private static $_ON = array('ALARM_ARMED');
  private static $_OFF = array('ALARM_RELEASED');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_ON)) {
        if (!in_array('action.devices.traits.ArmDisarm', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.ArmDisarm';
        }
        $return['customData']['ArmDisarm_cmdSetOn'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_OFF)) {
        if (!in_array('action.devices.traits.ArmDisarm', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.ArmDisarm';
        }
        $return['customData']['ArmDisarm_cmdSetOff'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
        $return['customData']['ArmDisarm_cmdGetState'] = $cmd->getId();
        if (!in_array('action.devices.traits.ArmDisarm', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.ArmDisarm';
        }
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('On',__FILE__) => self::$_ON,
      __('Off',__FILE__) => self::$_OFF,
      __('Etat',__FILE__) => self::$_STATE
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array('status' => 'NONE');
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.ArmDisarm':
          if(isset($execution['params']['arm'])){
            if($execution['params']['arm']){
              if (isset($_infos['customData']['ArmDisarm_cmdSetOn'])) {
                $cmd = cmd::byId($_infos['customData']['ArmDisarm_cmdSetOn']);
              }
              if (!is_object($cmd)) {
                break;
              }
              $cmd->execCmd();
              $return = array('status' => 'SUCCESS','states' => self::query($_device,$_infos));
            }else{
              if (isset($_infos['customData']['ArmDisarm_cmdSetOff'])) {
                $cmd = cmd::byId($_infos['customData']['ArmDisarm_cmdSetOff']);
              }
              if (!is_object($cmd)) {
                break;
              }
              $cmd->execCmd();
              $return = array('status' => 'SUCCESS');
            }
          }else if(isset($execution['params']['cancel']) && $execution['params']['cancel']){
            if (isset($_infos['customData']['ArmDisarm_cmdSetOff'])) {
              $cmd = cmd::byId($_infos['customData']['ArmDisarm_cmdSetOff']);
            }
            if (!is_object($cmd)) {
              break;
            }
            $cmd->execCmd();
            $return = array('status' => 'SUCCESS','states' => self::query($_device,$_infos));
          }
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
    $return = array('isJammed' => false);
    $cmd = null;
    if (isset($_infos['customData']['ArmDisarm_cmdGetState'])) {
      $cmd = cmd::byId($_infos['customData']['ArmDisarm_cmdGetState']);
    }
    if (!is_object($cmd)) {
      return $return;
    }
    $value = $cmd->execCmd();
    if ($cmd->getSubtype() == 'numeric') {
      $return['isArmed'] = ($value != 0);
    } else if ($cmd->getSubtype() == 'binary') {
      $return['isArmed'] = boolval($value);
      if ($cmd->getDisplay('invertBinary') == 1) {
        $return['isArmed'] = ($return['isArmed']) ? false : true;
      }
    }
    return $return;
  }
  
}
