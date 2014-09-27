<?php

class httpClient{
    
	public $http_info;

	public $postdata = null;
    
    public $host;

	public $timeout = 30;

	public $connecttimeout = 30;

	public $ssl_verifypeer = FALSE;
	
	public $useragent = 'ApiTool';
    
    public static $boundary = '';
    
	function oAuthRequest( $parameters,$method='GET',$headers=array(), $multi = false) {
        
        switch ($method) {
        	case 'DELETE':
            case 'GET':
                $url = $this->host . '?' . http_build_query($parameters,'','&');
                return $this->http($url,$method,null,$headers,false);
            default:
            	/*
                if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
                    $body = http_build_query($parameters,'','&');
                } else {
                    $body = self::build_http_query_multi($parameters);
                }*/
            
                return $this->http($this->host, $method, $parameters, $headers,$multi);
        }
	}

	function http($url, $method, $postfields = NULL, $headers = array(),$multi) {
		$this->http_info = array();

		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_BINARYTRANSFER, TRUE);

		curl_setopt($ci, CURLOPT_ENCODING, "gzip");
		//curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		//curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {

					if($multi){
						foreach ($postfields as $parameter => $value) {
							if(!empty($value) && $value{0} == '@' ) {
								$file = ltrim( $value, '@');
								curl_setopt($ci, CURLOPT_INFILESIZE,filesize($file));

								unset($postfields[$parameter]);
							}
						}
					}

					curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields,'','&'));
					$this->postdata = $postfields;

				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
		curl_setopt($ci, CURLOPT_HEADER, false);

		$response = curl_exec($ci);

		//$headerSize = curl_getinfo($ci, CURLINFO_HEADER_SIZE);
		//$response_header = substr($response, 0, $headerSize);
		//echo $response_header;

		$this->url = $url;
		$info  = curl_getinfo($ci);
		$this->http_info = $info;
		
		curl_close ($ci);
		return $response;
	}

	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
		}
		return strlen($header);
	}

}
