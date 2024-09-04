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

// Get names, npks, and category_id from POST request
$names = $_POST['names'];
$npks = $_POST['npks'];
$category_id = intval($_POST['category_id']); // Convert to integer for security

// Prepare an SQL statement to insert each participant
$stmt = $conn->prepare("INSERT INTO participants (npk, nama, category_id) VALUES (?, ?, ?)");

// Check if the statement was prepared successfully
if (!$stmt) {
    die("Statement preparation failed: " . $conn->error);
}

for ($i = 0; $i < count($names); $i++) {
    $name = trim($names[$i]);
    $npk = trim($npks[$i]);

    if (!empty($name) && !empty($npk)) {
        // Bind parameters to the SQL query
        $stmt->bind_param("ssi", $npk, $name, $category_id);
        
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
header("Location: manage-users.php?success=1");
exit();
?>
