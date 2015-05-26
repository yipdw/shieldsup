<?php

include("conf.php");

mysql_connect($db_host, $db_user, $db_pass);
mysql_select_db($db_name);

require "twitteroauth/twitteroauth.php";

session_start();

if(!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])){
	// We've got everything we need
	// TwitterOAuth instance, with two new parameters we got in twitter_login.php
	$twitteroauth = new TwitterOAuth($app_key, $app_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	// Let's request the access token
	$access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
	// Save it in a session var
	$_SESSION['access_token'] = $access_token;
	// Let's get the user's info
	$user_info = $twitteroauth->get('account/verify_credentials');

	if(isset($user_info->error)){
		// Something's wrong, go back to square 1
		header('Location: twitter_login.php');
	} else {
		// Let's find the user by its ID
		$query = mysql_query("SELECT * FROM oauth WHERE oauth_provider = 'twitter' AND oauth_uid = ". $user_info->id);
		$result = mysql_fetch_array($query);
 
		// If not, let's add it to the database
		if(empty($result)){
			$query = mysql_query("INSERT INTO oauth (oauth_provider, oauth_uid, username, oauth_token, oauth_secret) VALUES ('twitter', {$user_info->id}, '{$user_info->screen_name}', '{$access_token['oauth_token']}', '{$access_token['oauth_token_secret']}')");
			$query = mysql_query("SELECT * FROM oauth WHERE id = " . mysql_insert_id());
			$result = mysql_fetch_array($query);
		} else {
			// Update the tokens
			$query = mysql_query("UPDATE oauth SET oauth_token = '{$access_token['oauth_token']}', oauth_secret = '{$access_token['oauth_token_secret']}' WHERE oauth_provider = 'twitter' AND oauth_uid = {$user_info->id}");
		}
 
		$_SESSION['id'] = $result['id'];
		$_SESSION['username'] = $result['username'];
		$_SESSION['oauth_uid'] = $result['oauth_uid'];
		$_SESSION['oauth_provider'] = $result['oauth_provider'];
		$_SESSION['oauth_token'] = $result['oauth_token'];
		$_SESSION['oauth_secret'] = $result['oauth_secret'];
 
		header('Location: socket.php');
	}
} else {
    // Something's missing, go back to square 1
    header('Location: twitter_login.php');
}

?>

