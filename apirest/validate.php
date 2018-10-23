<?php
require("parse.php");
require("conecta.php");
$con = Conectar("write");

header('Content-type: application/json; charset=utf-8');
header('access-control-allow-origin: *');

$json = file_get_contents('php://input');
$obj = json_decode($json);

if ($obj != null) {
	if (strpos($json, "\"user\""))	$user = parse($obj->{'user'}, "");
	if (strpos($json, "\"pass\"")) 	$pass = parse($obj->{'pass'}, "");
}
elseif ($json != "") die('{"token":""}');

if ($json == "") die('{"token":""}');

$token = "";

$sql = "SELECT password FROM web_users WHERE login='$user' AND enabled='si'";
$res = mysqli_query($con, $sql);

$filas = $res->num_rows;

if ($filas == 1) {
	$row = $res->fetch_object();
	$dbpass = $row->password;

	if ($dbpass == md5($pass)) {
		$token = md5($user.":".microtime());

		$sql = "DELETE FROM sessions WHERE user='$user'";
		$res = mysqli_query($con, $sql);

		$sql = "INSERT INTO sessions (user, token) VALUES ('$user', '$token')";
		$res = mysqli_query($con, $sql);
	}
}

echo '{"token":"'.$token.'"}';
?>
