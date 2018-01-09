<?php 


class  HyApiException extends \Exception{

	function __construct( $message = '', $code = 0, $previous = null){
	
		parent::__construct($message, $code , $previous);	
	}
}

class HyApiClient{

	/**/
	private $_domain = null;
	private $_key= null;
	private $_secret= null;

	function __construct($key = null, $secret = null, $model = 'sms'){
		$this->_domain = 'b.huoyancloud.com/api/'.$model.'/';
		$this->_key= $key;
		$this->_secret= $secret;
	}


	/*Api Functions Group*/

	public function SendSms($PhoneNumbers, $SignName, $TemplateCode,$TemplateParam ='',$OutId = '' ){
		$params = array();
		$params['PhoneNumbers'] = $PhoneNumbers ;
		$params['SignName'] = $SignName;
		$params['TemplateCode'] = $TemplateCode;
		$params['TemplateParam'] = $TemplateParam;
		$params['OutId'] = $OutId;
		$params['Action'] ='SendSms' ;

		return $this->request($params);	
	}


	public function QuerySendDetails($PhoneNumber, $BizId, $SendDate,$PageSize=10,$CurrentPage= 1 ){
		$params = array();
		$params['PhoneNumber'] = $PhoneNumber ;
		$params['BizId'] = $BizId;
		$params['SendDate'] = $SendDate;
		$params['PageSize'] = $PageSize;
		$params['CurrentPage'] = $CurrentPage;
		$params['Action'] ='QuerySendDetails' ;
		return $this->request($params);	
	}


	/*Tool Functions Group*/

    public function request($params, $security=false , $accessKeyId = null, $accessKeySecret = null, $domain = null) {
        
		$url = $this->genUrl($params, $security,$accessKeyId, $accessKeySecret, $domain );
		
        $content = $this->fetchContent($url);
        return json_decode($content);
    }

    public function genUrl($params, $security=false , $accessKeyId = null, $accessKeySecret = null, $domain = null) {

		if(empty($accessKeyId)){
			$accessKeyId = $this->_key;	
		}

		if(empty($accessKeySecret)){
			$accessKeySecret = $this->_secret;	
		}

		if(empty($domain)){
			$domain= $this->_domain;	
		}

		if(empty($accessKeyId) || empty($accessKeySecret) || empty($domain)){
			throw new HyApiException('params error');
			return;
		}


        $apiParams = array_merge(array (
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
		
	    ksort($apiParams);
        $sortedQueryStringTmp = "";

        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .=  $this->encode($key) . "=" . $this->encode($value). '&';
        }

        $stringToSign =$sortedQueryStringTmp. $accessKeySecret;
		$sign = sha1($stringToSign);

        $url = ($security ? 'https' : 'http')."://{$domain}?{$sortedQueryStringTmp}Signature={$sign}";

		return $url;

    }
	

    private function encode($str)
    {
        $res = urlencode($str);
        return $res;
    }

    private function fetchContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if(substr($url, 0,5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if($rtn === false) {
            $msg = "[CURL_" . curl_errno($ch) . "]: " . curl_error($ch);
        }
        curl_close($ch);

		if($rtn === false){
			throw new HyApiException($msg);
		}

        return $rtn;
    }
}
