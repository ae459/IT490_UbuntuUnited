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

<h2>Login Page</h2>

<?php
if (!empty($error)) {
echo "<p style='color:red;'>$error</p>";
}
?>

<form method="post" action="login.php">
Username:<br>
<input type="text" name="username" required><br><br>

Password:<br>
<input type="password" name="password" required><br><br>

<input type="submit" value="Login">
</form>

<p><a href="register.php">Register</a></p>

</body>
</html>