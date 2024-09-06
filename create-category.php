<?php
// Start the session
session_start();

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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user_id from session
$id_user = $_SESSION['user_id'];

// Handle the category creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the category name from POST request
    $categoryName = $conn->real_escape_string($_POST['name']);

    // Prepare the SQL statement to insert the new category
    $sql = "INSERT INTO categories (name, id_user) VALUES ('$categoryName', $id_user)";

    if ($conn->query($sql) === TRUE) {
        // Redirect to the manage-users.php page after successful creation
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
