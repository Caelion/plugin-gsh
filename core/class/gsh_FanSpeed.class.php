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

class gsh_FanSpeed {
  
  /*     * *************************Attributs****************************** */
  
  private static $_FAN_SPEED = array('FAN_SPEED');
  private static $_FAN_SPEED_STATE = array('FAN_SPEED_STATE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array('commandOnlyFanSpeed' => true,'supportsFanSpeedPercent' => true));
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_FAN_SPEED)) {
        if (!in_array('action.devices.traits.FanSpeed', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.FanSpeed';
        }
        $return['attributes']['availableFanSpeeds'] = array(
          "speeds" => array(
            array(
              "speed_name"=> "0",
              "speed_values"=> array(
                array(
                  "speed_synonym"=> array("0%"),
                  "lang"=> substr(config::byKey('language'),0,2)
                )
              )
            ),
            array(
              "speed_name"=> "25",
              "speed_values"=> array(
                array(
                  "speed_synonym"=> array("25%"),
                  "lang"=> substr(config::byKey('language'),0,2)
                )
              )
            ),
            array(
              "speed_name"=> "50",
              "speed_values"=> array(
                array(
                  "speed_synonym"=> array("50%"),
                  "lang"=> substr(config::byKey('language'),0,2)
                )
              )
            ),
            array(
              "speed_name"=> "75",
              "speed_values"=> array(
                array(
                  "speed_synonym"=> array("75%"),
                  "lang"=> substr(config::byKey('language'),0,2)
                )
              )
            ),
            array(
              "speed_name"=> "100",
              "speed_values"=> array(
                array(
                  "speed_synonym"=> array("100%"),
                  "lang"=> substr(config::byKey('language'),0,2)
                )
              )
            ),
          ),
          "ordered"=> true
        );
        $return['customData']['FanSpeed_cmdSet'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_FAN_SPEED_STATE)) {
        $return['customData']['FanSpeed_cmdGet'] = $cmd->getId();
        $return['attributes']['commandOnlyFanSpeed'] = true;
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Ventilateur',__FILE__) => self::$_FAN_SPEED_STATE,
      __('Etat',__FILE__) => self::$_FAN_SPEED_STATE
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.SetFanSpeed':
          if (isset($_infos['customData']['FanSpeed_cmdSet'])) {
            $cmd = cmd::byId($_infos['customData']['FanSpeed_cmdSet']);
          }
          if (!is_object($cmd)) {
            break;
          }
          if(isset($execution['params']['fanSpeed'])){
            if ($cmd->getSubtype() == 'slider') {
              $value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['fanSpeed'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
              $cmd->execCmd(array('slider' => $value));
              $return = array('status' => 'SUCCESS');
            }
          }else{
            if ($cmd->getSubtype() == 'slider') {
              $value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['fanSpeedPercent'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
              $cmd->execCmd(array('slider' => $value));
              $return = array('status' => 'SUCCESS');
            }
          }
          break;
          case 'action.devices.commands.SetFanSpeedRelativeSpeed':
          $cmd = cmd::byId($_infos['customData']['FanSpeed_cmdSet']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd_info = cmd::byId($_infos['customData']['FanSpeed_cmdGet']);
          if (!is_object($cmd_info)) {
            break;
          }
          $value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['fanSpeedRelativePercent'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
          $cmd->execCmd(array('slider'=> $cmd_info->execCmd() + $value));
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
    if (isset($_infos['customData']['FanSpeed_cmdGet'])) {
      $cmd = cmd::byId($_infos['customData']['FanSpeed_cmdGet']);
      if (is_object($cmd)) {
        $return['currentFanSpeedPercent'] = $cmd->execCmd()/ 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0));
        if($return['currentFanSpeedPercent'] > 75){
          $return['currentFanSpeedSetting'] = '100';
        }elseif($return['currentFanSpeedPercent'] > 50){
          $return['currentFanSpeedSetting'] = '75';
        }elseif($return['currentFanSpeedPercent'] > 25){
          $return['currentFanSpeedSetting'] = '50';
        }elseif($return['currentFanSpeedPercent'] > 0){
          $return['currentFanSpeedSetting'] = '25';
        }else{
          $return['currentFanSpeedSetting'] = '0';
        }
      }
    }
    return $return;
  }
  
}
