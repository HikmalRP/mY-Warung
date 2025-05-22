<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['username'])) {
        $username = $_GET['username'];

        $db = new DBConnection();

        // Cari user_id berdasarkan username
        $userQuery = $db->conn->prepare("SELECT id FROM db_user WHERE username = ?");
        $userQuery->bind_param("s", $username);
        $userQuery->execute();
        $userResult = $userQuery->get_result();

        if ($userResult->num_rows > 0) {
            $userRow = $userResult->fetch_assoc();
            $userId = $userRow['id'];

            // Ambil data history pembelian dari db_jual berdasarkan user_id
            $purchaseQuery = $db->conn->prepare("SELECT * FROM db_jual WHERE user_id = ?");
            $purchaseQuery->bind_param("i", $userId);
            $purchaseQuery->execute();
            $purchaseResult = $purchaseQuery->get_result();

            $purchases = [];
            while ($row = $purchaseResult->fetch_assoc()) {
                $purchases[] = $row;
            }

            echo json_encode($purchases);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username is required']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
