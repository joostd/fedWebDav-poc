<?php
if( !isset($_SERVER['REMOTE_USER']) ) {
	echo "ERROR: REMOTE_USER not set!";
	exit();
}
$username = $_SERVER['REMOTE_USER'];

if( !isset($_SERVER['mail']) ) {
}
$mail = $_SERVER['mail'];

session_start();
$_SESSION['username'] = $username;
$_SESSION['mail'] = $mail;

### auto-provisioning

$base = '/var/webdav';
$userdir = "$base/$username";

if( !is_dir($userdir) ) {
	if( FALSE === mkdir($userdir, 0700) ) {
		echo "ERROR: user provisioning failed - cannot create directory!";
		exit();
	}
	$contents = "require user $username\n";
	if( FALSE === file_put_contents("$userdir/.htaccess", $contents) ) {
		echo "ERROR: user provisioning failed - cannot write file!";
		exit();
	}
}

### auto enable WEBDAV credentials (and timestamp)
require("../config.php");
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$user = $config['db']['username'];
$pass = $config['db']['password'];
$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$affected_rows = $db->exec("UPDATE authn SET enabled=TRUE where username='$username'");

if( isset($_GET['return']) ) {
	$return = $_GET['return'];
} else {
	$return = "/";
}
header("Location: $return");
