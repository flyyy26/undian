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

// Get the category_id from the POST request
$categoryId = intval($_POST['category_id']);

// Start a transaction
$conn->begin_transaction();

try {
    // Delete participants associated with the category
    $sql = "DELETE FROM participants WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    
    // Delete the category
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();
    
    echo "Category and associated participants deleted successfully.";
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo "Error deleting category: " . $e->getMessage();
}

// Close the connection
$stmt->close();
$conn->close();
?>
