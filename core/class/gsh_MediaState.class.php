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

class gsh_MediaState {
  
  /*     * *************************Attributs****************************** */
  
  private static $_MEDIA_PAUSE = array('MEDIA_PAUSE');
  private static $_MEDIA_RESUME= array('MEDIA_PAUSE');
  private static $_MEDIA_STOP= array('MEDIA_STOP');
  private static $_MEDIA_NEXT= array('MEDIA_NEXT');
  private static $_MEDIA_PREVIOUS= array('MEDIA_PREVIOUS');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_MEDIA_PAUSE)) {
        if (!in_array('action.devices.traits.MediaState', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.MediaState';
        }
        $return['customData']['MediaState_cmdSetPause'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_MEDIA_NEXT)) {
        if (!in_array('action.devices.traits.MediaState', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.MediaState';
        }
        $return['customData']['MediaState_cmdSetNext'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_MEDIA_PREVIOUS)) {
        if (!in_array('action.devices.traits.MediaState', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.MediaState';
        }
        $return['customData']['MediaState_cmdSetPrevious'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_MEDIA_RESUME)) {
        if (!in_array('action.devices.traits.MediaState', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.MediaState';
        }
        $return['customData']['MediaState_cmdSetResume'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_MEDIA_STOP)) {
        if (!in_array('action.devices.traits.MediaState', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.MediaState';
        }
        $return['customData']['MediaState_cmdSetStop'] = $cmd->getId();
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Pause',__FILE__) => self::$_MEDIA_PAUSE,
      __('Reprendre',__FILE__) => self::$_MEDIA_RESUME,
      __('Stop',__FILE__) => self::$_MEDIA_STOP,
      __('Suivant',__FILE__) => self::$_MEDIA_NEXT,
      __('Précedent',__FILE__) => self::$_MEDIA_PREVIOUS,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.mediaPause':
          $cmd = cmd::byId($_infos['customData']['MediaState_cmdSetPause']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.mediaResume':
          $cmd = cmd::byId($_infos['customData']['MediaState_cmdSetResume']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.mediaStop':
          $cmd = cmd::byId($_infos['customData']['MediaState_cmdSetStop']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.mediaNext':
          $cmd = cmd::byId($_infos['customData']['MediaState_cmdSetNext']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
          $return = array('status' => 'SUCCESS');
          break;
          case 'action.devices.commands.mediaPrevious':
          $cmd = cmd::byId($_infos['customData']['MediaState_cmdSetPrevious']);
          if (!is_object($cmd)) {
            break;
          }
          $cmd->execCmd();
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
    return $return;
  }
  
}
