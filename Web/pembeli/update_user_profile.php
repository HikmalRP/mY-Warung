<?php require_once '../db_connection.php';
header("Content-Type: application/json");
$db = new DBConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'get') { // Ambil data user berdasarkan username 
        $current_username = $_POST['current_username'];
        $getQuery = $db->conn->prepare("SELECT nama FROM db_user WHERE username = ?");
        $getQuery->bind_param("s", $current_username);
        $getQuery->execute();
        $result = $getQuery->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'nama' => $user['nama']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }
        exit;
    } // Mode update profil 
    $current_username = $_POST['current_username'];
    $new_username = $_POST['new_username'];
    $password = $_POST['password'];
    $nama = $_POST['nama']; // Cek apakah username baru sudah digunakan oleh user lain 
    $cekQuery = $db->conn->prepare("SELECT id FROM db_user WHERE username = ? AND username != ?");
    $cekQuery->bind_param("ss", $new_username, $current_username);
    $cekQuery->execute();
    $cekResult = $cekQuery->get_result();
    if ($cekResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan']);
        exit;
    } // Update profil 
    $updateQuery = $db->conn->prepare("UPDATE db_user SET username = ?, password = ?, nama = ? WHERE username = ?");
    $updateQuery->bind_param("ssss", $new_username, $password, $nama, $current_username);
    if ($updateQuery->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid']);
}
