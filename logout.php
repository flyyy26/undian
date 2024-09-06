<?php
session_start();
session_unset(); // Menghapus semua data sesi
session_destroy(); // Menghancurkan sesi
header('Location: login.php'); // Mengarahkan pengguna ke halaman login
exit();
?>
