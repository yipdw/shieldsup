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

fwrite($sock, "$app_key\n");
fwrite($sock, "$app_secret\n");
fwrite($sock, $_SESSION['oauth_token']."\n");
fwrite($sock, $_SESSION['oauth_secret']."\n");

$ret = chop(fgets($sock));
if ( $ret != "OK" ) {
	print("Uh, oh. I didn't write an exception for this yet, it's probably rate limiting: <strong>$ret</strong>!<br />\n");
	print("If you're getting this error this early on, you should stop repeatedly hitting the refresh button.\n");
	exit;
}

?>
<table><tr>
<td>blocked</td>
<?php
// am i blocked?
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
</tr>
<tr><td>friends</td>
<?php
// are my friends blocked?
fwrite($sock, 'friends?'."\n");
$found = 0;
while (true) {
	$ret = chop(fgets($sock));

	if (preg_match_all("/^BLOCKED_FRIEND_COUNT=(\d+)/", $ret, $matches_out)) {
		$friends_count=$matches_out[1][0];
		if ($friends_count == 0) {
			print("<td style='background-color: #00CC00'>OK</td></tr>\n");
		} elseif ($friends_count > 0) {
			$found = 1;
			print("<td style='background-color: #CC0000'>$friends_count FOUND</td></tr>\n");
			print("<tr><td style='padding=0px;' colspan=\"2\"><center><table id=\"t_list\">");
		}
		else {
			print("<td style='background-color: #FF6600'>ERROR</td></tr></table>\n");
			exit;
		}
		break;
	}
	elseif (preg_match_all("/^WAIT=(\d+)/", $ret, $matches_out)) {
		print("<td style='background-color: #FF6600'>RATE LIMITED (".$matches_out[1][0].")</td>\n");
		print("</tr></table>");
		exit;
		// sleep($matches_out[1][0]);
	}
}
	
while (true) {
	$ret = chop(fgets($sock));

	if ( $ret == "OK" ) {
		// print("</table>\n");
		if ( $found == 1 ) {
			print("</table></center></td>\n</tr>\n");
		}
		break;
	}

	if (preg_match_all("/^BLOCKED_FRIEND=(\d+):(\S+)/", $ret, $matches_out)) {
		$user_id = $matches_out[1][0];
		$screen_name = $matches_out[2][0];

		print("<tr><td><a href=\"https://twitter.com/$screen_name\">$screen_name</a></td></tr>\n");
	}
	elseif (preg_match_all("/^WAIT=(\d+)/", $ret, $matches_out)) {
		print("<tr><td style='background-color: #FF6600'>RATE LIMITED (".$matches_out[1][0].")</td></tr></table>\n</td></tr></table>\n");
		exit;
		// sleep($matches_out[1][0]);
	}
}
?>

<tr><td>followers</td>
<?php
// are my followers blocked?
fwrite($sock, 'followers?'."\n");
$found = 0;
while (true) {
	$ret = chop(fgets($sock));

	if (preg_match_all("/^BLOCKED_FOLLOWER_COUNT=(\d+)/", $ret, $matches_out)) {
		$followers_count=$matches_out[1][0];
		if ($followers_count == 0) {
			print("<td style='background-color: #00CC00'>OK</td></tr>\n");
		} elseif ($followers_count > 0) {
			$found = 1;
			print("<td style='background-color: #CC0000'>$followers_count FOUND</td></tr>\n");
			print("<tr><td style='padding=0px;' colspan=\"2\"><center><table id=\"t_list\">");
		} else {
			print("<td style='background-color: #FF6600'>ERROR</td></tr></table>\n");
			exit;
		}
		break;
	}
	elseif (preg_match_all("/^WAIT=(\d+)/", $ret, $matches_out)) {
		print("<td style='background-color: #FF6600'>RATE LIMITED (".$matches_out[1][0].")</td>\n");
		print("</tr></table>");
		exit;
		// sleep($matches_out[1][0]);
	}
}

while (true) {
	$ret = chop(fgets($sock));

	if ( $ret == "OK" ) {
		if ( $found == 1 ) {
			print("</table></center></td>\n</tr>\n");
		}
		break;
	}

	if (preg_match_all("/^BLOCKED_FOLLOWER=(\d+):(\S+)/", $ret, $matches_out)) {
		$user_id = $matches_out[1][0];
		$screen_name = $matches_out[2][0];

		print("<tr><td><a href=\"https://twitter.com/$screen_name\">$screen_name</a></td></tr>\n");
	}
	elseif (preg_match_all("/^WAIT=(\d+)/", $ret, $matches_out)) {
		print("<tr><td style='background-color: #FF6600'>RATE LIMITED (".$matches_out[1][0].")</td></tr></table></td></tr></table>\n");
		exit;
		// sleep($matches_out[1][0]);
	}
} 

?>

</table>
