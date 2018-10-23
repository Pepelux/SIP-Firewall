<?php
require("parse.php");
require("conecta.php");
$con = Conectar("read");

header('Content-type: application/json; charset=utf-8');
header('access-control-allow-origin: *');

$data = "";
$sql = "SELECT country, code FROM country_codes WHERE code NOT IN (SELECT country FROM blacklist WHERE type='country') AND code IN (SELECT country FROM blocked)";
$sql .= "ORDER BY code ASC";

// Obtenemos los registros paginados
$result = mysqli_query($con, $sql);
$totaldata = $result->num_rows;

$cont = 0;
$data .= '{"total":"'.$totaldata.'","data":[';

for ($i = 0; $i < $totaldata; $i++) {
	$row = $result->fetch_object();
	$data .= '{"value":"'.$row->code.'","text":"'.$row->country.' ('.$row->code.')"}';

	$cont++;
	if ($cont < $totaldata) $data .= ",";	
}

$data .= "]}";

echo $data;

mysqli_free_result($result);
mysqli_close($con);
?>
