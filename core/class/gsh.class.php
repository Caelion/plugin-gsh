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
include_file('core', 'gsh_light', 'class', 'gsh');
include_file('core', 'gsh_thermostat', 'class', 'gsh');
include_file('core', 'gsh_outlet', 'class', 'gsh');
include_file('core', 'gsh_camera', 'class', 'gsh');
include_file('core', 'gsh_scene', 'class', 'gsh');

class gsh extends eqLogic {

	/*     * *************************Attributs****************************** */

	public static $_supportedType = array(
		'action.devices.types.LIGHT' => array('class' => 'gsh_light', 'name' => 'Lumière'),
		'action.devices.types.THERMOSTAT' => array('class' => 'gsh_thermostat', 'name' => 'Thermostat'),
		'action.devices.types.OUTLET' => array('class' => 'gsh_outlet', 'name' => 'Bistable (Prise/Volet...)'),
		'action.devices.types.CAMERA' => array('class' => 'gsh_camera', 'name' => 'Caméra'),
		'action.devices.types.SCENE' => array('class' => 'gsh_scene', 'name' => 'Scene'),
	);

	/*     * ***********************Methode static*************************** */

	public static function generateConfiguration() {
		$return = array(
			"devPortSmartHome" => config::byKey('gshs::port', 'gsh'),
			"smartHomeProviderGoogleClientId" => config::byKey('gshs::clientId', 'gsh'),
			"smartHomeProvideGoogleClientSecret" => config::byKey('gshs::clientSecret', 'gsh'),
			"smartHomeProviderApiKey" => config::byKey('gshs::googleapikey', 'gsh'),
			"masterkey" => config::byKey('gshs::masterkey', 'gsh'),
			"jeedomTimeout" => config::byKey('gshs::timeout', 'gsh'),
			"url" => config::byKey('gshs::url', 'gsh'),
		);
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
					"password" => sha1(config::byKey('gshs::password', 'gsh')),
					"tokens" => array(config::byKey('gshs::token', 'gsh')),
					"url" => network::getNetworkAccess(config::byKey('gshs::jeedomnetwork', 'gsh', 'internal')),
					"apikey" => jeedom::getApiKey('gsh'),
				),
			),
			"usernames" => array(
				config::byKey('gshs::username', 'gsh') => config::byKey('gshs::userid', 'gsh'),
			),
		);
		return $return;
	}

	public static function sendDevices() {
		if (config::byKey('mode', 'gsh') == 'jeedom') {
			$market = repo_market::getJsonRpc();
			if (!$market->sendRequest('gsh::sync', array('devices' => self::sync()))) {
				throw new Exception($market->getError(), $market->getErrorCode());
			}
		} else {
			$request_http = new com_http(trim(config::byKey('gshs::url', 'gsh')) . '/jeedom/sync/devices');
			$post = array(
				'masterkey' => config::byKey('gshs::masterkey', 'gsh'),
				'userId' => config::byKey('gshs::userid', 'gsh'),
				'data' => json_encode(self::sync(), JSON_UNESCAPED_UNICODE),
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
	}

	public static function sync() {
		$return = array();
		$devices = gsh_devices::all(true);
		foreach ($devices as $device) {
			$info = $device->buildDevice();
			if (count($info) == 0) {
				$device->setOptions('configState', 'NOK');
				$device->save();
				continue;
			}
			$return[] = $info;
			$device->setOptions('configState', 'OK');
			$device->save();
		}
		return $return;
	}

	public static function exec($_data) {
		$return = array('commands' => array());
		foreach ($_data['data']['commands'] as $command) {
			foreach ($command['devices'] as $infos) {
				if (strpos($infos['id'], 'scene::') !== false) {
					$device = gsh_devices::byId(str_replace('scene::', '', $infos['id']));
				} else {
					$device = gsh_devices::byLinkTypeLinkId('eqLogic', $infos['id']);
				}
				$result = array('ids' => array($infos['id']));
				if (!is_object($device)) {
					$result['status'] = 'ERROR';
					$return['commands'][] = $result;
					continue;
				}
				if ($device->getEnable() == 0) {
					$result['status'] = 'OFFLINE';
					$return['commands'][] = $result;
					continue;
				}
				$result = array_merge($result, $device->exec($command['execution'], $infos));
				$return['commands'][] = $result;
			}
		}
		return $return;
	}

	public static function query($_data) {
		$return = array('devices' => array());
		foreach ($_data['commands']['devices'] as $infos) {
			$return['devices'][$infos['id']] = array();
			$device = gsh_devices::byLinkTypeLinkId('eqLogic', $infos['id']);
			if (!is_object($device)) {
				$return['devices'][$infos['id']] = array('status' => 'ERROR');
				continue;
			}
			if ($device->getEnable() == 0) {
				$return['devices'][$infos['id']] = array('status' => 'OFFLINE');
				continue;
			}
			$return['devices'][$infos['id']] = $device->query();
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

class gsh_devices {
	/*     * *************************Attributs****************************** */

	private $id;
	private $enable;
	private $link_type;
	private $link_id;
	private $type;
	private $options;
	private $_link = null;
	private $_cmds = null;

	/*     * ***********************Methode static*************************** */

	public static function all($_onlyEnable = false) {
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM gsh_devices';
		if ($_onlyEnable) {
			$sql .= ' WHERE enable=1';
		}
		return DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
	}

	public static function byId($_id) {
		$values = array(
			'id' => $_id,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM gsh_devices
		WHERE id=:id';
		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
	}

	public static function byLinkTypeLinkId($_link_type, $_link_id) {
		$values = array(
			'link_type' => $_link_type,
			'link_id' => $_link_id,
		);
		$sql = 'SELECT ' . DB::buildField(__CLASS__) . '
		FROM gsh_devices
		WHERE link_type=:link_type
		AND link_id=:link_id';
		return DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
	}

	/*     * *********************Methode d'instance************************* */

	public function preSave() {
		if ($this->getEnable() == 0) {
			$this->setOptions('configState', '');
		}
	}

	public function save() {
		return DB::save($this);
	}

	public function remove() {
		DB::remove($this);
	}

	public function getLink() {
		if ($this->_link != null) {
			return $this->_link;
		}
		if ($this->getLink_type() == 'eqLogic') {
			$this->_link = eqLogic::byId($this->getLink_id());
		}
		return $this->_link;
	}

	public function buildDevice() {
		if (!isset(gsh::$_supportedType[$this->getType()])) {
			return array();
		}
		$class = gsh::$_supportedType[$this->getType()]['class'];
		if (!class_exists($class)) {
			return array();
		}
		return $class::buildDevice($this);
	}

	public function exec($_execution, $_infos) {
		if (!isset(gsh::$_supportedType[$this->getType()])) {
			return;
		}
		$class = gsh::$_supportedType[$this->getType()]['class'];
		if (!class_exists($class)) {
			return array();
		}
		return $class::exec($this, $_execution, $_infos);
	}

	public function query() {
		if (!isset(gsh::$_supportedType[$this->getType()])) {
			return;
		}
		$class = gsh::$_supportedType[$this->getType()]['class'];
		if (!class_exists($class)) {
			return array();
		}
		return $class::query($this);
	}

	public function getPseudo() {
		$eqLogic = $this->getLink();
		$pseudo = array(trim($eqLogic->getName()), trim($eqLogic->getName()) . 's');
		if ($this->getOptions('pseudo') != '') {
			$pseudo = array_merge(explode(',', $this->getOptions('pseudo')), $pseudo);
		}
		return $pseudo;
	}

	/*     * **********************Getteur Setteur*************************** */
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getEnable() {
		return $this->enable;
	}

	public function setEnable($enable) {
		$this->enable = $enable;
	}

	public function getlink_type() {
		return $this->link_type;
	}

	public function setLink_type($link_type) {
		$this->link_type = $link_type;
	}

	public function getLink_id() {
		return $this->link_id;
	}

	public function setLink_id($link_id) {
		$this->link_id = $link_id;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getOptions($_key = '', $_default = '') {
		return utils::getJsonAttr($this->options, $_key, $_default);
	}

	public function setOptions($_key, $_value) {
		$this->options = utils::setJsonAttr($this->options, $_key, $_value);
	}
}