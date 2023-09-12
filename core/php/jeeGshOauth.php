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

if (init('response_type') == 'code') {
	// Allow access from local network only
	if (network::getUserLocation() != 'internal'){
		echo 'You are no comming from internal address';
		die();
	}
	include_file('core', 'authentification', 'php');
	// Check for a logged in user
	if (!isConnect()) {
		// If login and password supplied, try to login the user
		if (init('username', '') != '' || init('password', '') != '') {
			$user = user::connect(init('username'), init('password'));
			if (is_object($user)
					&& network::getUserLocation() != 'internal'
					&& $user->getOptions('twoFactorAuthentification', 0) == 1
					&& $user->getOptions('twoFactorAuthentificationSecret') != ''
					&& init('twoFactorCode') == '') {
				$error = __("Merci de fournir un Token 2FA", __FILE__);
				$mfa = true;

			} elseif (!login(init('username'), init('password'), init('twoFactorCode'))) {
				$error = __("Mot de passe ou nom d'utilisateur incorrect", __FILE__);
			}
		} else {
			$error = '';
		}
	}

	// Check if user is an Admin
	if (!isConnect('admin') && !isset($error)) {
		$error = __('Merci de vous connecter avec un compte Admin', __FILE__);
	}

	// If something failed prompt a login page
	if (isset($error)) {
		?>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes">
			<title>Jeedom</title>
			<link rel="icon" href="/core/img/logo-jeedom-petit-nom-couleur-128x128.png">
			<meta name="theme-color" content="#3f51b5">
			<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		</head>
		<body>
			<br/>
			<center>
				<img src='/core/img/logo-jeedom-petit-nom-couleur-128x128.png' /><br/><br/>
				<?php
				if ($error != ''){
					echo '<div class="alert alert-danger" role="alert" style="margin:10px">' . $error . '</div>';
				}
				?>
				<h2 id="welcome_message">{{Merci de vous connecter à votre Jeedom pour configurer la connexion avec Google}}</h2>
				<form method='post' action='<?php echo $_SERVER["PHP_SELF"]; ?>' style="margin:10px">
					<div class="form-group row"><div class="col-sm-4 col-md-4 col-lg-2 offset-md-4 offset-lg-5">
						<input class="form-control" name="username" placeholder="{{Nom d'utilisateur}}">
					</div></div>
					<div class="form-group row"><div class="col-sm-4 col-md-4 col-lg-2 offset-md-4 offset-lg-5">
						<input type="password" class="form-control" name="password" placeholder="{{Mot de passe}}">
					</div></div>
					<?php if ($mfa) { ?>
					<div class="form-group row"><div class="col-sm-4 col-md-4 col-lg-2 offset-md-4 offset-lg-5">
						<input class="form-control" name="twoFactorCode" placeholder="{{Authentification à 2 facteurs}}">
					</div></div>
					<?php 
					}
					foreach(array('response_type', 'client_id', 'redirect_uri', 'state') as $param) {
						$value = init($param);
						if($value != '')
							echo '			<input type="hidden" name="'. htmlspecialchars($param) .'" value="'. htmlspecialchars($value) .'" />';
					}
					?>
					<button type="submit" class="btn btn-primary mb-2">{{Valider}}</button>
				</form>
			</center>
			<pre><?php print_r($_REQUEST); ?></pre>
		</body>
		</html>
		<?php
		die();
	}
	if (init('client_id') == config::byKey('gshs::clientId', 'gsh')) {
		$authorization_code = config::genKey();
		config::save('OAuthAuthorizationCode', $authorization_code, 'gsh');
		header('Location: ' . init('redirect_uri') . '?code=' . $authorization_code . '&state=' . init('state'));
	}
} else if ($_POST['client_id'] == config::byKey('gshs::clientId', 'gsh') && $_POST['client_secret'] == config::byKey('gshs::clientSecret', 'gsh')) {
	if (!in_array(init('type', 'sh'), array('df', 'sh'))) {
		echo 'Le type ne peut etre que sh ou df';
		die();
	}
	header('Content-type: application/json');
	header('HTTP/1.1 200 OK');
	header('\'Access-Control-Allow-Origin\': *');
	header('\'Access-Control-Allow-Headers\': \'Content-Type, Authorization\'');
	if ($_POST['grant_type'] == 'authorization_code' && $_POST['code'] == config::byKey('OAuthAuthorizationCode', 'gsh') && config::byKey('OAuthAuthorizationCode', 'gsh') != '') {
		config::save('OAuthAuthorizationCode', '', 'gsh');
		$access_token = config::genKey();
		config::save('OAuthAccessToken' . init('type', 'sh'), $access_token, 'gsh');
		$refresh_token = config::genKey();
		config::save('OAuthRefreshToken' . init('type', 'sh'), $refresh_token, 'gsh');
		$response = array(
			'token_type' => 'bearer',
			'access_token' => $access_token,
			'refresh_token' => $refresh_token,
			'expires_in' => 3600 * 24,
		);
		echo json_encode($response);
	} elseif ($_POST['grant_type'] == 'refresh_token' && $_POST['refresh_token'] == config::byKey('OAuthRefreshToken' . init('type', 'sh'), 'gsh') && config::byKey('OAuthRefreshToken' . init('type', 'sh'), 'gsh') != '') {
		$access_token = config::genKey();
		config::save('OAuthAccessToken' . init('type', 'sh'), $access_token, 'gsh');
		$response = array(
			'token_type' => 'bearer',
			'access_token' => $access_token,
			'expires_in' => 3600 * 24,
		);
		echo json_encode($response);
	}
}
?>
