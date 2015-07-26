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

	$messages  = filter_input(INPUT_GET,'messages',FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	
	
?><html>
	<head>
		<title>Error</title>
	</head>
	<body>
		<h3>Error</h3>
		<ul>
			<?php foreach ($messages as $message) { ?>
				<li><?php echo $message ?></li>
			<?php } ?>
		</ul>
		<a href="login.php">try again</a>
	</body>
</html>