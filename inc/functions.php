<?php

	
	function oaGetAuthUrl() {
		global $config;
		
		$url = $config->oauth_loginurl;
		$url .= '?redirect_uri='.urlencode($config->oauth_redirurl);
		$url .= '&response_type=code';
		$url .= '&client_id='.$config->oauth_clientid;
		$url .= '&scope='.$config->oauth_scope;
		
		// google specific: force refresh token
		$url .= '&approval_prompt=force';
		$url .= '&access_type=offline';
		
		return $url;	
	}
	
	function oaGetAccessTokens($code) {
		global $config;
		
		$fields = array(
			'code'				=> $code,
			'client_id'			=> $config->oauth_clientid,
			'client_secret'		=> $config->oauth_clientsecret,
			'redirect_uri'		=> $config->oauth_redirurl,
			'grant_type'		=> 'authorization_code'
		);

		
		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
		rtrim($fields_string,'&');
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$config->oauth_tokenurl);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$raw = curl_exec($ch);
		if (!curl_error($ch)) {

			try {
				$result = json_decode($raw);
			} catch (Exception $err) {
				fatal('oaGetAccessTokens/01:','Cant parse json result: '.json_last_error());
			}
			
			return $result;
			
			
		} else {
			fatal('oaGetAccessTokens/02:','Curl error: '.curl_error($ch));
		}
	}
	
	function oaRefreshAccessTokens($refresh_token) {
	
		global $config;
		
		/*
			https://www.googleapis.com/oauth2/v3/token
			POST /oauth2/v3/token HTTP/1.1
			Host: www.googleapis.com
			Content-Type: application/x-www-form-urlencoded
			
			client_id=8819981768.apps.googleusercontent.com&
			client_secret={client_secret}&
			refresh_token=1/6BMfW9j53gdGImsiyUH5kU5RsR4zwI9lUVX-tqf8JXQ&
			grant_type=refresh_token
		*/
		
		$fields = array(
			'client_id'			=> $config->oauth_clientid,
			'client_secret'		=> $config->oauth_clientsecret,
			'refresh_token'		=> $refresh_token,
			'grant_type'		=> 'refresh_token'
		);

		
		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
		rtrim($fields_string,'&');

		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$config->oauth_refreshurl);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$raw = curl_exec($ch);
		if (!curl_error($ch)) {
			try {
				$result = json_decode($raw);
			} catch (Exception $err) {
				fatal('oaRefreshAccessTokens/01:','Cant parse json result: '.json_last_error());
			}
			return $result;
			
		} else {
			fatal('oaRefreshAccessTokens/02:','Curl error: '.curl_error($ch));
		}
	
	}
	
	function oaGetUserInfo($token) {
	
		$url = 'https://www.googleapis.com/plus/v1/people/me';
		$headers = array(
			'Authorization: Bearer '.$token
		);
		

		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$raw = curl_exec($ch);
		if (!curl_error($ch)) {
			try {
				$result = json_decode($raw);
			} catch (Exception $err) {
				fatal('oaGetUserInfo/01:','Cant parse json result: '.json_last_error());
			}
			return $result;
		} else {
			fatal('oaGetUserInfo/02:','Curl error: '.curl_error($ch));
		}
	
	}
	
	function dbGetUserID($remoteid) {
		global $mysqli;
		
		mysqlConnect();
	
		$query = 'SELECT id FROM users ';
		$query .= 'WHERE remoteid = "'.$remoteid.'" ';
		$res = $mysqli->query($query) or fatal('dbGetUserID',mysqli_error($mysqli));
		if ($row = $res->fetch_row()) {
			return $row[0];
		}
		return false;
	}
	
	function dbUpdateUser($uid,$info) {
		global $mysqli;
		mysqlConnect();
		$query = 'UPDATE users SET ';
		$query .= 'name = "'.$info->displayName.'",';
		$query .= 'email = "'.$info->emails[0]->value.'",';
		$query .= 'remoteid = "'.$info->id.'" ';
		$query .= 'WHERE id = "'.$uid.'" ';
		$res = $mysqli->query($query) or fatal('dbUpdateUser',mysqli_error($mysqli));
		return $res;
	}
	
	function dbCreateUser($info) {
		global $mysqli;
		mysqlConnect();
		$query = 'INSERT INTO users (name,email,remoteid) VALUES (';
		$query .= '"'.$info->displayName.'",';
		$query .= '"'.$info->emails[0]->value.'",';
		$query .= '"'.$info->id.'"';
		$query .= ')';
		$res = $mysqli->query($query) or fatal('dbCreateUser',mysqli_error($mysqli));
		return $mysqli->insert_id;
	}
	
	// bag of functions
	function fatal($context,$msg) {
		print '<xmp>Fatal error @ '.$context.'</xmp>';
		print '<xmp>'.$msg.'</xmp>';
		die();
	}
	
	function mysqlConnect() {
		global $config,$mysqli;
		
		// connect
		$mysqli = new mysqli("localhost", $config->dbuser, $config->dbpass, $config->dbname);
		if ($mysqli->connect_errno) {
    		fatal('mysqlConnect',"Failed to connect to MySQL: " . $mysqli->connect_error);
		}
		
	}
	
?>