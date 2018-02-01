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

class gsh_scene {

	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function buildDevice($_device) {
		$return = array();
		$return['id'] = $_device->getId();
		$return['type'] = $_device->getType();
		$return['name'] = array('name' => '', 'nicknames' => array($$_device->getOptions('name')));
		$return['willReportState'] = false;
		$return['attributes'] = array('sceneReversible' => false);
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
			switch ($execution['command']) {
				case 'action.devices.commands.ActivateScene	':
					$cmd = $_device->getCmdByGenericType(array('THERMOSTAT_SET_SETPOINT'));
					if (is_object($cmd)) {
						$cmd->execCmd(array('slider' => $execution['params']['thermostatTemperatureSetpoint']));
						$return = array('status' => 'SUCCESS');
					}
					break;
			}
		}
		$return['states'] = self::getState($_device);
		return $return;
	}

	public static function getState($_device) {
		$return = array();
		$return['online'] = true;
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}