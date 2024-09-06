<?php
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mencari pengguna berdasarkan username
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password yang diinput dengan yang ada di database
        if (password_verify($password, $user['password'])) {
            // Simpan informasi pengguna di session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php'); // Redirect ke dashboard atau halaman utama
            exit();
        } else {
            echo 'Password salah.';
        }
    } else {
        echo 'Username tidak ditemukan.';
    }
}
?>

<!-- HTML Form untuk Login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>
<body>
    <div class="page_login">
        <img src="images/heading_login.png" alt="" class="heading_login">
        <div class="box_login">
            <form method="POST" action="">
                <div class="input_login">
                    <iconify-icon icon="iconamoon:profile-fill"></iconify-icon>
                    <input type="text" name="username" placeholder="Username" required><br>
                </div>
                <div class="input_login">
                    <iconify-icon icon="mdi:password"></iconify-icon>
                    <input type="password" name="password" placeholder="Password" required><br>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
        <span class="link_other">Belum punya akun? <a href="register.php">Register</a></span>
    </div>
</body>
</html>

