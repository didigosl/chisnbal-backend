<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Di\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Output as FrontOutput;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Flash\Session as FlashSession;
use Common\Components\Assets;
use Common\Components\Fire;

/**
 * The FactoryDefault Dependency Injector automatically registers the right services to provide a full stack framework
 */
$di = new FactoryDefault();

/**
 * Registering a router
 */
$di->setShared('router', function () {
    $router = new Router();

    $router->setDefaultModule('admin');
    $router->setDefaultNamespace('Admin\Controllers');

    return $router;
});

/**
 * The URL component is used to generate all kinds of URLs in the application
 */
$di->set('url', function () use ($config){
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
/*$di->setShared('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
     
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
});*/

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($config) {
    return new DbAdapter($config->database->toArray());
});

$di->setShared('config', function () use ($config) {
    return $config;
});

$di->setShared('cache', function () use ($config) {
    $frontCache = new FrontOutput(array(
        "lifetime" => 172800
    ));

    $cache = new BackFile($frontCache, array(
        "cacheDir" => APP_PATH."/apps/runtime/cache/"
    ));

    return $cache;
});

$di->setShared('assets', function () use ($config) {
    return new Assets;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function () {
    return new MetaDataAdapter();
});


/**
* Set the default namespace for dispatcher
*/
$di->setShared('dispatcher', function() use ($di) {
	$dispatcher = new Phalcon\Mvc\Dispatcher();
	$dispatcher->setDefaultNamespace('Admin\Controllers');
	return $dispatcher;
});

/**
 * Starts the session the first time some component requests the session service
 */
$di->setShared('session', function () use ($di) {

    $session = new SessionAdapter([
        'uniqueId'=>'module_'.$di->get('dispatcher')->getModuleName().'_',
    ]);
    $session->start();

    return $session;
});

$di->setShared('flashSession', function () {
    $flashSession = new FlashSession([
        "error"   => "msg-box alert alert-danger",
        "success" => "msg-box alert alert-success",
        "notice"  => "msg-box alert alert-info",
        "warning" => "msg-box alert alert-warning",
    ]);
    return $flashSession;
});

$di->setShared('logger',function() use ($di) {
    $logger = new FileAdapter(APP_PATH."/logs/log.txt");
    return $logger;
});

// $di->setShared('fb', function() use ($di) {
//     $fb = new Common\Components\Fire;
//     return $fb;
// });

//profile
// $profiler = new \Fabfuel\Prophiler\Profiler();
// $profiler->addAggregator(new \Fabfuel\Prophiler\Aggregator\Database\QueryAggregator());
// $pluginManager = new \Fabfuel\Prophiler\Plugin\Manager\Phalcon($profiler);
// $pluginManager->register();


$di->setShared('profiler', function() use ($di){
    return  new \Fabfuel\Prophiler\Profiler();
});
$di->setShared('log', function() use ($di){
    return new \Fabfuel\Prophiler\Adapter\Psr\Log\Logger($di->getShared('profiler'));
});