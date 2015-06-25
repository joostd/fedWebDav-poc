<?php

function getUserData($db, $username) {
	$stmt = $db->prepare("SELECT * FROM authn WHERE username=?");
	$stmt->execute(array($username));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $rows;
}

###

if (array_key_exists('logout', $_REQUEST)) {
	$return = $_SERVER['PHP_SELF'];
	header("Location: saml/logout.php?return=$return");
	exit();
}
if (array_key_exists('login', $_REQUEST)) {
	$return = $_SERVER['PHP_SELF'];
	header("Location: saml/login.php?return=$return");
	exit();
}
if (array_key_exists('create', $_REQUEST)) {
	$return = $_SERVER['PHP_SELF'];
	header("Location: db/create.php?return=$return");
	exit();
}
if (array_key_exists('delete', $_REQUEST)) {
	$return = $_SERVER['PHP_SELF'];
	header("Location: db/delete.php?return=$return");
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="/shibboleth-sp/main.css" />
	<title>My Storage</title>
</head>
<body>

<h1>My Storage</h1>

<?php
session_start();
if (!isset($_SESSION['username']) ) {
	echo '<p><a href="?login">Log in</a></p>';
	exit();
} else {
	echo "<p><a href='?logout'>Log out</a> " . $_SESSION['username'] . "</p>";
}
$username = $_SESSION['username'];

$files = "data/$username";
echo "<p>Access your <a href='$files'>files</a> using a web browser</p>";
$webdav_url = "https://" . $_SERVER['SERVER_NAME'] . "/webdav/$username";
echo "<p>For WEBDAV access, use <code>$webdav_url</code> using your prefered WEBDAV client.</p>";

require("config.php");
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$user = $config['db']['username'];
$pass = $config['db']['password'];
$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

try {
	$data = getUserData($db, $username);
	if( count($data) < 1 ) {
		echo "<p>For WEBDAV access to files (using a non-web browser), you will need additional credentials. You have no such credentials configured.</p> ";
		echo "<a href='?create'>create WEBDAV credential</a>";
	} else {
		$user = $data[0];
		$email = $user['email'];
		#echo "$email";
		echo "<a href='?delete'>delete WEBDAV credential</a>";
	}
} catch(PDOException $ex) {
	die('oops');
}

?>
</body>
</html>
