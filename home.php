<?php
session_start();
require_once(__DIR__ . "/rabbitmq_helper.php");

if (!isset($_SESSION["session_key"])) {
    header("Location: login.php");
    exit();
}

$req = array();
$req["type"] = "validate_session";
$req["request_id"] = uniqid();
$req["session_key"] = $_SESSION["session_key"];

$res = sendToDB($req);

if(!isset($res["valid"]) || $res["valid"] != true) {
	session_destroy();
	header("Location: login.php");
	exit();
}

$username = isset($res["username"]) ? $res["username"] : "";

$events = array();
$eventsError = "";

$eventReq = array();
$eventReq["type"] = "get_ticketmaster_events";
$eventReq["request_id"] = uniqid();

$eventRes = sendToDB($eventReq);

if(is_array($eventRes) && isset($eventRes["success"]) && $eventRes["success"] === true) {
	if(isset($eventRes["events"]) && is_array($eventRes["events"])) {
		$events = $eventRes["events"];
	} else {
		$eventsError = isset($eventRes["message"]) ? $eventRes["message"] : "Could not retrieve events";
	}
} else {
	$eventsError = isset($eventRes["message"]) ? $eventRes["message"] : "Could Not Retrieve Events";
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Home Page</h2>

<p>Welcome <?php echo $username;  ?>!</p>




</body>
</html>
