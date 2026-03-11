<?php
$pendingNotifications = [
    [
        "title" => "2FA Login",
        "message" => "Verification code will be sent to the phone.",
        "channel" => "text",
        "status" => "pending"
    ],
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

<div class="container">

<h1>Notifications / 2FA</h1>

<h2>Send Notification</h2>

<form>

<label>Title</label>
<input type="text" placeholder="Notification title">

<label>Message</label>
<textarea placeholder="Notification message"></textarea>

<label>Channel</label>
<select>
<option>Email</option>
<option>Text</option>
</select>

<label>Phone Number</label>
<input type="text" placeholder="Enter phone number for SMS">

<label>2FA Code</label>
<input type="text" placeholder="Enter code or backend will generate">

<button>Send Notification</button>

<button type="button">Send 2FA Code</button>

</form>

<h2>Pending Notifications</h2>

<?php foreach ($pendingNotifications as $notification): ?>

<div class="container">

<h3><?php echo $notification["title"]; ?></h3>

<p><?php echo $notification["message"]; ?></p>

<p>Channel: <?php echo $notification["channel"]; ?></p>

<p>Status: <?php echo $notification["status"]; ?></p>

<button>Mark Sent</button>

</div>

<?php endforeach; ?>

</div>

</body>
</html>