<?php
session_start();
include 'koneksi.php';

// 1. Cek Admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='DashboardUser.php';</script>";
    exit;
}

$nama_admin = $_SESSION['username'];

// 2. QUERY DAFTAR PESANAN
// Update: Kita ambil 'orders.customer_name' juga.
$query = "SELECT 
            users.username,
            orders.customer_name, 
            orders.order_date,
            GROUP_CONCAT(CONCAT(orders.item_name, ' (', orders.quantity, ')') SEPARATOR ', ') as menu_list,
            SUM(orders.price * orders.quantity) as total_bayar
          FROM orders
          JOIN users ON orders.user_id = users.id
          GROUP BY orders.user_id, orders.order_date
          ORDER BY orders.order_date DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Transaksi - Admin</title>
  <link rel="stylesheet" href="styles/dashboard.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  
  <style>
    /* === CSS KHUSUS HALAMAN TRANSAKSI === */
    
    .table-container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    /* Wrapper Responsive */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    table { width: 100%; border-collapse: collapse; margin-top: 20px; min-width: 700px; }
    
    /* Header Tabel */
    th { 
        background-color: #f8f9fa; 
        color: #444; 
        font-weight: 700; 
        padding: 15px; 
        text-align: left; 
        border-bottom: 2px solid #eee;
    }
    
    /* Isi Tabel */
    td { 
        padding: 15px; 
        border-bottom: 1px solid #f0f0f0; 
        vertical-align: top; 
    }
    
    tr:hover { background-color: #fafafa; }
    
    /* --- STYLE BADGE MENU (Kotak Oranye) --- */
    .menu-badge {
        display: inline-block;
        background-color: #fff3e0;
        color: #e65100;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-right: 6px;
        margin-bottom: 6px;
        border: 1px solid #ffe0b2;
        white-space: nowrap;
    }

    /* --- STYLE HARGA (Kotak Hijau) --- */
    .price-badge {
        display: inline-block;
        font-weight: bold;
        color: #2e7d32;
        background: #e8f5e9;
        padding: 6px 12px;
        border-radius: 8px;
        white-space: nowrap;
    }
    
    /* Waktu */
    .time-text {
        color: #888;
        font-size: 0.85rem;
        margin-top: 4px;
        display: block;
        white-space: nowrap;
    }

    /* Info User Login (Kecil di bawah nama pemesan) */
    .user-login-info {
        font-size: 0.75rem;
        color: #999;
        display: block;
        margin-top: 4px;
        font-weight: normal;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>Coffee Time</h2>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="produk.php">Produk</a>
      <a href="transaksi.php" class="active">Transaksi</a> 
      <a href="analisis.html">Analisis</a>
      <a href="pelanggan.php">Pelanggan</a>
      <a href="logout.php" style="color: #ff6b6b;">Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="main-content">
    <header class="topbar">
      <h1>Data Transaksi</h1>
      <span>Halo, <?= htmlspecialchars($nama_admin) ?> 👋</span>
    </header>

    <main>
      <section class="table-container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div>
                <h3 style="margin:0;">Semua Pesanan Masuk</h3>
                <p style="margin:5px 0 0; color:#888; font-size:0.9rem;">Daftar riwayat pembelian (Full Name dari Checkout).</p>
            </div>
            <button onclick="window.print()" style="background:#333; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:500;">
                🖨️ Cetak Data
            </button>
        </div>
        
        <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th width="5%">No</th>
                  <th width="18%">Waktu Order</th>
                  <th width="20%">Nama Pemesan (Struk)</th>
                  <th width="40%">Detail Menu</th>
                  <th width="17%">Total Bayar</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($result) > 0): 
                    while ($row = mysqli_fetch_assoc($result)): 
                        // Logic Fallback: Jika data lama belum ada customer_name, pakai username
                        $displayName = !empty($row['customer_name']) ? $row['customer_name'] : $row['username'];
                ?>
                    <tr>
                      <!-- Nomor -->
                      <td><?= $no++ ?></td>
                      
                      <!-- Tanggal & Jam -->
                      <td>
                        <span style="font-weight:500; color:#333; white-space:nowrap;">
                            <?= date('d M Y', strtotime($row['order_date'])) ?>
                        </span>
                        <span class="time-text">
                            <?= date('H:i', strtotime($row['order_date'])) ?> WIB
                        </span>
                      </td>
                      
                      <!-- Nama Pemesan (Full Name) -->
                      <td>
                        <span style="font-weight:600; text-transform:capitalize; font-size:1.05em; color:#333;">
                            <?= htmlspecialchars($displayName) ?>
                        </span>
                        <!-- Opsional: Menampilkan username akun aslinya kecil di bawah -->
                        <span class="user-login-info">
                            Akun: <?= htmlspecialchars($row['username']) ?>
                        </span>
                      </td>
                      
                      <!-- Daftar Menu (Looping Badge) -->
                      <td>
                        <?php 
                          $list_menu = explode(', ', $row['menu_list']);
                          foreach($list_menu as $menu) {
                              echo "<span class='menu-badge'>$menu</span>";
                          }
                        ?>
                      </td>
                      
                      <!-- Total Harga -->
                      <td>
                        <span class="price-badge">
                            Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                        </span>
                      </td>
                    </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:#aaa;">
                            <h4 style="margin:0;">Belum ada data transaksi.</h4>
                        </td>
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