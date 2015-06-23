<?php
require('conf.php');
require('lib.php');

    $sock = su_session_start($socket_file, $app_key, $app_secret);

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

$filename="shieldsup_" . $_SESSION["username"] . "_" . ($_SESSION["include_rt"] ? "rt" : "no-rt") . "_" . ($_SESSION["include_reply"] ? "reply" : "no-reply") . "_ids.csv"; 
// add our download headers. thanks, stackoverflow!
header('Content-Description: File Transfer');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=' . $filename);
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
