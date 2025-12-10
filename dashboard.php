<?php
session_start();
include 'koneksi.php'; // Hubungkan ke database

// 1. Cek Login & Role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href = 'DashboardUser.php';</script>";
    exit;
}

$nama_admin = $_SESSION['username'];

// --- LOGIC DATABASE ---

// A. Hitung Total Pelanggan
$q_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$d_user = mysqli_fetch_assoc($q_user);
$total_pelanggan = $d_user['total'];

// B. Hitung Total Transaksi
$q_trans = mysqli_query($conn, "SELECT COUNT(DISTINCT order_date) as total FROM orders");
$d_trans = mysqli_fetch_assoc($q_trans);
$total_transaksi = $d_trans['total'];

// C. Ambil 5 Pesanan Terbaru (Ringkas)
// Kita ambil orders.customer_name juga
$query_orders = "SELECT 
                    users.username,
                    orders.customer_name,
                    orders.order_date,
                    SUM(orders.price * orders.quantity) as total_bayar
                 FROM orders
                 JOIN users ON orders.user_id = users.id
                 GROUP BY orders.user_id, orders.order_date
                 ORDER BY orders.order_date DESC
                 LIMIT 5";
$result_orders = mysqli_query($conn, $query_orders);

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin - Coffee Time</title>
  <link rel="stylesheet" href="styles/dashboard.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  
  <style>
    /* === CSS TAMBAHAN === */
    
    /* Agar tabel responsive di layar kecil */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    /* Style Harga agar menonjol */
    .price-text {
        font-weight: bold;
        color: #2e7d32; /* Hijau */
        background: #e8f5e9;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    
    /* Style Waktu */
    .time-text {
        font-size: 0.85rem;
        color: #888;
    }

    /* Tabel Layout */
    table { width: 100%; border-collapse: collapse; }
    td { padding: 15px 10px; vertical-align: middle; border-bottom: 1px solid #eee; }
    th { padding: 15px 10px; text-align: left; background: #f9f9f9; color: #555; }
  </style>
</head>

<body>

  <!-- ===== Sidebar ===== -->
  <aside class="sidebar" id="sidebar">
    <h2>Coffee Time</h2>
    <nav>
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="produk.php">Produk</a>
      <a href="transaksi.php">Transaksi</a> 
      <a href="analisis.html">Analisis</a>
      <a href="pelanggan.php">Pelanggan</a>
      <a href="logout.php" style="color: #ff6b6b;">Logout</a>
    </nav>
  </aside>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <header class="topbar">
      <h1>Dashboard Admin</h1>
      <span>Halo, <?= htmlspecialchars($nama_admin) ?> 👋</span>
    </header>

    <main>
      <!-- Statistik Ringkas -->
      <section class="stats-overview">
        <div class="card stat">
          <h3>Total Produk</h3>
          <p>4</p> 
        </div>
        <div class="card stat">
          <h3>Total Transaksi</h3>
          <p><?= $total_transaksi ?></p>
        </div>
        <div class="card stat">
          <h3>Total Pelanggan</h3>
          <p><?= $total_pelanggan ?></p> 
        </div>
        <div class="card stat">
          <h3>Rating Rata-rata</h3>
          <p>4.8 ⭐</p>
        </div>
      </section>

      <!-- Grafik & Analisis (Baris Tengah) -->
      <section class="chart-section card">
        <div class="section-header">
          <h3>Grafik Penjualan</h3>
          <span class="subtitle">Performa per hari</span>
        </div>
        <canvas id="salesChart"></canvas>
      </section>

      <section class="sentiment-section card">
        <div class="section-header">
          <h3>Sentimen Pelanggan</h3>
          <span class="subtitle">Ulasan & Rating</span>
        </div>
        <div class="sentiment-stats">
          <p>👍 Positif: <strong>75%</strong></p>
          <p>😐 Netral: <strong>20%</strong></p>
          <p>👎 Negatif: <strong>5%</strong></p>
        </div>
        <div class="bar">
          <div class="pos"></div>
          <div class="neu"></div>
          <div class="neg"></div>
        </div>
      </section>

      <!-- Rekomendasi -->
      <section class="card recommendations">
        <div class="section-header">
          <h3>Produk Populer</h3>
          <span class="subtitle">Top selling items</span>
        </div>
        <ul>
          <li>1. Caramel Latte</li>
          <li>2. Cappuccino</li>
          <li>3. Vanilla Cold Brew</li>
        </ul>
      </section>

      <!-- TABEL PESANAN TERBARU (RINGKAS) -->
      <section class="card orders">
        <div class="section-header">
          <h3>Pesanan Terbaru</h3>
          <span class="subtitle">5 Transaksi terakhir</span>
        </div>
        
        <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th width="40%">Nama Pemesan</th>
                  <th width="30%">Total Harga</th>
                  <th width="30%">Waktu</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                if(mysqli_num_rows($result_orders) > 0){
                    while($row = mysqli_fetch_assoc($result_orders)) { 
                        // Cek nama: Pakai Customer Name (Checkout), kalau kosong pakai Username (Login)
                        $displayName = !empty($row['customer_name']) ? $row['customer_name'] : $row['username'];
                ?>
                    <tr>
                      <!-- 1. Nama -->
                      <td>
                        <strong style="color:#333; font-size:0.95rem; text-transform:capitalize;">
                            <?= htmlspecialchars($displayName) ?>
                        </strong>
                      </td>
                      
                      <!-- 2. Total Harga -->
                      <td>
                        <span class="price-text">
                            Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                        </span>
                      </td>
                      
                      <!-- 3. Waktu -->
                      <td>
                        <span class="time-text">
                            <?= date('d M, H:i', strtotime($row['order_date'])) ?>
                        </span>
                      </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align:center; padding:20px; color:#aaa;'>Belum ada pesanan masuk.</td></tr>";
                }
                ?>
              </tbody>
            </table>
        </div>
        
      </section>
      
    </main>
  </div>

  <div id="overlay" class="overlay"></div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="scripts/dashboard.js"></script>
</body>
</html>