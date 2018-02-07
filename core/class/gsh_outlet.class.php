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

class gsh_outlet {

	/*     * *************************Attributs****************************** */

	private static $_ON = array('FLAP_BSO_UP', 'FLAP_SLIDER', 'FLAP_UP', 'ENERGY_ON', 'FLAP_SLIDER', 'HEATING_ON', 'LOCK_OPEN', 'SIREN_ON', 'GB_OPEN', 'GB_TOGGLE');
	private static $_OFF = array('FLAP_BSO_DOWN', 'FLAP_SLIDER', 'FLAP_DOWN', 'ENERGY_OFF', 'FLAP_SLIDER', 'HEATING_OFF', 'LOCK_CLOSE', 'SIREN_OFF', 'GB_CLOSE', 'GB_TOGGLE');
	private static $_STATE = array('ENERGY_STATE', 'FLAP_STATE', 'FLAP_BSO_STATE', 'HEATING_STATE', 'LOCK_STATE', 'SIREN_STATE', 'GARAGE_STATE', 'BARRIER_STATE');

	/*     * ***********************Methode static*************************** */

	public static function buildDevice($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		$return = array();
		$return['id'] = $eqLogic->getId();
		$return['type'] = $_device->getType();
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => array($eqLogic->getName(), $eqLogic->getName() . 's'));
		$return['traits'] = array();
		$return['willReportState'] = false;
		if (!in_array('action.devices.traits.OnOff', $return['traits']) && count(cmd::byGenericType(array_merge(self::$_ON, self::$_OFF), $_device->getLink_id())) > 0) {
			$return['traits'][] = 'action.devices.traits.OnOff';
		}
		if ($_device->getCmdByGenericType(self::$_STATE) != null) {
			$return['willReportState'] = true;
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		return $return;
	}

	public static function query($_device) {
		return self::getState($_device);
	}

	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return $return;
		}
		foreach ($_executions as $execution) {
			try {
				switch ($execution['command']) {
					case 'action.devices.commands.OnOff':
						if ($execution['params']['on']) {
							$cmd = cmd::byGenericType(self::$_ON, $_device->getLink_id(), true);
							if ($cmd == null) {
								break;
							}
							if ($cmd->getSubtype() == 'other') {
								$cmd->execCmd();
								$return = array('status' => 'SUCCESS');
							} else if ($cmd->getSubtype() == 'slider') {
								if (in_array($cmd->getDisplay('generic_type'), array('FLAP_SLIDER'))) {
									$cmd->execCmd(array('slider' => 0));
								} else {
									$cmd->execCmd(array('slider' => 100));
								}
								$return = array('status' => 'SUCCESS');
							}
						} else {
							$cmd = cmd::byGenericType(self::$_OFF, $_device->getLink_id(), true);
							if ($cmd == null) {
								break;
							}
							if ($cmd->getSubtype() == 'other') {
								$cmd->execCmd();
								$return = array('status' => 'SUCCESS');
							} else if ($cmd->getSubtype() == 'slider') {
								if (in_array($cmd->getDisplay('generic_type'), array('FLAP_SLIDER'))) {
									$cmd->execCmd(array('slider' => 100));
								} else {
									$cmd->execCmd(array('slider' => 0));
								}
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
		$return['states'] = self::getState($_device);
		return $return;
	}

	public static function getState($_device) {
		$return = array();
		$cmds = cmd::byGenericType(self::$_STATE, $_device->getLink_id());
		if ($cmds == null) {
			return $return;
		}
		if (!is_array($cmds)) {
			$cmds = array($cmds);
		}
		foreach ($cmds as $cmd) {
			$value = $cmd->execCmd();
			if ($cmd->getSubtype() == 'numeric') {
				$return['on'] = ($value > 0);
			}
			if ($cmd->getSubtype() == 'binary') {
				$return['on'] = boolval($value);
			}
			if (in_array($cmd->getDisplay('generic_type'), array('FLAP_SLIDER'))) {
				$return['on'] = (!$return['on']);
			}
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}