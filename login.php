<?php
session_start();
require_once("rabbitmq_helper.php");

$err = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_POST["username"];
$password = $_POST["password"];

$resp = sendToDB([
"type" => "login",
"request_id" => uniqid(),
"username" => $username,
"password" => $password
]);

if (isset($resp["success"]) && $resp["success"] == true) {
$_SESSION["session_key"] = $resp["session_key"];
header("Location: home.php");
exit();
} else {
$err = isset($resp["message"]) ? $resp["message"] : "Login failed";
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
if (!empty($err)) {
echo "<p class='error'>" . htmlspecialchars($err) . "</p>";
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
