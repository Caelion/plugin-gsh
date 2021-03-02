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

class gsh_OnOff {
  
  /*     * *************************Attributs****************************** */
  
  private static $_ON = array('FLAP_BSO_UP', 'FLAP_UP', 'ENERGY_ON', 'HEATING_ON', 'LOCK_OPEN', 'SIREN_ON', 'GB_OPEN', 'GB_TOGGLE','ENERGY_ON', 'LIGHT_ON');
  private static $_OFF = array('FLAP_BSO_DOWN', 'FLAP_DOWN', 'ENERGY_OFF', 'HEATING_OFF', 'LOCK_CLOSE', 'SIREN_OFF', 'GB_CLOSE', 'GB_TOGGLE','ENERGY_OFF', 'LIGHT_OFF');
  private static $_STATE = array('ENERGY_STATE', 'FLAP_STATE', 'FLAP_BSO_STATE', 'HEATING_STATE', 'LOCK_STATE', 'SIREN_STATE', 'GARAGE_STATE', 'BARRIER_STATE', 'OPENING', 'OPENING_WINDOW','ENERGY_STATE', 'LIGHT_STATE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_ON)) {
        if (!in_array('action.devices.traits.OnOff', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.OnOff';
        }
        $return['customData']['OnOff_cmdSetOn'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_OFF)) {
        if (!in_array('action.devices.traits.OnOff', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.OnOff';
        }
        $return['customData']['OnOff_cmdSetOff'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
        $return['customData']['OnOff_cmdGetState'] = $cmd->getId();
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
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.OnOff':
          if ($execution['params']['on']) {
            if ($_infos['customData']['OnOff_cmdSetOn'] == 'fake') {
              $return = array('status' => 'SUCCESS');
              break;
            }
            if (isset($_infos['customData']['OnOff_cmdSetOn'])) {
              $cmd = cmd::byId($_infos['customData']['OnOff_cmdSetOn']);
            }
            if (!is_object($cmd)) {
              break;
            }
            if ($cmd->getSubtype() == 'other') {
              $cmd->execCmd();
              $return = array('status' => 'SUCCESS');
            } else if ($cmd->getSubtype() == 'slider') {
              $value = (in_array($cmd->getGeneric_type(), array('FLAP_SLIDER'))) ? 0 : 100;
              $cmd->execCmd(array('slider' => $value));
              $return = array('status' => 'SUCCESS');
            }else{
              $return = array('status' => 'ERROR');
            }
          } else {
            if ($_infos['customData']['OnOff_cmdSetOff'] == 'fake') {
              $return = array('status' => 'SUCCESS');
              break;
            }
            if (isset($_infos['customData']['OnOff_cmdSetOff'])) {
              $cmd = cmd::byId($_infos['customData']['OnOff_cmdSetOff']);
            }
            if (!is_object($cmd)) {
              break;
            }
            if ($cmd->getSubtype() == 'other') {
              $cmd->execCmd();
              $return = array('status' => 'SUCCESS');
            } else if ($cmd->getSubtype() == 'slider') {
              $value = (in_array($cmd->getGeneric_type(), array('FLAP_SLIDER'))) ? 100 : 0;
              $cmd->execCmd(array('slider' => $value));
              $return = array('status' => 'SUCCESS');
            }else{
              $return = array('status' => 'ERROR');
            }
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
    $return = array();
    $cmd = null;
    if (isset($_infos['customData']['OnOff_cmdGetState'])) {
      $cmd = cmd::byId($_infos['customData']['OnOff_cmdGetState']);
    }
    if (!is_object($cmd)) {
      return $return;
    }
    $value = $cmd->execCmd();
    if ($cmd->getSubtype() == 'numeric') {
      $return['on'] = ($value > 0);
    } else if ($cmd->getSubtype() == 'binary') {
      $return['on'] = boolval($value);
    }
    if (in_array($cmd->getGeneric_type(), array('FLAP_BSO_STATE', 'FLAP_STATE'))) {
      $return['on'] = ($return['on']) ? false : true;
    }
    return $return;
  }
  
}
