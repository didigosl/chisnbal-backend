<?php
// $app_config = include(SITE_PATH.'/../config.php');
$app_config = [];
$default = [
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => '127.0.0.1',
        'username' => 'root',
        'password' => '!Qazxsw2',
        'dbname'   => 'chisnbal',
        'charset'  => 'utf8',
    ],
    'application' => [
        'modelsDir'     => __DIR__ . '/../common/models/',
        
    ],
    'params'=>[
        'staticsPath'  => 'https://statics.didigo.es/',
        'staticDir'=>'/public/',
        'uploadDir' => '/uploads/', 
        'staticsDomain'=>'',
        'bigThumbWidth'=>'500',
        'bigThumbHeight'=>'500',
        'midThumbWidth'=>'200',
        'midThumbHeight'=>'200',
        'smallThumbWidth'=>'100',
        'smallThumbHeight'=>'100', 
        
    ],
    'oss'=>[
        'domain' => '/uploads/',
        'accessId'=>'',
        'accessKey'=>'',
        'endPoint'=>'',
        'bucketName'=>''
    ],
];

$config = array_merge($default,$app_config);
// var_dump($config);exit;
return new \Phalcon\Config($config);
