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

class gsh_SensorState {
	
	/*     * *************************Attributs****************************** */
	
	private $_SMOKE = array('SMOKE');
	private $_WATER_LEAK = array('WATER_LEAK');
	private $_AIR_QUALITY = array('AIR_QUALITY');
	private $_FILTER_CLEANLINESS = array('FILTER_CLEAN_STATE');
	
	
	/*     * ***********************Methode static*************************** */
	
	public static function discover($_eqLogic) {
		$return = array('traits' => array(),'customData' => array(),'attributes' => array());
		$return['attributes']['dataTypesSupported'] = array();
		foreach ($_eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), self::$_SMOKE)) {
				$return['customData']['SensorState_cmdGetSmokeLevel'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.SensorState';
					$return['attributes']['sensorStatesSupported'][] = array(
						'name' => 'SmokeLevel',
						'descriptiveCapabilities' => array(
							'availableStates' => array('smoke detected','no smoke detected')
						)
					);
				}
			}
			if (in_array($cmd->getGeneric_type(), self::$_WATER_LEAK)) {
				$return['customData']['SensorState_cmdGetWaterLeak'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.SensorState';
					$return['attributes']['sensorStatesSupported'][] = array(
						'name' => 'WaterLeak',
						'descriptiveCapabilities' => array(
							'availableStates' => array('leak','no leak')
						)
					);
				}
			}
			if (in_array($cmd->getGeneric_type(), self::$_AIR_QUALITY)) {
				$return['customData']['SensorState_cmdGetAirQuality'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.SensorState';
					$return['attributes']['sensorStatesSupported'][] = array(
						'name' => 'AirQuality',
						'descriptiveCapabilities' => array(
							'availableStates' => array('healthy','moderate','unhealthy','very unhealthy')
						)
					);
				}
			}
			if (in_array($cmd->getGeneric_type(), self::$_FILTER_CLEANLINESS)) {
				$return['customData']['SensorState_cmdGetFilterCleanliness'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.SensorState';
					$return['attributes']['sensorStatesSupported'][] = array(
						'name' => 'FilterCleanliness',
						'descriptiveCapabilities' => array(
							'availableStates' => array('clean','dirty')
						)
					);
				}
			}
		}
		return $return;
	}
	
	public static function needGenericType(){
		return array(
			__('Fumée',__FILE__) => self::$_SMOKE,
			__('Fuite d\'eau',__FILE__) => self::$_WATER_LEAK,
			__('Qualité d\'air',__FILE__) => self::$_AIR_QUALITY,
			__('Etat du filtre',__FILE__) => self::$_FILTER_CLEANLINESS
		);
	}
	
	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
		return $return;
	}
	
	public static function query($_device, $_infos) {
		$return = array();
		$return['online'] = true;
		$return['on'] = true;
		$eqLogic = $_device->getLink();
		$return['currentSensorStateData'] = array();
		
		foreach ($_infos['customData'] as $key => $cmd_id) {
			$cmd = cmd::byId($cmd_id);
			if(!is_object($cmd)){
				continue;
			}
			$type = strtolower(str_replace('SensorState_cmdGet','',$key));
			$value = $cmd->execCmd();
			switch ($type) {
				case 'SmokeLevel':
				$value = ($value == 1) ? 'smoke detected'  : 'no smoke detected';
				break;
				case 'WaterLeak':
				$value = ($value == 1) ? 'leak'  : 'no leak';
				break;
				case 'AirQuality':
				if($value > 75){
					$value = 'very unhealthy';
				}elseif($value > 50){
					$value = 'unhealthy';
				}elseif($value > 25){
					$value = 'moderate';
				}else{
					$value = 'healthy';
				}
				break;
				case 'FilterCleanliness':
				$value = ($value == 1) ? 'dirty'  : 'clean';
				break;
			}
			
			$return['currentSensorStateData'][] = array(
				'name' => $key,
				'currentSensorState' =>  $value
			);
		}
		return $return;
	}
	
	/*     * *********************Méthodes d'instance************************* */
	
	/*     * **********************Getteur Setteur*************************** */
	
}
