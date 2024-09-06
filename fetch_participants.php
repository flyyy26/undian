<?php
session_start(); // Pastikan sesi dimulai

// Database connection parameters
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil id_user dari sesi
$id_user = $_SESSION['user_id'];

// Ambil kategori berdasarkan id_user
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

$participantsArray = [];

// Siapkan query untuk mengambil peserta yang belum pernah menang (is_winner = 0)
$query = "SELECT npk, nama, unit_kerja FROM participants WHERE id_user = ? AND is_winner = 0";

// Jika category_id ada dan bukan 'all', tambahkan filter berdasarkan category_id
if ($category_id !== null && $category_id != 'all') {
    $query .= " AND category_id = ?";
}

$stmt = $conn->prepare($query);

if ($category_id !== null && $category_id != 'all') {
    // Bind id_user dan category_id jika ada
    $stmt->bind_param("ii", $id_user, $category_id);
} else {
    // Bind hanya id_user jika category_id tidak ada
    $stmt->bind_param("i", $id_user);
}

$stmt->execute();
$result = $stmt->get_result();

// Loop melalui hasil query dan masukkan ke dalam array peserta
while ($row = $result->fetch_assoc()) {
    $participantsArray[] = [
        'npk' => $row['npk'],
        'nama' => $row['nama'],
        'unit_kerja' => $row['unit_kerja']
    ];
}

$stmt->close();
$conn->close();

// Output as JSON
header('Content-Type: application/json');
echo json_encode($participantsArray);
?>
