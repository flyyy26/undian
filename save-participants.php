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

// Get names, npks, unit_kerja, category_id, and id_user from POST request
$names = $_POST['names'];
$npks = $_POST['npks'];
$unit_kerjas = $_POST['unit_kerja']; // Get unit_kerja from the form
$category_id = intval($_POST['category_id']); // Convert to integer for security
$id_user = intval($_POST['id_user']); // Convert to integer for security

// Prepare an SQL statement to insert each participant
$stmt = $conn->prepare("INSERT INTO participants (npk, nama, unit_kerja, category_id, id_user) VALUES (?, ?, ?, ?, ?)");

// Check if the statement was prepared successfully
if (!$stmt) {
    die("Statement preparation failed: " . $conn->error);
}

for ($i = 0; $i < count($names); $i++) {
    $name = trim($names[$i]);
    $npk = trim($npks[$i]);
    $unit_kerja = trim($unit_kerjas[$i]); // Get unit_kerja for each participant

    if (!empty($name) && !empty($npk) && !empty($unit_kerja)) {
        // Bind parameters to the SQL query
        $stmt->bind_param("sssii", $npk, $name, $unit_kerja, $category_id, $id_user);
        
        // Execute the statement
        if (!$stmt->execute()) {
            // Handle execution error
            echo "Failed to insert participant: " . $stmt->error;
        }
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Redirect to a success page or back to the input form
header("Location: dashboard.php?success=1");
exit();
?>
