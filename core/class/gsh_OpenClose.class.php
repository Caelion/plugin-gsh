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

class gsh_OpenClose {
  
  /*     * *************************Attributs****************************** */
  
  private static $_SLIDER = array('FLAP_SLIDER');
  private static $_ON = array('FLAP_BSO_UP', 'FLAP_UP','GB_OPEN');
  private static $_OFF = array('FLAP_BSO_DOWN', 'FLAP_DOWN','GB_CLOSE');
  private static $_STATE = array('FLAP_STATE', 'FLAP_BSO_STATE','GARAGE_STATE','BARRIER_STATE','OPENING', 'OPENING_WINDOW');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array(),'attributes' => array('openDirection' => array('DOWN'),'queryOnlyOpenClose' => true,'discreteOnlyOpenClose' => true));
	if ($_device->getOptions('OpenClose::partial')) {
		$return['attributes']['discreteOnlyOpenClose'] = false;
	}
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_ON)) {
        if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.OpenClose';
        }
        $return['customData']['OpenClose_cmdSetOn'] = $cmd->getId();
        $return['attributes']['queryOnlyOpenClose'] = false;
      }
      if (in_array($cmd->getGeneric_type(), self::$_OFF)) {
        if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.OpenClose';
        }
        $return['customData']['OpenClose_cmdSetOff'] = $cmd->getId();
        $return['attributes']['queryOnlyOpenClose'] = false;
      }
      if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
        if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.OpenClose';
        }
        $return['customData']['OpenClose_cmdGetState'] = $cmd->getId();
      }
      if (in_array($cmd->getGeneric_type(), self::$_SLIDER)) {
        if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.OpenClose';
        }
        $return['customData']['OpenClose_cmdSetSlider'] = $cmd->getId();
        $return['attributes']['queryOnlyOpenClose'] = false;
        $return['attributes']['discreteOnlyOpenClose'] = false;
      }
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Position',__FILE__) => self::$_SLIDER,
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
          case 'action.devices.commands.OpenClose':
          if (isset($_infos['customData']['OpenClose_cmdSetSlider'])) {
            $cmd = cmd::byId($_infos['customData']['OpenClose_cmdSetSlider']);
            if (is_object($cmd)) {
              $value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['openPercent'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
              if($_device->getOptions('OpenClose::invertSet',0) == 1){
                $value = 100 - $value;
              }
              $cmd->execCmd(array('slider' => $value));
              $return = array('status' => 'SUCCESS');
            }
            break;
          }
          if ($execution['params']['openPercent'] > 50) {
            if (isset($_infos['customData']['OpenClose_cmdSetOn'])) {
              $cmd = cmd::byId($_infos['customData']['OpenClose_cmdSetOn']);
            }
            if (!is_object($cmd)) {
              break;
            }
            $cmd->execCmd();
            $return = array('status' => 'SUCCESS');
          } else if ($execution['params']['openPercent'] > 0 && $execution['params']['openPercent']<100) {
            if ($_device->getOptions('OpenClose::partialCommand','') != '') {
				 $cmd = cmd::byId($_device->getOptions('OpenClose::partialCommand',''));
			}
            if (!is_object($cmd)) {
              break;
            }
            $cmd->execCmd();
            $return = array('status' => 'SUCCESS');
          } else {
            if (isset($_infos['customData']['OpenClose_cmdSetOff'])) {
              $cmd = cmd::byId($_infos['customData']['OpenClose_cmdSetOff']);
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
    $return = array();
    $cmd = null;
    if (isset($_infos['customData']['OpenClose_cmdGetState'])) {
      $cmd = cmd::byId($_infos['customData']['OpenClose_cmdGetState']);
    }
    if (!is_object($cmd)) {
      return $return;
    }
    $value = $cmd->execCmd();
    $openState = array('openPercent' => 0,'openDirection' => 'DOWN');
    if ($cmd->getSubtype() == 'numeric') {
      $openState['openPercent'] = $value;
    } else if ($cmd->getSubtype() == 'binary') {
      $openState['openPercent'] = boolval($value);
      if ($cmd->getDisplay('invertBinary') == 1) {
        $openState['openPercent'] = ($openState['openPercent']) ? false : true;
      }
      $openState['openPercent'] = ($openState['openPercent']) ? 0 : 100;
    }
    $return['openState'] = array($openState,array('openPercent' => $openState['openPercent']));
    if($_device->getOptions('OpenClose::invertGet')){
      $return['openPercent'] = 100 - $return['openPercent'];
    }
    return $return;
  }
  
  public static function getHtmlConfiguration($_eqLogic){
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Inverser l\'action}}</label>';
    echo '<div class="col-sm-3">';
    echo '<input type="checkbox" class="deviceAttr" data-l1key="options" data-l2key="OpenClose::invertSet"></input>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Inverser l\'état}}</label>';
    echo '<div class="col-sm-3">';
    echo '<input type="checkbox" class="deviceAttr" data-l1key="options" data-l2key="OpenClose::invertGet"></input>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Autoriser ouverture partielle}}</label>';
    echo '<div class="col-sm-3">';
    echo '<input type="checkbox" class="deviceAttr" data-l1key="options" data-l2key="OpenClose::partial"></input>';
    echo '</div>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Commande partielle}}</label>';
    echo '<div class="col-sm-3">';
    echo '<select class="form-control deviceAttr" data-l1key="options" data-l2key="OpenClose::partial">';
    echo '<option value="">{{Aucun}}</option>';
    foreach ($_eqLogic->getCmd('action', null, null, true) as $cmd) {
      echo '<option value="' . $cmd->getId() . '">' . $cmd->getName() . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
  }
  
}
