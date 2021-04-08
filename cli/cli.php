<?php

use Phalcon\Di\FactoryDefault\Cli as CliDI,
    Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Common\Components\Cache;

ini_set('display_errors', 1);
error_reporting(E_ALL^E_NOTICE);

define('VERSION', '1.0.0');
date_default_timezone_set('Asia/Shanghai');

define('DS',DIRECTORY_SEPARATOR);


// 定义应用目录路径
defined('APP_PATH')
|| define('APP_PATH', realpath(dirname(__FILE__).'/../').DS);

define('SITE_PATH', realpath(APP_PATH.DS.'public'));

require_once APP_PATH . '/vendor/autoload.php';
    
require_once APP_PATH . '/common/libs/functions.php';

// 使用CLI工厂类作为默认的服务容器
$di = new CliDI();

/**
 * 注册类自动加载器
 */
$loader = new \Phalcon\Loader();
$loader->registerDirs(
    array(
        APP_PATH . '/cli/tasks',
        APP_PATH . '/cli/components/',
    )
);
$loader->registerNamespaces([
    'Common' => APP_PATH . '/common/',
    'Common\Models' => APP_PATH . '/common/models/',
    'Common\Components' => APP_PATH . '/common/components/',
    'Cli' => APP_PATH . '/cli/',
    'Cli\Components' => APP_PATH . '/cli/components/',
    'Cli\Models' => APP_PATH . '/cli/models/',
    'Impt\Models' => APP_PATH . '/apps/impt/models/'

    ]);
$loader->register();

// 加载配置文件（如果存在）
if('Linux'==PHP_OS){
    $config_file = 'config.server.php';
}
else{
    $config_file = 'config.cli.php';
}
if (is_readable(APP_PATH . '/config/'.$config_file)) {
    $config = include APP_PATH . '/config/'.$config_file;
    $di->set('config', $config);
}

$di->setShared('db', function () use ($config) {
    return new DbAdapter($config->database->toArray());
});

$di->setShared('conf', function () use ($config) {
    $ret = Cache::init()->getConf();
    return $ret;
});

$di->setShared('settings', function () use ($config) {
    $ret = Cache::init()->getSettings();
    return $ret;
});

// 创建console应用
$console = new ConsoleApp();
$console->setDI($di);

/**
 * 处理console应用参数
 */
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

// 定义全局的参数， 设定当前任务及动作
define('CURRENT_TASK',   (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    // 处理参数
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    echo $e->getFile();
    echo $e->getLine();
    exit(255);
}