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
		foreach ($eqLogic->getCmd() as $cmd) {
			if (!in_array('action.devices.traits.OnOff', $return['traits']) && in_array($cmd->getDisplay('generic_type'), array('LIGHT_ON', 'LIGHT_OFF'))) {
				$return['traits'][] = 'action.devices.traits.OnOff';
			}
			if (!in_array('action.devices.traits.ColorTemperature', $return['traits']) && in_array($cmd->getDisplay('generic_type'), array('LIGHT_COLOR_TEMP'))) {
				$return['traits'][] = 'action.devices.traits.ColorTemperature';
			}
			if (!in_array('action.devices.traits.Brightness', $return['traits']) && in_array($cmd->getDisplay('generic_type'), array('LIGHT_SLIDER'))) {
				$return['traits'][] = 'action.devices.traits.Brightness';
			}
			if (!in_array('action.devices.traits.OnOff', $return['traits']) && in_array($cmd->getDisplay('generic_type'), array('LIGHT_SLIDER'))) {
				$return['traits'][] = 'action.devices.traits.OnOff';
			}
			if (!in_array('action.devices.traits.ColorSpectrum', $return['traits']) && in_array($cmd->getDisplay('generic_type'), array('LIGHT_SET_COLOR'))) {
				$return['traits'][] = 'action.devices.traits.ColorSpectrum';
			}
			if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_STATE'))) {
				$return['willReportState'] = true;
			}
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		return $return;
	}

	public static function exec($_device, $_executions, $_infos) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		$cmds = $eqLogic->getCmd();
		log::add('gsh', 'debug', print_r($_executions, true));
		foreach ($_executions as $execution) {
			switch ($execution['command']) {
				case 'action.devices.commands.OnOff':
					if ($execution['params']['on']) {
						foreach ($cmds as $cmd) {
							if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_ON'))) {
								$cmd->execCmd();
								return 'SUCCESS';
							}
						}
						foreach ($cmds as $cmd) {
							if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_SLIDER'))) {
								$cmd->execCmd(array('slider' => 100));
								return 'SUCCESS';
							}
						}
					} else {
						foreach ($cmds as $cmd) {
							if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_OFF'))) {
								$cmd->execCmd();
								return 'SUCCESS';
							}
						}
						foreach ($cmds as $cmd) {
							if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_SLIDER'))) {
								$cmd->execCmd(array('slider' => 0));
								return 'SUCCESS';
							}
						}
					}
					break;
			}
		}
		return 'notSupported';
	}

	public static function query($_device) {

	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}