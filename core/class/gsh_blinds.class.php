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

class gsh_blinds {
	
	/*     * *************************Attributs****************************** */
	
	private static $_SLIDER = array('FLAP_SLIDER');
	private static $_ON = array('FLAP_BSO_UP', 'FLAP_UP','GB_OPEN');
	private static $_OFF = array('FLAP_BSO_DOWN', 'FLAP_DOWN','GB_CLOSE');
	private static $_STATE = array('FLAP_STATE', 'FLAP_BSO_STATE','GARAGE_STATE','BARRIER_STATE');
	
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
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => $_device->getPseudo());
		$return['traits'] = array();
		$return['attributes'] = array();
		$return['willReportState'] = ($_device->getOptions('reportState') == 1);
		foreach ($eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), self::$_ON)) {
				if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.OpenClose';
				}
				$return['customData']['cmd_set_on'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_OFF)) {
				if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.OpenClose';
				}
				$return['customData']['cmd_set_off'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
				$return['customData']['cmd_get_state'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_SLIDER)) {
				if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.OpenClose';
				}
				$return['customData']['cmd_set_slider'] = $cmd->getId();
			}
		}
		if (count($return['traits']) == 0) {
			return array('missingGenericType' => array(
				__('Position',__FILE__) => self::$_SLIDER,
				__('On',__FILE__) => self::$_ON,
				__('Off',__FILE__) => self::$_OFF,
				__('Etat',__FILE__) => self::$_STATE
			));
		}
		$return['attributes']['openDirection'] = array('DOWN');
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
					case 'action.devices.commands.OpenClose':
					if (isset($_infos['customData']['cmd_set_slider'])) {
						$cmd = cmd::byId($_infos['customData']['cmd_set_slider']);
						if (is_object($cmd)) {
							$value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['openPercent'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
							if($_device->getOptions('blinds::invert',0) == 1){
								$value = 100 - $value;
							}
							$cmd->execCmd(array('slider' => $value));
							$return = array('status' => 'SUCCESS');
						}
						break;
					}
					if ($execution['params']['openPercent'] > 50) {
						if (isset($_infos['customData']['cmd_set_on'])) {
							$cmd = cmd::byId($_infos['customData']['cmd_set_on']);
						}
						if (!is_object($cmd)) {
							break;
						}
						$cmd->execCmd();
						$return = array('status' => 'SUCCESS');
					} else {
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
		$openState = array('openPercent' => 0,'openDirection' => 'DOWN');
		if ($cmd->getSubtype() == 'numeric') {
			$openState['openPercent'] = $value;
		} else if ($cmd->getSubtype() == 'binary') {
			$openState['openPercent'] = boolval($value);
			if ($cmd->getDisplay('invertBinary') == 0) {
				$openState['openPercent'] = ($return['openPercent']) ? false : true;
			}
			$openState['openPercent'] = ($return['openPercent']) ? 0 : 100;
		}
		$return['openState'] = array($openState,array('openPercent' => $openState['openPercent']));
		return $return;
	}
	
	/*     * *********************Méthodes d'instance************************* */
	
	/*     * **********************Getteur Setteur*************************** */
	
}
