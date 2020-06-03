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

class gsh_Reboot {
  
  /*     * *************************Attributs****************************** */
  
  private static $_REBOOT = array('REBOOT');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_REBOOT)) {
        if (!in_array('action.devices.traits.Reboot', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Reboot';
        }
        $return['customData']['Reboot_cmdSet'] = $cmd->getId();
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Redémarrage',__FILE__) => self::$_REBOOT
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.Reboot':
          if (isset($_infos['customData']['Reboot_cmdSet'])) {
            $cmd = cmd::byId($_infos['customData']['Reboot_cmdSet']);
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
    return array();
  }
  
}
