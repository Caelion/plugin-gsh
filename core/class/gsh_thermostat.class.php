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

class gsh_thermostat {

	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function buildDevice($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		$return = array();
		$return['id'] = $eqLogic->getId();
		$return['type'] = $_device->getType();
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => $_device->getPseudo());
		$return['customData'] = array();
		$return['willReportState'] = false;
		foreach ($eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_SET_SETPOINT'))) {
				if (!in_array('action.devices.traits.TemperatureSetting', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.TemperatureSetting';
				}
				$return['customData']['cmd_set_thermostat'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_STATE'))) {
				$return['customData']['cmd_get_state'] = $cmd->getId();
				$return['willReportState'] = true;
			}
			if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_STATE_NAME'))) {
				$return['customData']['cmd_get_mode'] = $cmd->getId();
				$return['willReportState'] = true;
			}
			if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_SETPOINT'))) {
				$return['customData']['cmd_get_setpoint'] = $cmd->getId();
				$return['willReportState'] = true;
			}
			if (in_array($cmd->getGeneric_type(), array('THERMOSTAT_TEMPERATURE'))) {
				$return['customData']['cmd_get_temperature'] = $cmd->getId();
				$return['willReportState'] = true;
			}
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		$return['attributes'] = array('availableThermostatModes' => 'on,off,heat,cool', 'thermostatTemperatureUnit' => 'C');
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
		foreach ($_executions as $execution) {
			try {
				switch ($execution['command']) {
					case 'action.devices.commands.ThermostatTemperatureSetpoint':
						if (isset($_infos['customData']['cmd_set_thermostat'])) {
							$cmd = cmd::byId($_infos['customData']['cmd_set_thermostat']);
						}
						if (!is_object($cmd)) {
							break;
						}
						$cmd->execCmd(array('slider' => $execution['params']['thermostatTemperatureSetpoint']));
						$return = array('status' => 'SUCCESS');
						break;
					case 'action.devices.commands.ThermostatSetMode':
						$cmds = cmd::byGenericType('THERMOSTAT_SET_MODE', $_device->getLink_id(), true);
						if ($cmds == null) {
							break;
						}
						if (is_array($cmds)) {
							$cmds = array($cmds);
						}
						foreach ($cmds as $cmd) {
							if ($execution['params']['thermostatMode'] == $cmd->getName()) {
								$cmd->execCmd();
							}
						}
						break;
				}
			} catch (Exception $e) {
				$return = array('status' => 'ERROR');
			}
		}
		$return['states'] = self::getState($_device, $_infos);
		return $return;
	}

	public static function getState($_device, $_infos) {
		$return = array();
		$return['online'] = true;
		if (isset($_infos['customData']['cmd_get_state'])) {
			$cmd = cmd::byId($_infos['customData']['cmd_get_state']);
			if (is_object($cmd)) {
				$return['thermostatMode'] = ($cmd->execCmd()) ? 'on' : 'off';
			}
		}
		if (isset($_infos['customData']['cmd_get_mode'])) {
			$cmd = cmd::byId($_infos['customData']['cmd_get_mode']);
			if (is_object($cmd)) {
				$value = $cmd->execCmd();
				if ($value == __('Chauffage', __FILE__)) {
					$return['thermostatMode'] = 'heat';
				}
				if ($value == __('Climatisation', __FILE__)) {
					$return['thermostatMode'] = 'cool';
				}
			}
		}
		if (isset($_infos['customData']['cmd_get_setpoint'])) {
			$cmd = cmd::byId($_infos['customData']['cmd_get_setpoint']);
			if (is_object($cmd)) {
				$return['thermostatTemperatureSetpoint'] = $cmd->execCmd();
			}
		}
		if (isset($_infos['customData']['cmd_get_temperature'])) {
			$cmd = cmd::byId($_infos['customData']['cmd_get_temperature']);
			if (is_object($cmd)) {
				$return['thermostatTemperatureAmbient'] = $cmd->execCmd();
			}
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}