<?php

// uncomment the following to define a path alias
Yii::setPathOfAlias('uploads',dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'uploads');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Sistema di Gestione Documentale',
        'defaultController' => 'document',
        'homeUrl'=>array('/document/search'),
	'localeDataPath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR.'data',

	// preloading 'log' component
	'preload'=>array('log', 'bootstrap'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'ext.yii-mail.YiiMailMessage',
		'ext.CAdvancedArBehavior'
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
			'loginUrl'=>'/auth/login',
			'class'=>'WebUser'                    
		),
                'authgateway'=>array(
                     'class'=>'AuthorizationGateway',
                     'loggedInUrls'=>array(
                        'profile/edit',
                        'geo/cities',
                        'tag/autocomplete',
                        'tag/autocompletetoken',
                        'sender/autocomplete',
                        'user/autocomplete',
                        'user/autocompleter',
                        'auth/logout'
                     ),
                     'publicUrls'=>array(
                         'site/error',
                         'auth/login',
                         'auth/forgotpassword',
                         'auth/resetpassword'
                     ),
                     'superadminUrls'=>array(
                        'right/*',
                     )
                ),
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=dms',
			'emulatePrepare' => true,
			'username' => 'dmspa',
			'password' => 'Glenn7John',
			'charset' => 'utf8',
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
                        'errorAction'=>'site/error',
                ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(

				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
		'format'=>array(
			'datetimeFormat'=>'d-m-Y H:i:s',
			'dateFormat'=>'d-m-Y',
			'timeFormat'=>'H:i:s'
		),
		'mail' => array(
			'class' => 'ext.yii-mail.YiiMail',
			'transportType' => 'php',
			'viewPath' => 'application.views.email',
			'logging' => true,
			'dryRun' => false
		),
                'bootstrap'=>array(
                    'class'=>'ext.bootstrap.components.Bootstrap', 
                    'coreCss'=>true, 
                    'responsiveCss'=>false
                ),
		'clientScript'=>array(
                        'scriptMap'=>array(
                            'jquery-ui.min.js'=>false,
                            'jquery-ui.css'=>false,
                            'jquery.js'=>false,
                            'jquery.min.js'=>false                            
                        ),
			'packages'=>array(
				'tokenInput' => array(
					'baseUrl'=>'/js',
					'js'=>array('jquery.tokeninput.js'),
					'css'=>array('../css/token-input-facebook.css'),
				),
				'tagit' => array(
					'baseUrl'=>'/js',
					'js'=>array('tag-it.js'),
					'css'=>array('../css/jquery.tagit.css'),
					'depends'=>array('jquery-ui.min.js', 'jquery-ui')
				),
				'jquery' => array(
					'baseUrl'=>'/js',
					'js'=>array('jquery.latest.js')
				),                                                        
				'jquery-ui' => array(
					'baseUrl'=>'/js',
					'js'=>array('jquery-ui.min.latest.js'),
					'css'=>array('../css/smoothness/jquery-ui.latest.css'),
					'depends'=>array('jquery')
				),                            
				'jquery.form' => array(
					'baseUrl'=>'/js',
					'js'=>array('jquery.form.js'),
					'depends'=>array('jquery')
				),
				'jquery.validate' => array(
					'baseUrl' => '/js',
					'js'=>array('jquery.validate.js', 'localization/messages_it.js'),
					'depends'=>array('jquery')
				)
			)
		),			
	),

	'sourceLanguage' => 'en_us',
	'language' => 'it',
		
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'adminEmail'=>'info@dms.engisolution.it',
		'supportEmail'=>'support@dms.engisolution.it',
		'superadmins'=>array('superadmin@comune1.fr.it'),
		'entity' => 'Comune XXX',
		'isDemo' => true
	),
);