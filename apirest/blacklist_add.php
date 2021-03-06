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
$value = "";
$ban = "yes";
$action = "";
$code = "";
$text = "";
$type = "";
$detail = "";

if ($obj != null) {
	if (strpos($json, "\"user\""))  $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\"")) $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"value\""))  $value = parse($obj->{'value'}, "");
	if (strpos($json, "\"ban\""))    $ban = parse($obj->{'ban'}, "");
	if (strpos($json, "\"action\"")) $action = parse($obj->{'action'}, "");
	if (strpos($json, "\"code\""))   $code = parse($obj->{'code'}, "");
	if (strpos($json, "\"text\""))   $text = parse($obj->{'text'}, "");
	if (strpos($json, "\"type\""))   $type = parse($obj->{'type'}, "");
	if (strpos($json, "\"detail\"")) $detail = parse($obj->{'detail'}, "");
}
elseif ($json != "") die('{"data":""}');

if ($json == "" || $user == "" || $token == "") die('{"data":""}');

$sql = "SELECT id FROM sessions WHERE user='$user' AND token='$token'";
$res = mysqli_query($con, $sql);
if ($res->num_rows < 1) die('{"data":""}');

if ($action == "drop") {
	$code = "";
	$text = "";
}

$sql = "INSERT INTO blacklist (value, detail, ban, action, code, text, type) VALUES ('$value', '$detail', '$ban', '$action', '$code', '$text', '$type')";
$res = mysqli_query($con, $sql);

echo '{"success":true, "msg":"Los cambios han sido guardados"}';

mysqli_close($con);
?>
