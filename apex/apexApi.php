<?php

include(dirname(__FILE__).DIRECTORY_SEPARATOR.'httpClient.php');

class apexApi
{
    private $_http;
    
    public $version = '1.0';
    
    public $format = 'json';
    public $charset = 'utf-8';

    public $_testmode = false;

    public $platform;
    public $uuid;

    function __construct($client_key,$client_secret,$host){
        
        $this->client_key = $client_key;
        $this->client_secret = $client_secret;

        $_header = array(
            "content-type: application/x-www-form-urlencoded;charset=".$this->charset
        );
        $this->_http = new httpClient($client_key,$client_secret,$_header);  
        $this->_http->host = $host;
    }

    function set_uuid($uuid,$platform){
        $this->uuid = $uuid;
        $this->platform = $platform;
    }

    function debugMode(){
    	$this->_http->debug = true;
    }

    function testMode(){
        $this->_testmode = true;
    }

    function getHttpInfo(){
        return $this->_http->http_info;
    }
    
    function api($api,$data=array(),$mothed = 'GET',$ef_return = false){

        $ar = explode('_', $api);
        $method = implode('.', $ar);

    	$request_data = array(
    		
    		'v' => $this->version,
    		'appid' => $this->client_key,
    		'method' => $method,
            'uuid'  => $this->uuid,
            'platform' => $this->platform,
            '_timestamp' => time(),
    	);

    	$data = array_merge($data,$request_data);
		return  $this->_http->oAuthRequest($data,$mothed);
    }
}