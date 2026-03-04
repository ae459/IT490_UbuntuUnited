<?php
session_start();
require_once(__DIR__ . "/rabbitmq_helper.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
 {

	$username = $_POST["username"];
	$password = $_POST["password"];
echo "REGISTER\n";
$req = [
	"type" => "register",
	"request_id" => uniqid(),
	"username" => $username,
	"password" => $password

];
$res = $client->send_request($req);
print_r($res);

echo "\nBAD LOGIN\n";
$req2 = [
	"type" => "login",
	"request_id" => uniqid(),
	"username" => $username,
	"password" => "wrongpass"

];
$res2 = $client->send_request($req2);
print_r($res2);

echo "\nGOOD LOGIN\n";
$req3 = [
	"type" => "login",
	"request_id" => uniqid(),
	"username" => $username,
	"password" => $password
];
$res3 = $client->send_request($req3);
print_r($res3);

if (isset($res3["session_key"])) {
	echo "\nVALIDATE SESSION\n";
	$req4 = [
		"type" => "validate_session",
		"request_id" => uniqid(),
		"session_key" => $res3["session_key"]
	];
	$res4 = $client->send_request($req4);
	print_r($res4);
}
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
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
    <input type="text" name="username"><br><br>

    Password:<br>
    <input type="password" name="password"><br><br>

    <input type="submit" value="Login">
</form>

<p><a href="register.php">Register</a></p>

</body>
</html>
