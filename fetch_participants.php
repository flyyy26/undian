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
$query = "SELECT npk, nama FROM participants WHERE id_user = $id_user";

if ($category_id !== null && $category_id != 'all') {
    $query .= " AND category_id = $category_id";
}

$participantsResult = $conn->query($query);

$participantsArray = [];

while ($row = $participantsResult->fetch_assoc()) {
    $participantsArray[] = [
        'npk' => $row['npk'],
        'nama' => $row['nama']
    ];
}

$conn->close();

// Output as JSON
header('Content-Type: application/json');
echo json_encode($participantsArray);
?>
