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

class gsh_securitysystem {
	
	/*     * *************************Attributs****************************** */
	
	private static $_STATE = array('ALARM_ENABLE_STATE');
	private static $_ON = array('ALARM_ARMED');
	private static $_OFF = array('ALARM_RELEASED');
	private static $_SET_MODE = array('ALARM_SET_MODE');
	private static $_GET_MODE = array('ALARM_MODE');
	
	/*     * ***********************Methode static*************************** */
	
	public static function buildDevice($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		$return = array();
		$return['id'] = $eqLogic->getId();
		$return['type'] = $_device->getType();
		if (is_object($eqLogic->getObject())) {
			$return['roomHint'] = $eqLogic->getObject()->getName();
		}
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => $_device->getPseudo(), 'defaultNames' => $_device->getPseudo());
		$return['customData'] = array();
		$return['willReportState'] = ($_device->getOptions('reportState') == 1);
		$return['traits'] = array();
		$settings = array();
		foreach ($eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), self::$_ON)) {
				if (!in_array('action.devices.traits.ArmDisarm', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.ArmDisarm';
				}
				$return['customData']['cmd_set_on'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_OFF)) {
				if (!in_array('action.devices.traits.ArmDisarm', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.ArmDisarm';
				}
				$return['customData']['cmd_set_off'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
				$return['customData']['cmd_get_state'] = $cmd->getId();
				if (!in_array('action.devices.traits.ArmDisarm', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.ArmDisarm';
				}
			}
			if (in_array($cmd->getGeneric_type(), self::$_SET_MODE)) {
				$settings[] = gsh_devices::traitsModeBuildSetting($cmd->getId(),array($cmd->getName()));
				if (!in_array('action.devices.traits.Modes', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Modes';
				}
			}
			if (in_array($cmd->getGeneric_type(), self::$_GET_MODE)) {
				$return['customData']['cmd_get_mode'] = $cmd->getId();
				if (!in_array('action.devices.traits.Modes', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Modes';
				}
			}
		}
		if (in_array('action.devices.traits.Modes', $return['traits']) && count($settings) == 0) {
			unset($return['traits']['action.devices.traits.Modes']);
			unset($return['customData']['cmd_get_mode']);
		}
		if (in_array('action.devices.traits.Modes', $return['traits'])) {
			if (!isset($return['attributes'])) {
				$return['attributes'] = array();
			}
			$return['attributes']['availableModes'] =  array(
				'name' => 'mode',
				'name_values' => array(array('name_synonym' => array('mode'),'lang' => 'en')),
				'settings'  => $settings,
				'ordered'=> true
			);
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		return $return;
	}
	
	public static function query($_device, $_infos) {
		return self::getState($_device, $_infos);
	}
	
	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return $return;
		}
		if ($eqLogic->getIsEnable() == 0) {
			return $return;
		}
		foreach ($_executions as $execution) {
			try {
				switch ($execution['command']) {
					case 'action.devices.commands.SetModes':
					foreach ($execution['command']['params'] as $key => $value) {
						$cmd = cmd::byId($key);
						if (!is_object($cmd)) {
							continue;
						}
						$cmd->execCmd();
					}
					break;
					case 'action.devices.commands.ArmDisarm':
					if($execution['params']['arm']){
						if (isset($_infos['customData']['cmd_set_on'])) {
							$cmd = cmd::byId($_infos['customData']['cmd_set_on']);
						}
						if (!is_object($cmd)) {
							break;
						}
						$cmd->execCmd();
						$return = array('status' => 'SUCCESS');
					}else	if($execution['params']['cancel']){{
						if (isset($_infos['customData']['cmd_set_off'])) {
							$cmd = cmd::byId($_infos['customData']['cmd_set_off']);
						}
						if (!is_object($cmd)) {
							break;
						}
						$cmd->execCmd();
						$return = array('status' => 'SUCCESS');
					}
					break;
				}
			}
		} catch (Exception $e) {
			log::add('gsh', 'error', $e->getMessage());
			$return = array('status' => 'ERROR');
		}
	}
	$return['states'] = self::getState($_device, $_infos);
	return $return;
}

public static function getState($_device, $_infos) {
	$return = array();
	$cmd = null;
	if (isset($_infos['customData']['cmd_get_state'])) {
		$cmd = cmd::byId($_infos['customData']['cmd_get_state']);
	}
	if (!is_object($cmd)) {
		return $return;
	}
	$value = $cmd->execCmd();
	if ($cmd->getSubtype() == 'numeric') {
		$return['isArmed'] = ($value != 0);
	} else if ($cmd->getSubtype() == 'binary') {
		$return['isArmed'] = boolval($value);
		if ($cmd->getDisplay('invertBinary') == 1) {
			$return['isArmed'] = ($return['isArmed']) ? false : true;
		}
	}
	if(isset($_infos['attributes']['availableModes']) && count($_infos['attributes']['availableModes']) > 0 && isset($_infos['customData']['cmd_get_mode'])){
		$cmd = cmd::byId($_infos['customData']['cmd_get_mode']);
		if(is_object($cmd)){
			$return['currentModeSettings'] = array();
			$value = $cmd->execCmd();
			if(count($_infos['attributes']['availableModes']) > 0){
				foreach ($_infos['attributes']['availableModes'] as $mode) {
					$found = null;
					if(count($mode['settings']) > 0){
						foreach ($mode['settings'] as $setting) {
							if(strtolower($value) == strtolower($setting['setting_values']['setting_synonym'][0])){
								$found = $setting['setting_name'];
								break;
							}
						}
					}
					$return['currentModeSettings'][$mode['name']] =  $found;
				}
			}
		}
	}
	return $return;
}

/*     * *********************Méthodes d'instance************************* */

/*     * **********************Getteur Setteur*************************** */

}
