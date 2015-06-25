<?php

# create credentials for access using BASIC authN via non-browsers

function generate_password($length = 8) {
	#$chars = "+-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	# NOTE: avoid chars like #, ?, and / etc if you want to be able to embed credentials in a URL
	$chars = "23456789-bcd+fgh.jkmnpqrst:vwxyz";	# 32 characters => 5 bits entropy. Need 20 chars for 80 bit entropy
	#$length = 20;
	$length = 12;	# settle for 60 bit

	$count = mb_strlen($chars);
	for ($i = 0, $result = ''; $i < $length; $i++) {
		$index = rand(0, $count - 1);
		$result .= mb_substr($chars, $index, 1);
	}
	return $result;
}

/*
$ htpasswd -nbm username password
username:$apr1$Uo5DHXjy$DnEb1WC4sX6j9m2RFq/IF/
$ openssl passwd -apr1 -salt Uo5DHXjy password
$apr1$Uo5DHXjy$DnEb1WC4sX6j9m2RFq/IF/
*/

# http://php.net/crypt
function crypt_apr1_md5($password) {
	$tmp = '';
	$chars = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	$salt = substr(str_shuffle($chars), 0, 8);
	$len = strlen($password);
	$text = $password.'$apr1$'.$salt;
	$bin = pack("H32", md5($password.$salt.$password));
	for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
	for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $password{0}; }
	$bin = pack("H32", md5($text));
	for($i = 0; $i < 1000; $i++) {
		$new = ($i & 1) ? $password : $bin;
		if ($i % 3) $new .= $salt;
		if ($i % 7) $new .= $password;
		$new .= ($i & 1) ? $bin : $password;
		$bin = pack("H32", md5($new));
	}
	for ($i = 0; $i < 5; $i++) {
		$k = $i + 6;
		$j = $i + 12;
		if ($j == 16) $j = 5;
		$tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
	}
	$tmp = chr(0).chr(0).$bin[11].$tmp;
	$tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
	return "$"."apr1"."$".$salt."$".$tmp;
}


####


session_start();
if( !isset($_SESSION['username']) ) {
	echo "Please log in first";
	exit();
}
$username = $_SESSION['username'];

$mail = '';
if( isset($_SESSION['mail']) ) {
	$mail = $_SESSION['mail'];
}

$return = "/";
if( isset($_GET['return']) ) {
	$return = $_GET['return'];
}

$password = generate_password();
$encrypted_password = crypt_apr1_md5($password);

require("../config.php");
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$user = $config['db']['username'];
$pass = $config['db']['password'];
$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$result = $db->exec("INSERT INTO authn(username, password, email, enabled) VALUES('$username', '$encrypted_password', '$mail', TRUE) ON DUPLICATE KEY UPDATE password='$encrypted_password'");

echo "Your WEBDAV credentials are:";
echo "<li><b>username</b>:<code>$username</code></li>";
echo "<li><b>password</b>:<code>$password</code></li>";
#$test = "http://" . $username . ":" . $password . "@" . $_SERVER['SERVER_NAME'] . "/webdav";
$test = "https://" . $username . "@" . $_SERVER['SERVER_NAME'] . "/webdav";
echo "<p>You can use the following URL in your WEBDAV client: <a href='$test'>$test</a></p>";
echo "<p><a href='$return'>continue</a></p>";
