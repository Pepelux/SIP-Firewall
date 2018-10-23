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
$g = 1;
$type = "all";

if ($obj != null) {
	if (strpos($json, "\"user\""))  $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\"")) $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"g\""))     $g = parsen($obj->{'g'}, 1);
	if (strpos($json, "\"type\""))  $type = parse($obj->{'type'}, "all");
}
elseif ($json != "") die('{"data":""}');

if ($json == "" || $user == "" || $token == "") die('{"data":""}');

$data = "";

if ($g == 1) {
	$sql = "SELECT COUNT(*) AS total FROM blocked";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d1 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d1 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM iplog";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d2 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d2 = $row->total;
	}

	$data .= '{"deny":"'.$d1.'","allow":"'.$d2.'"}';
}
elseif ($g == 2) {
	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='ip'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d1 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d1 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='ua'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d2 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d2 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='country'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d3 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d3 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='pike'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d4 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d4 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='rules'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d5 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d5 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='destination'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d6 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d6 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE type='user'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d7 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d7 = $row->total;
	}

	$data .= '{"ip":"'.$d1.'",';
	$data .= '"ua":"'.$d2.'",';
	$data .= '"country":"'.$d3.'",';
	$data .= '"dos":"'.$d4.'",';
	$data .= '"sqli":"'.$d5.'",';
	$data .= '"dst":"'.$d6.'",';
	$data .= '"user":"'.$d7.'"}';
}
elseif ($g == 5) {
	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE method='OPTIONS'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d1 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d1 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE method='INVITE'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d2 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d2 = $row->total;
	}

	$sql = "SELECT COUNT(*) AS total FROM blocked WHERE method='REGISTER'";
	if ($type == "ip") $sql .= " GROUP BY ipsrc, method, useragent";
	$res = mysqli_query($con, $sql);
	if ($type == "ip") $d3 = $res->num_rows;
	else {
		$row = $res->fetch_object();
		$d3 = $row->total;
	}

	$data .= '{"options":"'.$d1.'",';
	$data .= '"invite":"'.$d2.'",';
	$data .= '"register":"'.$d3.'"}';
}
elseif ($g == 3) {
	$cont = 0;
	$data2 = '{"results":[';

	if ($type == "ip") {
		$sql = "SELECT blocked.useragent, COUNT(*) AS total FROM blocked, ( SELECT useragent FROM blocked GROUP BY useragent, ipsrc ORDER BY useragent ASC ) AS ua WHERE  blocked.useragent = ua.useragent GROUP BY useragent ORDER BY total DESC LIMIT 10";
		$res = mysqli_query($con, $sql);

		while ($row = $res->fetch_object()) {
			if ($cont > 0) $data2 .= ",";
			$data2 .= '{"name":"'.$row->useragent.'","value":"'.$row->total.'"}';
			$cont++;
		}

		$data2 .= ']}';

		$data3 = json_decode($data2, true)['results'];
		usort($data3, function ($a, $b) {
			return $b['value'] <=> $a['value'];
		});

		$data = '{"data":';
		$data .= json_encode($data3);
		$data .= '}';

		echo $data;
		exit;
	}
	else {
		$sql = "SELECT useragent, COUNT(*) AS total FROM blocked GROUP BY useragent ORDER BY total DESC LIMIT 10";
		$res = mysqli_query($con, $sql);

		while ($row = $res->fetch_object()) {
			$ua = $row->useragent;
			$t = $row->total;

			if ($cont > 0) $data .= ",";
			$data .= '{"name":"'.$ua.'","value":"'.$t.'"}';
			$cont++;
		}

		$data = '{"data":['.$data.']}';

		echo $data;
		exit;
	}
}
else {
	$cont = 0;
	$data2 = '{"results":[';

	if ($type == "ip") {
		$sql = "SELECT DISTINCT country FROM blocked ORDER BY country ASC LIMIT 10";
		$res = mysqli_query($con, $sql);

		while ($row = $res->fetch_object()) {
			$sql2 = "SELECT country FROM blocked WHERE country='".$row->country."' GROUP BY country, ipsrc";
			$res2 = mysqli_query($con, $sql2);
			$t = $res2->num_rows;

			if ($cont > 0) $data2 .= ",";
			$data2 .= '{"name":"'.$row->country.'","value":"'.$t.'"}';
			$cont++;
		}

		$data2 .= ']}';

		$data3 = json_decode($data2, true)['results'];
		usort($data3, function ($a, $b) {
			return $b['value'] <=> $a['value'];
		});

		$data = '{"data":';
		$data .= json_encode($data3);
		$data .= '}';

		echo $data;
		exit;
	}
	else {
		$sql = "SELECT country, COUNT(*) AS total FROM blocked GROUP BY country ORDER BY total DESC LIMIT 10";
		$res = mysqli_query($con, $sql);

		while ($row = $res->fetch_object()) {
			$country = $row->country;
			$t = $row->total;

			if ($cont > 0) $data .= ",";
			$data .= '{"name":"'.$country.'","value":"'.$t.'"}';
			$cont++;
		}

		$data = '{"data":['.$data.']}';

		echo $data;
		exit;
	}
}

echo $data;

mysqli_free_result($res);
mysqli_close($con);
?>
