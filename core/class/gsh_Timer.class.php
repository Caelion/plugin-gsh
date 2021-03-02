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

class gsh_Timer {
  
  /*     * *************************Attributs****************************** */
  
  private static $_TIMER = array('TIMER');
  private static $_SET_TIMER = array('SET_TIMER');
  private static $_TIMER_PAUSE = array('TIMER_PAUSE');
  private static $_TIMER_RESUME = array('TIMER_RESUME');
  private static $_TIMER_STATE = array('TIMER_STATE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array('maxTimerLimitSec' => 60,'commandOnlyTimer' => true));
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_TIMER)) {
        if (!in_array('action.devices.traits.Timer', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Timer';
        }
        $return['attributes']['commandOnlyTimer'] = false;
        $return['customData']['Timer_cmdGetTimer'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_SET_TIMER)) {
        if (!in_array('action.devices.traits.Timer', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Timer';
        }
        $return['attributes']['maxTimerLimitSec'] = $cmd->getConfiguration('maxValue',60);
        $return['customData']['Timer_cmdSetTimer'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_TIMER_PAUSE)) {
        if (!in_array('action.devices.traits.Timer', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Timer';
        }
        $return['customData']['Timer_cmdSetTimerPause'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_TIMER_RESUME)) {
        if (!in_array('action.devices.traits.Timer', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Timer';
        }
        $return['customData']['Timer_cmdSetTimerResume'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_TIMER_STATE)) {
        if (!in_array('action.devices.traits.Timer', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Timer';
        }
        $return['customData']['Timer_cmdGetTimerState'] = $cmd->getId();
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Etat Timer',__FILE__) => self::$_TIMER,
      __('Timer',__FILE__) => self::$_SET_TIMER,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.TimerStart':
          $cmd = cmd::byId($_infos['customData']['Timer_cmdSetTimer']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider'=> $execution['params']['timerTimeSec']));
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.TimerAdjust':
          $cmd = cmd::byId($_infos['customData']['Timer_cmdSetTimer']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd_info = cmd::byId($_infos['customData']['Timer_cmdGetTimer']);
          if (!is_object($cmd_info)) {
            break;
          }
          $cmd->execCmd(array('slider'=> $cmd_info->execCmd() + $execution['params']['timerTimeSec']));
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.TimerCancel':
          $cmd = cmd::byId($_infos['customData']['Timer_cmdSetTimer']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd(array('slider'=> 0));
          break;
          case 'action.devices.commands.TimerPause':
          $cmd = cmd::byId($_infos['customData']['Timer_cmdSetTimerPause']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
          break;
          case 'action.devices.commands.TimerResume':
          $cmd = cmd::byId($_infos['customData']['Timer_cmdSetTimerResume']);
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
    if (isset($_infos['customData']['Timer_cmdGetTimer'])) {
      $cmd = cmd::byId($_infos['customData']['Timer_cmdGetTimer']);
      if (is_object($cmd)) {
        $return['timerRemainingSec'] = $cmd->execCmd();
        $return['timerPaused'] = false;
      }
    }
    if (isset($_infos['customData']['Timer_cmdGetTimerState'])) {
      $cmd = cmd::byId($_infos['customData']['Timer_cmdGetTimerState']);
      if (is_object($cmd)) {
        $return['timerPaused'] = ($cmd->execCmd());
      }
    }
    return $return;
  }
  
}
