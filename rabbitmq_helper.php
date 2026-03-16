<?php
require_once(__DIR__ . "/rabbitmqphp_example/rabbitMQLib.inc");

function sendToDB($req) {
ini_set("default_socket_timeout", "3");

$iniPath = __DIR__ . "/rabbitmqphp_example/testRabbitMQ.ini";

try {
$client = new rabbitMQClient($iniPath, "testServer");

$resp = $client->send_request($req);

if ($resp === null || $resp === false) {
return ["success" => false, "message" => "No response from DB listener (is it running?)"];
}

return $resp;

} catch (Throwable $e) {
return ["success" => false, "message" => "RabbitMQ error: " . $e->getMessage()];
}
}
