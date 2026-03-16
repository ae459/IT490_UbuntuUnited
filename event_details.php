<?php
session_start();

require_once("rabbitmq_helper.php");
require_once(__DIR__ . "/database/listener/db.php");

$pdo = getDb();

$eventId = isset($_GET["event_id"]) ? (int)$_GET["event_id"] : 0;
if ($eventId <= 0) {
echo "Missing event_id.";
exit();
}

$sessionKey = isset($_SESSION["session_key"]) ? $_SESSION["session_key"] : "";

$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "add_review") {

$rating = isset($_POST["rating"]) ? (int)$_POST["rating"] : 0;
$reviewText = isset($_POST["review_text"]) ? trim($_POST["review_text"]) : "";

if ($sessionKey === "") {
$errorMsg = "You must be logged in to post a review.";
} else if ($rating < 1 || $rating > 5) {
$errorMsg = "Rating must be 1 to 5.";
} else if ($reviewText === "") {
$errorMsg = "Review text cannot be empty.";
} else {

$resp = sendToDB([
"type" => "add_review",
"request_id" => uniqid(),
"session_key" => $sessionKey,
"event_id" => $eventId,
"rating" => $rating,
"review_text" => $reviewText
]);

if (is_array($resp) && isset($resp["success"]) && $resp["success"] == true) {
$successMsg = "Review saved!";
} else {
$errorMsg = isset($resp["message"]) ? $resp["message"] : "Review failed.";
}
}
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "book_event") {

$qty = isset($_POST["qty"]) ? (int)$_POST["qty"] : 1;
if ($qty < 1) $qty = 1;

if ($sessionKey === "") {
$errorMsg = "You must be logged in to book.";
} else {
$resp = sendToDB([
"type" => "book_event",
"request_id" => uniqid(),
"session_key" => $sessionKey,
"event_id" => $eventId,
"qty" => $qty
]);

if (is_array($resp) && isset($resp["success"]) && $resp["success"] == true) {
$successMsg = "Booked successfully!";
} else {
$errorMsg = isset($resp["message"]) ? $resp["message"] : "Booking failed.";
}
}
}

$q = $pdo->prepare("
SELECT
e.id,
e.tm_event_id,
e.title,
e.event_date,
e.status,
v.name AS venue_name,
v.city AS venue_city,
v.state AS venue_state
FROM events e
LEFT JOIN venues v ON v.id = e.venue_id
WHERE e.id = ?
LIMIT 1
");
$q->execute([$eventId]);
$event = $q->fetch(PDO::FETCH_ASSOC);

if (!$event) {
echo "Event not found.";
exit();
}

$q2 = $pdo->prepare("
SELECT r.rating, r.review_text, r.created_at, u.username
FROM reviews r
JOIN users u ON u.id = r.user_id
WHERE r.event_id = ?
ORDER BY r.created_at DESC
LIMIT 50
");
$q2->execute([$eventId]);
$reviews = $q2->fetchAll(PDO::FETCH_ASSOC);

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

<h1><?php echo htmlspecialchars($event["title"]); ?></h1>

<?php if ($errorMsg != "") { ?>
<p class="error"><?php echo htmlspecialchars($errorMsg); ?></p>
<?php } ?>

<?php if ($successMsg != "") { ?>
<p style="color:green;"><?php echo htmlspecialchars($successMsg); ?></p>
<?php } ?>

<p><strong>Date:</strong> <?php echo htmlspecialchars($event["event_date"]); ?></p>

<p><strong>Venue:</strong>
<?php
$venueText = "Unknown Venue";
if (!empty($event["venue_name"])) {
$venueText = $event["venue_name"];
if (!empty($event["venue_city"]) || !empty($event["venue_state"])) {
$venueText .= " (" . $event["venue_city"] . ", " . $event["venue_state"] . ")";
}
}
echo htmlspecialchars($venueText);
?>
</p>

<p><strong>Status:</strong> <?php echo htmlspecialchars($event["status"]); ?></p>
<p><strong>Ticketmaster ID:</strong> <?php echo htmlspecialchars($event["tm_event_id"] ? $event["tm_event_id"] : "N/A"); ?></p>

<hr>

<h2>Book This Event</h2>
<form method="post" action="event_details.php?event_id=<?php echo (int)$eventId; ?>">
<input type="hidden" name="action" value="book_event">
<label>Quantity:</label>
<input type="number" name="qty" value="1" min="1">
<button type="submit">Book</button>
</form>

<hr>

<h2>Reviews</h2>

<?php if (!$reviews || count($reviews) == 0) { ?>
<p>No reviews yet.</p>
<?php } else { ?>
<?php foreach ($reviews as $r) { ?>
<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
<p><strong><?php echo htmlspecialchars($r["username"]); ?></strong>
(<?php echo htmlspecialchars($r["rating"]); ?>/5)
- <?php echo htmlspecialchars($r["created_at"]); ?></p>
<p><?php echo nl2br(htmlspecialchars($r["review_text"])); ?></p>
</div>
<?php } ?>
<?php } ?>

<hr>

<h2>Write a Review</h2>
<form method="post" action="event_details.php?event_id=<?php echo (int)$eventId; ?>">
<input type="hidden" name="action" value="add_review">

<label>Rating (1-5):</label>
<input type="number" name="rating" min="1" max="5" required>

<br><br>

<label>Review:</label><br>
<textarea name="review_text" rows="4" cols="50" required></textarea>

<br><br>
<button type="submit">Submit Review</button>
</form>

</div>
</div>

</body>
</html>
