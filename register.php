<?php
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
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Periksa apakah password dan konfirmasi password cocok
    if ($password !== $confirm_password) {
        echo 'Password dan konfirmasi password tidak cocok!';
        exit();
    }

    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah username atau email sudah digunakan
    $checkUserQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo 'Username atau email sudah digunakan, silakan pilih yang lain.';
        exit();
    }


    // Jika username tersedia, simpan data pengguna baru
    $insertQuery = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('sss', $username, $hashed_password, $email);

    if ($stmt->execute()) {
        echo 'Registrasi berhasil! Anda sekarang bisa login.';
        header('Location: login.php'); // Redirect ke halaman login
        exit();
    } else {
        echo 'Terjadi kesalahan saat registrasi, silakan coba lagi.';
    }
}
?>

<!-- HTML Form untuk Registrasi -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>
<body>
    <div class="page_login">
        <img src="images/heading_register.png" alt="" class="heading_register">
        <div class="box_login">
            <form method="POST" action="">
                <div class="input_login">
                    <iconify-icon icon="iconamoon:profile-fill"></iconify-icon>
                    <input type="text" name="username" placeholder="Username" required><br>
                </div>
                <div class="input_login">
                    <iconify-icon icon="ic:round-email"></iconify-icon>
                    <input type="email" name="email" placeholder="Email" required><br>
                </div>

                <div class="input_login">
                    <iconify-icon icon="mdi:password"></iconify-icon>
                    <input type="password" name="password" placeholder="Password" required><br>
                </div>

                <div class="input_login">
                    <iconify-icon icon="mdi:password"></iconify-icon>
                    <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required><br>
                </div>

                <button type="submit">Register</button>
            </form>
        </div>
        <span class="link_other">Sudah punya akun? <a href="login.php">Login</a></span>
    </div>
</body>
</html>
