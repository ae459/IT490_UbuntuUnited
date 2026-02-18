<?php
session_start();
session_destory();
header("Location: login.php");
exit();
?>