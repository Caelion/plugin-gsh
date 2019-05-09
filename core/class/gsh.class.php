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
require_once dirname(__FILE__) . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;

include_file('core', 'gsh_light', 'class', 'gsh');
include_file('core', 'gsh_thermostat', 'class', 'gsh');
include_file('core', 'gsh_outlet', 'class', 'gsh');
include_file('core', 'gsh_camera', 'class', 'gsh');
include_file('core', 'gsh_scene', 'class', 'gsh');
include_file('core', 'gsh_blinds', 'class', 'gsh');
include_file('core', 'gsh_sensor', 'class', 'gsh');
include_file('core', 'gsh_door', 'class', 'gsh');
include_file('core', 'gsh_securitysystem', 'class', 'gsh');
include_file('core', 'gsh_lock', 'class', 'gsh');
include_file('core', 'gsh_tv', 'class', 'gsh');
include_file('core', 'gsh_speaker', 'class', 'gsh');

class gsh extends eqLogic {
	
	/*     * *************************Attributs****************************** */
	
	public static $_supportedType = array(
		'action.devices.types.LIGHT' => array('class' => 'gsh_light', 'name' => 'Lumière'),
		'action.devices.types.THERMOSTAT' => array('class' => 'gsh_thermostat', 'name' => 'Thermostat'),
		'action.devices.types.OUTLET' => array('class' => 'gsh_outlet', 'name' => 'Prise'),
		'action.devices.types.CAMERA' => array('class' => 'gsh_camera', 'name' => 'Caméra'),
		'action.devices.types.SCENE' => array('class' => 'gsh_scene', 'name' => 'Scene'),
		'action.devices.types.BLINDS' => array('class' => 'gsh_blinds', 'name' => 'Store'),
		'action.devices.types.SHUTTER' => array('class' => 'gsh_blinds', 'name' => 'Volet'),
		'action.devices.types.CURTAIN' => array('class' => 'gsh_curtain', 'name' => 'Rideaux'),
		'action.devices.types.VALVE' => array('class' => 'gsh_valve', 'name' => 'Vanne'),
		'action.devices.types.SENSOR' => array('class' => 'gsh_sensor', 'name' => 'Capteur'),
		'action.devices.types.WINDOW' => array('class' => 'gsh_door', 'name' => 'Fenêtre'),
		'action.devices.types.DOOR' => array('class' => 'gsh_door', 'name' => 'Porte'),
		'action.devices.types.GARAGE' => array('class' => 'gsh_door', 'name' => 'Porte Garage'),
		'action.devices.types.SECURITYSYSTEM' => array('class' => 'gsh_securitysystem', 'name' => 'Alarme'),
		'action.devices.types.LOCK' => array('class' => 'gsh_lock', 'name' => 'Verrou'),
		'action.devices.types.TV' => array('class' => 'gsh_tv', 'name' => 'TV'),
		'action.devices.types.SPEAKER' => array('class' => 'gsh_speaker', 'name' => 'Enceinte'),
	);
	
	/*     * ***********************Methode static*************************** */
	
	public static function cronDaily() {
		shell_exec('sudo rm -rf ' . __DIR__ . '/../../data/*');
	}
	
	public static function cronHourly() {
		$processes = array_merge(system::ps('stream2chromecast.py'), system::ps('avconv -i'));
		foreach ($processes as $process) {
			$duration = shell_exec('ps -p ' . $process['pid'] . ' -o etimes -h');
			if ($duration < 3600) {
				continue;
			}
			system::kill($process['pid']);
		}
	}
	
	public static function sendJeedomConfig() {
		$market = repo_market::getJsonRpc();
		if (!$market->sendRequest('gsh::configGsh', array('gsh::apikey' => jeedom::getApiKey('gsh'), 'gsh::url' => network::getNetworkAccess('external')))) {
			throw new Exception($market->getError(), $market->getErrorCode());
		}
	}
	
	public static function sendDevices() {
		if (config::byKey('mode', 'gsh') == 'jeedom') {
			$request_http = new com_http('https://api-gh.jeedom.com/jeedom/sync');
			$request_http->setPost(http_build_query(array(
				'apikey' =>  jeedom::getApiKey('gsh'),
				'data' => json_encode(self::sync())
			)));
			$result = $request_http->exec(30);
		} else {
			$request_http = new com_http('https://homegraph.googleapis.com/v1/devices:requestSync?key=' . config::byKey('gshs::googleapikey', 'gsh'));
			$request_http->setPost(json_encode(array('agent_user_id' => config::byKey('gshs::useragent', 'gsh'),'async' => true)));
			$request_http->setHeader(array('Content-Type: application/json'));
			$result = is_json($request_http->exec(30), true);
			if (isset($result['error'])) {
				throw new Exception(json_encode($result));
			}
		}
	}
	
	public static function sync() {
		$return = array();
		$devices = gsh_devices::all(true);
		foreach ($devices as $device) {
			$info = $device->buildDevice();
			if (!is_array($info) || count($info) == 0) {
				$device->setOptions('configState', 'NOK');
				$device->save();
				continue;
			}
			$return[] = $info;
			$device->setOptions('configState', 'OK');
			$device->setOptions('build', json_encode($info));
			$device->save();
			if (isset($info['willReportState']) && $info['willReportState']) {
				$device->addListner();
			} else {
				$device->removeListner();
			}
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
				if($device->getOptions('challenge') == 'ackNeeded' && (!isset($command['execution'][0]['challenge']) || !isset($command['execution'][0]['challenge']['ack']) || !$command['execution'][0]['challenge']['ack'])){
					$result = array_merge($result,array(
						'status' => 'ERROR',
						'errorCode' => 'challengeNeeded',
						'challengeNeeded' => array(
							'type' => 'ackNeeded'
						)));
					}else if($device->getOptions('challenge') == 'pinNeeded' && (!isset($command['execution'][0]['challenge']) || !isset($command['execution'][0]['challenge']['pin']) ||  $device->getOptions('challenge_pin')  == '' || $device->getOptions('challenge_pin') != $command['execution'][0]['challenge']['pin'])){
						if( $device->getOptions('challenge_pin') == ''){
							$result = array_merge($result,array(
								'status' => 'ERROR',
								'errorCode' => 'challengeFailedNotSetup '
							));
						}else{
							$result = array_merge($result,array(
								'status' => 'ERROR',
								'errorCode' => 'challengeNeeded',
								'challengeNeeded' => array(
									'type' => 'pinNeeded'
								)));
							}
						}else{
							$result = array_merge($result, $device->exec($command['execution'], $infos));
						}
						$return['commands'][] = $result;
					}
				}
				return $return;
			}
			
			public static function query($_data) {
				$return = array('devices' => array());
				foreach ($_data['devices'] as $infos) {
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
					$return['devices'][$infos['id']] = $device->query($infos);
				}
				return $return;
			}
			
			public static function reportState($_options) {
				$cmd = cmd::byId($_options['event_id']);
				if (!is_object($cmd)) {
					return;
				}
				$device = gsh_devices::byLinkTypeLinkId('eqLogic', $cmd->getEqLogic_id());
				if (!is_object($device)) {
					return;
				}
				$return = array(
					'requestId' => config::genKey(),
					'agentUserId' => config::byKey('gshs::useragent', 'gsh'),
					'payload' => array(
						'devices' => array(
							'states' => array(
								$cmd->getEqLogic_id() => $device->query(json_decode($device->getOptions('build'), true)),
							),
						),
					),
				);
				if ($device->getCache('lastState') == json_encode($return['payload']['devices']['states'][$cmd->getEqLogic_id()])) {
					return;
				}
				$device->setCache('lastState', json_encode($return['payload']['devices']['states'][$cmd->getEqLogic_id()]));
				log::add('gsh', 'debug', 'Report state : ' . json_encode($return));
				if (config::byKey('mode', 'gsh') == 'jeedom') {
					$request_http = new com_http('https://api-gh.jeedom.com/jeedom/reportState');
					$request_http->setPost(http_build_query(array(
						'apikey' =>  jeedom::getApiKey('gsh'),
						'data' => json_encode($return)
					)));
					$result = $request_http->exec(30);
				} else {
					$request_http = new com_http('https://homegraph.googleapis.com/v1/devices:reportStateAndNotification');
					$request_http->setHeader(array(
						'Authorization: Bearer ' . self::jwt(),
						'X-GFE-SSL: yes',
						'Content-Type: application/json',
					));
					$request_http->setPost(json_encode($return));
					$result = $request_http->exec(30);
					
					if (!is_json($result)) {
						throw new Exception($result);
					}
					$result = json_decode($result, true);
					if (isset($result['error'])) {
						throw new Exception($result['error']['message'] . ' => ' . json_encode($return));
					}
				}
			}
			
			public static function jwt() {
				$prevToken = cache::byKey('gsh::jwt:token');
				if ($prevToken->getValue() != '' && is_array($prevToken->getValue())) {
					$token = $prevToken->getValue();
					if (isset($token['token']) && isset($token['exp']) && $token['exp'] > (strtotime('now') + 60)) {
						return $token['token'];
					}
				}
				$now = strtotime('now');
				$token = array(
					'iat' => $now,
					'exp' => $now + 3600,
					'scope' => 'https://www.googleapis.com/auth/homegraph',
					'iss' => config::byKey('gshs::jwtclientmail', 'gsh'),
					'aud' => 'https://accounts.google.com/o/oauth2/token',
				);
				$jwt = JWT::encode($token, str_replace('\n', "\n", config::byKey('gshs::jwtprivkey', 'gsh')), 'RS256');
				$request_http = new com_http('https://accounts.google.com/o/oauth2/token');
				$request_http->setHeader(array('content-type : application/x-www-form-urlencoded'));
				$request_http->setPost('grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=' . $jwt);
				$result = is_json($request_http->exec(30), array());
				if (!isset($result['access_token'])) {
					throw new Exception(__('JWT aucun token : ', __FILE__) . json_encode($result));
				}
				cache::set('gsh::jwt:token', array('token' => $result['access_token'], 'exp' => $token['exp']));
				return $result['access_token'];
			}
			
			public static function buildDialogflowResponse($_data, $_response) {
				$return = array();
				$return['fulfillmentText'] = $_response['reply'];
				if (isset($_response['file'])) {
					$carroussel = array('items' => array());
					$urls = array();
					$output_dir = __DIR__ . '/../../data/';
					$items = array(
						array(
							'simpleResponse' => array(
								'textToSpeech' => $_response['reply'],
							),
						),
					);
					foreach ($_response['file'] as $file) {
						$filename = config::genKey();
						copy($file, $output_dir . $filename . '.' . pathinfo($file, PATHINFO_EXTENSION));
						$urls[] = network::getNetworkAccess('external') . '/plugins/gsh/data/' . $filename . '.' . pathinfo($file, PATHINFO_EXTENSION);
					}
					
					if (count($urls) == 1) {
						$items[] = array(
							'basicCard' => array(
								"title" => $_response['reply'],
								"image" => array(
									"url" => $urls[0],
									"accessibilityText" => $_response['reply'],
								),
								"buttons" => array(
									array(
										"title" => 'Jeedom',
										"openUrlAction" => array(
											"url" => network::getNetworkAccess('external'),
										),
									),
								),
							),
						);
					} else {
						foreach ($urls as $url) {
							$carroussel['items'][] = array(
								"title" => $_response['reply'],
								"image" => array(
									"url" => $url,
									"accessibilityText" => $_response['reply'],
								),
								"openUrlAction" => array(
									"url" => network::getNetworkAccess('external'),
								),
							);
						}
						$items[] = array('carouselBrowse' => $carroussel);
					}
					
					$return['payload'] = array(
						"google" => array(
							'expectUserResponse' => true,
							'richResponse' => array(
								'items' => $items,
							),
						),
					);
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
			private $_cache = null;
			private $_link = null;
			private $_cmds = null;
			private $_changed = false;
			
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
			
			/*     * ***********************Methode utils*************************** */
			
			public function traitsModeBuildSetting($_name,$_synonyms,$_lang = 'fr'){
				return array(
					'setting_name'=> $_name,
					'setting_values' => array(array('setting_synonym'=> $_synonyms,'lang'=> $_lang))
				);
			}
			
			/*     * *********************Methode d'instance************************* */
			
			public function preSave() {
				if ($this->getEnable() == 0) {
					$this->setOptions('configState', '');
					$this->removeListner();
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
				$result = $class::exec($this, $_execution, $_infos);
				return $result;
			}
			
			public function query($_infos) {
				if (!isset(gsh::$_supportedType[$this->getType()])) {
					return;
				}
				$class = gsh::$_supportedType[$this->getType()]['class'];
				if (!class_exists($class)) {
					return array();
				}
				$result = $class::query($this, $_infos);
				if (isset($result['status']) && $result['status'] == 'SUCCESS') {
					$this->setCache('lastState', json_encode($result['state']));
				}
				return $result;
			}
			
			public function getPseudo() {
				$eqLogic = $this->getLink();
				$pseudo = array(trim($eqLogic->getName()), trim($eqLogic->getName()) . 's');
				if ($this->getOptions('pseudo') != '') {
					$pseudo = explode(',', $this->getOptions('pseudo'));
				}
				return $pseudo;
			}
			
			public function addListner() {
				if ($this->getLink_type() != 'eqLogic') {
					return;
				}
				$eqLogic = $this->getLink();
				$listener = listener::byClassAndFunction('gsh', 'reportState', array('eqLogic_id' => intval($eqLogic->getId())));
				if (!is_object($listener)) {
					$listener = new listener();
				}
				$listener->setClass('gsh');
				$listener->setFunction('reportState');
				$listener->setOption(array('eqLogic_id' => intval($eqLogic->getId())));
				$listener->emptyEvent();
				foreach ($eqLogic->getCmd('info') as $cmd) {
					$listener->addEvent($cmd->getId());
				}
				$listener->save();
			}
			
			public function removeListner() {
				if ($this->getLink_type() != 'eqLogic') {
					return;
				}
				$eqLogic = $this->getLink();
				$listener = listener::byClassAndFunction('gsh', 'reportState', array('eqLogic_id' => intval($eqLogic->getId())));
				if (is_object($listener)) {
					$listener->remove();
				}
			}
			
			/*     * **********************Getteur Setteur*************************** */
			public function getId() {
				return $this->id;
			}
			
			public function setId($_id) {
				$this->_changed = utils::attrChanged($this->_changed,$this->id,$_id);
				$this->id = $_id;
			}
			
			public function getEnable() {
				return $this->enable;
			}
			
			public function setEnable($_enable) {
				$this->_changed = utils::attrChanged($this->_changed,$this->enable,$_enable);
				$this->enable = $_enable;
			}
			
			public function getlink_type() {
				return $this->link_type;
			}
			
			public function setLink_type($_link_type) {
				$this->_changed = utils::attrChanged($this->_changed,$this->link_type,$_link_type);
				$this->link_type = $_link_type;
			}
			
			public function getLink_id() {
				return $this->link_id;
			}
			
			public function setLink_id($_link_id) {
				$this->_changed = utils::attrChanged($this->_changed,$this->link_id,$_link_id);
				$this->link_id = $_link_id;
			}
			
			public function getType() {
				return $this->type;
			}
			
			public function setType($_type) {
				$this->_changed = utils::attrChanged($this->_changed,$this->type,$_type);
				$this->type = $_type;
			}
			
			public function getOptions($_key = '', $_default = '') {
				return utils::getJsonAttr($this->options, $_key, $_default);
			}
			
			public function setOptions($_key, $_value) {
				$options = utils::setJsonAttr($this->options, $_key, $_value);
				$this->_changed = utils::attrChanged($this->_changed,$this->options,$options);
				$this->options = $options;
			}
			
			public function getCache($_key = '', $_default = '') {
				if ($this->_cache == null) {
					$this->_cache = cache::byKey('gshDeviceCache' . $this->getId())->getValue();
				}
				return utils::getJsonAttr($this->_cache, $_key, $_default);
			}
			
			public function setCache($_key, $_value = null) {
				$this->_cache = utils::setJsonAttr(cache::byKey('gshDeviceCache' . $this->getId())->getValue(), $_key, $_value);
				cache::set('gshDeviceCache' . $this->getId(), $this->_cache);
			}
			
			public function getChanged() {
				return $this->_changed;
			}
			
			public function setChanged($_changed) {
				$this->_changed = $_changed;
				return $this;
			}
		}
		