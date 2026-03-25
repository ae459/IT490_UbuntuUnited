<?php

function getDb() {
	$host = "10.185.132.28";
	$db   = "ticketdb";
	$user = "appuser";
	$pass = "app123";

	$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
	$pdo = new PDO($dsn, $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $pdo;
}
