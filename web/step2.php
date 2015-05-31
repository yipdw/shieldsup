<?php
// basically a garbage file - some logic is good to use, but this was pulled from a diff project.
require('conf.php');
require('lib.php');

    su_session_start();

// get username from POST data
$username = $_POST["username"];

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
fwrite($sock, "RT 1\n");
fwrite($sock, "REPLY 0\n");

// kick off the backend processing
fwrite($sock, "GO\n");

// redirect to step3.php, which is our refresh loop
header('Location: step3.php');
