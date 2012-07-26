<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class TestPostCommand extends CConsoleCommand{
    
    public function run($args)
    {
        
        $item = array('id'=>10, 'title'=>'test 123');
        $item_1 = array('id'=>11, 'title'=>'test 456');
        $post = CJSON::encode(array($item, $item_1));

        $headers = array();
        $headers['Content-MD5'] = md5($post);
        $headers['Content-Type'] = 'application/json';
        $headers['Date'] = gmdate("D, d M Y H:i:s", time())." GMT";
        $canonicalized_resource_uri = '/api/syncspending';
        
        $string_to_sign = "POST\n".
                        $headers['Content-MD5']."\n".
                        $headers['Content-Type']."\n".
                        $headers['Date']."\n".
                        $canonicalized_resource_uri;
        
        $headers['Authorization'] = "DMS ".Yii::app()->params['api_username'].":".hash_hmac('ripemd160', $string_to_sign, Yii::app()->params['api_password']);
        
        $client = new Guzzle\Service\Client(Yii::app()->params['api_url']);
        $request = $client->post('syncspending', $headers, $post);
        echo $request;
        try
        {
            $response = $request->send();
            echo $response->getStatusCode().' '.$response->getReasonPhrase()."\n";
            echo "\n\n".$response;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
}