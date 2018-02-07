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

	private static $_ON = array('ENERGY_ON', 'LIGHT_ON');
	private static $_OFF = array('ENERGY_OFF', 'LIGHT_OFF');
	private static $_STATE = array('ENERGY_STATE', 'LIGHT_STATE');

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
		if (!in_array('action.devices.traits.ColorTemperature', $return['traits']) && count(cmd::byGenericType(array('LIGHT_COLOR_TEMP'), $_device->getLink_id())) > 0) {
			$return['traits'][] = 'action.devices.traits.ColorTemperature';
		}
		if (!in_array('action.devices.traits.Brightness', $return['traits']) && count(cmd::byGenericType(array('LIGHT_SLIDER'), $_device->getLink_id())) > 0) {
			$return['traits'][] = 'action.devices.traits.Brightness';
		}
		if (!in_array('action.devices.traits.OnOff', $return['traits']) && count(cmd::byGenericType(array('LIGHT_SLIDER'), $_device->getLink_id())) > 0) {
			$return['traits'][] = 'action.devices.traits.OnOff';
		}
		if (!in_array('action.devices.traits.ColorSpectrum', $return['traits']) && count(cmd::byGenericType(array('LIGHT_SET_COLOR'), $_device->getLink_id())) > 0) {
			$return['traits'][] = 'action.devices.traits.ColorSpectrum';
			if (!isset($return['attributes'])) {
				$return['attributes'] = array();
			}
			$return['attributes']['colorModel'] = 'RGB';
		}
		if (count(cmd::byGenericType(self::$_STATE, $_device->getLink_id())) > 0) {
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
								$cmd->execCmd(array('slider' => 100));
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
								$cmd->execCmd(array('slider' => 0));
								$return = array('status' => 'SUCCESS');
							}
						}
						break;
					case 'action.devices.commands.ColorAbsolute':
						$cmd = cmd::byGenericType('LIGHT_SET_COLOR', $_device->getLink_id(), true);
						if (is_object($cmd)) {
							$cmd->execCmd(array('color' => '#' . str_pad(dechex($execution['params']['color']['spectrumRGB']), 6, '0', STR_PAD_LEFT)));
							$return = array('status' => 'SUCCESS');
						}
						break;
					case 'action.devices.commands.BrightnessAbsolute':
						$cmd = cmd::byGenericType('LIGHT_SLIDER', $_device->getLink_id(), true);
						if (is_object($cmd)) {
							$value = $cmd->getConfiguration('minValue', 0) + ($execution['params']['brightness'] / 100 * ($cmd->getConfiguration('maxValue', 100) - $cmd->getConfiguration('minValue', 0)));
							$cmd->execCmd(array('slider' => $value));
							$return = array('status' => 'SUCCESS');
						}
						break;
				}
			} catch (Exception $e) {
				$return = array('status' => 'ERROR');
			}
		}
		$return['states'] = self::getState($_device);
		return $return;
	}

	public static function getState($_device) {
		$return = array();
		$cmds = cmd::byGenericType(array_merge(self::$_STATE, array('LIGHT_COLOR')), $_device->getLink_id());
		if ($cmds == null) {
			return $return;
		}
		foreach ($cmds as $cmd) {
			$value = $cmd->execCmd();
			if ($cmd->getSubtype() == 'numeric') {
				$return['brightness'] = $value / $cmd->getConfiguration('maxValue', 100) * 100;
				$return['on'] = ($return['brightness'] > 0);
			}
			if ($cmd->getSubtype() == 'binary') {
				$return['on'] = boolval($value);
			}
			if ($cmd->getSubtype() == 'string') {
				$return['color'] = array(
					'spectrumRGB' => hexdec(str_replace('#', '', $value)),
				);
			}
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}