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

class gsh_Toggle {
  
  /*     * *************************Attributs****************************** */
  
  private static $_SET_TOGGLE = array('GB_TOGGLE','LIGHT_TOGGLE','TOGGLE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    $availableToggle = array();
    foreach ($_eqLogic->getCmd() as $cmd) {
        if (!in_array($cmd->getGeneric_type(), self::$_SET_TOGGLE)) {
            continue;
        }
        $names = explode(',',$_device->getOptions('Toggle::'.$cmd->getId().'::name'));
        $names[] = $cmd->getName();
        $availableToggle[] = array(
            'name' => $cmd->getId(),
            'name_values' => array(
                array('name_synonym' => array_values(array_filter($names)),'lang' => substr(config::byKey('language'),0,2))
            )
        );
        if (!in_array('action.devices.traits.Toggles', $return['traits'])) {
            $return['traits'][] = 'action.devices.traits.Toggles';
        }
    }
    if (in_array('action.devices.traits.Toggles', $return['traits'])) {
      if (!isset($return['attributes'])) {
        $return['attributes'] = array();
      }
      $return['attributes']['availableToggles'] =  $availableToggle;
      $return['attributes']['commandOnlyToggles'] =  true;
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Toggle',__FILE__) => self::$_SET_TOGGLE,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.SetToggles':
          foreach ($execution['params']['updateToggleSettings'] as $key => $value) {
            $cmd = cmd::byId($value);
            if (!is_object($cmd)) {
              continue;
            }
            $cmd->execCmd();
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
    return $return;
  }
  
  public static function getHtmlConfiguration($_eqLogic){
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Nom du toggle}}</label>';
    echo '<div class="col-sm-3">';
    echo '<input class="deviceAttr form-control" data-l1key="options" data-l2key="Toggle::modename"></input>';
    echo '</div>';
    echo '</div>';
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_SET_TOGGLE)) {
        echo '<div class="form-group">';
        echo '<label class="col-sm-3 control-label">{{Nom du toggle pour la commande}} : '.$cmd->getName().'</label>';
        echo '<div class="col-sm-3">';
        echo '<input class="deviceAttr form-control" data-l1key="options" data-l2key="Toggle::'.$cmd->getId().'::name"></input>';
        echo '</div>';
        echo '</div>';
      }
    }
  }
  
}
