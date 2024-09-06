<?php
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$npk = isset($_POST['npk']) ? $conn->real_escape_string($_POST['npk']) : '';
$nama = isset($_POST['nama']) ? $conn->real_escape_string($_POST['nama']) : '';
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;

// Prepare and execute the SQL query
$sql = "INSERT INTO winner (npk, nama, category_id, id_user) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $npk, $nama, $category_id, $id_user);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
