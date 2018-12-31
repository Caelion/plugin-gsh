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

class gsh_door {

	/*     * *************************Attributs****************************** */

	private static $_STATE = array('OPENING', 'OPENING_WINDOW');

	/*     * ***********************Methode static*************************** */

	public static function buildDevice($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		if ($eqLogic->getIsEnable() == 0) {
			return 'deviceNotFound';
		}
		$return = array();
		$return['id'] = $eqLogic->getId();
		$return['type'] = $_device->getType();
		if (is_object($eqLogic->getObject())) {
			$return['roomHint'] = $eqLogic->getObject()->getName();
		}
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => $_device->getPseudo(), 'defaultNames' => $_device->getPseudo());
		$return['customData'] = array();
		$return['willReportState'] = ($_device->getOptions('reportState') == 1);
		$return['traits'] = array();
		$modes = '';
		foreach ($eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), self::$_STATE)) {
				$return['customData']['cmd_get_state'] = $cmd->getId();
				if (!in_array('action.devices.traits.OpenClose', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.OpenClose';
				}
			}
		}
		if (count($return['traits']) == 0) {
			return array();
		}
		return $return;
	}

	public static function query($_device, $_infos) {
		return self::getState($_device, $_infos);
	}

	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
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
		if ($cmd->getSubtype() == 'numeric') {
			$return['on'] = ($value > 0);
		} else if ($cmd->getSubtype() == 'binary') {
			$return['on'] = boolval($value);
			if ($cmd->getDisplay('invertBinary') == 0) {
				$return['on'] = ($return['on']) ? false : true;
			}
		}
		if (in_array($cmd->getGeneric_type(), array('FLAP_BSO_STATE', 'FLAP_STATE'))) {
			$return['on'] = ($return['on']) ? false : true;
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}
