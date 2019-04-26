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

class gsh_shutter {
	
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
			return array();
		}
		$return['attributes']['openDirection'] = array('UP','DOWN');
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
							$execution['params']['openPercent'] = 100 - $execution['params']['openPercent'];
							$value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['openPercent'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
							if($_device->getOptions('shutter::invert',0) == 1){
								$value = $cmd->getConfiguration('maxValue', 100) - $value;
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
						if ($cmd->getSubtype() == 'other') {
							$cmd->execCmd();
							$return = array('status' => 'SUCCESS');
						} else if ($cmd->getSubtype() == 'slider') {
							$value = (in_array($cmd->getGeneric_type(), array('FLAP_SLIDER'))) ? 0 : 100;
							$cmd->execCmd(array('slider' => $value));
							$return = array('status' => 'SUCCESS');
						}
					} else {
						if (isset($_infos['customData']['cmd_set_off'])) {
							$cmd = cmd::byId($_infos['customData']['cmd_set_off']);
						}
						if (!is_object($cmd)) {
							break;
						}
						if ($cmd->getSubtype() == 'other') {
							$cmd->execCmd();
							$return = array('status' => 'SUCCESS');
						} else if ($cmd->getSubtype() == 'slider') {
							$value = (in_array($cmd->getGeneric_type(), array('FLAP_SLIDER'))) ? 100 : 0;
							$cmd->execCmd(array('slider' => $value));
							$return = array('status' => 'SUCCESS');
						}
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
		$return['openState'] = array('openPercent' => 0 , 'openDirection' => 'UP');
		if ($cmd->getSubtype() == 'numeric') {
			$return['openState']['openPercent'] = $value;
		} else if ($cmd->getSubtype() == 'binary') {
			$return['openState']['openPercent'] = boolval($value);
			if ($cmd->getDisplay('invertBinary') == 0) {
				$return['openState']['openPercent'] = ($return['openPercent']) ? false : true;
			}
			$return['openState']['openPercent'] = ($return['openPercent']) ? 0 : 100;
		}
		return $return;
	}
	
	/*     * *********************Méthodes d'instance************************* */
	
	/*     * **********************Getteur Setteur*************************** */
	
}
