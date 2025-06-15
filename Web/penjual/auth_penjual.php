<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['penjual_logged_in'])) {
    header("Location: penjual_login.php");
    exit();
}
