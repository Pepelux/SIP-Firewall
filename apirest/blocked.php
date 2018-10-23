<?php
require("parse.php");
require("conecta.php");
$con = Conectar("read");

header('Content-type: application/json; charset=utf-8');
header('access-control-allow-origin: *');

$json = file_get_contents('php://input');
$obj = json_decode($json);
$filters = $obj->{'filters'};

$id = 0;
$user = "";
$token = "";
$page = 1;
$end = 10;
$order = "date2";
$dir = "ASC";

if ($obj != null) {
	if (strpos($json, "\"user\""))    $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\""))   $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"id\""))      $id = parse($obj->{'id'}, "");
	if (strpos($json, "\"page\""))    $page = parsen($obj->{'page'}, 1);
	if (strpos($json, "\"end\""))     $end = parsen($obj->{'end'}, 10);
	if (strpos($json, "\"order\""))   $order = parse($obj->{'order'}, "value");
	if (strpos($json, "\"dir\""))     $dir = parse($obj->{'dir'}, "ASC");
}
elseif ($json != "") die('{"data":"error"}');

if ($json == "" || $user == "" || $token == "") die('{"data":"error"}');

$sql = "SELECT id FROM sessions WHERE user='$user' AND token='$token'";
$res = mysqli_query($con, $sql);
if ($res->num_rows < 1) die('{"data":"error"}');

if ($id > 0) {
	$sql = "SELECT message FROM blocked WHERE id=$id";
	$result = mysqli_query($con, $sql);

	if ($result->num_rows == 0) {
		echo '{"data":"error"}';
	}
	else {
		$row = mysqli_fetch_object($result);
		$message = $row->message;

		$data = '"message":"'.base64_encode($message).'"';

		echo "{".$data."}";
		exit;
	}
}

$start = ($page-1)*$end;

if ($order == "date2") $order = "date";

$data = "";
$sql_filter = "";

foreach ($filters as $f) {
	$type = $f->type;
	$value = $f->value;
	
//	if ($type == "date")
//		$value = substr($value, 6, 4)."-".substr($value, 3, 2)."-".substr($value, 0, 2)." ".substr($value, 11);

	if ($f->op == "igual")       $sql_filter .= " AND UCASE(".$type.")='".strtoupper($value)."'";
	if ($f->op == "distinto")    $sql_filter .= " AND UCASE(".$type.")!='".strtoupper($value)."'";
	if ($f->op == "contiene")    $sql_filter .= " AND UCASE(".$type.") LIKE '%".strtoupper($value)."%'";
	if ($f->op == "no contiene") $sql_filter .= " AND UCASE(".$type.") NOT LIKE '%".strtoupper($value)."%'";
}

//$sql = "SELECT id, ipsrc, method, proto, date, DATE_FORMAT(date, '%d/%m/%Y %H:%i:%s') AS date2, country, useragent, type FROM blocked WHERE 1=1 ";
$sql = "SELECT id, ipsrc, method, proto, date, country, useragent, type FROM blocked WHERE 1=1 ";
$sqlcont = "SELECT count(*) AS total FROM blocked WHERE 1=1 ";

$sql .= $sql_filter;
$sqlcont .= $sql_filter;

$sql .= " ORDER BY $order $dir LIMIT ".$end." OFFSET ".$start;

// Obtenemos el número total de registros
$result = mysqli_query($con, $sqlcont);
$row = mysqli_fetch_object($result);
$totaldata = $row->total;

// Obtenemos los registros paginados
$result = mysqli_query($con, $sql);

$cont = 0;
$data .= '{"total":"'.$totaldata.'","data":[';

while ($row = mysqli_fetch_object($result)) { 
	$type = $row->type;
	
	if ($type == "ip") $type = "Bloqueo por IP";
	if ($type == "ua") $type = "Bloqueo por UserAgent";
	if ($type == "country") $type = "Bloqueo por País";
	if ($type == "pike") $type = "Bloqueo por DoS";
	if ($type == "user") $type = "Bloqueo por Usuario";
	if ($type == "domain") $type = "Bloqueo por Dominio";
	if ($type == "destination") $type = "Destino no permitido";

	if ($cont > 0) $data .= ",";
	$data .= '{"id":"'.$row->id.'",';
	$data .= '"ipsrc":"'.$row->ipsrc.'",';
	$data .= '"method":"'.$row->method.'",';
	$data .= '"proto":"'.$row->proto.'",';
//	$data .= '"date2":"'.$row->date2.'",';
	$data .= '"date":"'.$row->date.'",';
	$data .= '"country":"'.$row->country.'",';
	$data .= '"useragent":"'.$row->useragent.'",';
	$data .= '"type":"'.$type.'",';
	$data .= '"proto":"'.strtoupper($row->proto).'"}';
	
	$cont++;
}

$data .= "]}";

echo $data;

mysqli_free_result($result);
mysqli_close($con);
?>
