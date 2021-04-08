<?php

namespace Api;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Common\Components\Cache;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {

        $loader = new Loader();

        $loader->registerNamespaces(array(
            'Api\Controllers' => __DIR__ . '/controllers/',
            'Common\Models' => __DIR__ . '/../../common/models/',
            'Api\Components' => __DIR__ . '/components/',
            'Common\Components' => __DIR__ . '/../../common/components/',
            'Common\Libs' => __DIR__ . '/../../common/libs/',
        ));

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {

        $di['view'] = function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');

            return $view;
        };

        $di['dispatcher'] = function() use ($di) {
            // 创建一个事件管理
            $eventsManager = new EventsManager();

            // 附上一个侦听者
            $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
                
                header("Access-Control-Allow-Origin: *");
                header("Content-type: application/json; charset=utf-8");
                echo json_encode([
                        'status'=>'fail',
                        'code'=>$exception->getCode(),
                        'msg'=>$exception->getMessage(),
                        'line'=>$exception->getLine(),
                        'file'=>$exception->getFile(),
                    ],JSON_UNESCAPED_UNICODE);
                exit;
            });


            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('Api\Controllers');
            return $dispatcher;
        };

        $di['conf'] = function() use ($di){

            $ret = Cache::init()->getConf();
    
            return $ret;
        };

        $di['settings'] = function() use ($di){
           
            $ret = Cache::init()->getSettings();
    
            return $ret;
        };

        $di['d'] = function (){
            return new \Phalcon\Debug\Dump;
        };

        $di['isWxmp'] = function () {
            return 1;
        };
    }

    

}
