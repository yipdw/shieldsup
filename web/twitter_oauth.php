<?php

require("conf.php");
require("twitteroauth/twitteroauth.php");

session_start();

if(!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])){

	// TwitterOAuth instance, with two new parameters we got in twitter_login.php
	$twitteroauth = new TwitterOAuth($app_key, $app_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

    // Let's request the access token
	$access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);

    // Let's get the user's info
	$user_info = $twitteroauth->get('account/verify_credentials');

    if (isset($user_info->errors)){
        // Something's wrong, go back to square 1, unless we're rate limited
        error_log("Twitter returned error: " . print_r($user_info->errors, TRUE));
        if ($user_info->errors[0]->code == "88") {  // rate limiting
            die("Rate limited; not retrying");
        }
    } else {

        // TODO: This needs to get updated to MySQLi or PDO_MySQL

        $link = mysql_connect($db_host, $db_user, $db_pass);
        if (!$link) die('Could not connect: ' . mysql_error());
        $db_selected = mysql_select_db($db_name, $link);
        if (!$db_selected) die ('Could not connect to DB:' . mysql_error());

        // Let's find the user by its ID
		$query = sprintf("SELECT id, userid, oauth_token, oauth_secret FROM tokens WHERE userid = '%d'", mysql_real_escape_string($user_info->id));
        $query_result = mysql_query($query, $link);
        if(!$query_result) die ("Error querying database: ". mysql_error());

 
		// If not, let's add it to the database
		if(mysql_num_rows($query_result) == 0){
			$query = sprintf("INSERT INTO tokens (userid, oauth_token, oauth_secret, added) VALUES ('%d', '%s', '%s', NOW())",mysql_real_escape_string($user_info->id), mysql_real_escape_string($access_token['oauth_token']), mysql_real_escape_string($access_token['oauth_token_secret']));
            $result = mysql_query($query, $link);
            if(!$result) die("Problems writing to the database");

			$query_result = mysql_query("SELECT id, userid, oauth_token, oauth_secret FROM tokens WHERE id = " . mysql_insert_id(), $link);

        } else {
			// Update the tokens
			$query = sprintf("UPDATE tokens SET oauth_token = '%s', oauth_secret = '%s', accessed = NOW() WHERE userid = '%d'", mysql_real_escape_string($access_token['oauth_token']), mysql_real_escape_string($access_token['oauth_token_secret']), mysql_real_escape_string($user_info->id));
            mysql_query($query, $link); // We don't want to overwrite this $result.
		}

        $result = mysql_fetch_array($query_result);

        $_SESSION['access_token'] = $access_token;
		$_SESSION['id'] = $result['id'];
		$_SESSION['oauth_uid'] = $result['userid'];
		$_SESSION['oauth_token'] = $result['oauth_token'];
		$_SESSION['oauth_token_secret'] = $result['oauth_secret'];

        mysql_close($link);
		header('Location: step1.php');
	}
} else {
    // Something's missing, go back to square 1
    header('Location: twitter_login.php');
}

?>
