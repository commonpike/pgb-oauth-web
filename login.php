<?php
	
	require('inc/config.php');
	require('inc/functions.php');
	
	// just redirect to the oauth 'authentication endpoint'
	// and make that redirect to us again

	
      
	header('Location :'.oaGetAuthUrl(), true, 303);
	
?><!doctype html>
<html>
	<head>
		<title>Redirecting to authentication ..</title>
	</head>
	<body>
		<b>Redirecting to authentication ..</b>
	</body>
</html>