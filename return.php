<?php
	
	require('inc/config.php');
	require('inc/functions.php');
	
	$success	= false;
	$messages 	= array();
	$uid 		= 0;
	$tokens		= new stdClass();
	
	$code = filter_input(INPUT_GET,'code');
	$error = filter_input(INPUT_GET,'error');
	
	if ($error) $messages[] = $error;
	
	if (!$error && $code) {
		// exchange this for a token
		
		$tokens = oaGetAccessTokens($code);
		
		
		if ($tokens->error) {
		
			/* 
				{ 
					"error": "invalid_request", 
					"error_description": "Missing required parameter: code" 
				} 
			*/
			
			$messages[] = 'oaGetAccessTokens: ['. $tokens->error.'] '.$tokens->error_description;
			
		} else {
		
			/*
				{ 
					["access_token"]=> string(73) "ya29.xxxxxjGBzX5oQS9SN" 
					["token_type"]=> string(6) "Bearer" 
					["expires_in"]=> int(3600) 
					["refresh_token"]=> string(45) "1/DJPHpcrcMl...
					["id_token"]=> string(884) "..." 
				}
			*/
			
			$info = oaGetUserInfo($tokens->access_token);
			if ($info->error) {
			
				/*
					{ 
						["error"]=> object(stdClass)#4 (3) { 
							["errors"]=> array(1) { 
								[0]=> object(stdClass)#5 (4) { 
									["domain"]=> string(11) "usageLimits" 
									["reason"]=> string(19) "accessNotConfigured" 
									["message"]=> string(148) "Access Not Configured. The API (Google+ API) is not enabled for your project. Please use the Google Developers Console to update your configuration." ["extendedHelp"]=> string(37) "https://console.developers.google.com" 
								} 
							} 
							["code"]=> int(403) 
							["message"]=> string(148) "Access Not Configured. The API (Google+ API) is not enabled for your project. Please use the Google Developers Console to update your configuration." 
						} 
					}
				*/
				$messages[] = 'oaGetUserInfo: ['. $info->error->code.'] '.$info->error->message;

			} else {

				/*
					{
						"kind": "plus#person", 
						"displayName": "xxxxxxx", 
						"name": {
							"givenName": "xxxx", 
							"familyName": "xxxx"
						}, 
						"language": "en_GB", 
						"isPlusUser": true, 
						"url": "https://plus.google.com/xxxxx", 
						"gender": "other", 
						"image": {
							"url": "https://lh5.googleusercontent.com/xxxxxxx", 
							"isDefault": false
						}, 
						"cover": {
							"coverInfo": {
							"leftImageOffset": 0, 
							"topImageOffset": 0
						}, 
						"layout": "banner", 
						"coverPhoto": {
							"url": "https://lh3.googleusercontent.com/xxxxxxxx", 
							"width": 940, 
							"height": 624
						}
						}, 
						"placesLived": [
							{
								"primary": true, 
								"value": "xxxxxxx"
							}
						], 
						"emails": [
							{
								"type": "account", 
								"value": "test@test.nl"
							}
						], 
						"etag": "\"xxxxxxxx\"", 
						"verified": false, 
						"circledByCount": 0, 
						"id": "xxxxxxxxxxx", 
						"objectType": "person"
					}
					
				*/
				
				if ($uid = dbGetUserID($info->id) !== false) {
				
					if (dbLoginUser($uid)) {
						dbUpdateUser($uid, $info);
						$success=true;
					} else {
						$messages[] = 'dbUpdateUser: '. mysqli_error();
					}
					
				} else {
					if ($uid = dbCreateUser($info)) {
						dbLoginUser($uid);
						$success=true;
					} else {
						$messages[] = 'dbCreateUser: '. mysqli_error();
					}
				}
				
			}

		}
			
		
		
	} else {
		$messages[] = 'return.php : no auth code received';
	}
	
	if ($success) {
	
		$url = $config->baseurl.'/success.php';
		$url .= '?user_id='.urlencode($uid);
		$url .= '&access_token='.urlencode($tokens->access_token);
		$url .= '&refresh_token='.urlencode($tokens->refresh_token);
		header('Location :'.$url, true, 303);
		
	} else {
	
	
		$url = $config->baseurl.'/error.php?';
		foreach ($messages as $message) {
			$url .= 'messages[]='.urlencode($message).'&';
		}
		header('Location :'.$url, true, 303);
		
	}
	
?><!doctype html>
<html>
	<head>
		<title>Processing result ..</title>
	</head>
	<body>
		<b>Processing result ..</b>
	</body>
</html>