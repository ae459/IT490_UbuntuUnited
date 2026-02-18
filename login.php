<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username == "admin" && $password =="1234") {
        $_SESSION["user"] = $username;
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid username or password";
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
if (isset($error)) {
    echo "<p style='color:red;'>$error</p>";
}
?>

<form methods="post" action="login.php">
    Username:<br>
    <input type="text" name="username"><br><br>

    Password: <br>
    <input type="password" name="password"><br><br>

    <input type="submit" value="Login">
</form>

</body>
</html>