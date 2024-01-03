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

class gsh_OccupancySensing {

	/*     * *************************Attributs****************************** */

	private static $_PRESENCE = array('PRESENCE');


	/*     * ***********************Methode static*************************** */

	public static function discover($_device, $_eqLogic) {
		$return = array('traits' => array(), 'customData' => array(), 'attributes' => array());
		$return['attributes']['dataTypesSupported'] = array();
		foreach ($_eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), self::$_PRESENCE)) {
				$return['customData']['OccupancySensing_cmdGetOccupancy'] = $cmd->getId();
				if (!in_array('action.devices.traits.OccupancySensing', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.OccupancySensing';
					$return['attributes']['occupancySensorConfiguration'][] = array(
						'occupancySensorType' => 'PIR',
						'occupiedToUnoccupiedDelaySec' => 30,
						'unoccupiedToOccupiedDelaySec' => 0,
						'unoccupiedToOccupiedEventThreshold' => 1,
					);
				}
			}
		}
		return $return;
	}

	public static function needGenericType() {
		return array(
			__('Présence', __FILE__) => self::$_PRESENCE
		);
	}

	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
		return $return;
	}

	public static function query($_device, $_infos) {
		$return = array();
		$return['online'] = true;
		$return['on'] = true;
		$eqLogic = $_device->getLink();
		$return['occupancy'] = 'UNKNOWN_OCCUPANCY_STATE';

		foreach ($_infos['customData'] as $key => $cmd_id) {
			$cmd = cmd::byId($cmd_id);
			if (!is_object($cmd)) {
				continue;
			}
			$type = str_replace('OccupancySensing_cmdGet', '', $key);
			$value = $cmd->execCmd();
			switch ($type) {
				case 'Occupancy':
					$value = ($value == 1) ? 'OCCUPIED'  : 'UNOCCUPIED';
					break;
			}

			$return['occupancy'] = $value;
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */
}
