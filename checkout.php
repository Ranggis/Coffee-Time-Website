<?php
session_start();
// Cek keamanan: User harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout | Coffee Time ☕</title>
  <!-- Pastikan path css benar, sesuaikan jika ada di folder styles/ -->
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background-color: #faf3e0;
      font-family: 'Poppins', sans-serif;
      color: #333;
      padding-top: 50px;
    }
    .checkout-container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .checkout-container h2 {
      color: #8B4513;
      margin-bottom: 20px;
      text-align: center;
    }
    .checkout-summary table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    th { background: #f8f2ea; }
    .total {
      text-align: right;
      font-weight: bold;
      font-size: 1.1rem;
      color: #8B4513;
      margin-top: 20px;
    }
    .checkout-form label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
    }
    .checkout-form input,
    .checkout-form select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 5px;
      font-size: 0.95rem;
    }
    .checkout-form input:invalid,
    .checkout-form select:invalid {
      border-color: crimson;
      background-color: #fff8f8;
    }
    .checkout-form button {
      margin-top: 25px;
      width: 100%;
      padding: 14px;
      background-color: #8B4513;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
    }
    .checkout-form button:hover {
      background-color: #6b3310;
    }
    .back-btn {
      display: inline-block;
      margin-top: 15px;
      color: #8B4513;
      text-decoration: none;
    }
    .back-btn:hover { text-decoration: underline; }

    /* Toast Notification */
    .toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #4CAF50;
      color: #fff;
      padding: 14px 18px;
      border-radius: 90px;
      font-size: 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.4s ease;
      z-index: 9999;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.error { background: #e74c3c; }

    /* QRIS Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 10000;
    }
    .modal-content {
      background: white;
      padding: 25px;
      border-radius: 10px;
      text-align: center;
      max-width: 350px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      animation: fadeIn 0.3s ease;
    }
    .modal-content img { width: 220px; margin: 20px auto; }
    .modal-content h3 { color: #8B4513; margin-bottom: 10px; }
    .modal-content p { color: #444; font-size: 0.95rem; }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 768px) {
      .checkout-container { padding: 20px; }
      th, td { font-size: 0.9rem; }
    }
  </style>
</head>
<body>

  <div class="checkout-container">
    <h2>Checkout</h2>

    <div class="checkout-summary">
      <table id="summary-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <div class="total">Total: <span id="total-price">Rp0</span></div>
    </div>

    <form class="checkout-form" id="checkout-form" novalidate>
      <label for="fullname">Full Name</label>
      <input type="text" id="fullname" placeholder="Enter your full name" required minlength="3" />

      <label for="email">Email</label>
      <input type="email" id="email" placeholder="example@email.com" required />

      <label for="method">Payment Method</label>
      <select id="method" required>
        <option value="">-- Select Payment Method --</option>
        <option value="QRIS">QRIS</option>
        <option value="Bank Transfer">Bank Transfer</option>
        <option value="Cash">Cash on Delivery</option>
      </select>

      <button type="submit">Confirm & Pay</button>
      <!-- Pastikan link ini mengarah ke dashboard user kamu -->
      <a href="DashboardUser.php" class="back-btn">← Back to Menu</a>
    </form>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast"></div>

  <!-- QRIS Modal -->
  <div id="qrisModal" class="modal">
    <div class="modal-content">
      <h3>Scan to Pay via QRIS</h3>
      <p>Use your favorite payment app to scan this code.</p>
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=CoffeeTimeQRIS-Dummy" alt="QRIS Dummy">
    </div>
  </div>

  <script>
    // 1. Load checkout data from LocalStorage
    // Data ini diambil dari halaman sebelumnya (Menu/Cart)
    const checkoutData = JSON.parse(localStorage.getItem("checkout_data")) || {};
    const tbody = document.querySelector("#summary-table tbody");
    const totalEl = document.getElementById("total-price");
    let total = 0;

    // Render Table
    Object.keys(checkoutData).forEach((name) => {
      const item = checkoutData[name];
      const subtotal = item.price * item.qty;
      total += subtotal;
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${name}</td>
        <td>${item.qty}</td>
        <td>Rp${item.price.toLocaleString()}</td>
        <td>Rp${subtotal.toLocaleString()}</td>
      `;
      tbody.appendChild(row);
    });
    totalEl.textContent = "Rp" + total.toLocaleString();

    // Toast Notification Function
    function showToast(message, type = "success") {
      const toast = document.getElementById("toast");
      toast.textContent = message;
      toast.className = "toast " + (type === "error" ? "error" : "");
      setTimeout(() => toast.classList.add("show"), 50);
      setTimeout(() => toast.classList.remove("show"), 3000);
    }

  // --- Di dalam file checkout.php ---

function saveOrderToDatabase(paymentMethod) {
    // 1. AMBIL VALUE FULL NAME DARI FORM
    const fullNameInput = document.getElementById("fullname").value;
    
    // Validasi sederhana (jika kosong, pakai "Guest")
    const customerName = fullNameInput.trim() !== "" ? fullNameInput : "Guest";

    let itemsArray = [];
    Object.keys(checkoutData).forEach((key) => {
        itemsArray.push({
            name: key,
            price: checkoutData[key].price,
            qty: checkoutData[key].qty
        });
    });

    fetch('save_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            payment_method: paymentMethod,
            customer_name: customerName, // <--- 2. KIRIM DATA INI
            items: itemsArray
        })
    })
    // ... sisa kodingan sama ...
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Jika sukses disimpan di database:
                if (paymentMethod !== 'QRIS') {
                   // Jika bukan QRIS, tampilkan toast sukses (QRIS punya toast sendiri di bawah)
                   showToast(`✅ Payment confirmed via ${paymentMethod}!`);
                }
                
                // Bersihkan Keranjang
                localStorage.removeItem("coffee_cart");
                localStorage.removeItem("checkout_data");
                
                // Redirect setelah 2.5 detik
                setTimeout(() => {
                    window.location.href = "DashboardUser.php";
                }, 2500);
            } else {
                showToast("❌ Error: " + data.message, "error");
            }
        })
        .catch(error => {
            console.error(error);
            showToast("❌ Terjadi kesalahan sistem.", "error");
        });
    }

    // 3. Logika QRIS
    const qrisModal = document.getElementById("qrisModal");
    function openQris() {
      qrisModal.style.display = "flex";

      // Reset teks status
      const oldText = document.getElementById("wait-text");
      if (oldText) oldText.remove();

      const modal = qrisModal.querySelector(".modal-content");
      const text = document.createElement("p");
      text.id = "wait-text";
      text.style.marginTop = "10px";
      text.style.color = "#8B4513";
      text.style.fontWeight = "600";
      text.textContent = "⏳ Menunggu pembayaran dari aplikasi Anda...";
      modal.appendChild(text);

      // Simulasi delay 6 detik (Pura-pura user lagi scan)
      setTimeout(() => {
        text.textContent = "✅ Pembayaran diterima!";
        text.style.color = "green";
        showToast("✅ Payment successful via QRIS!");

        // SETELAH BAYAR SUKSES, BARU SIMPAN KE DB
        saveOrderToDatabase('QRIS');
        
        // Tutup modal visual
        setTimeout(() => {
          qrisModal.style.display = "none";
        }, 2000);
      }, 6000);
    }

    // 4. Form Submit Handler
    const form = document.getElementById("checkout-form");
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      if (!form.checkValidity()) {
        showToast("⚠️ Please fill in all required fields correctly.", "error");
        return;
      }

      // Pastikan ada barang
      if (Object.keys(checkoutData).length === 0) {
          showToast("⚠️ Keranjang kosong!", "error");
          return;
      }

      const method = document.getElementById("method").value;
      
      if (method === "QRIS") {
        openQris(); // Jalankan simulasi QRIS dulu, baru save di dalamnya
      } else {
        // Metode lain (Cash/Transfer) langsung simpan
        saveOrderToDatabase(method);
      }
    });
  </script>

</body>
</html>