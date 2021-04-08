<?php

/**
 * Register application modules
 */
$application->registerModules(array(
    'api' => array(
        'className' => 'Api\Module',
        'path' => __DIR__ . '/../apps/api/Module.php'
    ),
    'admin' => array(
        'className' => 'Admin\Module',
        'path' => __DIR__ . '/../apps/admin/Module.php'
    ),
    'w' => array(
        'className' => 'W\Module',
        'path' => __DIR__ . '/../apps/w/Module.php'
    ),
    'impt' => array(
        'className' => 'Impt\Module',
        'path' => __DIR__ . '/../apps/impt/Module.php'
    ),

));
