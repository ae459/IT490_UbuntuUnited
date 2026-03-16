<?php

function tmSearchEvents($keyword, $apiKey, $size = 50, $city = "", $stateCode = "") {
$keyword = trim($keyword);
$apiKey = trim($apiKey);

if ($keyword === "") {
return [
"success" => false,
"message" => "Keyword is empty"
];
}

if ($apiKey === "") {
return [
"success" => false,
"message" => "API key is empty"
];
}

$params = [];
$params["apikey"] = $apiKey;
$params["keyword"] = $keyword;

if (!is_numeric($size) || (int)$size < 1) {
$size = 50;
}
$params["size"] = (int)$size;

if (trim($city) !== "") {
$params["city"] = trim($city);
}

if (trim($stateCode) !== "") {
$params["stateCode"] = trim($stateCode);
}

$url = "https://app.ticketmaster.com/discovery/v2/events.json?" . http_build_query($params);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$body = curl_exec($ch);

if ($body === false) {
$err = curl_error($ch);
curl_close($ch);
return [
"success" => false,
"message" => "cURL error: " . $err
];
}

$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$json = json_decode($body, true);

if (!is_array($json)) {
return [
"success" => false,
"message" => "Bad JSON returned"
];
}

if ($status !== 200) {
$msg = "HTTP " . $status;
if (isset($json["errors"][0]["detail"])) {
$msg = $json["errors"][0]["detail"];
}
return [
"success" => false,
"message" => $msg,
"data" => $json
];
}

$count = 0;
if (isset($json["page"]["totalElements"])) {
$count = (int)$json["page"]["totalElements"];
}

return [
"success" => true,
"count" => $count,
"data" => $json
];
}

