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

class gsh extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function generateConfiguration() {
		$return = array();
		$return["devPortSmartHome"] = config::byKey('gshs::port', 'gsh');
		$return["smartHomeProviderGoogleClientId"] = config::byKey('gshs::clientId', 'gsh');
		$return["smartHomeProvideGoogleClientSecret"] = config::byKey('gshs::clientSecret', 'gsh');
		$return["smartHomeProviderApiKey"] = config::byKey('gshs::googleapikey', 'gsh');
		$return["masterkey"] = config::byKey('gshs::masterkey', 'gsh');
		$return["jeedomTimeout"] = config::byKey('gshs::timeout', 'gsh');
		$return["url"] = config::byKey('gshs::url', 'gsh');
		return $return;
	}

	public static function generateUserConf() {
		$return = array(
			"tokens" => array(
				config::byKey('gshs::token', 'gsh') => array(
					"uid" => config::byKey('gshs::userid', 'gsh'),
					"accessToken" => config::byKey('gshs::token', 'gsh'),
					"refreshToken" => config::byKey('gshs::token', 'gsh'),
					"userId" => config::byKey('gshs::userid', 'gsh'),
				),
			),
			"users" => array(
				config::byKey('gshs::userid', 'gsh') => array(
					"uid" => config::byKey('gshs::userid', 'gsh'),
					"name" => config::byKey('gshs::username', 'gsh'),
					"password" => config::byKey('gshs::password', 'gsh'),
					"tokens" => array(config::byKey('gshs::token', 'gsh')),
					"url" => network::getNetworkAccess('external'),
					"apikey" => jeedom::getApiKey('gsh'),
				),
			),
			"usernames" => array(
				config::byKey('gshs::username', 'gsh') => config::byKey('gshs::userid', 'gsh'),
			),
		);
		return $return;
	}

	public static function sendUsers() {
		$request_http = new com_http(trim(config::byKey('gshs::url', 'gsh')) . '/jeedom/sync/users');
		$post = array(
			'masterkey' => config::byKey('gshs::masterkey', 'gsh'),
			'data' => json_encode(self::generateUserConf(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		);
		$request_http->setPost(http_build_query($post));
		$result = $request_http->exec(60);
		if (!is_json($result)) {
			throw new Exception($result);
		}
		$result = json_decode($result, true);
		if (!isset($result['success']) || !$result['success']) {
			throw new Exception($result);
		}
	}

	public static function sendDevices() {
		$request_http = new com_http(trim(config::byKey('gshs::url', 'gsh')) . '/jeedom/sync/devices');
		$post = array(
			'masterkey' => config::byKey('gshs::masterkey', 'gsh'),
			'userId' => config::byKey('gshs::userid', 'gsh'),
			'data' => json_encode(self::sync(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		);
		$request_http->setPost(http_build_query($post));
		$result = $request_http->exec(60);
		if (!is_json($result)) {
			throw new Exception($result);
		}
		$result = json_decode($result, true);
		if (!isset($result['success']) || !$result['success']) {
			throw new Exception($result);
		}
	}

	public static function sync() {
		$return = array();
		$eqLogicSyncs = config::byKey('syncEqLogic', 'gsh');
		foreach ($eqLogicSyncs as $eqLogicSync) {
			if ($eqLogicSync['enable'] == 0) {
				continue;
			}
			$eqLogic = eqLogic::byId($eqLogicSync['id']);
			if (!is_object($eqLogic)) {
				continue;
			}
			$info = self::buildEqlogic($eqLogic, $eqLogicSync);
			if (count($info) == 0) {
				continue;
			}
			$return[] = $info;
		}
		return $return;
	}

	public static function exec($_data) {
		foreach ($_data['data']['commands'] as $command) {
			return array('status' => self::execCmd($command));
		}
	}

	public static function execCmd($_command) {
		log::add('gsh', 'debug', print_r($_command, true));
		$eqLogic = eqLogic::byId($_command['devices'][0]['id']);
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		$sync = self::getSync($eqLogic->getId());
		if (count($sync) == 0) {
			return 'deviceNotFound';
		}
		if ($sync['enable'] == 0) {
			return 'deviceOffline';
		}
		switch ($sync['type']) {
			case 'action.devices.types.LIGHT':
				$cmds = $eqLogic->getCmd();
				foreach ($_command['execution'] as $execution) {
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
				break;
		}
		return 'notSupported';
	}

	public static function getSync($_id) {
		$eqLogicSyncs = config::byKey('syncEqLogic', 'gsh');
		if (!isset($eqLogicSyncs[$_id])) {
			return array();
		}
		return $eqLogicSyncs[$_id];

	}

	public static function query() {

	}

	public static function buildEqlogic($_eqLogic, $_sync) {
		$return = array();
		$return['id'] = $_eqLogic->getId();
		$return['type'] = $_sync['type'];
		$return['name'] = array('name' => $_eqLogic->getName(), 'nicknames' => array($_eqLogic->getHumanName()));
		switch ($_sync['type']) {
			case 'action.devices.types.LIGHT':
				$return['traits'] = array();
				$return['willReportState'] = false;
				foreach ($_eqLogic->getCmd() as $cmd) {
					if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_ON', 'LIGHT_OFF'))) {
						$return['traits'][] = 'action.devices.traits.OnOff';
					}
					if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_COLOR_TEMP'))) {
						$return['traits'][] = 'action.devices.traits.ColorTemperature';
					}
					if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_SLIDER'))) {
						$return['traits'][] = 'action.devices.traits.Brightness';
					}
					if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_SET_COLOR'))) {
						$return['traits'][] = 'action.devices.traits.ColorSpectrum';
					}
					if (in_array($cmd->getDisplay('generic_type'), array('LIGHT_STATE'))) {
						$return['willReportState'] = true;
					}
				}
				if (count($return['traits']) == 0) {
					return array();
				}
				break;
			default:
				return array();
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */
}

class gshCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */
}
