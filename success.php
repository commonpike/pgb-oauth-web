<!doctype html>
<?php

	/* 
		within the app, the inappbrowser
		that shows this page is closed before it 
		is even loaded. all the info needed is
		in the query string.
		
		everything in here is for debugging outside
		of the app.
		
	*/

	$user_id  = filter_input(INPUT_GET,'user_id');
	$access_token  = filter_input(INPUT_GET,'access_token');
	$refresh_token  = filter_input(INPUT_GET,'refresh_token');

	
?><html>
	<head>
		<title>Success</title>
	</head>
	<body>
		<h3>Success</h3>
		<b>user_id:</b><?php echo $user_id ?><br>
		<b>access_token:</b><?php echo $access_token ?><br>
		<b>refresh_token:</b><?php echo $refresh_token ?><br>
		<form action="reconnect.json.php" target="json" method="post">
			<input type="hidden" name="access_token" value="<?php echo $access_token ?>" >
			<input type="hidden" name="refresh_token" value="<?php echo $refresh_token ?>" >
			<button>reconnect</button>
		</form>
		<a href="login.php">login again</a>
		<script>
			if (window.opener && window.opener.uiLoggedIn) {
				// we are not in a inAppBrowser
				window.opener.uiLoggedIn('<?php echo $access_token ?>','<?php echo $refresh_token ?>','<?php echo $user_id ?>');
			}
		</script>
				
	</body>
</html>