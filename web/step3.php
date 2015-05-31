<?php
require('conf.php');
require('lib.php');

    su_session_start();

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
    print_html_header();
	print("Processing, refreshing in 10 seconds...\n");
	print("</body></html>\n");
	exit;
}

// if neither of those were caught, redirect to twitter login because something went wrong.
header('Location: twitter_login.php');

