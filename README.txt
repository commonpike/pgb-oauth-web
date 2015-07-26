   	---------
   	PGB-OAUTH-WEB 201507*pike
   	---------
   	
   	This is the web part of pgb-oauth. Most of the oauth
   	handling is done here. The purpose is to log users in
   	to a service (in this demo, google) and keep a local
   	user registry (in this case, mysql) in sync while
   	they reconnect later. if a user account doesnt exist
   	it is created on the fly.
   	
   	To set it up, 
   	- create the sql database (sql is in this dir)
   	- create an app on google with the right permissions
   	- edit the config file to match your app
   	- load login.php in a browser and check if it works
   	
   	Once that works, install the app and see it work there.
   	
   	------------
   	
   	the pgb app part simply sees if you've already loggedin,
   	and if not, opens in inappbrowser with login.php, and
   	watches until it loads success.php or error.php. on
   	success, it closes the inappbrowser and you are loggedin.
   	
   	if you have previously logged in, it calls reconnect.php,
   	and checks the json result for errors. if there are errors, 
   	it opens an inappbrowser with login.php - etcetera. otherwise
   	you are logged in and nothing happens.
   	
   	there are 5 pages on the serverside:
   	- login.php				opened in the inappbrowser
   	- return.php			where oauth returns to
   	- success.php			redirected to on success
   	- error.php				redirected to on error
   	- reconnect.json.php	called by xhr
   	
   	------
    
    login.php
    	simply redirects you $config->oauth_login_url, like 

    https://accounts.google.com/o/oauth2/auth?
      redirect_uri=https://developers.google.com/oauthplayground&
      response_type=code&
      client_id=407408718192.apps.googleusercontent.com&
      scope=https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/userinfo.profile&
      approval_prompt=force&
      access_type=offline
  
    this displays a prompt, and on successfull login, returns to
    the redirect_uri given in the request
    
    https://developers.google.com/oauthplayground/oauthplayground/?
      code=4/qiQB8qQ-LvH9TKhs81XnHowDu_q97tkvfacaItdl76U 
    
    --------
    
    return.php
    
    take out the code and quickly exchanges it for an access token
    using POST to $config->oauth_token_url
    passing $config->oauth_client_id and $config->oauth_client_secret
    
    POST /oauth2/v3/token HTTP/1.1
    Host: www.googleapis.com
    Content-length: 233
    content-type: application/x-www-form-urlencoded
    user-agent: google-oauth-playground
      code=4%2FcNdBNwIvsVTt7lUvq4HDzYQXNoWytHL5yj1e8k578VQ&
      redirect_uri=https%3A%2F%2Fdevelopers.google.com%2Foauthplayground&
      client_id=407408718192.apps.googleusercontent.com&
      client_secret=************&
      scope=&
      grant_type=authorization_code
    
    the response is json :
    {
      "access_token": "ya29.uwHoxZZPKK8n2CyimmEBsaBiZ1NaZWkYp-PBdHhvlnw4hgLWrLx2hX5p02Ls8nf8YZHn", 
      "token_type": "Bearer", 
      "expires_in": 3600, 
      "refresh_token": "1/kTYpUiNd8p0vJD1yW0MYZy9BDcZMGBdjB3CdTS02GarBactUREZofsF9C7PrpE-j", 
      "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjliNGE4ODQ5NGI2OTFhMzRhODljMGFlN2Y1NjNjNGJiY2ViOTk0ZmEifQ.eyJpc3MiOiJhY2NvdW50cy5nb29nbGUuY29tIiwic3ViIjoiMTA5NjYwODE2MTU0MDE3NzI0MzcxIiwiYXpwIjoiNDA3NDA4NzE4MTkyLmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwiZW1haWwiOiJwaWtlLWNvbW1vbkBrdy5ubCIsImF0X2hhc2giOiJ5ZHM2aXg3RmVUVFR4dUxXR09GQzZ3IiwiZW1haWxfdmVyaWZpZWQiOnRydWUsImF1ZCI6IjQwNzQwODcxODE5Mi5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsImlhdCI6MTQzNzgxOTYwNywiZXhwIjoxNDM3ODIzMjA3fQ.DDvAtZyIo_rDxoAUcV9wx5ttbF-Suji-VXc5ubV9iK-9VvUKgLjbdylpMZaBtsBIOc90cXomXmYSI1-H6gYJhhT4Ao0GdiPEJITmVvEnaiUSp7q94a4NiABEh29f31p1ypRU6KCX79YZUnbrquORMIK5LH-hR5_p33BsyjCiMNrwTgKdzFWyyKurg7WeO1RN4QhXV9gJ8m7k5P7OLqV9eGjdj-OJCBexOffKyCe1jINKvq0XG9h99naNI9vvHxMD-IeuaiYc0RPWvw--PPK8WRG9mpu7xHPJeALY3ZwTMFs3SZkB1VjvbNsOWBinryxvw17aMEDTX2QTvcdky-ayIg"
    }
              
    
    using this access token, you can use the api on behalf of the user, like
    
    https://www.googleapis.com/plus/v1/people/me
    
    GET /plus/v1/people/me HTTP/1.1
	Host: www.googleapis.com
	Content-length: 0
	Authorization: Bearer ya29.uwHoxZZPKK8n2CyimmEBsaBiZ1NaZWkYp-PBdHhvlnw4hgLWrLx2hX5p02Ls8nf8YZHn


    the response is json:
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
	
	or
		HTTP/1.1 401 Unauthorized
		..
		Content-type: application/json; charset=UTF-8
		Www-authenticate: Bearer realm="https://accounts.google.com/", error=invalid_token
		{
		  "error": {
			"code": 401, 
			"message": "Invalid Credentials", 
			"errors": [
			  {
				"locationType": "header", 
				"domain": "global", 
				"message": "Invalid Credentials", 
				"reason": "authError", 
				"location": "Authorization"
			  }
			]
		  }
		}

		
	for our application, return.php
		- takes out the user id
		- tries to find an existing user in the db
		- if it doesnt exist, creates one
		- updates its values
		- redirects to success.php or error.php
		  with some values in the query string to pass on to the pgb app
		  
	----
		
	success.php, error.php
	
		basicly a page with a message. the purpose is to load it
		in the InAppBrowser, so that the app sees the url changing.
		
		on the url are some parameters the app will store
		to reconnect later. on success, the app will close
		the inAppBrowser and continued as if loggedin. on error,
		there could be a link to login.php
		
		in this demo, these pages contain a button
		to reconnect, so you can test the oauth flow 
		without using the app.
		
	----
	
	reconnect.json.php?user_id=xx&access_token=yy&refresh_token=yy
	
		used to 'reconnect' the user at a later stage, and
		updates the info at the same time. returns json. it
		- finds the user by id
		- checks if the token is correct (..)
		- tries to get the info as in return.php
		- on error, tries to refresh the token by 
		
		https://www.googleapis.com/oauth2/v3/token
		POST /oauth2/v3/token HTTP/1.1
		Host: www.googleapis.com
		Content-Type: application/x-www-form-urlencoded
		
		client_id=8819981768.apps.googleusercontent.com&
		client_secret={client_secret}&
		refresh_token=1/6BMfW9j53gdGImsiyUH5kU5RsR4zwI9lUVX-tqf8JXQ&
		grant_type=refresh_token
		
		on success
			{
			  "access_token":"1/fFBGRNJru1FQd44AzqT3Zg",
			  "expires_in":3920,
			  "token_type":"Bearer",
			}
			
			tries to get info again 
			on success,
				updates the db and returns the new token to the app
			
			on failure, returns error to the app
			
		on failure probably
			{
			  "error": {
				"code": 401, 
				"message": "Invalid Credentials", 
				"errors": ...
			  }
			}
		
			return the error to the app
		
		
