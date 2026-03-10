<?php
$events = [
    [
        "id" => 1,
        "title" => "Drake Live in Newark",
        "date" => "March 20, 2026",
        "location" => "Prudential Center",
        "category" => "Concert",
        "description" => "Live music event with a huge crowd."
    ],
    [
        "id" => 2,
        "title" => "Summer EDM Festival",
        "date" => "April 5, 2026",
        "location" => "MetLife Stadium",
        "category" => "Festival",
        "description" => "Outdoor EDM festival with DJs and lights."
    ],
    [
        "id" => 3,
        "title" => "Comedy Night Special",
        "date" => "April 15, 2026",
        "location" => "NJPAC",
        "category" => "Comedy",
        "description" => "Stand-up comedy night with top comedians."
    ]
];
?>

<!DOCTYPE html>
<html>
<head>
<title>UbuntuUnited - Events</title>
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
<a href="logout.php">Logout</a>
</div>
</div>

<div class="page-wrapper">
<div class="container">

<h1>Browse Events</h1>

<div class="search-box">
<input type="text" placeholder="Search events, artists, venues">
<button>Search</button>
</div>

<div class="card-grid">

<?php foreach ($events as $event): ?>

<div class="card">

<h2><?php echo $event["title"]; ?></h2>

<p><strong>Date:</strong> <?php echo $event["date"]; ?></p>

<p><strong>Location:</strong> <?php echo $event["location"]; ?></p>

<p><?php echo $event["description"]; ?></p>

<div class="button-row">

<a class="btn" href="event_details.php?id=<?php echo $event["id"]; ?>">
View Event
</a>

<a class="btn secondary-btn" href="reviews.php">
Write Review
</a>

<a class="btn secondary-btn" href="#">
Book Event
</a>

<a class="btn secondary-btn" href="#">
Invite Friends
</a>

</div>

</div>

<?php endforeach; ?>

</div>

</div>
</div>

</body>
</html>