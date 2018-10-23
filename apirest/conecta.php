<?php
$pbxip = "MY_IP_ADDRESS";

class DB {
	// Configuration information:
	private static $user = 'security';
	private static $pass = 'STRONG_WEB_PASSWORD';
	private static $config = array(
		'write' => array('localhost'),
		'read' => array('localhost')
	);

	// Static method to return a database connection:
	// $server = ['write', 'read']
	public static function getConnection($mode) {
		$servers = self::$config[$mode];

		$connection = false;

		// Keep trying to make a connection:
		while (!$connection && count($servers)) {
			$key = array_rand($servers);
			$connection = mysqli_connect($servers[$key], self::$user, self::$pass, 'security');

			if (!$connection) unset($servers[$key]); // We couldn't connect.  Remove this server
		}

		// If we never connected to any database, throw an exception:
		if (!$connection) throw new Exception("Failed: {$server} database");

		mysqli_set_charset($connection, 'utf8');
		mysqli_query($connection, 'SET sql_safe_updates=0');

		return $connection;
	}
}

function Conectar($mode='write') {
	return DB::getConnection($mode);
}

function Desconectar($con) { 
	$con->close();
}
?>
