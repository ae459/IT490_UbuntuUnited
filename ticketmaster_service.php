<?php

require_once(__DIR__ . "/env_loader.php");

loadEnvFile(__DIR__ . "/.env");

function fetchTicketmasterEvents(array $params = array()) {
	$apiKey = getenv('TICKETMASTER_API_KEY');
	if ($apiKey === false || trim($apiKey) === "") {
		return array(
			"success" => false,
			"message" => "Missing TICKETMASTER_API_KEY"
		);
	}

	$query = array(
		"apikey" => $apiKey,
		"size" =>isset($params["size"]) ? $params["size"] : 10,
	);

	$allowed = array(
		"keyword", "city","stateCode", "countryCode", "postalCode", 
		"startDateTime", "endDateTime", "classificationName", "page", "sort"
	);

	foreach ($allowed as $key) {
		if (isset($params[$key]) && $params[$key] !== "") {
			$query[$key] = $params[$key];
		}
	}

	$url = "https://app.ticketmaster.com/discovery/v2/events.json?" . http_build_query($query); 

	$context = stream_context_create(array(
		"http" => array(
			"method" => "GET",
			"timeout"=> 10,
			"ignore_errors" => true
		)
	));

	$raw = file_get_contents($url, false, $context);
	if($raw === false) {
		$err = error_get_last();
		return array (
			"success" => false,
			"message" => isset($err["message"]) ? $err["message"] : "Ticketmaster request failed"
		);
	}

	$data = json_decode($raw, true);
	if (!is_array($data)) {
		return array(
			"success" => false,
			"message" => "Invalid JSON response from Ticketmaster"
		);
	}

	if (isset($data["fault"])) {
		return array(
			"success" => false,
			"message" => "Ticketmaster API error",
			"fault" => $data["fault"]
		);
	}

	$events = array();
	if(isset($data["_embedded"]["events"]) && is_array($data["_embedded"]["events"])) {
		foreach ($data["_embedded"]["events"] as $event) {
			$venueName = "";
			if (isset($event["_embedded"]["venues"][0]["name"])) {
				$venueName = $event["_embedded"]["venues"][0]["name"];
			}

			$events[] = array(
				"id" => isset($event["id"]) ? $event["id"] : "",
				"name" => isset($event["name"]) ? $event["name"] : "",
				"date" => isset($event["dates"]["start"]["localDate"]) ? $event["dates"]["start"]["localDate"] : "",
				"time" => isset($event["dates"]["start"]["localTime"]) ? $event["dates"]["start"]["localTime"] : "",
				"url" => isset($event["url"]) ? $event["url"] : "",
				"venue" => $venueName
			);
		}
	}

	return array(
		"success" => true,
		"count" => count($events),
		"events" => $events
	);
}

?>

