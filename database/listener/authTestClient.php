<?php

require_once(__DIR__ . "/../../rabbitmqphp_example/rabbitMQLib.inc");

$client = new rabbitMQClient(__DIR__ . "/../../rabbitmqphp_example/testRabbitMQ.ini", "testServer");

$username = "ridham_test1";
$password = "pass123";


echo "REGISTER\n";
$req = [
	"type" => "register",
	"request_id" => uniqid(),
	"username" => $username,
	"password" => $password

];
$res = $client->send_request($req);
print_r($res);

echo "\nBAD LOGIN\n";
$req2 = [
	"type" => "login",
	"request_id" => uniqid(),
	"username" => $username,
	"password" => "wrongpass"

];
$res2 = $client->send_request($req2);
print_r($res2);

echo "\nGOOD LOGIN\n";
$req3 = [
	"type" => "login",
	"request_id" => uniqid(),
	"username" => $username,
	"password" => $password
];
$res3 = $client->send_request($req3);
print_r($res3);

if (isset($res3["session_key"])) {
	echo "\nVALIDATE SESSION\n";
	$req4 = [
		"type" => "validate_session",
		"request_id" => uniqid(),
		"session_key" => $res3["session_key"]
	];
	$res4 = $client->send_request($req4);
	print_r($res4);
}
