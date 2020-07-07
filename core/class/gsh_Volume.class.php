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

class gsh_Volume {
  
  /*     * *************************Attributs****************************** */
  
  private static $_VOLUME = array('VOLUME');
  private static $_SET_VOLUME = array('SET_VOLUME');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_VOLUME)) {
        if (!in_array('action.devices.traits.Volume', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Volume';
        }
        $return['customData']['Volume_cmdGetVolume'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_SET_VOLUME)) {
        if (!in_array('action.devices.traits.Volume', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Volume';
        }
        $return['customData']['Volume_cmdSetVolume'] = $cmd->getId();
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Etat volume',__FILE__) => self::$_VOLUME,
      __('Volume',__FILE__) => self::$_SET_VOLUME,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.setVolume':
          $cmd = cmd::byId($_infos['customData']['Volume_cmdSetVolume']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider'=> $execution['params']['volumeLevel']));
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.volumeRelative':
          $cmd = cmd::byId($_infos['customData']['Volume_cmdSetVolume']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd_info = cmd::byId($_infos['customData']['Volume_cmdGetVolume']);
          if (!is_object($cmd_info)) {
            break;
          }
          $cmd->execCmd(array('slider'=> $cmd_info->execCmd() + $execution['params']['volumeRelativeLevel']));
          $return = array('status' => 'SUCCESS');
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
    if (isset($_infos['customData']['Volume_cmdGetVolume'])) {
      $cmd = cmd::byId($_infos['customData']['Volume_cmdGetVolume']);
      if (is_object($cmd)) {
        $return['currentVolume'] = $cmd->execCmd();
        $return['isMute'] = false;
      }
    }
    return $return;
  }
  
}
