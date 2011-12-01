<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{areaBundles},bundle_server_requestUrl';
$GLOBALS['TL_DCA']['tl_settings']['config']['onload_callback'][] = array('tl_bundle_client_settings','onload_callback');

$GLOBALS['TL_DCA']['tl_settings']['fields']['bundle_server_requestUrl'] = array(
	'label'		=>	&$GLOBALS['TL_LANG']['tl_settings']['bundle_server_requestUrl'],
	'eval'		=>	array('rgxp'=>'url', 'mandatory'=>true),
	'inputType'	=>	'text',
);



class tl_bundle_client_settings extends Backend
{
	public function onload_callback(DataContainer $dc)
	{
		if (!$GLOBALS['TL_CONFIG']['bundle_server_requestUrl'])
		{
			$this->import("Config");
			
			$GLOBALS['TL_CONFIG']['bundle_server_requestUrl']='http://contao-bundles.org/';
			$this->Config->update("\$GLOBALS['TL_CONFIG']['bundle_server_requestUrl']", $GLOBALS['TL_CONFIG']['bundle_server_requestUrl']);
	
			
		}
	}

}