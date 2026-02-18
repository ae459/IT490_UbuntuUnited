<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
</head>
<body>

<h2>Home Page</h2>

<p>Welcome <?php echo $_SESSION["user"]; ?>!</p>

<a hred="logout.php">Logout</a>

</body>
</html>