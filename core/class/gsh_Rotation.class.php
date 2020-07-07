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

class gsh_Rotation {
  
  /*     * *************************Attributs****************************** */
  
  private static $_ROTATION = array('ROTATION');
  private static $_ROTATION_STATE = array('ROTATION_STATE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array(
      'commandOnlyRotation' => false,
      'supportsContinuousRotation' => true,
      'supportsDegrees'=>true,
      'supportsPercent'=> true,
      'rotationDegreesRange' => array(
        'rotationDegreesMin' => 0,
        'rotationDegreesMax' => 360,
      )
    ));
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_ROTATION)) {
        if (!in_array('action.devices.traits.Rotation', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Rotation';
        }
        $return['customData']['Rotation_cmdSet'] = $cmd->getId();
        if(isset($return['attributes']['rotationDegreesRange'])){
          $return['attributes']['rotationDegreesRange']['rotationDegreesMin'] = $cmd->getConfiguration('minValue',0);
          $return['attributes']['rotationDegreesRange']['rotationDegreesMax'] = $cmd->getConfiguration('maxValue',360);
        }
      }
      if (in_array($cmd->getGeneric_type(), self::$_ROTATION_STATE)) {
        $return['customData']['Rotation_cmdGet'] = $cmd->getId();
        $return['attributes']['commandOnlyRotation'] = true;
        if($cmd->getUnite() == '%'){
          $return['attributes']['supportsDegrees'] = false;
          $return['attributes']['supportsPercent'] = true;
          if(isset($return['attributes']['rotationDegreesRange'])){
            unset($return['attributes']['rotationDegreesRange']);
          }
        }
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Rotation',__FILE__) => self::$_ROTATION
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.RotateAbsolute':
          if (isset($_infos['customData']['Rotation_cmdSet'])) {
            $cmd = cmd::byId($_infos['customData']['Rotation_cmdSet']);
          }
          if (!is_object($cmd)) {
            break;
          }
          $value == null;
          if(isset($execution['params']['rotationPercent'])){
            $value = $execution['params']['rotationPercent'];
          }else if(isset($execution['params']['rotationDegrees'])){
            $value = $execution['params']['rotationDegrees'];
          }
          if($value == null){
            return;
          }
          $cmd->execCmd(array('slider' => $value));
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
    if (isset($_infos['customData']['Rotation_cmdGet'])) {
      $cmd = cmd::byId($_infos['customData']['Rotation_cmdGet']);
      if (is_object($cmd)) {
        if($this->getUnite() == '%'){
          $return['rotationPercent'] = $cmd->execCmd();
        }else{
          $return['rotationDegrees'] = $cmd->execCmd();
        }
      }
    }
    return $return;
  }
  
}
