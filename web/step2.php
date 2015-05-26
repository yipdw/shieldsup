<?php
// basically a garbage file - some logic is good to use, but this was pulled from a diff project.
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
	print("</body></html>\n");
	exit;
}

// create a connection to our backend server
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
	print("</body></html>\n");
	exit;
}

// figure out how to get the html form stuff... oh god. how. it's post. i don't know.
// this is for debug and not legitimate.

print("<html><body>");

print("<b>env: </b>\n");
print_r($_ENV);
print("\n<p></p>\n";
print("<b>post: </b>\n");
print_r($_POST);
print("\n</body></html>\n");

exit;

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
