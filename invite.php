<?php
$generatedLink = "http://0.0.0.0:8000/accept_invite.php?token=abc123invite";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UbuntuUnited - Invites</title>
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
        <h1>Create Invite</h1>
        <p class="subtitle">Make an invite link for an event and send it to a friend.</p>

        <div class="form-card">
            <h2>Invite Form</h2>
            <form action="#" method="post">
                <label for="event_id">Event ID</label>
                <input type="text" id="event_id" name="event_id" placeholder="Enter event id">

                <label for="invitee_user_id">Invitee User ID (optional)</label>
                <input type="text" id="invitee_user_id" name="invitee_user_id" placeholder="Enter user id if needed">

                <div class="button-row">
                    <button type="submit">Create Invite</button>
                </div>
            </form>
        </div>

        <div class="info-box">
            <h2>Generated Invite Link</h2>
            <p><?php echo $generatedLink; ?></p>

            <div class="button-row">
                <a class="btn" href="accept_invite.php?token=abc123invite">Open Invite</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>