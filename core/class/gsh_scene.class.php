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
		$return['id'] = 'scene::' . $_device->getId();
		$return['type'] = $_device->getType();
		$return['name'] = array('name' => $_device->getOptions('name'), 'defaultNames' => array(), 'nicknames' => array());
		$return['traits'] = array('action.devices.traits.Scene');
		$return['willReportState'] = false;
		$return['attributes'] = array('sceneReversible' => (count($_device->getOptions('outAction')) > 0));
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
					case 'action.devices.commands.ActivateScene':
						if ($execution['params']['deactivate']) {
							self::doAction($_device, 'outAction');
						} else {
							self::doAction($_device, 'inAction');
						}
						$return = array('status' => 'SUCCESS');
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
		$return['online'] = true;
		return $return;
	}

	public function doAction($_device, $_action) {
		if (!is_array($_device->getOptions($_action))) {
			return;
		}
		foreach ($_device->getOptions($_action) as $action) {
			try {
				$options = array();
				if (isset($action['options'])) {
					$options = $action['options'];
				}
				scenarioExpression::createAndExec('action', $action['cmd'], $options);
			} catch (Exception $e) {
				log::add('gsh', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
			}
		}
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}