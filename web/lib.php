<?php

function login_to_backend($sock, $app_key, $app_secret, $oauth_token, $oauth_token_secret) {
	fwrite($sock, "$app_key\n");
	fwrite($sock, "$app_secret\n");
	fwrite($sock, $_SESSION['oauth_token']."\n");
	fwrite($sock, $_SESSION['oauth_token_secret']."\n");

	$ret = chop(fgets($sock));

	return $ret;
}

    /* Print HTML Header
     (None) -> None
     
     Print standard HTML header for page (title, style).
     */
    function print_html_header(){
        ?>
        <!DOCTYPE html>
        <html lang="en-US">
        <head>
        <title>OAPI: Shields Up</title>
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="layout.css" media="all" type="text/css" />
        </head>

        <body>
        <h1>Shields Up</h1>

        <div class="warning">
            <h3>Warning</h3>
            <p>This is a <strong>very early beta</strong>! Use at your own risk.</p>
        </div>
        <?php
    } // print_html_header()


    /* Shields Up Start Session
     (Socket File, Twitter API Key, Twitter API Secret) -> Stream Socket

     Start PHP session. Redirect to twitter_login.php if no oauth session.
     Open connection to backend via socket. Throw error if can't connect.
     Log in to backend. Throw error on auth issue.
     
     Returns stream socket.
     */
    function su_session_start($socket_file, $app_key, $app_secret) {
        session_start();

        // User is not logged in, redirect
        if(empty($_SESSION['oauth_uid'])){
            header('Location: twitter_login.php');
            exit;
        }
        
        $sock = stream_socket_client("unix://$socket_file", $errno, $errstr);
        
        // todo: make this error message prettier?
        
        if ($errno) {
            print_html_header();
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
            print_html_header();
            print("There seems to be a problem logging you in to twitter. ");
            print("Try hitting back in your browser to restart the process.\n");
            print("</body></html>");
            exit;
        }

        return $sock;

    } // su_session_start()

?>
