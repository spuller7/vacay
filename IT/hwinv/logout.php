<?php
    session_start();
    unset($_SESSION["username"]);
    unset($_SESSION["password"]);

    echo "Logged out successfully";
    header('Location: login.php');
?>

