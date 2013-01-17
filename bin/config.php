<?php

define('APP_DIR', ROOT_DIR . '/app');
define('CONFIG_DIR', APP_DIR . '/config');
define('MODELS_DIR', APP_DIR . '/models');
define('LIB_DIR', ROOT_DIR . '/lib');
define('TMP_DIR', ROOT_DIR . '/tmp');
define('LOG_DIR', ROOT_DIR . '/logs');
define('ERR_LOG', false);
define('ENABLE_DEBUG', false);
define('DEBUG_LOG', false);

ini_set('display_errors', false);

date_default_timezone_set('Asia/Tokyo');

set_include_path(LIB_DIR . PATH_SEPARATOR . MODELS_DIR . PATH_SEPARATOR . get_include_path());

include 'Zend/Loader.php';
Zend_Loader::registerAutoload();

require_once 'Bll/Config.php';
$config = Bll_Config::get(CONFIG_DIR . '/mixi-config.xml');
Zend_Registry::set('MemcacheOptions', $config->cache->memcache->servers->toArray());
Zend_Registry::set('secret', $config->secret->toArray());
Zend_Registry::set('host', $config->server->host);
Zend_Registry::set('static', $config->server->static);

function buildAdapter()
{
    require_once 'Zend/Config/Xml.php';
    require_once 'Bll/Config.php';
    $config = Bll_Config::get(Zend_Registry::get('db.xml'));
    $params = $config->database->db_basic->config->toArray();
    $params['driver_options'] = array(
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_TIMEOUT => 2
    );
    $dbAdapter =  Zend_Db::factory($config->database->db_basic->adapter, $params);
    $dbAdapter->query("SET NAMES utf8");

    return $dbAdapter;
}

function getDBConfig()
{
    $key = 'dbConfig';
    if (Zend_Registry::isRegistered($key)) {
        $dbConfig = Zend_Registry::get($key);
    }
    else {
        $adp = buildAdapter();
        $dbConfig = array('readDB' => $adp, 'writeDB' => $adp);

        Zend_Registry::set($key, $dbConfig);
    }

    return $dbConfig;
}

function build_logger($prefix = '', $cache = true)
{
	require_once 'Zend/Log.php';
	if ($cache) {
		require_once 'MyLib/Zend/Log/Writer/Stream.php';
		$writer1 = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/' . $prefix . '-error.log');
		$writer2 = new MyLib_Zend_Log_Writer_Stream(LOG_DIR . '/' . $prefix . '-debug.log');
	} else {
		require_once 'Zend/Log/Writer/Stream.php';
		$writer1 = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $prefix . '-error.log');
		$writer2 = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $prefix . '-debug.log');
	}

	Zend_Registry::set('error_logger', new Zend_Log($writer1));
	Zend_Registry::set('debug_logger', new Zend_Log($writer2));
}

function err_log($msg)
{
	$logger = Zend_Registry::get('error_logger');

	try {
		$logger->log($msg, Zend_Log::ERR);
	}
	catch (Exception $e) {

	}
}

function debug_log($msg)
{
	$logger = Zend_Registry::get('debug_logger');

	try {
		$logger->log($msg, Zend_Log::DEBUG);
	}
	catch (Exception $e) {

	}
}

function info_log($msg, $prefix = 'default')
{
    require_once 'Zend/Log.php';
    $log_name = $prefix . '_logger';
    if (!Zend_Registry::isRegistered($log_name)) {
        require_once 'Zend/Log/Writer/Stream.php';
        $writer = new Zend_Log_Writer_Stream(LOG_DIR . '/' . $log_name . '.log');
        $logger = new Zend_Log($writer);
        Zend_Registry::set($log_name, $logger);
    }
    else {
        $logger = Zend_Registry::get($log_name);
    }

    try {
        $logger->log($msg, Zend_Log::INFO);
    }
    catch (Exception $e) {

    }
}