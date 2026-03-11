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

$username = "";
if (isset($res["username"])) {
	$username = $res["username"];
}
?>
<!DOCTYPE html>
<html>
<head>
<!DOCTYPE html>
<html>
<head>
<title>UbuntuUnited Home</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<h2>Welcome <?php echo $username; ?>!</h2>

<p>This is the main homepage after login.</p>

<h3>Main Pages</h3>

<p><a href="events.php">Browse Events</a></p>

<p><a href="reviews.php">Reviews</a></p>

<p><a href="recommendations.php">Recommendations</a></p>

<p><a href="friends.php">Friends</a></p>

<p><a href="invite.php">Create Invite</a></p>

<p><a href="notifications.php">Notifications / 2FA</a></p>

<p><a href="search.php">Search Artists / Events / Venues</a></p>

<p><a href="logout.php">Logout</a></p>

</body>
</html>