<?php

session_start();

session_unset();
session_destroy();

header("Location: admin_dashboard.php");

session_start();
$_SESSION['message'] = "You have been successfully logged out.";

header("Location: admin_login.php");


exit();

?>