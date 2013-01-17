<?php

define('ROOT_DIR', realpath('../'));
define('STATISTICS_REMOTE_SERVER_HOST', 'http://watch.apps.communityfactory.net');

require(ROOT_DIR . '/bin/config.php');

build_logger('AppStatistics', true);

Zend_Registry::set('db.xml', CONFIG_DIR . '/db.xml');

// statistics batch execute
require_once 'Bll/Statistics/Init.php';
$reportDate = time();
try {
	//debug_log('Batch in!');
	$batStatistics = new Bll_Statistics_Init();
	$batStatistics->getStatistics('16235','res_user_profile',$reportDate,2);
}
catch (Exception $e) {
	err_log($e->getMessage());
}