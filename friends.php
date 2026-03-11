<?php
$friends = [
    ["user_id" => 101, "username" => "alex23", "status" => "Friend"],
    ["user_id" => 102, "username" => "maria_dev", "status" => "Friend"],
    ["user_id" => 103, "username" => "samit", "status" => "Pending"]
];

$requests = [
    ["user_id" => 201, "username" => "kevinIT"],
    ["user_id" => 202, "username" => "jessica99"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UbuntuUnited - Friends</title>
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
        <h1>Friends</h1>
        <p class="subtitle">Add friends, view your friend list, and accept requests.</p>

        <div class="form-card">
            <h2>Add Friend</h2>
            <form action="#" method="post">
                <label for="friend_input">Username or User ID</label>
                <input type="text" id="friend_input" name="friend_input" placeholder="Enter username or user id">

                <div class="button-row">
                    <button type="submit">Add Friend</button>
                </div>
            </form>
        </div>

        <h2 class="section-title">Your Friends</h2>
        <div class="card-grid">
            <?php foreach ($friends as $friend): ?>
                <div class="card">
                    <h2><?php echo $friend["username"]; ?></h2>
                    <p><strong>User ID:</strong> <?php echo $friend["user_id"]; ?></p>
                    <p><strong>Status:</strong> <?php echo $friend["status"]; ?></p>

                    <div class="button-row">
                        <a class="btn secondary-btn" href="#">View Profile</a>
                        <a class="btn secondary-btn" href="#">Invite to Event</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 class="section-title">Pending Friend Requests</h2>
        <div class="card-grid">
            <?php foreach ($requests as $request): ?>
                <div class="card">
                    <h2><?php echo $request["username"]; ?></h2>
                    <p><strong>User ID:</strong> <?php echo $request["user_id"]; ?></p>

                    <div class="button-row">
                        <a class="btn" href="#">Accept Request</a>
                        <a class="btn danger-btn" href="#">Decline</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>