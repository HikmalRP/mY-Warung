<?php require_once '../db_connection.php';
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['username'])) {
        $username = $_GET['username'];
        $db = new DBConnection(); // Cari user_id berdasarkan username 
        $userQuery = $db->conn->prepare("SELECT id, nama FROM db_user WHERE username = ?");
        $userQuery->bind_param("s", $username);
        $userQuery->execute();
        $userResult = $userQuery->get_result();
        if ($userResult->num_rows > 0) {
            $userRow = $userResult->fetch_assoc();
            $userId = $userRow['id'];
            $namaUser = $userRow['nama']; // Ambil data history pembelian dari db_jual berdasarkan user_id 
            $purchaseQuery = $db->conn->prepare("SELECT * FROM db_jual WHERE user_id = ? ORDER BY created_at ASC");
            $purchaseQuery->bind_param("i", $userId);
            $purchaseQuery->execute();
            $purchaseResult = $purchaseQuery->get_result();
            $purchases = [];
            $urutan = 1;
            while ($row = $purchaseResult->fetch_assoc()) {
                $row['no_transaksi'] = $urutan;
                $row['nama'] = $namaUser;
                $purchases[] = $row;
                $urutan++;
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
