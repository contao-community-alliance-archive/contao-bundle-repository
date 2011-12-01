<?php 

array_insert($GLOBALS['BE_MOD']['system'], -1, array
(
	'bundle_overview' => array (
		'tables'   => array('tl_bundle_client_overview'),
		'stylesheet' => 'system/modules/bundle_client/html/bundle_client_backend.css',
	),
));

