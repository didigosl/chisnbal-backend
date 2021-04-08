<?php

namespace Admin;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Http\Response\Cookies;
use Phalcon\Di;
use Admin\Components\Auth;
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
            'Admin\Controllers' => __DIR__ . '/controllers/',
            'Common\Models' => __DIR__ . '/../../common/models/',
            'Admin\Components' => __DIR__ . '/components/',
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
        // var_export($di['flashSession']->setAutoescape ());exit;
        $di['flashSession']->setAutoescape(false);
        /**
         * Setting up the view component
         */
        $di['d'] = function (){
            return new \Phalcon\Debug\Dump;
        };


        $di['dispatcher'] = function() use ($di) {
            // 创建一个事件管理
            $eventsManager = new EventsManager();

            // 附上一个侦听者
            $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
                $di = DI::getDefault();
                if('index'==$dispatcher->getControllerName()){
                    echo $exception->getMessage();
                    exut;
                }
                else{
                    if(!DI::getDefault()->get('request')->isAjax()){
                        //error_reporting(E_ALL ^ E_NOTICE);
                        // 处理异常
                        if ($exception) {
                            if(0 or $exception->getCode()==1){
                                $di->get('flashSession')->error($exception->getMessage());
                                $di->get('response')->redirect($di->get('request')->getHTTPReferer())->sendHeaders();
                            }
                            else{
                                $dispatcher->forward(
                                    array(
                                        'module'=>'admin',
                                        'controller' => 'dashboard',
                                        'action'     => 'error',
                                        'params'=>array(
                                            'msg'=>$exception->getMessage(),
                                            'trace'=>$exception->getTraceAsString()
                                        ),
                                    )
                                );
                            }
                            
                            return false;
                        }
                        else{

                        }
                        
                        // 代替控制器或者动作不存在时的路径
                        switch ($exception->getCode()) {
                            case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                            case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                                $dispatcher->forward(
                                    array(
                                        'controller' => 'dashboard',
                                        'action'     => 'show404'
                                    )
                                );

                            return false;
                        }
                    }
                }
                
                
                
            });


            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('admin\Controllers');
            return $dispatcher;
        };
        

        $di['view'] = function () use ($di) {
            
            $eventsManager = $di['eventsManager'];
            $eventsManager->attach("view:beforeRender", function ($event, $view) use ($di) {
                //echo $event->getType(), ' - ', $view->getActiveRenderPath(), PHP_EOL;
         
                $breadcrumbs = $di['dispatcher']->getActiveController()->breadcrumbs;
    
                $view->setVar('breadcrumbs',$breadcrumbs);
     
            });

            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');
            $view->setEventsManager($eventsManager);

            return $view;
        };

        $di['auth'] = function () {
            return new Auth;
        };

        $di['user'] = function () use ($di){
            if($di['auth']){
                return $di['auth']->getUser();
            }
            else{
                return null;
            }
        };

        $di['acl'] = function () use ($di) {
            $D = new \Phalcon\Debug\Dump;
            $acl = new AclList();
            $acl->setDefaultAction(\Phalcon\Acl::DENY); 
            
            $roles = $di['db']->fetchAll("select * from s_acl_role");
            foreach ($roles as $r) {
                $Role = new Role($r['name']);
                $acl->addRole($Role);
            }
            unset($roles,$Role,$r);

            $resources = $di['db']->fetchAll("select * from s_acl_resource");
            $actions = $di['db']->fetchAll("select * from s_acl_action");
            $resourceActions = array();
            foreach ($actions as $a) {
               $resourceActions[$a['resource_id']][] = $a['name'];
            }
            unset($actions,$a);
            foreach ($resources as $res) {
                $Resource = new Resource($res['name']);
                if(!empty($resourceActions[$res['id']])){
                    $acl->addResource($Resource,$resourceActions[$res['id']]);
                }                
            }
            unset($Resource,$resourceActions,$res);

            $accesses = $di['db']->fetchAll("select * from s_acl_access");
            foreach ($accesses as $a) {
                // echo $D->one($a);
                $acl->allow($a['role_name'], $a['resource_name'], $a['action_name']);
            }
            
            // echo $D->one($acl);
            // echo $D->one($acl->getRoles());
            // echo $D->one($acl->getResources());
    
            return $acl;
        };

        $di['cookies'] = function() use ($di){

            $cookies = new Cookies();
            $cookies->useEncryption(false);
            return $cookies;
        };

        
        /*$di['glbs'] = function() use ($di){
            $glbs = [];

            $Glbs = new \stdClass;
            $Glbs->settings = Cache::init()->getSettings();
    
            return $Glbs;
        };*/
        $di['settings'] = function() use ($di){
           
            $settings = Cache::init()->getSettings();
    
            return $settings;
        };

        $di['conf'] = function() use ($di){
           
            $conf = Cache::init()->getConf();
    
            return $conf;
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        // $di['db'] = function () use ($config) {
        //     return new DbAdapter($config->toArray());
        // };


        
    }
}
