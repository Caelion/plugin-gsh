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

include_file('core', 'gsh_scene', 'class', 'gsh');
include_file('core', 'gsh_camera', 'class', 'gsh');
include_file('core', 'gsh_ArmDisarm', 'class', 'gsh');
include_file('core', 'gsh_Brightness', 'class', 'gsh');
include_file('core', 'gsh_Channel', 'class', 'gsh');
include_file('core', 'gsh_ColorSetting', 'class', 'gsh');
include_file('core', 'gsh_LockUnlock', 'class', 'gsh');
include_file('core', 'gsh_MediaState', 'class', 'gsh');
include_file('core', 'gsh_Modes', 'class', 'gsh');
include_file('core', 'gsh_OnOff', 'class', 'gsh');
include_file('core', 'gsh_OpenClose', 'class', 'gsh');
include_file('core', 'gsh_SensorState', 'class', 'gsh');
include_file('core', 'gsh_TemperatureSetting', 'class', 'gsh');
include_file('core', 'gsh_Volume', 'class', 'gsh');
include_file('core', 'gsh_FanSpeed', 'class', 'gsh');
include_file('core', 'gsh_StartStop', 'class', 'gsh');
include_file('core', 'gsh_Reboot', 'class', 'gsh');
include_file('core', 'gsh_Rotation', 'class', 'gsh');
include_file('core', 'gsh_TemperatureControl', 'class', 'gsh');
include_file('core', 'gsh_Dock', 'class', 'gsh');

class gsh extends eqLogic {
	
	/*     * *************************Attributs****************************** */
	
	public static function getSupportedType(){
		return array(
			'action.devices.types.LIGHT' => array('name' => __('Lumière',__FILE__) ,'traits' =>array('Brightness','ColorSetting','OnOff')),
			'action.devices.types.THERMOSTAT' => array('name' => __('Thermostat',__FILE__) ,'traits' =>array('TemperatureSetting')),
			'action.devices.types.OUTLET' => array('name' => __('Prise',__FILE__) ,'traits' =>array('OnOff')),
			'action.devices.types.SWITCH' => array('name' => __('Interrupteur',__FILE__) ,'traits' =>array('OnOff')),
			'action.devices.types.CAMERA' => array('name' => __('Caméra',__FILE__),'class' => 'gsh_camera'),
			'action.devices.types.SCENE' => array('name' => __('Scene',__FILE__),'class' => 'gsh_scene'),
			'action.devices.types.BLINDS' => array('name' => __('Store',__FILE__) ,'traits' =>array('Modes','OpenClose')),
			'action.devices.types.SHUTTER' => array('name' => __('Volet',__FILE__) ,'traits' =>array('Modes','OpenClose','Rotation')),
			'action.devices.types.CURTAIN' => array('name' => __('Rideaux',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.VALVE' => array('name' => __('Vanne',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.SENSOR' => array('name' => __('Capteur',__FILE__) ,'traits' =>array('OnOff','SensorState')),
			'action.devices.types.WINDOW' => array('name' => __('Fenêtre',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.DOOR' => array('name' => __('Porte',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.GARAGE' => array('name' => __('Porte Garage',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.SECURITYSYSTEM' => array('name' => __('Alarme',__FILE__) ,'traits' =>array('ArmDisarm','StatusReport')),
			'action.devices.types.LOCK' => array('name' => __('Verrou',__FILE__) ,'traits' =>array('LockUnlock')),
			'action.devices.types.TV' => array('name' => __('TV',__FILE__) ,'traits' =>array('OnOff','MediaState','InputSelector','AppSelector','TransportControl','Volume','Modes')),
			'action.devices.types.FAN' => array('name' => __('Ventilateur',__FILE__) ,'traits' =>array('OnOff','FanSpeed','Modes','Toggles')),
			'action.devices.types.HOOD' => array('name' => __('Hotte',__FILE__) ,'traits' =>array('OnOff','FanSpeed','Modes','Toggles')),
			'action.devices.types.AC_UNIT' => array('name' => __('Climatiseur',__FILE__) ,'traits' =>array('FanSpeed','TemperatureSetting','OnOff')),
			'action.devices.types.AIRCOOLER' => array('name' => __('Refroidisseur d\'air',__FILE__) ,'traits' =>array('FanSpeed','TemperatureSetting','OnOff','HumiditySetting')),
			'action.devices.types.AIRFRESHENER' => array('name' => __('Désodorisant',__FILE__) ,'traits' =>array('Modes','Toggles','OnOff')),
			'action.devices.types.AIRPURIFIER' => array('name' => __('Purificateur d\'air',__FILE__) ,'traits' =>array('FanSpeed','Modes','OnOff','SensorState','Toggles')),
			'action.devices.types.AWNING' => array('name' => __('Store',__FILE__) ,'traits' =>array('Modes','OpenClose')),
			'action.devices.types.BATHTUB ' => array('name' => __('Baignoire',__FILE__) ,'traits' =>array('OnOff','Fill')),
			'action.devices.types.BOILER' => array('name' => __('Chaudiere',__FILE__) ,'traits' =>array('Modes','TemperatureControl','OnOff','Toggles')),
			'action.devices.types.CARBON_MONOXIDE_DETECTOR' => array('name' => __('Detecteur de CO',__FILE__) ,'traits' =>array('SensorState')),
			'action.devices.types.CLOSET' => array('name' => __('Placard',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.DEHUMIDIFIER' => array('name' => __('Déshumidificateur',__FILE__) ,'traits' =>array('FanSpeed','HumiditySetting','OnOff','Modes','RunCycle','StartStop','Toggles')),
			'action.devices.types.DEHYDRATOR' => array('name' => __('Déshydrateur',__FILE__) ,'traits' =>array('Cook','Timer','OnOff','Modes','Toggles','StartStop')),
			'action.devices.types.DISHWASHER' => array('name' => __('Lave-vaiselle',__FILE__) ,'traits' =>array('OnOff','StartStop','Modes','Toggles','RunCycle')),
			'action.devices.types.DRAWER' => array('name' => __('Tiroir',__FILE__) ,'traits' =>array('OpenClose')),
			'action.devices.types.DRYER' => array('name' => __('Séche linge',__FILE__) ,'traits' =>array('OnOff','StartStop','Modes','Toggles','RunCycle')),
			'action.devices.types.FIREPLACE' => array('name' => __('Cheminée',__FILE__) ,'traits' =>array('OnOff','Modes','Toggles')),
			'action.devices.types.FREEZER' => array('name' => __('Congélateur',__FILE__) ,'traits' =>array('OnOff','Modes','Toggles','TemperatureControl')),
			'action.devices.types.HEATER' => array('name' => __('Chauffe-eau',__FILE__) ,'traits' =>array('FanSpeed','TemperatureSetting','OnOff')),
			'action.devices.types.HUMIDIFIER' => array('name' => __('Humidificateur',__FILE__) ,'traits' =>array('FanSpeed','HumiditySetting','OnOff','Modes','StartStop','Toggles')),
			'action.devices.types.KETTLE' => array('name' => __('Bouilloire',__FILE__) ,'traits' =>array('Modes','TemperatureControl','OnOff','Toggles')),
			'action.devices.types.REMOTECONTROL' => array('name' => __('Télécommande',__FILE__) ,'traits' =>array('OnOff','MediaState','InputSelector','AppSelector','TransportControl','Volume')),
			'action.devices.types.MOWER' => array('name' => __('Tondeuse',__FILE__) ,'traits' =>array('Dock','Locator','Modes','OnOff','StartStop','Toggles')),
			'action.devices.types.NETWORK' => array('name' => __('Réseaux',__FILE__) ,'traits' =>array('Modes','Toggles','Reboot','SoftwareUpdate','NetworkControl')),
			'action.devices.types.PERGOLA' => array('name' => __('Pergola',__FILE__) ,'traits' =>array('OpenClose','Rotation')),
			'action.devices.types.RADIATOR' => array('name' => __('Radiateur',__FILE__) ,'traits' =>array('Modes','Toggles','OnOff')),
			'action.devices.types.REFRIGERATOR' => array('name' => __('Frigo',__FILE__) ,'traits' =>array('Modes','Toggles','OnOff','TemperatureControl')),
			'action.devices.types.ROUTER' => array('name' => __('Router',__FILE__) ,'traits' =>array('Modes','Toggles','Reboot','SoftwareUpdate','NetworkControl')),
			'action.devices.types.SHOWER' => array('name' => __('Douche',__FILE__) ,'traits' =>array('Modes','Toggles','OnOff','TemperatureControl')),
			'action.devices.types.SMOKE_DETECTOR' => array('name' => __('Détecteur de fumée',__FILE__) ,'traits' =>array('SensorState')),
			'action.devices.types.VACUUM' => array('name' => __('Aspirateur',__FILE__) ,'traits' =>array('Dock','Locator','Modes','OnOff','StartStop','Toggles','RunCycle')),
			'action.devices.types.MOP' => array('name' => __('Balai',__FILE__) ,'traits' =>array('Dock','Locator','Modes','OnOff','StartStop','Toggles','RunCycle')),
			'action.devices.types.WASHER' => array('name' => __('Machine à laver',__FILE__) ,'traits' =>array('Modes','OnOff','StartStop','Toggles','RunCycle')),
			'action.devices.types.WATERPURIFIER' => array('name' => __('Purificateur d\'eau',__FILE__) ,'traits' =>array('Modes','Toggles','OnOff','TemperatureControl')),
			'action.devices.types.WATERSOFTENER' => array('name' => __('Adoucisseur d\'eau',__FILE__) ,'traits' =>array('Modes','Toggles','OnOff','SensorState')),
		);
	}
	
	/*     * ***********************Methode static*************************** */
	
	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'gsh_update';
		$return['progress_file'] = jeedom::getTmpFolder('gsh') . '/dependance';
		$return['state'] = 'ok' ;
		if (exec('which npm | wc -l') == 0) {
			$return['state'] = 'nok';
		}
		if (exec('which nodejs | wc -l') == 0) {
			$return['state'] = 'nok';
		}
		return $return;
	}
	
	public static function dependancy_install() {
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('gsh') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
	}
	
	public static function deamon_info() {
		$return = array();
		$return['state'] = 'nok';
		$return['launchable'] = 'ok';
		$pid_file = jeedom::getTmpFolder('gsh') . '/deamon.pid';
		if (file_exists($pid_file)) {
			if (posix_getsid(trim(file_get_contents($pid_file)))) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
			}
		}
		return $return;
	}
	
	public static function deamon_start($_debug = false) {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$cmd = 'sudo nodejs '.__DIR__.'/../../resources/gshd/gshd.js';
		$cmd .= ' --udp_discovery_port 3311';
		$cmd .= ' --udp_discovery_packet 4A6565646F6D';
		$cmd .= ' --pid ' . jeedom::getTmpFolder('gsh') . '/deamon.pid';
		$cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('gsh'));
		$cmd .= ' >> ' . log::getPathToLog('gshd') . ' 2>&1 &';
		log::add('gsh', 'info', 'Lancement : '.$cmd);
		exec($cmd);
		log::add('gsh', 'info', 'Démon Google Smarthome local lancé');
		sleep(5);
	}
	
	public static function deamon_stop() {
		$deamon_info = self::deamon_info();
		$pid_file = jeedom::getTmpFolder('gsh') . '/deamon.pid';
		if (file_exists($pid_file)) {
			$pid = intval(trim(file_get_contents($pid_file)));
			system::kill($pid);
		}
		sleep(2);
	}
	
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
		if (!$market->sendRequest('gsh::configGsh', array('gsh::apikey' => jeedom::getApiKey('gsh'),'gsh::url' => network::getNetworkAccess('external')))) {
			throw new Exception($market->getError(), $market->getErrorCode());
		}
	}
	
	public static function voiceAssistantInfo() {
		$market = repo_market::getJsonRpc();
		if (!$market->sendRequest('voiceAssistant::info')) {
			throw new Exception($market->getError(), $market->getErrorCode());
		}
		return $market->getResult();
	}
	
	public static function sendDevices() {
		if (config::byKey('mode', 'gsh') == 'jeedom') {
			$request_http = new com_http('https://cloud.jeedom.com/service/googlehome');
			$request_http->setHeader(array(
				'Content-Type: application/json',
				'Autorization: '.sha512(strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))
			));
			$request_http->setPost(json_encode(array('action' => 'sync')));
			$result = json_decode($request_http->exec(30),true);
			if(!isset($result['state']) || $result['state'] != 'ok'){
				throw new \Exception(__('Erreur sur la demande de synchronisation :',__FILE__).' '.json_encode($result));
			}
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
	
	public static function sync($_group='') {
		$return = array();
		$devices = gsh_devices::all(true);
		foreach ($devices as $device) {
			if($device->getOptions('group') != '' && $device->getOptions('group') != $_group){
				continue;
			}
			$info = $device->buildDevice();
			if (!is_array($info) || count($info) == 0 || isset($info['missingGenericType'])) {
				$device->setOptions('configState', 'NOK');
				if(isset($info['missingGenericType'])){
					$device->setOptions('missingGenericType',$info['missingGenericType']);
				}
				$device->save();
				continue;
			}
			if(config::byKey('gshs::allowLocalApi','gsh') == 1){
				$info['otherDeviceIds'] = array(array('deviceId' => $info['id']));
				if(!isset($info['customData'])){
					$info['customData'] = array();
				}
				$info['customData']['local_execution::apikey'] = jeedom::getApiKey('gsh');
			}
			$return[] = $info;
			$device->setOptions('configState', 'OK');
			$device->setOptions('build', json_encode($info));
			$device->setOptions('missingGenericType','');
			$device->save();
			if (isset($info['willReportState']) && $info['willReportState']) {
				$device->addListener();
			} else {
				$device->removeListener();
			}
		}
		return $return;
	}
	
	public static function exec($_data) {
		$return = array('commands' => array());
		foreach ($_data['data']['commands'] as $command) {
			foreach ($command['devices'] as $infos) {
				if($infos['id'] == 'fake-jeedom-local'){
					$return['commands'][] = array('ids' => array($infos['id']),'status'=>'SUCCESS','states' => array('on' => true));
					continue;
				}
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
						if(isset($command['execution'][0]['challenge']) && $device->getOptions('challenge_pin') != $command['execution'][0]['challenge']['pin']){
							$result = array_merge($result,array(
								'status' => 'ERROR',
								'errorCode' => 'challengeNeeded',
								'challengeNeeded' => array(
									'type' => 'challengeFailedPinNeeded'
								)));
							}else{
								if( $device->getOptions('challenge_pin') == ''){
									$result = array_merge($result,array(
										'status' => 'ERROR',
										'errorCode' => 'challengeFailedNotSetup'
									));
								}else{
									$result = array_merge($result,array(
										'status' => 'ERROR',
										'errorCode' => 'challengeNeeded',
										'challengeNeeded' => array(
											'type' => 'pinNeeded'
										)));
									}
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
						if($infos['id'] == 'fake-jeedom-local'){
							$return['devices'][$infos['id']] = array('on' => true);
							continue;
						}
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
					if($device->getType() == 'action.devices.types.SENSOR'){
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
						$request_http = new com_http('https://cloud.jeedom.com/service/googlehome');
						$request_http->setHeader(array(
							'Content-Type: application/json',
							'Autorization: '.sha512(strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))
						));
						$request_http->setPost(json_encode(array('action' => 'reportState','data' => json_encode($return))));
						$result = json_decode($request_http->exec(30),true);
						if(!isset($result['state']) || $result['state'] != 'ok'){
							throw new \Exception(__('Erreur sur la demande de remonté d\'état :',__FILE__).' '.json_encode($result));
						}
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
				
				/*     * *********************Methode d'instance************************* */
				
				public function preSave() {
					if ($this->getEnable() == 0) {
						$this->setOptions('configState', '');
						$this->removeListener();
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
					$supportedType = gsh::getSupportedType();
					if (!isset($supportedType[$this->getType()])) {
						return array();
					}
					if(isset($supportedType[$this->getType()]['class'])){
						$class = $supportedType[$this->getType()]['class'];
						if (!class_exists($class)) {
							return array();
						}
						if ($this->getLink_type() == 'eqLogic') {
							$eqLogic = $this->getLink();
							if(!is_object($eqLogic) || $eqLogic->getIsEnable() == 0){
								return array();
							}
						}
						return $class::buildDevice($this);
					}
					if(isset($supportedType[$this->getType()]['traits'])){
						$eqLogic = $this->getLink();
						if (!is_object($eqLogic)) {
							return array();
						}
						
						$return = array();
						$return['id'] = $eqLogic->getId();
						$return['type'] = $this->getType();
						if (is_object($eqLogic->getObject())) {
							$return['roomHint'] = $eqLogic->getObject()->getName();
						}
						$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => $this->getPseudo());
						$return['traits'] = array();
						$return['willReportState'] = ($this->getOptions('reportState::enable') == 1);
						foreach ($supportedType[$this->getType()]['traits'] as $traits) {
							$class = 'gsh_'.$traits;
							if (!class_exists($class)) {
								continue;
							}
							$return = array_merge_recursive($return,$class::discover($eqLogic));
						}
						if(count($return['traits']) == 0){
							return array();
						}
						return $return;
					}
				}
				
				public function exec($_execution, $_infos) {
					$supportedType = gsh::getSupportedType();
					if (!isset($supportedType[$this->getType()])) {
						return;
					}
					if(isset($supportedType[$this->getType()]['class'])){
						$class = $supportedType[$this->getType()]['class'];
						if (!class_exists($class)) {
							return array();
						}
						$result = $class::exec($this, $_execution, $_infos);
						return $result;
					}
					if(isset($supportedType[$this->getType()]['traits'])){
						$return = array();
						foreach ($supportedType[$this->getType()]['traits'] as $traits) {
							$class = 'gsh_'.$traits;
							if (!class_exists($class)) {
								continue;
							}
							$return = array_merge_recursive($return,$class::exec($this, $_execution, $_infos));
						}
						return $return;
					}
				}
				
				public function query($_infos) {
					$supportedType = gsh::getSupportedType();
					if (!isset($supportedType[$this->getType()])) {
						return;
					}
					if(isset($supportedType[$this->getType()]['class'])){
						$class = $supportedType[$this->getType()]['class'];
						if (!class_exists($class)) {
							return array();
						}
						$result = $class::query($this, $_infos);
						if (isset($result['status']) && $result['status'] == 'SUCCESS') {
							$this->setCache('lastState', json_encode($result['state']));
						}
						return $result;
					}
					if(isset($supportedType[$this->getType()]['traits'])){
						$return = array();
						foreach ($supportedType[$this->getType()]['traits'] as $traits) {
							$class = 'gsh_'.$traits;
							if (!class_exists($class)) {
								continue;
							}
							$return = array_merge_recursive($return,$class::query($this, $_infos));
						}
						return $return;
					}
				}
				
				public function getPseudo() {
					$eqLogic = $this->getLink();
					$pseudo = array(trim($eqLogic->getName()), trim($eqLogic->getName()) . 's');
					if ($this->getOptions('pseudo') != '') {
						$pseudo = explode(',', $this->getOptions('pseudo'));
					}
					if (is_object($eqLogic->getObject())) {
						$pseudo[] = $eqLogic->getName().' '.$eqLogic->getObject()->getName();
					}
					return $pseudo;
				}
				
				public function addListener() {
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
				
				public function removeListener() {
					if ($this->getLink_type() != 'eqLogic') {
						return;
					}
					$listener = listener::byClassAndFunction('gsh', 'reportState', array('eqLogic_id' => intval($this->getLink_id())));
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
			