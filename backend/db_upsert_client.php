<?php
require_once(__DIR__ . "/../rabbitmqphp_example/rabbitMQLib.inc");

function dbSend($req) {
$iniPath = __DIR__ . "/../rabbitmqphp_example/testRabbitMQ.ini";
$client = new rabbitMQClient($iniPath, "testServer");
return $client->send_request($req);
}

function upsertVenue($tmId, $name, $city, $state) {
return dbSend([
"type" => "upsert_venue",
"request_id" => uniqid(),
"tm_id" => (string)$tmId,
"name" => (string)$name,
"city" => (string)$city,
"state" => (string)$state
]);
}

function upsertEvent($tmId, $title, $eventDate, $venueId, $status) {
return dbSend([
"type" => "upsert_event",
"request_id" => uniqid(),
"tm_id" => (string)$tmId,
"title" => (string)$title,
"event_date" => (string)$eventDate,
"venue_id" => (int)$venueId,
"status" => (string)$status
]);
}
