<?php
function parsen($valor, $default)
{
	if (!is_numeric($valor))
		return $default;

	$valor = addslashes($valor);
	$valor = htmlspecialchars($valor, ENT_QUOTES);
	
	return $valor;
}

function parse($valor, $default)
{
	$valor = addslashes($valor);
	$valor = htmlspecialchars($valor, ENT_QUOTES);
	
	if ($valor == "")
		return $default;

	return $valor;
}

function parsed($valor)
{
	$valor = addslashes($valor);
	$valor = htmlspecialchars($valor, ENT_QUOTES);
	
	if (is_numeric(substr($valor, 0, 2)) && is_numeric(substr($valor, 3, 2)) && is_numeric(substr($valor, 6, 4)) &&
		substr($valor, 2, 1) == "/" && substr($valor, 5, 1) == "/" and strlen($valor) == 10)
			return $valor;
	else		
		return date('d/m/Y');
}
?>
