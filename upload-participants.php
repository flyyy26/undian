<?php
require 'vendor/autoload.php'; // Sertakan autoload jika menggunakan Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection parameters
$servername = "localhost";
$username = "root"; // Sesuaikan dengan username database Anda
$password = ""; // Sesuaikan dengan password database Anda
$dbname = "lucky_draw";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a file was uploaded, a category_id was submitted, and id_user was provided
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0 && isset($_POST['category_id']) && isset($_POST['id_user'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $category_id = $_POST['category_id']; // Get category_id from form
    $id_user = $_POST['id_user']; // Get id_user from form

    // Load the uploaded Excel file
    $spreadsheet = IOFactory::load($fileTmpPath);
    $worksheet = $spreadsheet->getActiveSheet();

    // Prepare an SQL statement to insert each participant, including category_id and id_user
    $stmt = $conn->prepare("INSERT INTO participants (npk, nama, category_id, id_user) VALUES (?, ?, ?, ?)");

    // Iterate through the rows in the worksheet, starting from the second row
    $rowIterator = $worksheet->getRowIterator(2); // Start from row 2

    foreach ($rowIterator as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells, even if not set

        // Assuming the NPK is in the second column (B) and Name is in the third column (C)
        $cells = [];
        foreach ($cellIterator as $cell) {
            $cells[] = trim($cell->getValue());
        }

        $npk = $cells[1]; // NPK (Column B)
        $nama = $cells[2]; // Name (Column C)

        // Check if NPK and Name are not empty
        if (!empty($npk) && !empty($nama)) {
            $stmt->bind_param("ssii", $npk, $nama, $category_id, $id_user); // "s" for string, "i" for integer
            $stmt->execute();
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect to manage-users.php with success message
    header("Location: dashboard.php?success=1");
    exit();
} else {
    echo "Error: File, category, or user ID was not uploaded correctly.";
}
