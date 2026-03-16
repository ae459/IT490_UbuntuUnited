<?php

function getDb() {
	$host = getenv("DB_HOST") ?: "10.185.132.28";
	$db   = getenv("DB_NAME") ?: "ticketdb";
	$user = getenv("DB_USER") ?: "appuser";
	$pass = getenv("DB_PASS") ?: "app123";
	$port = getenv("DB_PORT") ?: "3306";

	$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
	$pdo = new PDO($dsn, $user, $pass, [
		PDO::ATTR_TIMEOUT => 3,
	]);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $pdo;
}	
