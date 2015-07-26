<?php
	
	class Config { };
	$config = new Config();
	
	$baseurl  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$baseurl .= $_SERVER['SERVER_NAME'];
	$baseurl .= strtok($_SERVER["REQUEST_URI"],'?');
	$config->baseurl = dirname($baseurl);
	
	$config->dbname = "pgb_oauth";
	$config->dbuser = "pgb_oauth";
	$config->dbpass = "PGB_04UTH!";
	
	$config->oauth_loginurl		= "https://accounts.google.com/o/oauth2/auth";
	$config->oauth_tokenurl		= "https://www.googleapis.com/oauth2/v3/token"; 
	$config->oauth_refreshurl	= "https://www.googleapis.com/oauth2/v3/token"; 
	$config->oauth_redirurl 	= $config->baseurl.'/return.php';
	$config->oauth_clientid 	= "847525400387-ovojlu3ab4jpg73f7vatciv7c2bsqdcv.apps.googleusercontent.com";
	$config->oauth_clientsecret	= "P6OOthtESnqSyJH6JID01rCJ";
	$config->oauth_scope		= 'https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/userinfo.profile';
	
	
	
	
	
?>