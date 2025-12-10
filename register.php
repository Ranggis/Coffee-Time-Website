<?php
// Hubungkan ke database
include 'koneksi.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Cek apakah username sudah ada di database?
    $check_query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($check_query) > 0) {
        // Jika username sudah terpakai
        echo "<script>alert('Username sudah digunakan, silakan cari yang lain!');</script>";
    } else {
        // 2. Jika username aman, masukkan ke database
        // (Default role kita anggap 'user' biasa)
        $insert = mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '$password')");

        if ($insert) {
            echo "<script>
                    alert('Registrasi Berhasil! Silakan Login.');
                    window.location = 'login.php';
                  </script>";
        } else {
            echo "<script>alert('Gagal Mendaftar!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #eee; }
        .box { background: white; padding: 30px; border-radius: 8px; width: 300px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h3 { color: #b6895b; margin-bottom: 20px; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; background: #b6895b; color: white; border: none; cursor: pointer; border-radius: 5px; font-weight: bold; }
        button:hover { background: #8c6a45; }
        .link { margin-top: 15px; font-size: 14px; }
        .link a { color: #b6895b; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Buat Akun Baru</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Buat Username" required>
            <input type="password" name="password" placeholder="Buat Password" required>
            <button type="submit" name="register">Daftar Sekarang</button>
        </form>
        
        <div class="link">
            Sudah punya akun? <br>
            <a href="login.php">Login disini</a>
        </div>
    </div>
</body>
</html>