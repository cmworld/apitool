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

    function call($api,$args=array()){

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

        if($_http->http_info['content_type'] == 'application/json'){
            $json_decode_data =json_decode($result,true);
            if(json_last_error() == JSON_ERROR_NONE){
                $data_type = 'json';
                $result = $json_decode_data;
            }else{
                $data_type = 'txt';
            }
        }else if($_http->http_info['content_type'] == 'text/xml'){
             $data_type = 'xml';
        }else{
            $data_type = 'txt';
        }

        $res = array(
            'header' => array(
                'url' => $_http->http_info['url'],
                'request' => $_http->http_info['request_header']
            ),
            'data_type' => $data_type,
            'response' => $result
        );

        if($ar['method'] == 'POST'){
            $res['header']['post_body'] = $_http->postdata;
        }

        return $res;
    }
}