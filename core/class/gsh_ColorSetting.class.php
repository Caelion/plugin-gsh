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

class gsh_ColorSetting {

  /*     * *************************Attributs****************************** */

  private static $_COLOR = array('LIGHT_SET_COLOR');
  private static $_COLOR_STATE = array('LIGHT_COLOR');
  private static $_COLOR_TEMP = array('LIGHT_SET_COLOR_TEMP');
  private static $_COLOR_TEMP_STATE = array('LIGHT_COLOR_TEMP');

  /*     * ***********************Methode static*************************** */

  public static function discover($_device, $_eqLogic) {
    $return = array('traits' => array(), 'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_COLOR)) {
        if (!in_array('action.devices.traits.ColorSetting', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.ColorSetting';
        }
        $return['customData']['ColorSetting_cmdSetColor'] = $cmd->getId();
        if (!isset($return['attributes'])) {
          $return['attributes'] = array();
        }
        $return['attributes']['colorModel'] = 'rgb';
      }
      if (in_array($cmd->getGeneric_type(), self::$_COLOR_TEMP)) {
        if (!in_array('action.devices.traits.ColorSetting', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.ColorSetting';
        }
        $return['customData']['ColorSetting_cmdSetTempColor'] = $cmd->getId();
        if (!isset($return['attributes'])) {
          $return['attributes'] = array();
        }
        $return['attributes']['colorTemperatureRange'] = array(
          'temperatureMinK' => intval($cmd->getConfiguration('minValue')),
          'temperatureMaxK' => intval($cmd->getConfiguration('maxValue'))
        );
      }
      if (in_array($cmd->getGeneric_type(), self::$_COLOR_STATE)) {
        $return['customData']['ColorSetting_cmdGetColor'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_COLOR_TEMP_STATE)) {
        $return['customData']['ColorSetting_cmdGetTempColor'] = $cmd->getId();
      }
    }
    return $return;
  }

  public static function needGenericType() {
    return array(
      __('Couleur', __FILE__) => self::$_COLOR,
      __('Etat couleur', __FILE__) => self::$_COLOR_STATE,
    );
  }

  public static function exec($_device, $_executions, $_infos) {
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.ColorAbsolute':
            if (isset($execution['params']['color']['spectrumRGB'])) {
              if (isset($_infos['customData']['ColorSetting_cmdSetColor'])) {
                $cmd = cmd::byId($_infos['customData']['ColorSetting_cmdSetColor']);
              }
              if (is_object($cmd)) {
                $cmd->execCmd(array('color' => '#' . str_pad(dechex($execution['params']['color']['spectrumRGB']), 6, '0', STR_PAD_LEFT)));
              }
            }
            if (isset($execution['params']['color']['temperature'])) {
              if (isset($_infos['customData']['ColorSetting_cmdSetTempColor'])) {
                $cmd = cmd::byId($_infos['customData']['ColorSetting_cmdSetTempColor']);
              }
              if (is_object($cmd)) {
                $cmd->execCmd(array('slider' => $execution['params']['color']['temperature']));
              }
            }
            $return = array('status' => 'SUCCESS');
            if (isset($_infos['customData']['OnOff_cmdGetState'])) {
              $state = cmd::byId($_infos['customData']['OnOff_cmdGetState']);
              if (is_object($state)) {
                sleep(1);
                $return['on'] = boolval($state->execCmd());
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

  public static function query($_device, $_infos) {
    $return = array();
    $cmd = null;
    if (isset($_infos['customData']['ColorSetting_cmdGetColor'])) {
      $cmd = cmd::byId($_infos['customData']['ColorSetting_cmdGetColor']);
      if (!isset($return['color'])) {
        $return['color'] = array();
      }
      $return['color']['spectrumRGB'] = hexdec(str_replace('#', '', $cmd->execCmd()));
    }
    if (isset($_infos['customData']['ColorSetting_cmdGetTempColor'])) {
      $cmd = cmd::byId($_infos['customData']['ColorSetting_cmdGetTempColor']);
      if (!isset($return['color'])) {
        $return['color'] = array();
      }
      $return['color']['temperatureK'] = $cmd->execCmd();
    }
    return $return;
  }
}
