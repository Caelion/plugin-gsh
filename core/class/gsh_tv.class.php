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

class gsh_tv {
	
	/*     * *************************Attributs****************************** */
	
	private static $_CHANNEL = array('CHANNEL');
	private static $_SET_CHANNEL = array('SET_CHANNEL');
	private static $_VOLUME = array('VOLUME');
	private static $_SET_VOLUME = array('SET_VOLUME');
	
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
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => $_device->getPseudo());
		$return['traits'] = array();
		$return['willReportState'] = ($_device->getOptions('reportState') == 1);
		foreach ($eqLogic->getCmd() as $cmd) {
			if (in_array($cmd->getGeneric_type(), self::$_VOLUME)) {
				if (!in_array('action.devices.traits.Volume', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Volume';
				}
				$return['customData']['cmd_get_volume'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_SET_VOLUME)) {
				if (!in_array('action.devices.traits.Volume', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Volume';
				}
				$return['customData']['cmd_set_volume'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_CHANNEL)) {
				if (!in_array('action.devices.traits.Channel', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Channel';
				}
				$return['customData']['cmd_get_channel'] = $cmd->getId();
			}
			if (in_array($cmd->getGeneric_type(), self::$_SET_CHANNEL)) {
				if (!in_array('action.devices.traits.Channel', $return['traits'])) {
					$return['traits'][] = 'action.devices.traits.Channel';
				}
				$return['customData']['cmd_set_channel'] = $cmd->getId();
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
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return $return;
		}
		if ($eqLogic->getIsEnable() == 0) {
			return $return;
		}
		foreach ($_executions as $execution) {
			try {
				switch ($execution['command']) {
					case 'action.devices.commands.setVolume':
					$cmd = cmd::byId($_infos['customData']['cmd_set_volume']);
					if (!is_object($cmd)) {
						break;
					}
					$cmd->execCmd(array('slider'=> $execution['params']['volumeLevel']));
					$return = array('status' => 'SUCCESS');
					break;
					case 'action.devices.commands.volumeRelative':
					$cmd = cmd::byId($_infos['customData']['cmd_set_volume']);
					if (!is_object($cmd)) {
						break;
					}
					$cmd_info = cmd::byId($_infos['customData']['cmd_get_volume']);
					if (!is_object($cmd_info)) {
						break;
					}
					$cmd->execCmd(array('slider'=> $cmd_info->execCmd() + $execution['params']['volumeRelativeLevel']));
					$return = array('status' => 'SUCCESS');
					break;
					case 'action.devices.commands.selectChannel':
					$cmd = cmd::byId($_infos['customData']['cmd_set_channel']);
					if (!is_object($cmd)) {
						break;
					}
					$cmd->execCmd(array('slider'=> $execution['params']['channelNumber']));
					$return = array('status' => 'SUCCESS');
					break;
					case 'action.devices.commands.relativeChannel':
					$cmd = cmd::byId($_infos['customData']['cmd_set_channel']);
					if (!is_object($cmd)) {
						break;
					}
					$cmd_info = cmd::byId($_infos['customData']['cmd_get_channel']);
					if (!is_object($cmd_info)) {
						break;
					}
					$cmd->execCmd(array('slider'=> $cmd_info->execCmd() + $execution['params']['relativeChannelChange']));
					$return = array('status' => 'SUCCESS');
					break;
				}
			} catch (Exception $e) {
				log::add('gsh', 'error', $e->getMessage());
				$return = array('status' => 'ERROR');
			}
		}
		$return['states'] = self::getState($_device, $_infos);
		return $return;
	}
	
	public static function getState($_device, $_infos) {
		$return = array();
		$cmd = null;
		if (isset($_infos['customData']['cmd_get_volume'])) {
			$cmd = cmd::byId($_infos['customData']['cmd_get_volume']);
			if (is_object($cmd)) {
				$return['currentVolume'] = $cmd->execCmd();
				$return['isMute'] = false;
			}
		}
		if (isset($_infos['customData']['cmd_get_channel'])) {
			$cmd = cmd::byId($_infos['customData']['cmd_get_channel']);
			if (is_object($cmd)) {
				$return['currentChannel'] = $cmd->execCmd();
			}
		}
		return $return;
	}
	
	/*     * *********************Méthodes d'instance************************* */
	
	/*     * **********************Getteur Setteur*************************** */
	
}
