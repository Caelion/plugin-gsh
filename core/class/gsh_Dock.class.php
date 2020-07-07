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

class gsh_Dock {
  
  /*     * *************************Attributs****************************** */
  
  private static $_DOCK = array('DOCK');
  private static $_DOCK_STATE = array('DOCK_STATE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_DOCK)) {
        if (!in_array('action.devices.traits.Dock', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Dock';
        }
        $return['customData']['Dock_cmdSet'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_DOCK_STATE)) {
        $return['customData']['Dock_cmdGet'] = $cmd->getId();
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Retour sur sa base',__FILE__) => self::$_DOCK_STATE,
      __('Sur sa base',__FILE__) => self::$_DOCK
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.Dock':
          if (isset($_infos['customData']['Dock_cmdSet'])) {
            $cmd = cmd::byId($_infos['customData']['Dock_cmdSet']);
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
    $cmd = null;
    if (isset($_infos['customData']['Dock_cmdGet'])) {
      $cmd = cmd::byId($_infos['customData']['Dock_cmdGet']);
    }
    if (!is_object($cmd)) {
      return $return;
    }
    $value = $cmd->execCmd();
    if ($cmd->getSubtype() == 'numeric') {
      $return['isDocked'] = ($value > 0);
    } else if ($cmd->getSubtype() == 'binary') {
      $return['isDocked'] = boolval($value);
    }
    return $return;
  }
  
}
