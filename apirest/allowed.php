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
	$sql = "SELECT callid, message FROM iplog WHERE id=$id";
	$res = mysqli_query($con, $sql);
	$row = mysqli_fetch_object($res);
	$callid = $row->callid;
	$message = $row->message;

	if ($callid != "") {
		$message = "";

		$sql = "SELECT message FROM iplog WHERE callid='$callid'";
		$res = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_object($res))
			$message .= $row->message."\n";
	}

	$data = '"message":"'.base64_encode($message).'"';

	echo "{".$data."}";
	exit;
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

//$sql = "SELECT id, ipsrc, method, proto, date, DATE_FORMAT(date, '%d/%m/%Y %H:%i:%s') AS date2, country, useragent, callid FROM iplog WHERE ipsrc<>'$pbxip'";
$sql = "SELECT id, ipsrc, method, proto, date, country, useragent, callid FROM iplog WHERE ipsrc<>'$pbxip'";
$sqlcont = "SELECT id FROM iplog WHERE ipsrc<>'$pbxip'";

$sql .= $sql_filter;
$sqlcont .= $sql_filter;

$sql .= " GROUP BY callid ORDER BY $order $dir LIMIT ".$end." OFFSET ".$start;
$sqlcont .= " GROUP BY callid";

// Obtenemos el número total de registros
$result = mysqli_query($con, $sqlcont);
$row = mysqli_fetch_object($result);
$totaldata = $result->num_rows;

// Obtenemos los registros paginados
$result = mysqli_query($con, $sql);

$cont = 0;
$data .= '{"total":"'.$totaldata.'","data":[';

while ($row = mysqli_fetch_object($result)) { 
	$blacklisted = 0;

	$sql2 = "SELECT code, reason FROM iplog WHERE callid='".$row->callid."' ORDER BY id DESC LIMIT 1";
	$res2 = mysqli_query($con, $sql2);
	$row2 = mysqli_fetch_object($res2);
	$code = $row2->code;
	$reason = $row2->reason;

	$sql2 = "SELECT COUNT(*) AS total FROM iplog WHERE callid='".$row->callid."'";
	$res2 = mysqli_query($con, $sql2);
	$row2 = mysqli_fetch_object($res2);
	$total = $row2->total;
	
	$sql2 = "SELECT id FROM blacklist WHERE type='ip' AND value='".$row->ipsrc."'";
	$res2 = mysqli_query($con, $sql2);
	if ($res2->num_rows > 0) $blacklisted = 1;
	else {
		$sql2 = "SELECT id FROM blacklist WHERE type='country' AND value='".$row->country."'";
		$res2 = mysqli_query($con, $sql2);
		if ($res2->num_rows > 0) $blacklisted = 1;
		else {
			$sql2 = "SELECT id FROM blacklist WHERE type='ua' AND value='".$row->useragent."'";
			$res2 = mysqli_query($con, $sql2);
			if ($res2->num_rows > 0) $blacklisted = 1;
		}
	}
	
	if ($cont > 0) $data .= ",";
	$data .= '{"id":"'.$row->id.'",';
	$data .= '"ipsrc":"'.$row->ipsrc.'",';
	$data .= '"method":"'.$row->method.'",';
	$data .= '"proto":"'.$row->proto.'",';
//	$data .= '"date2":"'.$row->date2.'",';
	$data .= '"date":"'.$row->date.'",';
	$data .= '"country":"'.$row->country.'",';
	$data .= '"useragent":"'.$row->useragent.'",';
	$data .= '"total":"'.$total.'",';
	$data .= '"code":"'.$code." ".$reason.'",';
	$data .= '"blacklisted":"'.$blacklisted.'",';
	$data .= '"proto":"'.strtoupper($row->proto).'"}';
	
	$cont++;
}

$data .= "]}";

echo $data;

mysqli_free_result($result);
mysqli_close($con);
?>
