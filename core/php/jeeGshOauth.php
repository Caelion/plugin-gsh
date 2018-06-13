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
require_once __DIR__ . '/../../../../core/php/core.inc.php';
if (isset($_GET['response_type'])) {
	include_file('core', 'authentification', 'php');
	if (!isConnect('admin')) {
		echo 'Merci de vous connecter à Jeedom avant de configurer la connexion avec Google';
		die();
	}
	if ($_GET['client_id'] == config::byKey('gshs::clientId', 'gsh') && $_GET['response_type'] == 'code') {
		$authorization_code = config::genKey();
		config::save('OAuthAuthorizationCode', $authorization_code, 'gsh');
		header('Location: ' . $_GET['redirect_uri'] . '?code=' . $authorization_code . '&state=' . $_GET['state']);
	}
} else if ($_POST['client_id'] == config::byKey('gshs::clientId', 'gsh') && $_POST['client_secret'] == config::byKey('gshs::clientSecret', 'gsh')) {
	header('Content-type: application/json');
	header('HTTP/1.1 200 OK');
	header('\'Access-Control-Allow-Origin\': *');
	header('\'Access-Control-Allow-Headers\': \'Content-Type, Authorization\'');
	if ($_POST['grant_type'] == 'authorization_code' && $_POST['code'] == config::byKey('OAuthAuthorizationCode', 'gsh') && config::byKey('OAuthAuthorizationCode', 'gsh') != '') {
		config::save('OAuthAuthorizationCode', '', 'gsh');
		$access_token = config::genKey();
		config::save('OAuthAccessToken', $access_token, 'gsh');
		$refresh_token = config::genKey();
		config::save('OAuthRefreshToken', $refresh_token, 'gsh');
		$response = array(
			'token_type' => 'bearer',
			'access_token' => $access_token,
			'refresh_token' => $refresh_token,
			'expires_in' => 60 * 24 * 2,
		);
		echo json_encode($response);
	} elseif ($_POST['grant_type'] == 'refresh_token' && $_POST['refresh_token'] == config::byKey('OAuthRefreshToken', 'gsh') && config::byKey('OAuthRefreshToken', 'gsh') != '') {
		$access_token = config::genKey();
		config::save('OAuthAccessToken', $access_token, 'gsh');
		$response = array(
			'token_type' => 'bearer',
			'access_token' => $access_token,
			'expires_in' => 60 * 24 * 2,
		);
		echo json_encode($response);
	}
}
?>