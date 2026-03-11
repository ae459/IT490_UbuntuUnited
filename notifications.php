<?php
$pendingNotifications = [
    [
        "title" => "Event Reminder",
        "message" => "Your event starts in 2 hours.",
        "channel" => "email",
        "status" => "pending"
    ]
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>UbuntuUnited - Notifications</title>
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

<h1>Notifications / 2FA</h1>

<h2>Send Notification</h2>

<form method="post" class="form-card">

<label>Title</label>
<input type="text" placeholder="Notification title">

<label>Message</label>
<textarea placeholder="Notification message"></textarea>

<label>Channel</label>
<select>
<option>Email</option>
<option>Text</option>
</select>

<button>Send Notification</button>

</form>

<h2>Pending Notifications</h2>

<?php foreach ($pendingNotifications as $notification): ?>

<div class="review-card">

<h3><?php echo $notification["title"]; ?></h3>

<p><?php echo $notification["message"]; ?></p>

<p>Channel: <?php echo $notification["channel"]; ?></p>

<p>Status: <?php echo $notification["status"]; ?></p>

<button>Mark Sent</button>

</div>

<?php endforeach; ?>

</div>
</div>

</body>
</html>