<?php
require("parse.php");
require("conecta.php");
$con = Conectar("read");

header('Content-type: application/json; charset=utf-8');
header('access-control-allow-origin: *');

$json = file_get_contents('php://input');
$obj = json_decode($json);

$user = "";
$token = "";
$login = "";
$oldpass = "";
$newpass = "";

if ($obj != null) {
	if (strpos($json, "\"user\""))     $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\""))    $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"login\""))    $login = parse($obj->{'login'}, "");
	if (strpos($json, "\"oldpass\""))  $oldpass = parse($obj->{'oldpass'}, "");
	if (strpos($json, "\"newpass\""))  $newpass = parse($obj->{'newpass'}, "");
}
elseif ($json != "") die('{"data":""}');

if ($json == "" || $user == "" || $token == "" || $login == "" || $newpass == "") die('{"data":""}');

$sql = "SELECT id FROM sessions WHERE user='$user' AND token='$token'";
$res = mysqli_query($con, $sql);
if ($res->num_rows < 1) die('{"data":""}');

$sql = "UPDATE web_users SET password='".md5($newpass)."' WHERE login='$login' AND password='".md5($oldpass)."'";
mysqli_query($con, $sql);

die('{"data":""}');
?>
