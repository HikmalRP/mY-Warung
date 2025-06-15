<?php
session_start();
session_unset();
session_destroy();
header("Location: penjual_login.php");
exit();
