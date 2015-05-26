<?php
require('conf.php');
require('lib.php');

session_start();

// User is not logged in, redirect
if(empty($_SESSION['oauth_uid'])){
	header('Location: twitter_login.php');
	exit;
}

$sock = stream_socket_client("unix://$socket_file", $errno, $errstr);

// todo: make this error message prettier?

if ($errno) {
	print("<html><body>\n");
	print("something is wrong: <strong>$errstr!</strong><br />\n");
	print("the backend server probably isn't running. kick it.\n");
	print("</body></html>");
	exit;
}

// create a connection to our backend server
// serves no purpose beyond ensuring our auth works
$ret = login_to_backend(	$sock,
				$app_key,
				$app_secret,
				$_SESSION['oauth_token'],
				$_SESSION['oauth_token_secret'] );

// todo: put in a link to the webpage. we should add that to the conf file.
if ( $ret == "AUTH_ERR" ) {
	print("<html><body>\n");
	print("There seems to be a problem logging you in to twitter. ");
	print("Try hitting back in your browser to restart the process.\n");
	print("</body></html>");
	exit;
}

// i have literally no idea why i'm writing newlines like this.
fwrite($sock, 'STATUS'."\n");
$ret = chop(fgets($sock));

// if status is DONE, we can go to our final step!
if ( $ret == "DONE" ) {
	header('Location: step4.php');
	exit;
}

// if status is WAIT, set a refresh for 10 seconds.
if ( $ret == "WAIT" ) {
	header("refresh: 10;");
	print("<html><body>\n");
	print("Processing, refreshing in 10 seconds...\n");
	print("</body></html>\n");
	exit;
}

// if neither of those were caught, redirect to twitter login because something went wrong.
header('Location: twitter_login.php');

