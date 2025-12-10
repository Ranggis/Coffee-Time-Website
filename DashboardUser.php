<?php
session_start();
include 'koneksi.php'; // WAJIB: Hubungkan ke database

// Cek status login
$is_logged_in = isset($_SESSION['user_id']) ? 'true' : 'false';
$current_username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// --- QUERY AMBIL MENU DARI DATABASE ---
$query_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Coffee Time ☕ - User Dashboard</title>
  
  <link rel="stylesheet" href="styles/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="scripts/script.js"></script>

  <style>
      /* Tambahan CSS untuk tombol Sold Out */
      .btn-disabled {
          background-color: #ccc !important;
          cursor: not-allowed;
          pointer-events: none;
      }
      .stock-label {
          font-size: 0.8rem;
          color: #d84315;
          margin-top: 5px;
          display: block;
      }
  </style>
</head>
<body>
  
  <!-- Navbar -->
  <header id="navbar">
    <div class="logo">CoffeeTime</div>
    <nav>
      <ul>
        <li><a href="#hero">Home</a></li>
        <li><a href="#menu">Menu</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
        
        <?php if($is_logged_in == 'true'): ?>
            <li><a href="#" style="color:#b6895b; font-weight:bold;">Hi, <?= htmlspecialchars($current_username) ?></a></li>
            <li><a href="logout.php" style="color:red;">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php" class="btn" style="padding: 5px 15px;">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="cart-icon" id="cartButton">
      🛒 <span id="cart-count">0</span>
    </div>
  </header>

  <!-- Hero -->
  <section id="hero">
    <div class="hero-content">
      <h1>Wake Up & Smell The Coffee</h1>
      <p>Start your day with the perfect cup brewed just for you.</p>
      <a href="#menu" class="btn">Explore Menu</a>
    </div>
  </section>

  <!-- Menu -->
  <section id="menu">
    <h2>Our Menu</h2>
    <div class="menu-container">

      <?php 
      // --- LOOPING PHP DIMULAI DI SINI ---
      if (mysqli_num_rows($query_products) > 0) {
          while ($row = mysqli_fetch_assoc($query_products)) {
              
              // Cek Stok
              $stok = $row['stock'];
              $is_sold_out = ($stok <= 0);
              
              // Siapkan class tombol (jika habis, jadi abu-abu)
              $btn_class = $is_sold_out ? "order-btn btn btn-disabled" : "order-btn btn";
              $btn_text  = $is_sold_out ? "Sold Out" : "Order";
              $onclick   = $is_sold_out ? "" : "onclick=\"event.stopPropagation(); checkLoginAndOrder('{$row['name']}', {$row['price']}, '{$row['image']}')\"";
      ?>

          <!-- Item Menu Dinamis -->
          <div class="menu-item" 
               onclick="openDetail('<?= $row['name'] ?>')"
               data-name="<?= $row['name'] ?>"
               data-price="<?= $row['price'] ?>"
               data-rating="<?= $row['rating'] ?>"
               data-desc="<?= htmlspecialchars($row['description']) ?>"
               data-img="<?= $row['image'] ?>">
            
            <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
            <h3><?= $row['name'] ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            
            <!-- Tampilkan Sisa Stok (Opsional) -->
            <?php if(!$is_sold_out && $stok < 10): ?>
                <span class="stock-label">Sisa stok: <?= $stok ?></span>
            <?php endif; ?>

            <!-- Tombol Order -->
            <button class="<?= $btn_class ?>" <?= $onclick ?>>
                <?= $btn_text ?>
            </button>
          </div>

      <?php 
          } // End While
      } else {
          echo "<p style='text-align:center; width:100%;'>Menu belum tersedia.</p>";
      }
      ?>

    </div>
  </section>

  <!-- About -->
  <section id="about">
    <div class="about-img">
      <img src="assets/image/logo.png" alt="Coffee Shop" />
    </div>
    <div class="about-text">
      <h2>About Us</h2>
      <p>We are passionate coffee lovers crafting the finest blends. Every bean tells a story, and every cup brings warmth and comfort.</p>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials">
    <h2>What Our Customers Say</h2>
    <div class="testimonial-slider">
      <div class="testimonial active">
        <p>“The best latte I’ve ever had! Cozy place and friendly staff.”</p>
        <h4>– Sarah M.</h4>
      </div>
      <div class="testimonial">
        <p>“Great coffee and amazing atmosphere. Perfect for study or chill.”</p>
        <h4>– Daniel K.</h4>
      </div>
      <div class="testimonial">
        <p>“Love the aroma and the vibe here. Highly recommended!”</p>
        <h4>– Amanda R.</h4>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section id="contact">
    <h2>Contact Us</h2>
    <form id="contact-form">
      <input type="text" id="name" placeholder="Your Name" required />
      <input type="email" id="email" placeholder="Your Email" required />
      <textarea id="message" rows="4" placeholder="Your Message" required></textarea>
      <button type="submit" class="btn">Send Message</button>
    </form>
  </section>

  <footer>
    <p>&copy; 2025 CoffeeTime. All Rights Reserved.</p>
  </footer>

  <!-- Modal Order & Cart -->
  <div id="orderModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <span class="close-btn" id="orderClose">&times;</span>
      <h3 id="modal-title">Order</h3>
      <p id="modal-desc" class="muted"></p>
      <div id="modal-rating" class="rating-stars"></div>
      <p id="modal-price" class="muted" style="margin-top:8px;"></p>

      <div class="qty-control">
        <button id="qty-decrease" class="qty-btn">−</button>
        <input type="number" id="quantity" min="1" value="1" />
        <button id="qty-increase" class="qty-btn">+</button>
      </div>

      <div class="modal-actions">
        <button id="confirmOrder" class="btn">Confirm</button>
        <button id="cancelOrder" class="btn secondary">Cancel</button>
      </div>
    </div>
  </div>

  <div id="cartModal" class="modal" aria-hidden="true">
    <div class="modal-content cart-content">
      <span class="close-btn" id="cartClose">&times;</span>
      <h3>Your Cart</h3>
      <div id="cart-items" class="cart-items"></div>
      <div class="cart-summary">
        <div class="cart-total">
          <span>Total:</span>
          <strong id="cart-total-price">Rp0</strong>
        </div>
        <div class="cart-actions">
          <button id="clearCart" class="btn secondary">Clear Cart</button>
          <button id="checkoutBtn" class="btn">Checkout</button>
        </div>
      </div>
    </div>
  </div>

  <!-- === SCRIPT LOGIC LOGIN === -->
  <script>
    const isLoggedIn = <?php echo $is_logged_in; ?>;

    function checkLoginAndOrder(name, price, img) {
        if (isLoggedIn) {
            if(typeof openOrder === 'function'){
                openOrder(name, price, img);
            } else {
                console.error("Fungsi openOrder tidak ditemukan. Cek script.js");
            }
        } else {
            Swal.fire({
                title: 'Harus Login Dahulu!',
                text: 'Silakan login untuk mulai memesan kopi.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b6895b',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Login Sekarang',
                cancelButtonText: 'Nanti'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
        }
    }
  </script>

</body>
</html>