<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$sql = "SELECT npk, nama FROM participants";
if ($category_id > 0) {
    $sql .= " WHERE category_id = $category_id";
}

$result = $conn->query($sql);

$participants = [];
while ($row = $result->fetch_assoc()) {
    $participants[] = [
        'npk' => $row['npk'],
        'nama' => $row['nama']
    ];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($participants);
?>
