<?php
require("parse.php");
require("conecta.php");
$con = Conectar("read");

header('Content-type: application/json; charset=utf-8');
header('access-control-allow-origin: *');

$json = file_get_contents('php://input');
$obj = json_decode($json);

$id = 0;
$user = "";
$token = "";
$value = "";
$detail = "";

if ($obj != null) {
	if (strpos($json, "\"id\""))     $id = parsen($obj->{'id'}, 0);
	if (strpos($json, "\"user\""))  $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\"")) $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"value\""))  $value = parse($obj->{'value'}, "");
	if (strpos($json, "\"detail\"")) $detail = parse($obj->{'detail'}, "");
}
elseif ($json != "") die('{"data":""}');

if ($json == "" || $user == "" || $token == "") die('{"data":""}');

$sql = "SELECT id FROM sessions WHERE user='$user' AND token='$token'";
$res = mysqli_query($con, $sql);
if ($res->num_rows < 1) die('{"data":""}');

$sql = "UPDATE whitelist SET value='$value', detail='$detail' WHERE id=$id";
$res = mysqli_query($con, $sql);

echo '{"success":true, "msg":"Los cambios han sido guardados"}';

mysqli_close($con);
?>
