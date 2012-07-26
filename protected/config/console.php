<?php

Yii::setPathOfAlias('vendors',dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendors');

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'DMS Cron',
	// application components
        'preload' => array('log'),
    	'import'=>array(
		'application.models.*',
                'application.components.*'
        ),
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=dms',
			'emulatePrepare' => true,
			'username' => 'dmspa',
			'password' => 'Glenn7John',
			'charset' => 'utf8',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
                                        'logFile'=>'dms_cron.log',
					'levels'=>'error, warning'
				),
				array(
					'class'=>'CFileLogRoute',
                                        'logFile'=>'dms_cron_messages.log',
					'levels'=>'trace'
				)
			),
		),            
	),

        // application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'supportEmail'=>'api@dms.engisolution.it',
		'entity' => 'Comune XXX',
                'api_url' => 'http://demo.noticeboard.it/api',
                'api_username' => 'API_USERNAME',
                'api_password' => 'API_KEY',
                'api_timeout'  => 20, // seconds
                'max_documents' => 3,
                'max_spendings' => 3,
                'max_deletion_documents' => 3,
                'max_deletion_spendings' => 3
	),
);