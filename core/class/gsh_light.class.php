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

class gsh_light {

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
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => array($eqLogic->getName()));
		$return['traits'] = array();
		$return['willReportState'] = false;
		if (!in_array('action.devices.traits.OnOff', $return['traits']) && $_device->getCmdByGenericType(array('LIGHT_ON', 'LIGHT_OFF')) != null) {
			$return['traits'][] = 'action.devices.traits.OnOff';
		}
		if (!in_array('action.devices.traits.ColorTemperature', $return['traits']) && $_device->getCmdByGenericType(array('LIGHT_COLOR_TEMP')) != null) {
			$return['traits'][] = 'action.devices.traits.ColorTemperature';
		}
		if (!in_array('action.devices.traits.Brightness', $return['traits']) && $_device->getCmdByGenericType(array('LIGHT_SLIDER')) != null) {
			$return['traits'][] = 'action.devices.traits.Brightness';
		}
		if (!in_array('action.devices.traits.OnOff', $return['traits']) && $_device->getCmdByGenericType(array('LIGHT_SLIDER')) != null) {
			$return['traits'][] = 'action.devices.traits.OnOff';
		}
		if (!in_array('action.devices.traits.ColorSpectrum', $return['traits']) && $_device->getCmdByGenericType(array('LIGHT_SET_COLOR')) != null) {
			$return['traits'][] = 'action.devices.traits.ColorSpectrum';
		}
		if ($_device->getCmdByGenericType(array('LIGHT_STATE')) != null) {
			$return['willReportState'] = true;
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		return $return;
	}

	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return $return;
		}
		$cmds = $eqLogic->getCmd();
		foreach ($_executions as $execution) {
			switch ($execution['command']) {
				case 'action.devices.commands.OnOff':
					if ($execution['params']['on']) {
						$cmd = $_device->getCmdByGenericType(array('LIGHT_ON'));
						if (is_object($cmd)) {
							$cmd->execCmd();
							$return = array('status' => 'SUCCESS');
						}
						$cmd = $_device->getCmdByGenericType(array('LIGHT_SLIDER'));
						if (is_object($cmd)) {
							$cmd->execCmd(array('slider' => 100));
							$return = array('status' => 'SUCCESS');
						}
					} else {
						$cmd = $_device->getCmdByGenericType(array('LIGHT_OFF'));
						if (is_object($cmd)) {
							$cmd->execCmd(array('slider' => 100));
							$return = array('status' => 'SUCCESS');
						}
						$cmd = $_device->getCmdByGenericType(array('LIGHT_SLIDER'));
						if (is_object($cmd)) {
							$cmd->execCmd(array('slider' => 0));
							$return = array('status' => 'SUCCESS');
						}
					}
					break;
			}
		}
		return $return;
	}

	public static function query($_device) {

	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}