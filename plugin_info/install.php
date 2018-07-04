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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function gsh_install() {
	$sql = file_get_contents(dirname(__FILE__) . '/install.sql');
	DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
	if (config::byKey('gshs::clientId', 'gsh') == '') {
		config::save('gshs::clientId', config::genKey(10), 'gsh');
	}
	if (config::byKey('gshs::clientSecret', 'gsh') == '') {
		config::save('gshs::clientSecret', config::genKey(30), 'gsh');
	}
	if (config::byKey('gshs::authkey', 'gsh') == '') {
		config::save('gshs::authkey', config::genKey(16), 'gsh');
	}
	if (config::byKey('dialogflow::authkey', 'gsh') == '') {
		config::save('dialogflow::authkey', config::genKey(16), 'gsh');
	}
	if (config::byKey('homegraph_userid', 'gsh') == '') {
		config::save('homegraph_userid', 'jeedom-gsh-' . config::genKey(10), 'gsh');
	}
	jeedom::getApiKey('gsh');
}

function gsh_update() {

}

function gsh_remove() {

}

?>
