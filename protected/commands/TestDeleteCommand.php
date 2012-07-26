<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class TestDeleteCommand extends CConsoleCommand{
    
    public function run($args)
    {
        $id = 10;
        $headers = array();
        $headers['Content-MD5'] = '';
        $headers['Content-Type'] = 'application/json';
        $headers['Date'] = gmdate("D, d M Y H:i:s", time())." GMT";
        $canonicalized_resource_uri = '/api/deletespending/'.$id;
        
        $string_to_sign = "DELETE\n".
                        $headers['Content-MD5']."\n".
                        $headers['Content-Type']."\n".
                        $headers['Date']."\n".
                        $canonicalized_resource_uri;
        
        $headers['Authorization'] = "DMS ".Yii::app()->params['api_username'].":".hash_hmac('ripemd160', $string_to_sign, Yii::app()->params['api_password']);
        
        $client = new Guzzle\Service\Client(Yii::app()->params['api_url']);
        $response = $client->delete('deletespending/'.$id, $headers)->send();
        echo $response->getStatusCode().' '.$response->getReasonPhrase()."\n";
        
        echo "\n\n".$response;
    }
    
}