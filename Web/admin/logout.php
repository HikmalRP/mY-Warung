<?php
session_start();
session_unset(); // hapus semua variabel session
session_destroy(); // hancurkan session di server
header("Location: admin_login.php");
exit();
