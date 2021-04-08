<?php

$router = $di->get("router");

$router->add('/super', array(
    // 'namespace' => $namespace,
    'module' => 'admin',
    'controller' => 'index',
    'action' => 'super',
));

foreach ($application->getModules() as $key => $module) {
    $namespace = str_replace('Module','Controllers', $module["className"]);

    $routeModuleName = $key;
    
    $router->add('/'.$routeModuleName.'/:params', array(
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 'index',
        'action' => 'index',
        'params' => 1
    ))->setName($key);
    $router->add('/'.$routeModuleName.'/:controller/:params', array(
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ));
    $router->add('/'.$routeModuleName.'/:controller/:action/:params', array(
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ));
}
