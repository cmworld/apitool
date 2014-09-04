<?php

include(dirname(__FILE__).DIRECTORY_SEPARATOR.'httpClient.php');


class apicall{
    public $apiurl = '';
    public $apihost = '';

    public $conf = array();

    public $format = 'json';
    public $charset = 'utf-8';
    

    function getApi($api){
        return isset($this->conf['apis'][$api]) ? $this->conf['apis'][$api] : array();
    }

    function signature($data,$secret_key){
        if(isset($data['filename'])) unset($data['filename']);
        if(isset($data['sign'])) unset($data['sign']);
        
        ksort($data);

        $sign_str = ""; 
        foreach($data as $k=>$v){
            if(is_array($v)){
                $v = serialize($v);
            }
            $sign_str .="$k$v";
        }
        $sign_str .= $secret_key;

        return md5($sign_str);
    }

    function call($api,$args=array(),$returnherader = false){

        $ar = $this->getApi($api);
        $args[$this->conf['api_name']] = $ar['api'];

        if($this->conf['use_sign']){
            $args[$this->conf['sign_name']] = $this->signature($args,$this->conf['sign_key']);
        }

        $header = array(
            'Host: '.$this->apihost,
            "content-type: application/x-www-form-urlencoded;charset=".$this->charset
        );

        $_http = new httpClient();  
        $_http->host = $this->apiurl;
        $result = $_http->oAuthRequest($args,$ar['method'],$header);

        $response = json_decode($result,true);
        if($returnherader){
            $res = array(
                'header' => $_http->http_info,
                'response' => $response ? $response : $result
            );

            return json_encode($res);
        }

        return $result; 
    }
}