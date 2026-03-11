<?php
$results = [
    ["type" => "Artist", "name" => "Drake", "extra" => "Concert Artist"],
    ["type" => "Event", "name" => "Summer EDM Festival", "extra" => "MetLife Stadium"],
    ["type" => "Venue", "name" => "Prudential Center", "extra" => "Newark, NJ"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UbuntuUnited - Search</title>
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
        <h1>Search</h1>
        <p class="subtitle">Search for artists, events, and venues.</p>

        <div class="form-card">
            <h2>Search Form</h2>
            <form action="#" method="get">
                <label for="search_term">Search Term</label>
                <input type="text" id="search_term" name="search_term" placeholder="Type something to search">

                <label for="search_type">Search Type</label>
                <select id="search_type" name="search_type">
                    <option value="artists">Artists</option>
                    <option value="events">Events</option>
                    <option value="venues">Venues</option>
                </select>

                <div class="button-row">
                    <button type="submit">Search</button>
                </div>
            </form>
        </div>

        <h2 class="section-title">Search Results</h2>
        <div class="card-grid">
            <?php foreach ($results as $result): ?>
                <div class="card">
                    <h2><?php echo $result["name"]; ?></h2>
                    <p><strong>Type:</strong> <?php echo $result["type"]; ?></p>
                    <p><strong>Info:</strong> <?php echo $result["extra"]; ?></p>

                    <div class="button-row">
                        <a class="btn secondary-btn" href="#">View More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>