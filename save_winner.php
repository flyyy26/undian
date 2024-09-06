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
$unitKerja = isset($_POST['unit_kerja']) ? $conn->real_escape_string($_POST['unit_kerja']) : '';
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;

if ($npk && $nama && $category_id && $id_user) {
    // First, insert the winner into the 'winner' table
    $insertWinnerQuery = "INSERT INTO winner (npk, nama, category_id, unit_kerja, id_user) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertWinnerQuery);
    $stmtInsert->bind_param("ssisi", $npk, $nama, $category_id, $unitKerja, $id_user);

    if ($stmtInsert->execute()) {
        // Then, update the 'participants' table to set is_winner = 1
        $updateParticipantQuery = "UPDATE participants SET is_winner = 1 WHERE npk = ? AND category_id = ?";
        $stmtUpdate = $conn->prepare($updateParticipantQuery);
        $stmtUpdate->bind_param("si", $npk, $category_id);

        if ($stmtUpdate->execute()) {
            echo "Success: Winner saved and participant updated.";
        } else {
            echo "Error updating participant: " . $stmtUpdate->error;
        }

        $stmtUpdate->close();
    } else {
        echo "Error inserting winner: " . $stmtInsert->error;
    }

    $stmtInsert->close();
} else {
    echo "Error: Missing required data.";
}

$conn->close();
?>
