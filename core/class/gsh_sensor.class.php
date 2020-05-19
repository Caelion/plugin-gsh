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

class gsh_sensor {
	
	/*     * *************************Attributs****************************** */
	private static $_CONVERSION = array(
		'TEMPERATURE' => array(
			'name' => 'temperature',
			'data_type' => array(array('type_synonym'=>  array('température'), 'lang'=> 'fr')),
			'default_device_unit' => '°C',
		),
		'HUMIDITY' =>array(
			'name' => 'humidity',
			'data_type' => array(array('type_synonym'=>  array('humidité'), 'lang'=> 'fr')),
			'default_device_unit' => '%',
		),
		'AIR_QUALITY' => array(
			'name' => 'air_quality_co2',
			'data_type' => array(array('type_synonym'=>  array('qualité de l\'air'), 'lang'=> 'fr')),
			'default_device_unit' => 'ppm',
		),
		'DEPTH' => array(
			'name' => 'depth',
			'data_type' => array(array('type_synonym'=>  array('profondeur'), 'lang'=> 'fr')),
			'default_device_unit' => 'm',
		),
		'WIND_DIRECTION' => array(
			'name' => 'direction',
			'data_type' => array(array('type_synonym'=>  array('Direction du vent'), 'lang'=> 'fr')),
			'default_device_unit' => '',
		),
		'CONSUMPTION' => array(
			'name' => 'energy_usage',
			'data_type' => array(array('type_synonym'=>  array('Consommation'), 'lang'=> 'fr')),
			'default_device_unit' => 'kWh',
		),
		'SPEED' => array(
			'name' => 'speed',
			'data_type' => array(array('type_synonym'=>  array('Vitesse'), 'lang'=> 'fr')),
			'default_device_unit' => 'km/h',
		),
		'DISTANCE' => array(
			'name' => 'distance',
			'data_type' => array(array('type_synonym'=>  array('Distance'), 'lang'=> 'fr')),
			'default_device_unit' => 'm',
		)
	);
	/*     * ***********************Methode static*************************** */
	
	public static function buildDevice($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
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
		$return['willReportState'] = ($_device->getOptions('reportState::enable') == 1);
		$return['traits'] = array();
		$return['attributes'] = array();
		$return['attributes']['dataTypesSupported'] = array();
		$modes = '';
		foreach ($eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), array('TEMPERATURE'))) {
				$return['customData']['cmd_get_temperature'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['TEMPERATURE'];
			}
			if (in_array($cmd->getGeneric_type(), array('HUMIDITY'))) {
				$return['customData']['cmd_get_humidity'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['HUMIDITY'];
			}
			if (in_array($cmd->getGeneric_type(), array('AIR_QUALITY'))) {
				$return['customData']['cmd_get_air_quality'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['AIR_QUALITY'];
			}
			if (in_array($cmd->getGeneric_type(), array('DEPTH'))) {
				$return['customData']['cmd_get_depth'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['DEPTH'];
			}
			if (in_array($cmd->getGeneric_type(), array('WIND_DIRECTION','WEATHER_WIND_DIRECTION'))) {
				$return['customData']['cmd_get_wind_direction'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['WIND_DIRECTION'];
			}
			if (in_array($cmd->getGeneric_type(), array('CONSUMPTION'))) {
				$return['customData']['cmd_get_consumption'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['CONSUMPTION'];
			}
			if (in_array($cmd->getGeneric_type(), array('SPEED','WEATHER_WIND_SPEED'))) {
				$return['customData']['cmd_get_speed'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['SPEED'];
			}
			if (in_array($cmd->getGeneric_type(), array('DISTANCE'))) {
				$return['customData']['cmd_get_speed'] = $cmd->getId();
				if (!in_array('action.devices.traits.SensorState', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Sensor';
				}
				$return['attributes']['dataTypesSupported'][] = self::$_CONVERSION['DISTANCE'];
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
		$return['online'] = true;
		$return['on'] = true;
		$eqLogic = $_device->getLink();
		
		$return['currentSensorData'] = array();
		
		foreach ($_infos['customData'] as $key => $cmd_id) {
			$cmd = cmd::byId($cmd_id);
			if(!is_object($cmd)){
				continue;
			}
			$type = str_replace('cmd_get_','',$key);
			switch ($type) {
				case 'temperature':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'temperature',
					'default_device_units' => '°C',
					'data_value' => $cmd->execCmd(),
				);
				break;
				case 'humidity':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'humidity',
					'data_value' => $cmd->execCmd(),
				);
				break;
				case 'depth':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'depth',
					'data_value' => $cmd->execCmd(),
				);
				break;
				case 'air_quality':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'air_quality_co2',
					'data_value' => $cmd->execCmd(),
				);
				break;
				case 'consumption':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'energy_usage',
					'data_value' => $cmd->execCmd(),
				);
				break;
				case 'speed':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'speed',
					'data_value' => $cmd->execCmd(),
				);
				break;
				case 'distance':
				$return['currentSensorData'][] = array(
					'name' =>self::$_CONVERSION[strtoupper($type)]['name'],
					'data_type_key' => 'distance',
					'data_value' => $cmd->execCmd(),
				);
				break;
			}
		}
		
		return $return;
	}
	
	/*     * *********************Méthodes d'instance************************* */
	
	/*     * **********************Getteur Setteur*************************** */
	
}
