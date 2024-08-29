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

class gsh_Modes {
  
  /*     * *************************Attributs****************************** */
  
  private static $_SET_MODE = array('ALARM_SET_MODE','MODE_SET_STATE');
  private static $_GET_MODE = array('ALARM_MODE','MODE_STATE');
  
  /*     * ***********************Methode static*************************** */
  
  public static function buildSetting($_name,$_synonyms,$_lang = 'fr'){
    return array(
      'setting_name'=> $_name,
      'setting_values' => array(array('setting_synonym'=> array_values(array_filter($_synonyms)),'lang'=> $_lang))
    );
  }
  
  public static function discover($_device,$_eqLogic){
    $return = array('traits' => array(),'customData' => array());
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_SET_MODE)) {
        $names = explode(',',$_device->getOptions('Modes::'.$cmd->getId().'::name'));
        $names[] = $cmd->getName();
        $settings[] = self::buildSetting($cmd->getId(),$names,substr(config::byKey('language'),0,2));
        if (!in_array('action.devices.traits.Modes', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Modes';
        }
      }
      if (in_array($cmd->getGeneric_type(), self::$_GET_MODE)) {
        $return['customData']['Modes_cmdGetMode'] = $cmd->getId();
        if (!in_array('action.devices.traits.Modes', $return['traits'])) {
          $return['traits'][] = 'action.devices.traits.Modes';
        }
      }
    }
    if (in_array('action.devices.traits.Modes', $return['traits']) && count($settings) == 0) {
      unset($return['traits']['action.devices.traits.Modes']);
      unset($return['customData']['Modes_cmdGetMode']);
    }
    if (in_array('action.devices.traits.Modes', $return['traits'])) {
      if (!isset($return['attributes'])) {
        $return['attributes'] = array();
      }
      $names = array_merge(array('mode'),explode(',',$_device->getOptions('Modes::modename')));
      $return['attributes']['availableModes'] =  array(array(
        'name' => 'mode',
        'name_values' => array(array('name_synonym' => array_values(array_filter($names)),'lang' => substr(config::byKey('language'),0,2))),
        'settings'  => $settings,
        'ordered'=> true,
        'commandOnlyModes' => (!isset($return['customData']['Modes_cmdGetMode'])),
        'commandOnlyModes' => (count($settings) == 0) 
      ));
    }
    return $return;
  }
  
  public static function needGenericType(){
    return array(
      __('Mode',__FILE__) => self::$_SET_MODE,
      __('Etat mode',__FILE__) => self::$_GET_MODE,
    );
  }
  
  public static function exec($_device, $_executions, $_infos){
    $return = array();
    foreach ($_executions as $execution) {
      try {
        switch ($execution['command']) {
          case 'action.devices.commands.SetModes':
          foreach ($execution['params']['updateModeSettings'] as $key => $value) {
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
    $cmd = null;
    if(isset($_infos['attributes']['availableModes']) && count($_infos['attributes']['availableModes']) > 0 && isset($_infos['customData']['Modes_cmdGetMode'])){
      $cmd = cmd::byId($_infos['customData']['Modes_cmdGetMode']);
      if(is_object($cmd)){
        $return['currentModeSettings'] = array();
        $value = $cmd->execCmd();
        if(isset($_infos['attributes']['availableModes']) && count($_infos['attributes']['availableModes']) > 0){
          foreach ($_infos['attributes']['availableModes'] as $mode) {
            if(!isset($mode['name'])){
              continue;
            }
            $found = null;
            if(isset($mode['settings']) && count($mode['settings']) > 0){
              foreach ($mode['settings'] as $setting) {
                if(strtolower($value) == strtolower($setting['setting_values']['setting_synonym'][0])){
                  $found = $setting['setting_name'];
                  break;
                }
              }
              $return['currentModeSettings'][$mode['name']] =  $found;
            }
          }
        }
      }
    }
    return $return;
  }
  
  public static function getHtmlConfiguration($_eqLogic){
    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">{{Nom du mode}}</label>';
    echo '<div class="col-sm-3">';
    echo '<input class="deviceAttr form-control" data-l1key="options" data-l2key="Modes::modename"></input>';
    echo '</div>';
    echo '</div>';
    foreach ($_eqLogic->getCmd() as $cmd) {
      if (in_array($cmd->getGeneric_type(), self::$_SET_MODE)) {
        echo '<div class="form-group">';
        echo '<label class="col-sm-3 control-label">{{Nom du mode pour la commande}} : '.$cmd->getName().'</label>';
        echo '<div class="col-sm-3">';
        echo '<input class="deviceAttr form-control" data-l1key="options" data-l2key="Modes::'.$cmd->getId().'::name"></input>';
        echo '</div>';
        echo '</div>';
      }
    }
  }
  
}
