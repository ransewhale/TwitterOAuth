<?php

require_once('config.php');

function randstr($length = 32){
	$randstr = "abcdefghijklmnopqrstuvwxyz0123456789";
	$randstr = str_split($randstr);
	$return="";
	for($i=0; $i<$length; $i++){
		$return .= $randstr[rand(0,35)];
	}
	return $return;
}

function OAuthSignature($method,$url,$params,$consumer_secret,$oauth_token_secret){
	$encoded = array();
	$param_str = "";
	$sign_key = rawurlencode($consumer_secret)."&".rawurlencode($oauth_token_secret);
	foreach($params as $key => $val){
		$encoded[rawurlencode($key)] = rawurlencode($val);
	}
	ksort($encoded);
	foreach($encoded as $key => $val){
		$param_str .= $key;
		$param_str .= "=";
		$param_str .= $val;
		$param_str .= "&";
	}
	$param_str = substr($param_str,0,-1);
	$base = rawurlencode($method)."&".rawurlencode($url)."&".rawurlencode($param_str);
	$signature = hash_hmac("sha1",$base,$sign_key,true);
	return base64_encode($signature);
}

function TwitterAPI($method,$url,$params,$consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret,$callback=null){
	$DST = "OAuth ";
	$header = array();
	$header['oauth_consumer_key'] = $consumer_key;
	$header['oauth_nonce'] = randstr();
	$header['oauth_version'] = "1.0";
	$header['oauth_signature_method'] = "HMAC-SHA1";
	$header['oauth_timestamp'] = time();
	if($oauth_token!=null){
		$header['oauth_token'] = $oauth_token;
	}
	if($callback!=null){
		$header['oauth_callback'] = $callback;
	}
	$params = ($params==null)?(array()):$params;
	$signature = OAuthSignature($method,$url,$header+$params,$consumer_secret,$oauth_token_secret);
	$header['oauth_signature'] = $signature;
	ksort($header);
	foreach($header as $key => $val){
		$DST .= rawurlencode($key);
		$DST .= "=\"";
		$DST .= rawurlencode($val);
		$DST .= "\", ";
	}
	$DST = substr($DST,0,-2);
	$data = http_build_query($params,"","&");
	$opts = array(
		"http" => array(
			"method" => $method,
			"header" => "Authorization: ".$DST,
		)
	);
	if($method === "POST"){
		$opts["http"]["content"] = $data;
	}else{
		$url .= "?";
		$url .= $data;
	}
	$context = stream_context_create($opts);
	return file_get_contents($url,false,$context);
}

class TwitterOAuth{
	private $ck;
	private $cs;
	private $ot;
	private $ots;

	function __construct($consumer_key,$consumer_secret,$oauth_token=null,$oauth_token_secret=null){
		$this->ck = $consumer_key;
		$this->cs = $consumer_secret;
		$this->ot = $oauth_token;
		$this->ots = $oauth_token_secret;
	}

	private function API($method,$url,$params,$callback=null){
		return TwitterAPI($method,$url,$params,$this->ck,$this->cs,$this->ot,$this->ots,$callback);
	}

	public function POST($url,$params){
		return $this->API("POST",$url,$params);
	}

	public function GET($url,$params){
		return $this->API("GET",$url,$params);
	}

	public function request_token($callback){
		$result = $this->API("POST","https://api.twitter.com/oauth/request_token",null,$callback);
		parse_str($result,$output);
		return $output;
	}

	public function access_token($oauth_verifier){
		$parameters = array(
			"oauth_verifier" => $oauth_verifier
		);
		$result = $this->API("POST","https://api.twitter.com/oauth/access_token",$parameters);
		parse_str($result,$output);
		return $output;
	}

	public function showTweet($id){
		$parameters = array(
			"id" => $id
		);
		$result = $this->GET("https://api.twitter.com/1.1/statuses/show.json",$parameters);
		return json_decode($result);
	}

	public function tweet($status,$in_reply_to_status_id=null){
		$parameters = array(
			"status" => $status
		);
		if($in_reply_to_status_id!=null){
			$parent = $this->showTweet($in_reply_to_status_id);
			$parameters["status"] = "@".$parent->user->screen_name." ".$parameters["status"];
			$parameters["in_reply_to_status_id"] = $in_reply_to_status_id;
		}
		$result = $this->POST("https://api.twitter.com/1.1/statuses/update.json",$parameters);
		return json_decode($result);
	}
}