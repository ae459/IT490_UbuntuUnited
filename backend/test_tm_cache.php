<?php
require_once(__DIR__ . "/env_loader.php");
require_once(__DIR__ . "/ticketmaster_api.php");
require_once(__DIR__ . "/db_upsert_client.php");

loadEnv(__DIR__ . "/../.env");

$key = isset($_ENV["TICKETMASTER_API_KEY"]) ? $_ENV["TICKETMASTER_API_KEY"] : "";
if ($key === "") {
echo "Missing TICKETMASTER_API_KEY in .env\n";
exit();
}

$res = tmSearchEvents("drake", $key);
if (!isset($res["success"]) || !$res["success"]) {
print_r($res);
exit();
}

if (!isset($res["data"]["_embedded"]["events"])) {
echo "No events found\n";
exit();
}

$events = $res["data"]["_embedded"]["events"];
$countSaved = 0;

foreach ($events as $ev) {

if (!isset($ev["id"]) || !isset($ev["name"])) {
continue;
}

$tmEventId = $ev["id"];
$title = $ev["name"];

$eventDate = "";
if (isset($ev["dates"]["start"]["dateTime"])) {
$eventDate = date("Y-m-d H:i:s", strtotime($ev["dates"]["start"]["dateTime"]));
} else if (isset($ev["dates"]["start"]["localDate"])) {
$date = $ev["dates"]["start"]["localDate"];
$time = isset($ev["dates"]["start"]["localTime"]) ? $ev["dates"]["start"]["localTime"] : "00:00:00";
$eventDate = $date . " " . $time;
} else {
continue;
}

$status = "AVAILABLE";

if (!isset($ev["_embedded"]["venues"][0])) {
continue;
}

$v = $ev["_embedded"]["venues"][0];

if (!isset($v["id"]) || !isset($v["name"])) {
continue;
}

$tmVenueId = $v["id"];
$venueName = $v["name"];
$venueCity = isset($v["city"]["name"]) ? $v["city"]["name"] : "";
$venueState = isset($v["state"]["stateCode"]) ? $v["state"]["stateCode"] : "";

$vRes = upsertVenue($tmVenueId, $venueName, $venueCity, $venueState);
if (!isset($vRes["success"]) || !$vRes["success"]) {
continue;
}

$venueId = isset($vRes["venue_id"]) ? (int)$vRes["venue_id"] : 0;
if ($venueId == 0) {
continue;
}

$eRes = upsertEvent($tmEventId, $title, $eventDate, $venueId, $status);
if (isset($eRes["success"]) && $eRes["success"]) {
$countSaved++;
}
}

echo "Saved/Updated events: " . $countSaved . "\n";
echo "DONE\n";
