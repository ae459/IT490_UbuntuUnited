<?php
session_start();
require_once(__DIR__ . "/rabbitmq_helper.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

$username = $_POST["username"];
$password = $_POST["password"];

$req = array();
$req["type"] = "login";
$req["request_id"] = uniqid();
$req["username"] = $username;
$req["password"] = $password;

$res = sendToDB($req);

if (is_array($res) && isset($res["success"]) && $res["success"] == true) {
$_SESSION["session_key"] = $res["session_key"];
header("Location: home.php");
exit();
} else {
if (is_array($res) && isset($res["message"])) {
$error = $res["message"];
} else {
$error = "Login failed";
}
}
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-container">
    <h1>Login Page</h1>
    <p class="subtitle">Sign in to continue to UbuntuUnited.</p>

    <?php
    if (!empty($error)) {
        echo "<p class='error'>$error</p>";
    }
    ?>

    <form method="post" action="login.php">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="auth-links">
        <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>