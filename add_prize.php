<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan pengguna sudah login
    if (!isset($_SESSION['user_id'])) {
        echo 'Unauthorized';
        exit();
    }

    $user_id = intval($_SESSION['user_id']);
    $winner_id = intval($_POST['winner_id']);
    $doorprize = trim($_POST['doorprize']);

    // Buat koneksi ke database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "lucky_draw";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update hadiah untuk pemenang
    $sql = "UPDATE winner SET doorprize = ? WHERE id = ? AND id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $doorprize, $winner_id, $user_id);

    if ($stmt->execute()) {
        echo 'Hadiah berhasil ditambahkan';
    } else {
        echo 'Error: ' . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
