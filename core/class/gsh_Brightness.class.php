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

class gsh_Brightness {
  
  /*     * *************************Attributs****************************** */
  
  private static $_SLIDER = array('FLAP_SLIDER', 'ENERGY_SLIDER','LIGHT_SLIDER');
  private static $_STATE = array('LIGHT_BRIGHTNESS');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_SLIDER)) {
        if (!in_array('action.devices.traits.Brightness', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Brightness';
        }
        $return['customData']['Brightness_cmdSetSlider'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
        $return['customData']['Brightness_cmdGetBrightness'] = $cmd->getId();
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Luminosité',__FILE__) => self::$_SLIDER,
      __('Etat Luminosité',__FILE__) => self::$_STATE
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.BrightnessAbsolute':
          if (isset($_infos['customData']['Brightness_cmdSetSlider'])) {
            $cmd = cmd::byId($_infos['customData']['Brightness_cmdSetSlider']);
          }
          if (is_object($cmd)) {
            $value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['brightness'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
            $cmd->execCmd(array('slider' => $value));
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
    $return = array();
    $cmd = null;
    if (isset($_infos['customData']['Brightness_cmdGetBrightness'])) {
      $cmd = cmd::byId($_infos['customData']['Brightness_cmdGetBrightness']);
    }
    if (!is_object($cmd)) {
      return $return;
    }
    $value = $cmd->execCmd();
    if ($cmd->getSubtype() == 'numeric') {
      $return['brightness'] = round(intval($value) / intval($cmd->getConfiguration('maxValue', 100)) * 100);
    }
    return $return;
  }
  
}
