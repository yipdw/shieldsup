<?php

include('conf.php');

session_start();

// User is not logged in, redirect
if(empty($_SESSION['username'])){
	header('Location: twitter_update.php');
	exit;
}

?>

<html>
<head>
<title>OAPI: Shields Up</title>
<style>
body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 1.2em;
}

table {
	margin: 100px;
}

table#t_list {
	margin: 0px;
	border: 0px;
	padding: 0px;
	width: 90%;
}

table#t_list td {
	border: 0px;
	padding: 4px;
	text-align: left;
	font-size: 0.8em;
	// font-family: "Lucida Console", Monaco, monospace;
}

table#t_list tr:nth-child(even) {
	background-color: #eee;
}

table#t_list tr:nth-child(odd) {
	background-color: #fff;
}

table, td {
	font-family: "Arial Black", Helvetica, sans-serif;
	border: 1px solid black;
	border-collapse: collapse;
}
td {
	padding: 8px;
	text-align: center;
}
</style>
</head>
<body>

<?php
$sock = stream_socket_client("unix://$socket_file", $errno, $errstr);

if ($errno) {
	print("something is wrong: <strong>$errstr!</strong><br />\n");
	print("the backend server probably isn't running. kick it.\n");
	exit;
}

// pretty much everything beneath this line needs to be changed.
// put in some intro comments about how things will go down.

fwrite($sock, "$app_key\n");
fwrite($sock, "$app_secret\n");
fwrite($sock, $_SESSION['oauth_token']."\n");
fwrite($sock, $_SESSION['oauth_secret']."\n");
$ret = chop(fgets($sock));

// CHANGEME
// won't be necessary to check for rate limiting. 
// instead, we'll be doing a refresh every 10s until we get an OK.
// our backend will be monitoring for rate limiting and returning an OK/WAIT code as needed.
if ( $ret != "OK" ) {
	print("Uh, oh. I didn't write an exception for this yet, it's probably rate limiting: <strong>$ret</strong>!<br />\n");
	print("If you're getting this error this early on, you should stop repeatedly hitting the refresh button.\n");
	exit;
}

?>
<table><tr>
<td>blocked</td>
<?php
// alllll of this logic needs to be changed to fit what shields up actually does.
// change protocol, change output.
fwrite($sock, 'blocked?'."\n");
$ret = chop(fgets($sock));

if (preg_match_all("/^BLOCK=(\d+)/", $ret, $matches_out)) {
	if ($matches_out[1][0] == 0) {
		print("<td style='background-color: #00CC00'>OK</td>\n");
	}
	elseif ($matches_out[1][0] == 1) {
		print("<td style='background-color: #CC0000'>BLOCKED</td>\n");
	}
	else {
		print("<td style='background-color: #FF6600'>ERROR</td>\n");
	}
} 

$ret = chop(fgets($sock));
if ( $ret != "OK" ) {
	print("</tr></table>\n");
	print("something is wrong in a really weird place. what are you doing? error: <strong>$ret</strong>\n");
	exit;
}

?>

</tr></table>
</body></html>
