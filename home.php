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
	<title>UbuntuUnited Home</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <div class="logo">UbuntuUnited</div>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="events.php">Events</a>
        <a href="reviews.php">Reviews</a>
        <a href="recommendations.php">Recommendations</a>
        <a href="friends.php">Friends</a>
        <a href="invite.php">Invites</a>
        <a href="notifications.php">Notifications</a>
        <a href="search.php">Search</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="container">
        <h1>Welcome <?php echo htmlspecialchars($username); ?>!</h1>
        <p class="subtitle">UbuntuUnited is your event hub for browsing events, reading reviews, inviting friends, and managing notifications.</p>

        <div class="quick-links">
            <div class="quick-link-card">
                <h3>Browse Events</h3>
                <p>Look through concerts, artists, and venues.</p>
                <a class="btn" href="events.php">Open Events</a>
            </div>

            <div class="quick-link-card">
                <h3>Reviews</h3>
                <p>Read reviews and write your own event feedback.</p>
                <a class="btn" href="reviews.php">Open Reviews</a>
            </div>

            <div class="quick-link-card">
                <h3>Recommendations</h3>
                <p>See suggested events based on ratings and reviews.</p>
                <a class="btn" href="recommendations.php">View Recommendations</a>
            </div>

            <div class="quick-link-card">
                <h3>Friends</h3>
                <p>Manage your friend list and requests.</p>
                <a class="btn" href="friends.php">Open Friends</a>
            </div>

            <div class="quick-link-card">
                <h3>Invites</h3>
                <p>Create invite links and share events with others.</p>
                <a class="btn" href="invite.php">Create Invite</a>
            </div>

            <div class="quick-link-card">
                <h3>Notifications / 2FA</h3>
                <p>Manage alerts and prepare the text-based 2FA flow.</p>
                <a class="btn" href="notifications.php">Open Notifications</a>
            </div>

            <div class="quick-link-card">
                <h3>Search</h3>
                <p>Search artists, events, and venues in one place.</p>
                <a class="btn" href="search.php">Open Search</a>
            </div>

            <div class="quick-link-card">
                <h3>Logout</h3>
                <p>Sign out of your account safely.</p>
                <a class="btn danger-btn" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>