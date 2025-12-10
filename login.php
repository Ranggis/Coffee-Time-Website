<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username dan password
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        // Simpan data ke session
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role']; 

        // LOGIKA PEMISAH HALAMAN
        if ($data['role'] == 'admin') {
            // Jika ADMIN, arahkan ke Dashboard Admin
            echo "<script>alert('Selamat Datang Admin!'); window.location='dashboard.php';</script>";
        } else {
            // Jika USER BIASA, arahkan ke Halaman Menu
            echo "<script>alert('Login Berhasil!'); window.location='DashboardUser.php';</script>";
        }

    } else {
        echo "<script>alert('Username atau Password salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #eee; }
        .box { background: white; padding: 30px; border-radius: 8px; width: 300px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h3 { color: #b6895b; }
        input { width: 90%; padding: 10px; margin: 10px 0; border:1px solid #ccc; border-radius:5px;}
        button { width: 100%; padding: 10px; background: #b6895b; color: white; border: none; cursor:pointer; border-radius:5px; font-weight:bold;}
        button:hover { background: #8c6a45; }
        .footer-link { margin-top: 15px; font-size: 14px; }
        .footer-link a { color: #b6895b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Login Member</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Masuk</button>
        </form>
        
        <!-- TAMBAHKAN BAGIAN INI -->
        <div class="footer-link">
            <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
            <a href="DashboardUser.php">Kembali ke Menu</a>
        </div>
        <!-- SELESAI -->
        
    </div>
</body>
</html>