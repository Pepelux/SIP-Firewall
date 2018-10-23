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
$t = "";
$country = "";
$type = "";
$custom = "";

if ($obj != null) {
	if (strpos($json, "\"user\""))         $user = parse($obj->{'user'}, "");
	if (strpos($json, "\"token\""))        $token = parse($obj->{'token'}, "");
	if (strpos($json, "\"t\""))            $t = parse($obj->{'t'}, "");
	if (strpos($json, "\"country\""))      $country = parse($obj->{'country'}, "");
	if (strpos($json, "\"type\""))         $type = parse($obj->{'type'}, "");
	if (strpos($json, "\"customvalue\""))  $custom = parse($obj->{'customvalue'}, "");
}
elseif ($json != "") die('{"data":""}');

if ($json == "" || $user == "" || $token == "") die('{"data":""}');


if ($country != "") {
	if ($type == "deny") { // añadir a denegados
		$sql = "DELETE FROM destinations_deny WHERE country='$country'";
		$res = mysqli_query($con, $sql);

		$sql = "INSERT INTO destinations_deny (country) VALUES ('$country')";
		$res = mysqli_query($con, $sql);
	}
	else { // añadir a permitidos
		if ($type == "allow") {
			$sql = "DELETE FROM destinations_deny WHERE country='$country'";
			$res = mysqli_query($con, $sql);
		}
	}
}

if ($type == "custom") { // configuración por defecto
	if ($custom != "") {
		switch($custom) {
			case "spain":
				$sql = "DELETE FROM destinations_deny";
				$result = mysqli_query($con, $sql);

				$sql = "SELECT DISTINCT country FROM destinations WHERE country<>'España' ORDER BY country ASC";
				$result = mysqli_query($con, $sql);
				while ($row = mysqli_fetch_object($result)) { 
					$sql2 = "INSERT INTO destinations_deny (country) VALUES ('".$row->country."')";
					$result2 = mysqli_query($con, $sql2);
				}
				break;
			case "none":
				$sql = "DELETE FROM destinations_deny";
				$result = mysqli_query($con, $sql);

				$sql = "SELECT DISTINCT country FROM destinations ORDER BY country ASC";
				$result = mysqli_query($con, $sql);
				while ($row = mysqli_fetch_object($result)) { 
					$sql2 = "INSERT INTO destinations_deny (country) VALUES ('".$row->country."')";
					$result2 = mysqli_query($con, $sql2);
				}
				break;
			case "all":
				$sql = "DELETE FROM destinations_deny";
				$result = mysqli_query($con, $sql);
				break;
		}
	}
}

if ($t == "allow")
	$sql = "SELECT DISTINCT country FROM destinations WHERE country NOT IN (SELECT country FROM destinations_deny) ORDER BY country ASC";
elseif ($t == "deny")
	$sql = "SELECT country FROM destinations_deny ORDER BY country ASC";
else die('{"data":""}');

$data = "";

// Obtenemos el número total de registros
$result = mysqli_query($con, $sql);

$cont = 0;
$data .= '{"data":[';

while ($row = mysqli_fetch_object($result)) { 
	if ($cont > 0) $data .= ",";
	$data .= '{"country":"'.$row->country.'"}';
	
	$cont++;
}

$data .= "]}";

echo $data;

mysqli_free_result($result);
mysqli_close($con);
?>
