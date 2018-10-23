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
$field = "";
$value = "";
$matches = 0;

if ($obj != null) {
	if (strpos($json, "\"user\""))    $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\""))   $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"field\""))   $field = parse($obj->{'field'}, "");
	if (strpos($json, "\"value\""))   $value = parse($obj->{'value'}, "");
	if (strpos($json, "\"matches\"")) $matches = parsen($obj->{'matches'}, 1);
}
elseif ($json != "") die('{"data":"error"}');

if ($json == "" || $user == "" || $token == "" || $field == "") die('{"data":"error"}');

$sql = "SELECT id FROM sessions WHERE user='$user' AND token='$token'";
$res = mysqli_query($con, $sql);
if ($res->num_rows < 1) die('{"data":"error"}');

if ($value != "") {
	$sql = "UPDATE rules SET value='$value', matches=$matches WHERE name='$field'";
	mysqli_query($con, $sql);

	if ($field == "ua") {
		$fp = fopen('/var/www/html/kam/kamailio-local.cfg', 'w');

		if ($value == "") fwrite($fp, "server_signature=no\n");
		else           fwrite($fp, "server_signature=yes\n");

		fwrite($fp, "server_header=\"Server: Private Proxy\"\n");
		fwrite($fp, "user_agent_header=\"User-Agent: Private Proxy\"\n");

		fclose($fp);

		$sql = "UPDATE reload SET reload='yes'";
		mysqli_query($con, $sql);
	}

	die('{"data":""}');
}

$data = "";
$sql = "SELECT value, matches FROM rules WHERE name='$field'";

$data .= '{"data":[';

$result = mysqli_query($con, $sql);
$row = $result->fetch_object();
$data .= '{"value":"'.$row->value.'"},';
$data .= '{"matches":"'.$row->matches.'"}';

$data .= "]}";

echo $data;

mysqli_free_result($result);
mysqli_close($con);
?>
