<?php
session_start();
include 'koneksi.php';

// 1. Cek Login & Role Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$nama_admin = $_SESSION['username'];

// --- LOGIKA UPDATE STOK ---
// Jika tombol "Simpan Stok" ditekan
if (isset($_POST['update_stock'])) {
    $id_produk = $_POST['id_produk'];
    $stok_baru = (int) $_POST['stok_baru'];

    // Update database
    $query_update = "UPDATE products SET stock = '$stok_baru' WHERE id = '$id_produk'";
    
    if (mysqli_query($conn, $query_update)) {
        echo "<script>alert('Stok berhasil diperbarui!'); window.location.href='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal update stok!');</script>";
    }
}

// --- LOGIKA AMBIL DATA PRODUK ---
$query = "SELECT * FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manajemen Produk - Admin</title>
  <link rel="stylesheet" href="styles/dashboard.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  
  <style>
    /* CSS Khusus Halaman Produk */
    .table-container {
        background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    table { width: 100%; border-collapse: collapse; margin-top: 20px; min-width: 600px; }
    th { background: #f8f9fa; padding: 15px; text-align: left; color: #444; }
    td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    /* Gambar Produk Kecil */
    .thumb-img {
        width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;
    }
    
    /* Input Stok */
    .input-stock {
        width: 70px; padding: 8px; border: 1px solid #ccc; border-radius: 5px; text-align: center; font-weight: bold;
    }
    
    /* Tombol Simpan Kecil */
    .btn-save {
        background: #2e7d32; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-left: 5px;
    }
    .btn-save:hover { background: #1b5e20; }
    
    /* Badge Stok Sedikit/Habis */
    .badge-danger { color: red; font-weight: bold; font-size: 0.8rem; display: block; margin-top: 5px; }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>Coffee Time</h2>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="produk.php" class="active">Produk</a> <!-- Aktif di sini -->
      <a href="transaksi.php">Transaksi</a>
      <a href="analisis.html">Analisis</a>
      <a href="pelanggan.php">Pelanggan</a>
      <a href="logout.php" style="color: #ff6b6b;">Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="main-content">
    <header class="topbar">
      <h1>Manajemen Produk</h1>
      <span>Halo, <?= htmlspecialchars($nama_admin) ?> 👋</span>
    </header>

    <main>
      <section class="table-container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Daftar Menu & Stok</h3>
            <!-- Tombol Tambah Produk (Nanti bisa dikembangkan) -->
            <!-- <button style="background:#b6895b; color:white; border:none; padding:10px 20px; border-radius:5px;">+ Tambah Menu</button> -->
        </div>
        
        <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th width="5%">No</th>
                  <th width="10%">Gambar</th>
                  <th width="30%">Nama Produk</th>
                  <th width="20%">Harga</th>
                  <th width="35%">Atur Stok</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($result) > 0): 
                    while ($row = mysqli_fetch_assoc($result)): 
                        $stok = $row['stock'];
                ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      
                      <!-- Gambar -->
                      <td>
                        <img src="<?= $row['image'] ?>" class="thumb-img" alt="Kopi">
                      </td>
                      
                      <!-- Nama -->
                      <td>
                        <strong><?= htmlspecialchars($row['name']) ?></strong>
                        <?php if($stok == 0): ?>
                            <span class="badge-danger">⚠️ Stok Habis!</span>
                        <?php elseif($stok < 10): ?>
                            <span class="badge-danger" style="color:#f57c00;">⚠️ Stok Menipis</span>
                        <?php endif; ?>
                      </td>
                      
                      <!-- Harga -->
                      <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                      
                      <!-- Form Update Stok -->
                      <td>
                        <form method="POST" style="display:flex; align-items:center;">
                            <!-- Input Hidden untuk ID Produk -->
                            <input type="hidden" name="id_produk" value="<?= $row['id'] ?>">
                            
                            <!-- Input Angka Stok -->
                            <input type="number" name="stok_baru" class="input-stock" value="<?= $stok ?>" min="0" required>
                            
                            <!-- Tombol Simpan -->
                            <button type="submit" name="update_stock" class="btn-save">
                                💾 Update
                            </button>
                        </form>
                      </td>
                    </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px;">Belum ada produk.</td>
                    </tr>
                <?php endif; ?>
              </tbody>
            </table>
        </div>
      </section>
    </main>
  </div>

</body>
</html>