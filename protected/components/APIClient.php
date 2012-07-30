<?php

require_once Yii::getPathOfAlias('vendors').DIRECTORY_SEPARATOR.'guzzle'.DIRECTORY_SEPARATOR.'guzzle.phar';

class APIClient {
    
    private $url;
    private $errors = array();
    private $response = null;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function sendRequest($method, $uri, $data, $client_id, $key)
    {
        $this->response = null;
        $this->errors = array();
        
        $headers = array();
        $headers['Content-MD5'] = $data!=null?md5(CJSON::encode($data)):'';
        $headers['Date'] = gmdate("D, d M Y H:i:s", time())." GMT";
        $canonicalized_resource_uri = $uri;
        
        $string_to_sign = $method."\n".
                        $headers['Content-MD5']."\n".
                        $headers['Date']."\n".
                        $canonicalized_resource_uri;
        
        $headers['Authorization'] = "DMS ".$client_id.":".hash_hmac('ripemd160', $string_to_sign, $key);
        
        $client = new Guzzle\Service\Client($this->url);
        if($method=='DELETE')
            $request = $client->delete($uri, $headers);
        elseif($method=='PUT')
            $request = $client->put($uri, $headers, $data);
        elseif($method=='POST')
            $request = $client->post($uri, $headers, $data);
        else
        {
            $this->erros[] = "Impossibile procedere con la richiesta API: metodo sconosciuto";
        }
        
        try{
            $this->response = $request->send();
            return true;
        }
        catch(Exception $e)
        {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
    
}
