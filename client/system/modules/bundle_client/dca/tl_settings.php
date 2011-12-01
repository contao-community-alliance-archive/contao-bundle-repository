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
 
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{areaBundles_legend},bundle_server_requestUrl';
$GLOBALS['TL_DCA']['tl_settings']['config']['onload_callback'][] = array('tl_bundle_client_settings','onload_callback');

$GLOBALS['TL_DCA']['tl_settings']['fields']['bundle_server_requestUrl'] = array(
	'label'		=>	&$GLOBALS['TL_LANG']['tl_settings']['bundle_server_requestUrl'],
	'eval'		=>	array('rgxp'=>'url', 'tl_class' => 'long', 'mandatory'=>true),
	'inputType'	=>	'text',
);


class tl_bundle_client_settings extends Backend
{
	public function onload_callback(DataContainer $dc)
	{
		if (!$GLOBALS['TL_CONFIG']['bundle_server_requestUrl'])
		{
			$this->import("Config");
			
			$GLOBALS['TL_CONFIG']['bundle_server_requestUrl'] = 'http://www.contao-bundles.org/';
			$this->Config->update("\$GLOBALS['TL_CONFIG']['bundle_server_requestUrl']", $GLOBALS['TL_CONFIG']['bundle_server_requestUrl']);			
		}
	}

}