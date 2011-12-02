<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Stefan Lindecke 2011, MEN AT WORK 2011 
 * @package    bundle_client
 * @license    GNU/LGPL 
 * @filesource
 */
 
array_insert($GLOBALS['BE_MOD']['system'], -1, array
(
	'bundle_overview' => array (
		'tables'   => array('tl_bundle_client_overview'),
		'stylesheet' => 'system/modules/bundle_client/html/bundle_client.css',
		'icon' => 'system/modules/bundle_client/html/iconRepository.png'
	),
));

// default url for the bundles xml
$GLOBALS['TL_CONFIG']['bundle_server_requestUrl'] = 'http://www.contao-bundles.org/';

