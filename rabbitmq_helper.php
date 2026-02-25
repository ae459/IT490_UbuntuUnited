<?php
require_once(__DIR__ . "/rabbitmqphp_example/rabbitMQLib.inc");

function sendToDB($msg) {
	$iniPath = __DIR__ . "/rabbitmqphp.example/testRabbitMQ.ini";
	$client = new rabbitMQClient($iniPath, "testServer");
	return $client->send_request($msg);
}
