// === Sidebar (tetap terbuka di desktop, tanpa toggle button) ===
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("overlay");

// Kalau kamu masih pakai overlay buat efek gelap di belakang sidebar di mobile
// pastikan overlay-nya tetap bisa menutup sidebar (optional)
if (overlay) {
  overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
  });
}

// === Grafik Penjualan Mingguan ===
const ctx = document.getElementById("salesChart");
if (ctx) {
  new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
      datasets: [{
        label: "Penjualan (cup)",
        data: [32, 45, 38, 50, 62, 70, 58],
        borderColor: "#6b3b2a",
        backgroundColor: "rgba(107, 59, 42, 0.2)",
        borderWidth: 2,
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          labels: {
            color: "#4a2c1f", // warna label legenda
            font: { size: 13 }
          }
        }
      },
      scales: {
        x: {
          ticks: { color: "#5c3d2e" },
          grid: { color: "rgba(0, 0, 0, 0.05)" }
        },
        y: {
          beginAtZero: true,
          ticks: { color: "#5c3d2e" },
          grid: { color: "rgba(0, 0, 0, 0.05)" }
        }
      }
    }
  });
}