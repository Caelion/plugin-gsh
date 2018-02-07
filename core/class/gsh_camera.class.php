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

class gsh_camera {

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
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => array($eqLogic->getName(), $eqLogic->getName() . 's'));
		$return['traits'] = array('action.devices.traits.CameraStream');
		$return['willReportState'] = false;
		$return['attributes'] = array(
			'cameraStreamSupportedProtocols' => array($eqLogic->getConfiguration('cameraStreamSupportedProtocols', 'HLS')),
			'cameraStreamNeedAuthToken' => false,
			'cameraStreamNeedDrmEncryption' => false,
		);
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
		$return = array('status' => 'SUCCESS');
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return $return;
		}
		$return['states'] = self::getState($_device);
		return $return;
	}

	public static function getState($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return array();
		}
		system::kill('stream2chromecast.py');
		system::kill('avconv -i');
		if (trim($eqLogic->getConfiguration('cameraStreamAccessUrl', '')) == '') {
			return array(
				"cameraStreamAccessUrl" => '',
			);
		}
		$replace = array(
			'#username#' => urlencode($eqLogic->getConfiguration('username')),
			'#password#' => urlencode($eqLogic->getConfiguration('password')),
			'#ip#' => urlencode($eqLogic->getConfiguration('ip')),
			'#port#' => urlencode($eqLogic->getConfiguration('port')),
		);
		if (file_exists(jeedom::getTmpFolder('gsh') . '/camera_stream')) {
			unlink(jeedom::getTmpFolder('gsh') . '/camera_stream');
			if (file_exists(jeedom::getTmpFolder('gsh') . '/camera_stream')) {
				shell_exec(system::getCmdSudo() . ' rm ' . jeedom::getTmpFolder('gsh') . '/camera_stream  > /dev/null 2>&1');
			}
		}
		shell_exec(system::getCmdSudo() . ' python ' . dirname(__FILE__) . '/../../resources/stream2chromecast.py ' . str_replace(array_keys($replace), $replace, $eqLogic->getConfiguration('cameraStreamAccessUrl', '')) . ' ' . jeedom::getTmpFolder('gsh') . ' > /dev/null 2>&1 &');
		$count = 0;
		while (!file_exists(jeedom::getTmpFolder('gsh') . '/camera_stream')) {
			usleep(1000);
			$count++;
			if ($count > 2000) {
				break;
			}
		}
		$return = array(
			"cameraStreamAccessUrl" => file_get_contents(jeedom::getTmpFolder('gsh') . '/camera_stream') . '/camera.mp4',
		);
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}