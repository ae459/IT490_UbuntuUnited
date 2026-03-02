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
    <title>Home Page</title>
</head>
<body>

<h2>Home Page</h2>

<p>Welcome <?php echo $username;  ?>!</p>

<p>This is the homepage after successful login.</p>

<p><a href="logout.php">Logout</a></p>

</body>
</html>
