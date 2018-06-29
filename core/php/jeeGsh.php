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
require_once __DIR__ . "/../../../../core/php/core.inc.php";
$headers = apache_request_headers();
$body = json_decode(file_get_contents('php://input'), true);
if (isset($body['originalDetectIntentRequest']) && isset($body['originalDetectIntentRequest']['payload']) && isset($body['originalDetectIntentRequest']['payload']['user']) & isset($body['originalDetectIntentRequest']['payload']['user']['accessToken'])) {
	$plugin = plugin::byId('gsh');
	if (!$plugin->isActive()) {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	if ($body['originalDetectIntentRequest']['payload']['user']['accessToken'] != config::byKey('OAuthAccessTokendf', 'gsh') || config::byKey('OAuthAccessTokendf', 'gsh') == '') {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	if (config::byKey('dialogflow::authkey', 'gsh') == '' || !isset($headers['authkey']) || config::byKey('dialogflow::authkey', 'gsh') != $headers['authkey']) {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	if (!isset($body['queryResult']) || !isset($body['queryResult']['queryText'])) {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	$query = $body['queryResult']['queryText'];
	$params = array('plugin' => 'gsh', 'reply_cmd' => null);
	$response = interactQuery::tryToReply(trim($query), $params);
	header('Content-type: application/json');
	log::add('gsh', 'debug', json_encode(gsh::buildDialogflowResponse($body, $response)));
	echo json_encode(gsh::buildDialogflowResponse($body, $response));
	die();
} else if (isset($headers['Authorization'])) {
	header('Content-type: application/json');
	if (config::byKey('gshs::authkey', 'gsh') == '' || init('secure') != config::byKey('gshs::authkey', 'gsh')) {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	$matches = array();
	preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
	if (!isset($matches[1]) || $matches[1] != config::byKey('OAuthAccessTokensh', 'gsh') || config::byKey('OAuthAccessTokensh', 'gsh') == '') {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	$plugin = plugin::byId('gsh');
	if (!$plugin->isActive()) {
		header('HTTP/1.1 401 Unauthorized');
		echo json_encode(array());
		die();
	}
	$reply = array();
	$reply['requestId'] = $body['requestId'];
	foreach ($body['inputs'] as $input) {
		if ($input['intent'] == 'action.devices.EXECUTE') {
			$reply['payload'] = gsh::exec(array('data' => $input['payload']));
		} else if ($input['intent'] == 'action.devices.QUERY') {
			$reply['payload'] = gsh::query($input['payload']);
		} else if ($input['intent'] == 'action.devices.SYNC') {
			log::add('gsh', 'debug', 'SYNC');
			$reply['payload'] = array();
			$reply['payload']['agentUserId'] = config::byKey('gshs::useragent', 'gsh');
			$reply['payload']['devices'] = gsh::sync();
		}
	}
	header('HTTP/1.1 200 OK');
	echo json_encode($reply);
	die();
}

if (init('apikey') != '') {
	if (!jeedom::apiAccess(init('apikey'), 'gsh')) {
		echo __('Vous n\'etes pas autorisé à effectuer cette action. Clef API invalide. Merci de corriger la clef API sur votre page profils du market et d\'attendre 24h avant de réessayer.', __FILE__);
		die();
	} else {
		echo __('Configuration OK', __FILE__);
		die();
	}
}
log::add('gsh', 'debug', $body['apikey']);
header('Content-type: application/json');
if (!isset($body['apikey']) || !jeedom::apiAccess($body['apikey'], 'gsh')) {
	echo json_encode(array(
		'status' => 'ERROR',
		'errorCode' => 'authFailure',
	));
	die();
}

$plugin = plugin::byId('gsh');
if (!$plugin->isActive()) {
	echo json_encode(array(
		'status' => 'ERROR',
		'errorCode' => 'authFailure',
	));
	die();
}
log::add('gsh', 'debug', json_encode($body));
if ($body['action'] == 'exec') {
	$result = json_encode(gsh::exec($body));
	log::add('gsh', 'debug', $result);
	echo $result;
	die();
} else if ($body['action'] == 'query') {
	$result = json_encode(gsh::query($body));
	log::add('gsh', 'debug', $result);
	echo $result;
	die();
} else if ($body['action'] == 'interact') {
	if (isset($data['queryResult']['languageCode']) && method_exists('translate', 'setLanguage') && str_replace('_', '-', strtolower(translate::getLanguage())) != $data['queryResult']['languageCode']) {
		if (strpos($data['queryResult']['languageCode'], 'en-') !== false) {
			translate::setLanguage('en_US');
		} elseif (strpos($data['queryResult']['languageCode'], 'fr-') !== false) {
			translate::setLanguage('fr_FR');
		}
	}
	$query = $body['data']['queryResult']['queryText'];
	$params = array('plugin' => 'gsh', 'reply_cmd' => null);
	$response = interactQuery::tryToReply(trim($query), $params);
	header('Content-type: application/json');
	log::add('gsh', 'debug', json_encode(gsh::buildDialogflowResponse($body['data'], $response)));
	echo json_encode(gsh::buildDialogflowResponse($body['data'], $response));
	die();
}

echo json_encode(array(
	'status' => 'SUCCESS',
));
die();
