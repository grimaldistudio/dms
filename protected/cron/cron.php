<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'yii.php');

$config_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'console.php';

Yii::createConsoleApplication($config_file)->run();

?>
