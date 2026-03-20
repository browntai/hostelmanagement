<?php
    session_start();
    include('../includes/dbconn.php');
    include_once('../includes/log-helper.php');
    if(isset($_SESSION['id'])) {
        logActivity($_SESSION['id'], $_SESSION['login'], ucfirst($_SESSION['role'] ?? 'Client'), 'Logout', 'Client logged out');
    }
    unset($_SESSION['id']);
    session_destroy();
    header('Location:../index.php');
?>
