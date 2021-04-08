<?php

$config = [
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
        'staticsPath'  => 'http://statics.xianggou.kz/',
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

return new \Phalcon\Config($config);