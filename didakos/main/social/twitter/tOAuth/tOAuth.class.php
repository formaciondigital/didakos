<?php
/*
* tOAuth Class
* Sergio Cruz aka scromega (scr.omega at gmail dot com) http://scromega.net
*
* Code based on:
* http://github.com/abraham/twitteroauth
*
* OAuth lib:
* http://oauth.googlecode.com/svn/code/php/
*/


require_once('OAuth.php');


class tOAuth {
	private static $sha1_method, $consumer, $token;
	public static $http_code;

        
	function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret=NULL) {
		self::$sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		self::$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		self::$token = NULL;
			if(!empty($oauth_token) && !empty($oauth_token_secret)) {
			self::$token = new OAuthConsumer($oauth_token, $oauth_token_secret);
			}
	}

	function curl($url, $post = NULL) {
		$ch = curl_init();
		//print_r("curl_init:".$ch);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /*
                if (iiset($proxy))
                {
                    $opts[CURLOPT_PROXY] = $proxy;
                    $opts[CURLOPT_PROXYUSERPWD] = $proxyuserpwd;	
                    $opts[CURLOPT_PROXYAUTH] =$proxyauth;
                }
	*/
          
                curl_setopt($ch, CURLOPT_PROXY, '10.2.11.1:3128');
        curl_setopt($ch, CURLOPT_PROXYUSERPWD,'nntt:n9n8t7t6');
        curl_setopt($ch, CURLOPT_PROXYAUTH,CURLAUTH_BASIC);
          
                 
                if(isset($post)) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		$result = curl_exec($ch);
		//print_r("result:".$result);
		self::$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//print_r("info: ".CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $result;
	}

	function request($url, $method, $args = array()) {
		$request = OAuthRequest::from_consumer_and_token(self::$consumer, self::$token, $method, $url, $args);
		$request->sign_request(self::$sha1_method, self::$consumer, self::$token);
			if($method == 'GET') {
				return self::curl($request->to_url());
			} else {
				return self::curl($request->get_normalized_http_url(), $request->to_postdata());
			}
	}

	function authenticate($pre = true, $oauth_token = NULL, $oauth_token_secret = NULL,$args = NULL) 
	{
		$url = ($pre) ? 'https://twitter.com/oauth/request_token' : 'https://twitter.com/oauth/access_token';
		$r = self::request($url, 'GET',$args);
		//print_r($r);
		parse_str($r, $token);
		//print_r($token);
		//die();
		self::$token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		if($pre) $token['request_link'] = 'https://twitter.com/oauth/authorize?oauth_token='.$token['oauth_token'];
		return $token;
	}

	function get($method, $args = NULL) {
		return json_decode(self::request('http://twitter.com/'.$method.'.json', 'GET', $args), true);
	}

	function post($method, $args = NULL) {
		return json_decode(self::request('http://twitter.com/'.$method.'.json', 'POST', $args), true);
	}
}

?>
