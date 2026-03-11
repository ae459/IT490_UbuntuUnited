<?php

$events = [

1 => [
"title" => "Drake Live in Newark",
"date" => "March 20, 2026",
"time" => "8:00 PM",
"location" => "Prudential Center",
"description" => "Big live concert experience.",
"rating" => "4.8/5"
],

2 => [
"title" => "Summer EDM Festival",
"date" => "April 5, 2026",
"time" => "6:00 PM",
"location" => "MetLife Stadium",
"description" => "EDM festival with DJs.",
"rating" => "4.6/5"
],

3 => [
"title" => "Comedy Night Special",
"date" => "April 15, 2026",
"time" => "7:30 PM",
"location" => "NJPAC",
"description" => "Comedy show with live audience.",
"rating" => "4.5/5"
]

];

$id = isset($_GET["id"]) ? $_GET["id"] : 1;

$event = $events[$id];

?>

<!DOCTYPE html>
<html>
<head>
<title>Event Details</title>
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

<h1><?php echo $event["title"]; ?></h1>

<p><strong>Date:</strong> <?php echo $event["date"]; ?></p>

<p><strong>Time:</strong> <?php echo $event["time"]; ?></p>

<p><strong>Location:</strong> <?php echo $event["location"]; ?></p>

<p><strong>Rating:</strong> <?php echo $event["rating"]; ?></p>

<p><?php echo $event["description"]; ?></p>

<div class="button-row">

<a class="btn" href="notifications.php">Get Alerts</a>

<a class="btn secondary-btn" href="reviews.php">
Write Review
</a>

<a class="btn secondary-btn" href="recommendations.php">
See Recommendations
</a>

<a class="btn secondary-btn" href="invite.php">
Invite Friends
</a>

</div>

</div>

</div>

</body>
</html>