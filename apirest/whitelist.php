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
$page = 1;
$end = 10;
$type = "";
$order = "value";
$dir = "ASC";

if ($obj != null) {
	if (strpos($json, "\"id\""))    $id = parsen($obj->{'id'}, 0);
	if (strpos($json, "\"user\""))  $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\"")) $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"page\""))  $page = parsen($obj->{'page'}, 1);
	if (strpos($json, "\"end\""))   $end = parsen($obj->{'end'}, 10);
	if (strpos($json, "\"type\""))  $type = parse($obj->{'type'}, "");
	if (strpos($json, "\"order\"")) $order = parse($obj->{'order'}, "value");
	if (strpos($json, "\"dir\""))   $dir = parse($obj->{'dir'}, "ASC");
}
elseif ($json != "") die('{"data":"error"}');

if ($json == "" || $user == "" || $token == "") die('{"data":"error"}');

$start = ($page-1)*$end;

$sql = "SELECT id FROM sessions WHERE user='$user' AND token='$token'";
$res = mysqli_query($con, $sql);
if ($res->num_rows < 1) die('{"data":"error"}');

if ($id != 0 && $type == "") {
	$sql = "SELECT id, value, detail FROM whitelist WHERE id=$id ";
	$sqlcont = "SELECT count(*) AS total FROM whitelist WHERE id=$id ";
}
else {
	$sql = "SELECT id, value, detail FROM whitelist WHERE type='$type'";
	$sqlcont = "SELECT count(*) AS total FROM whitelist WHERE type='$type'";
}

$sql .= " ORDER BY $order $dir LIMIT ".$end." OFFSET ".$start;

// Obtenemos el número total de registros
$res = mysqli_query($con, $sqlcont);
$row = mysqli_fetch_object($res);
$totaldata = $row->total;

// Obtenemos los registros paginados
$res = mysqli_query($con, $sql);

$cont = 0;
$data = '{"total":"'.$totaldata.'","data":[';

while ($row = mysqli_fetch_object($res)) { 
	if ($cont > 0) $data .= ",";
	$data .= '{"id":"'.$row->id.'",';
	$data .= '"value":"'.$row->value.'",';
	$data .= '"detail":"'.$row->detail.'"}';
	
	$cont++;
}

$data .= "]}";

echo $data;

mysqli_free_result($res);
mysqli_close($con);
?>
