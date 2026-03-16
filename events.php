<?php
session_start();

require_once("rabbitmq_helper.php");
require_once(__DIR__ . "/database/listener/db.php");

$keyword = "";
if (isset($_GET["keyword"])) {
$keyword = trim($_GET["keyword"]);
}

$errorMsg = "";

if ($keyword !== "") {

$req = [
"type" => "get_ticketmaster_events",
"request_id" => uniqid(),
"keyword" => $keyword,
"size" => 50
];

$resp = sendToDB($req);

if (!is_array($resp) || !isset($resp["success"]) || $resp["success"] != true) {
if (is_array($resp) && isset($resp["message"])) {
$errorMsg = $resp["message"];
} else {
$errorMsg = "Failed to fetch events from Ticketmaster.";
}
}
}

$pdo = getDb();

$sql = "
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
";

$params = [];

if ($keyword !== "") {
$sql .= " WHERE e.title LIKE ? ";
$params[] = "%" . $keyword . "%";
}

$sql .= " ORDER BY e.event_date ASC LIMIT 50 ";

$q = $pdo->prepare($sql);
$q->execute($params);
$events = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Events</title>
</head>
<body>

<h1>Events</h1>

<form method="get" action="events.php">
<label>Search:</label>
<input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
<button type="submit">Search</button>
<a href="events.php">Clear</a>
</form>

<br>

<?php if ($errorMsg != "") { ?>
<p style="color:red;"><?php echo htmlspecialchars($errorMsg); ?></p>
<?php } ?>

<?php if (!$events || count($events) == 0) { ?>
<p>No events found.</p>
<?php } else { ?>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
<th>Title</th>
<th>Date</th>
<th>Venue</th>
<th>Status</th>
<th>TM ID</th>
<th>Details</th>
</tr>

<?php foreach ($events as $e) { ?>
<tr>
<td><?php echo htmlspecialchars($e["title"]); ?></td>
<td><?php echo htmlspecialchars($e["event_date"]); ?></td>
<td>
<?php
$venueText = "";
if (!empty($e["venue_name"])) {
$venueText .= $e["venue_name"];
}
if (!empty($e["venue_city"]) || !empty($e["venue_state"])) {
$venueText .= " (" . $e["venue_city"] . ", " . $e["venue_state"] . ")";
}
if ($venueText == "") {
$venueText = "Unknown Venue";
}
echo htmlspecialchars($venueText);
?>
</td>
<td><?php echo htmlspecialchars($e["status"]); ?></td>
<td><?php echo htmlspecialchars(!empty($e["tm_event_id"]) ? $e["tm_event_id"] : "N/A"); ?></td>
<td>
<a href="event_details.php?event_id=<?php echo (int)$e["id"]; ?>">View</a>
</td>
</tr>
<?php } ?>

</table>

<?php } ?>

</body>
</html>
