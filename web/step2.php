<?php
// basically a garbage file - some logic is good to use, but this was pulled from a diff project.
require('conf.php');
require('lib.php');

    $sock = su_session_start($socket_file, $app_key, $app_secret);

// get username from POST data
// strip out anything but letter, number, underscore and truncate to 15 characters.
$username = preg_replace('/[^\w]/','',substr($_POST["username"],0,15));

// verify username is actually set. redirect if not.
// i remember php did weird stuff with variables being set vs false or something
// hopefully strlen is a safe way to do this and won't barf?
if (strlen($username) < 1) {
	header('Location: twitter_login.php');
	exit;
}

// send username to backend
fwrite($sock, 'USER '.$username."\n");

// these variables should be grabbed from post at a later date.
// for now, use these defaults.
$include_rt = 1;
$include_reply = 0;
fwrite($sock, "RT $include_rt\n");
fwrite($sock, "REPLY $include_reply\n");

// Store the request details in the PHP session
$_SESSION["username"]=$username;
$_SESSION["include_rt"]=$include_rt;
$_SESSION["include_reply"]=$include_reply;

// kick off the backend processing
fwrite($sock, "GO\n");

// redirect to step3.php, which is our refresh loop
header('Location: step3.php');
