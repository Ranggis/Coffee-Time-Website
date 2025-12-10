<?php
session_start();
// Hapus session di server (PHP)
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
</head>
<body>
    <script>
        // 1. Hapus semua data di LocalStorage (termasuk keranjang)
        localStorage.clear();

        // 2. (Opsional) Jika hanya ingin menghapus item tertentu
        // localStorage.removeItem('cart'); 
        // localStorage.removeItem('shoppingCart');

        // 3. Setelah bersih, baru pindah ke halaman index
        window.location.href = "DashboardUser.php";
    </script>
</body>
</html>