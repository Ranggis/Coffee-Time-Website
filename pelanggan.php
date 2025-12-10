<?php
session_start();
include 'koneksi.php';

// 1. Cek Keamanan
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='DashboardUser.php';</script>";
    exit;
}

$nama_admin = $_SESSION['username'];

// 2. QUERY UPDATE (LOGIKA BARU)
// - COUNT(DISTINCT o.order_date): Menghitung berapa kali dia checkout (transaksi unik).
// - GROUP_CONCAT: Menggabungkan semua barang yang PERNAH dia beli.
$query = "SELECT 
            u.id, 
            u.username, 
            u.created_at,
            COUNT(DISTINCT o.order_date) as jumlah_checkout,
            GROUP_CONCAT(CONCAT(o.item_name, ':', o.quantity) SEPARATOR '||') as data_belanjaan
          FROM users u
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE u.role = 'user'
          GROUP BY u.id
          ORDER BY jumlah_checkout DESC"; // User yang paling sering belanja muncul paling atas

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Pelanggan - Admin</title>
  <link rel="stylesheet" href="styles/dashboard.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  
  <style>
      .table-container {
          background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      }
      table { width: 100%; border-collapse: collapse; margin-top: 15px; }
      th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: top; }
      th { background-color: #f8f9fa; color: #444; font-weight: 700; }
      tr:hover { background-color: #fafafa; }
      
      /* Badge Total Transaksi */
      .badge-trx {
          background: #e3f2fd; color: #1565c0; 
          padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;
      }
      
      /* Badge Menu (Akumulasi) */
      .menu-accumulated {
          display: inline-block;
          background-color: #fff3e0;
          color: #e65100;
          border: 1px solid #ffe0b2;
          padding: 4px 10px;
          border-radius: 6px;
          font-size: 0.85rem;
          margin-right: 5px;
          margin-bottom: 5px;
          font-weight: 500;
      }
      
      .empty-state { text-align: center; color: #999; padding: 30px; }
  </style>
</head>

<body>

  <aside class="sidebar">
    <h2>Coffee Time</h2>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="produk.php">Produk</a>
      <a href="transaksi.php">Transaksi</a>
      <a href="analisis.html">Analisis</a>
      <a href="pelanggan.php" class="active">Pelanggan</a>
      <a href="logout.php" style="color: #ff6b6b;">Logout</a>
    </nav>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <h1>Data Pelanggan</h1>
      <span>Halo, <?= htmlspecialchars($nama_admin) ?> 👋</span>
    </header>

    <main>
      <section class="table-container">
        <h3>Aktivitas Belanja Pelanggan</h3>
        <p style="color:gray; font-size:14px; margin-bottom:20px;">
            Total akumulasi barang yang dibeli oleh setiap pengguna.
        </p>

        <table>
          <thead>
            <tr>
              <th width="5%">ID</th>
              <th width="20%">Nama Pelanggan</th>
              <th width="15%">Bergabung</th>
              <th width="15%">Total Checkout</th>
              <th width="45%">Total Barang Dibeli (Seumur Hidup)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?= $row['id'] ?></td>
                    
                    <td>
                        <strong style="font-size:1.05em;"><?= htmlspecialchars($row['username']) ?></strong>
                    </td>
                    
                    <td style="color: #666; font-size: 0.9em;">
                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                    </td>
                    
                    <td>
                        <?php if($row['jumlah_checkout'] > 0): ?>
                            <span class="badge-trx"><?= $row['jumlah_checkout'] ?>x Transaksi</span>
                        <?php else: ?>
                            <span style="color:#ccc;">Belum pernah</span>
                        <?php endif; ?>
                    </td>
                    
                    <td>
                        <?php 
                        // --- LOGIKA PHP UNTUK MENJUMLAHKAN TOTAL BARANG ---
                        $raw_data = $row['data_belanjaan']; 
                        
                        if ($raw_data) {
                            $summary = []; // Array penampung
                            
                            // 1. Pecah data "Espresso:1||Latte:2"
                            $items = explode('||', $raw_data);
                            
                            foreach($items as $item) {
                                // 2. Pisahkan Nama dan Qty
                                $parts = explode(':', $item);
                                
                                // Pastikan datanya utuh (ada nama dan ada qty)
                                if(count($parts) == 2) {
                                    $nama = $parts[0];
                                    $qty = (int)$parts[1];
                                    
                                    // 3. Masukkan ke keranjang hitung
                                    if(isset($summary[$nama])) {
                                        $summary[$nama] += $qty; // Tambahkan qty jika menu sudah ada
                                    } else {
                                        $summary[$nama] = $qty; // Buat baru jika belum ada
                                    }
                                }
                            }
                            
                            // 4. Tampilkan Hasil Hitungan
                            foreach($summary as $menu_name => $total_qty) {
                                echo "<span class='menu-accumulated'><b>{$total_qty}x</b> $menu_name</span>";
                            }
                            
                        } else {
                            echo "<span style='color:#ccc; font-style:italic;'>Tidak ada riwayat.</span>";
                        }
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state">Belum ada user yang terdaftar.</td>
                </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

</body>
</html>