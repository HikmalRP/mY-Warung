<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $weight = $_POST['weight'];
    $courier = $_POST['courier'];
    $service = $_POST['service'];
    $shipping_cost = $_POST['shipping_cost'];
    $total_with_shipping = $_POST['total_with_shipping'];
    $amount_paid = $_POST['amount_paid'];
    $change = $_POST['change'];
    $items = $_POST['items'];
    $paymentProof = $_FILES['payment_proof'];

    $db = new DBConnection();

    // Ambil user_id dari tabel db_user berdasarkan username
    $userQuery = $db->conn->prepare("SELECT id FROM db_user WHERE username = ?");
    $userQuery->bind_param("s", $username);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['id'];
    } else {
        file_put_contents('log.txt', "User tidak ditemukan untuk username: $username" . PHP_EOL, FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit();
    }

    // Validasi dan unggah file gambar
    if ($paymentProof['error'] === UPLOAD_ERR_OK && $paymentProof['size'] > 0) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($paymentProof['name']);
        if (!move_uploaded_file($paymentProof['tmp_name'], $targetFile)) {
            file_put_contents('log.txt', "Gagal mengunggah file: " . $paymentProof['name'] . PHP_EOL, FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file']);
            exit();
        }
    } else {
        file_put_contents('log.txt', "File gambar tidak valid." . PHP_EOL, FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'File gambar tidak valid']);
        exit();
    }

    // Simpan data ke database
    $query = $db->conn->prepare("INSERT INTO db_jual 
        (user_id, origin, destination, weight, courier, service, shipping_cost, 
        total_with_shipping, amount_paid, change_amount, items, payment_proof)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $query->bind_param(
        "isssssssssss",
        $user_id, $origin, $destination, $weight, $courier, $service,
        $shipping_cost, $total_with_shipping, $amount_paid, $change, $items, $targetFile
    );

    if ($query->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        file_put_contents('log.txt', "Error SQL: " . $query->error . PHP_EOL, FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => $query->error]);
    }
}
?>
