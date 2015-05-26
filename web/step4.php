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
fwrite($sock, 'GET_LIST'."\n");


$ret = chop(fgets($sock));

if ($ret != "BEGIN_LIST") {
	// crap, something went wrong. no idea what. screw it, back to narnia.
	header('Location: twitter_login.php');
	exit;
}

// i don't actually know if this needs to be done in php.
// php is weird.
$lines = array();


// read the output data from backend
while (true) {
	$ret = chop(fgets($sock));

	// keep getting ret until we find END_LIST
	if ( $ret == "END_LIST" ) {
		break;
	}

	// add values to lines array
	array_push($lines, $ret);
}


// create a temp file
// we could make this more efficient if we combine this writing part into the above block
// because i have no idea what i'm doing, leaving it like this for debugging purposes for now.
$tempfilename = tempnam(sys_get_temp_dir(), 'shieldsup');
$tempfile = fopen($tempfilename, 'w');

foreach ( $lines as $l ) {
	$words = explode(" ", $l);
	fwrite($tempfile, $words[0]."\n");
}

fclose($tempfile);

// add our download headers. thanks, stackoverflow!
header('Content-Description: File Transfer');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=twitter_ids.csv');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tempfilename));

ob_clean();
flush();
readfile($tempfilename);

unlink($tempfilename);

?>
