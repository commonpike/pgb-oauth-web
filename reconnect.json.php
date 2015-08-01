<?php
	
	require('inc/config.php');
	require('inc/functions.php');
	
	$success	= false;
	$request 	= array(
		'access_token'	=> filter_input(INPUT_POST,'access_token'),
		'refresh_token'	=> filter_input(INPUT_POST,'refresh_token')
	);
	$result		= array(
		'access_token'	=> $request['access_token'],
		'refresh_token'	=> $request['refresh_token']
	);
	$messages 	= array();
	

	
	if ($request['access_token']) {
		
		// try to get info with that token
		$info = oaGetUserInfo($request['access_token']);
		if ($info->error) {
			
			/*
				recognize a timeout ?
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

			$tokens = oaRefreshAccessTokens($request['access_token']);
			if ($tokens->error) {
			
				$messages[] = 'oaRefreshAccessTokens: ['.$tokens->error.'] '.$tokens->error_description;
				
				
			} else {
				/*
					{
					  "access_token":"1/fFBGRNJru1FQd44AzqT3Zg",
					  "expires_in":3920,
					  "token_type":"Bearer",
					}
				*/
				$messages[] = 'Access token refreshed';
				$result['access_token'] = $tokens['access_token'];
				$info = oaGetUserInfo($request['access_token']);
	
			}

			
		} 
			
		if (!$info->error) {

			if ($uid = dbGetUserID($info->id)) {
				if (dbLoginUser($uid)) {
					dbUpdateUser($uid, $info);
					$success=true;
					$result['user_id']=$uid;
				} else {
					$messages[] = 'dbUpdateUser: '. mysqli_error();
				}
				
			} else {
				$messages[] = 'dbGetUserID: No user registered with these tokens';
			}
				
		}
		
			
		
		
	} else {
		$messages[] = 'reconnect.json.php : no access code received';
	}
	
	
	$response = array(
		'error'		=> !$success,
		'messages'	=> $messages,
		'request'	=> $request,
		'result'	=> $result,
	);
	header('Content-type:','application/json');
	print json_encode($response);
	
?>