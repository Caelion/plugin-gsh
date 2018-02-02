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
			if (!isset($return['attributes'])) {
				$return['attributes'] = array();
			}
			$return['attributes']['colorModel'] = 'RGB';
		}
		if ($_device->getCmdByGenericType(array('LIGHT_STATE')) != null) {
			$return['willReportState'] = true;
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		return $return;
	}

	public static function query($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return array('status' => 'ERROR');
		}
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
					case 'action.devices.commands.ColorAbsolute':
						$cmd = $_device->getCmdByGenericType(array('LIGHT_SET_COLOR'));
						if (is_object($cmd)) {
							$cmd->execCmd(array('color' => '#' . str_pad(dechex($execution['params']['color']['spectrumRGB']), 6, '0', STR_PAD_LEFT)));
							$return = array('status' => 'SUCCESS');
						}
						break;
					case 'action.devices.commands.BrightnessAbsolute':
						$cmd = $_device->getCmdByGenericType(array('LIGHT_SLIDER'));
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
		$cmds = $_device->getCmdByGenericType(array('LIGHT_STATE', 'LIGHT_STATE_BOOL', 'LIGHT_SET_COLOR_TEMP'));
		if ($cmds == null) {
			return $return;
		}
		if (!is_array($cmds)) {
			$cmds = array($cmds);
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