<?php
return [
		    'rootLogger' => [
		        'appenders' => ['default'],
		    ],
		    'appenders' => [
		        'default' => [
		            'class' => 'LoggerAppenderFile',
		            'layout' => [
		                'class' => 'LoggerLayoutPattern',
		                'params' => [
		                    'conversionPattern' => '%date %logger %-5level %msg%n'
		                ]
		            ],
		            'params' => [
		            	'file' => APP_PATH.DS.'my.log',
		            	'append' => true
		            ]
		        ]
		    ]
		];