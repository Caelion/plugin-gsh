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

class gsh_LockUnlock {
  
  /*     * *************************Attributs****************************** */
  
  private static $_STATE = array('LOCK_STATE');
  private static $_ON = array('LOCK_OPEN');
  private static $_OFF = array('LOCK_CLOSE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_ON)) {
        if (!in_array('action.devices.traits.LockUnlock', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.LockUnlock';
        }
        $return['customData']['LockUnlock_cmdSetOn'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_OFF)) {
        if (!in_array('action.devices.traits.LockUnlock', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.LockUnlock';
        }
        $return['customData']['LockUnlock_cmdSetOff'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
        $return['customData']['LockUnlock_cmdGetState'] = $cmd->getId();
        if (!in_array('action.devices.traits.LockUnlock', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.LockUnlock';
        }
      }
    }
    return $return;
  }
  
  public static function needGenericType($_eqLogic){
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
          case 'action.devices.commands.LockUnlock':
          if($execution['params']['lock']){
            if (isset($_infos['customData']['LockUnlock_cmdSetOff'])) {
              $cmd = cmd::byId($_infos['customData']['LockUnlock_cmdSetOff']);
            }
            if (!is_object($cmd)) {
              break;
            }
            $cmd->execCmd();
            $return = array('status' => 'SUCCESS');
          }else{
            if (isset($_infos['customData']['LockUnlock_cmdSetOn'])) {
              $cmd = cmd::byId($_infos['customData']['LockUnlock_cmdSetOn']);
            }
            if (!is_object($cmd)) {
              break;
            }
            $cmd->execCmd();
            $return = array('status' => 'SUCCESS');
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
    if (isset($_infos['customData']['LockUnlock_cmdGetState'])) {
      $cmd = cmd::byId($_infos['customData']['LockUnlock_cmdGetState']);
    }
    if (!is_object($cmd)) {
      return $return;
    }
    $value = $cmd->execCmd();
    if ($cmd->getSubtype() == 'numeric') {
      $return['isLocked'] = ($value != 0);
    } else if ($cmd->getSubtype() == 'binary') {
      $return['isLocked'] = boolval($value);
      if ($cmd->getDisplay('invertBinary') == 1) {
        $return['isLocked'] = ($return['isLocked']) ? false : true;
      }
    }
    if($_device->getOptions('lock::invert')){
      $return['isLocked'] = ($return['isLocked']) ? false : true;
    }
    return $return;
  }
  
}
