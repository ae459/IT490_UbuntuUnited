<?php
session_start();
require_once(__DIR__ . "/rabbitmq_helper.php");

$msg= "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $req = array();
    $req["type"] = "register";
    $req["request_id"] = uniqid();
    $req["username"] = $username;
    $req["password"] = $password;

    $res = sendToDB($req);

    if (isset($res["success"]) && $res["success"] == true) {
        $msg= "Registration successful! You can login now.";
    } else {
        if (isset($res["message"])) {
            $msg= $res["message"];
        } else {
            $msg= "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Page</title>
</head>
<body>

<h2>Register Page</h2>

<?php
if (!empty($msg)) {
    echo "<p>$msg</p>";
}
?>

<form method="post" action="register.php">
    Username:<br>
    <input type="text" name="username" required><br><br>

    Password:<br>
    <input type="password" name="password"><br><br>

    <input type="submit" value="Register">
</form>

<p><a href="login.php">Back to Login</a></p>

</body>
</html>