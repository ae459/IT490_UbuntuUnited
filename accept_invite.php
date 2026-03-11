<?php
$token = isset($_GET["token"]) ? $_GET["token"] : "";
$message = "";

if ($token != "") {
    $message = "Invite token found. This would call accept_invite_by_token in the backend.";
} else {
    $message = "No invite token was found in the URL.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UbuntuUnited - Accept Invite</title>
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
        <h1>Accept Invite</h1>
        <p class="subtitle">This page reads the invite token from the link.</p>

        <div class="details-card">
            <p><strong>Token:</strong> <?php echo htmlspecialchars($token); ?></p>
            <p><?php echo $message; ?></p>

            <div class="button-row">
                <a class="btn" href="#">Accept Invite</a>
                <a class="btn secondary-btn" href="events.php">Back to Events</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>