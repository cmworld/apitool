<?php

class httpClient{
    
	public $client_key;
    
	public $client_secret;    
    
    public $header = array();
    
	public $http_code;
    
    public $host;

	public $timeout = 30;

	public $connecttimeout = 30;

	public $ssl_verifypeer = FALSE;
	
	public $useragent = 'apex sdk';
    
    public static $boundary = '';

    function __construct($client_key,$client_secret,$header){
        $this->client_key = $client_key;
        $this->client_secret = $client_secret;
        $this->header = $header;
    }
    
    function signature($data){
		if(isset($data['filename'])) unset($data['filename']);
        if(isset($data['sign'])) unset($data['sign']);
		
        ksort($data); //uksort($request, 'strcmp');   
        
        $sign_str = ""; 
        foreach($data as $k=>$v){
            //$v = $this->safe_encode($v);
            $sign_str .="$k$v";
        }
        $sign_str .= $this->client_secret;

        return md5($sign_str);
    }
    
    private function safe_encode($data) {
      if (is_array($data)) {
        return array_map(array($this, 'safe_encode'), $data);
      } else if (is_scalar($data)) {
        return str_ireplace(
          array('+', '%7E'),
          array(' ', '~'),
          rawurlencode($data)
        );
      } else {
        return '';
      }
    }    
    
	function oAuthRequest( $parameters,$method='GET', $multi = false) {
        
        $headers = $this->header;
        
        $parameters['sign'] = $this->signature($parameters);
         
        switch ($method) {
            case 'GET':
                $url = $this->host . '?' . http_build_query($parameters,'','&');
                return $this->http($url, 'GET',null,$headers);
            default:
                if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
                    $body = http_build_query($parameters,'','&');
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
            
                return $this->http($this->host, $method, $body, $headers);
        }   
	}

	function http($url, $method, $postfields = NULL, $headers = array()) {
		$this->http_info = array();

		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "gzip");
		//curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		//curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
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

		$response = curl_exec($ci);
		
		$this->url = $url;


		$info  = curl_getinfo($ci);
		$this->http_code = $info['http_code'];
		$this->http_info = array(
			'url' => $info['url'],
			'request_header' => isset($info['request_header']) ? $info['request_header'] : ''
		);

		if($method == 'POST'){
			$this->http_info = array_merge($this->http_info, array('post_body'=>$this->postdata));
		}

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

	/**
	 * @ignore
	 */
	public static function build_http_query_multi($params) {
		if (!$params) return '';

		uksort($params, 'strcmp');

		$pairs = array();

		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value) {

			if( $value{0} == '@' ) {
				$url = ltrim( $value, '@' );
				$content = file_get_contents( $url );
				$array = explode( '?', basename( $url ) );
				$filename = $array[0];

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
			}

		}

		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}
}
