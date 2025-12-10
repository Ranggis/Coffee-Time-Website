// Sticky Navbar
window.addEventListener("scroll", () => {
  const navbar = document.getElementById("navbar");
  navbar.classList.toggle("scrolled", window.scrollY > 100);
});

// Testimonial Slider
let current = 0;
const testimonials = document.querySelectorAll(".testimonial");
function showNext() {
  testimonials[current].classList.remove("active");
  current = (current + 1) % testimonials.length;
  testimonials[current].classList.add("active");
}
if (testimonials.length > 0) setInterval(showNext, 4000);

// Contact Form
const contactForm = document.getElementById("contact-form");
if (contactForm) {
  contactForm.addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Thank you for your message! ☕ We'll get back to you soon.");
    e.target.reset();
  });
}

// Utility: Format Harga
const formatIDR = (value) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(value);

// Elemen Modal
const orderModal = document.getElementById("orderModal");
const cartModal = document.getElementById("cartModal");
const modalTitle = document.getElementById("modal-title");
const modalPrice = document.getElementById("modal-price");
const modalDesc = document.getElementById("modal-desc");
const modalRating = document.getElementById("modal-rating");
const qtyInput = document.getElementById("quantity");
const qtyDec = document.getElementById("qty-decrease");
const qtyInc = document.getElementById("qty-increase");
const confirmOrderBtn = document.getElementById("confirmOrder");
const cancelOrderBtn = document.getElementById("cancelOrder");
const orderClose = document.getElementById("orderClose");
const cartButton = document.getElementById("cartButton");
const cartCountEl = document.getElementById("cart-count");
const cartClose = document.getElementById("cartClose");
const cartItemsContainer = document.getElementById("cart-items");
const cartTotalPriceEl = document.getElementById("cart-total-price");
const clearCartBtn = document.getElementById("clearCart");
const checkoutBtn = document.getElementById("checkoutBtn");

let selectedProduct = null;
let cart = {};

// ======== Load & Save Cart ========
function loadCart() {
  try {
    cart = JSON.parse(localStorage.getItem("coffee_cart")) || {};
  } catch {
    cart = {};
  }
  updateCartUI();
}

function saveCart() {
  localStorage.setItem("coffee_cart", JSON.stringify(cart));
  updateCartUI();
}

function updateCartUI() {
  cartCountEl.textContent = Object.values(cart).reduce((a, b) => a + b.qty, 0);
  renderCartItems();
}

// ======== Render Cart Items ========
function renderCartItems() {
  cartItemsContainer.innerHTML = "";
  const keys = Object.keys(cart);
  if (keys.length === 0) {
    cartItemsContainer.innerHTML = '<p style="color:#666;padding:12px;">Your cart is empty.</p>';
    cartTotalPriceEl.textContent = formatIDR(0);
    return;
  }
  let total = 0;
  keys.forEach((name) => {
    const item = cart[name];
    const subtotal = item.price * item.qty;
    total += subtotal;
    cartItemsContainer.innerHTML += `
      <div class="cart-item">
        <div class="ci-name">${name}</div>
        <div class="ci-meta">
          <small>${formatIDR(item.price)} each</small>
          <div style="margin-top:6px;">Qty: <input type="number" class="cart-qty" min="1" value="${item.qty}" data-name="${name}" style="width:60px;"></div>
          <div style="margin-top:6px;font-weight:600;color:#422a1a">${formatIDR(subtotal)}</div>
        </div>
        <div class="ci-actions">
          <button class="btn secondary remove-item" data-name="${name}">Remove</button>
        </div>
      </div>`;
  });
  cartTotalPriceEl.textContent = formatIDR(total);

  // Update qty
  document.querySelectorAll(".cart-qty").forEach((inp) => {
    inp.addEventListener("change", (e) => {
      const name = e.target.dataset.name;
      cart[name].qty = Math.max(1, parseInt(e.target.value));
      saveCart();
    });
  });

  // Remove item
  document.querySelectorAll(".remove-item").forEach((b) => {
    b.addEventListener("click", (e) => {
      delete cart[e.target.dataset.name];
      saveCart();
    });
  });
}

// ======== Order Button (modal) ========
document.querySelectorAll(".order-btn").forEach((btn) =>
  btn.addEventListener("click", (e) => {
    e.stopPropagation();
    const item = e.target.closest(".menu-item");
    const { name, price, rating, desc } = item.dataset;
    selectedProduct = { name, price: parseInt(price) };
    modalTitle.textContent = `Order ${name}`;
    modalPrice.textContent = formatIDR(price);
    modalDesc.textContent = desc;
    modalRating.innerHTML = generateStars(rating);
    qtyInput.value = 1;
    openModal(orderModal);
  })
);

// Klik Card untuk halaman detail
document.querySelectorAll(".menu-item").forEach((item) => {
  item.addEventListener("click", (e) => {
    if (e.target.classList.contains("order-btn")) return;
    const { name, price, rating, desc, img } = item.dataset;
    localStorage.setItem("coffee_detail", JSON.stringify({ name, price, rating, desc, img }));
    window.location.href = "menu-detail.html";
  });
});

// ======== Rating Bintang ========
function generateStars(rating) {
  const full = Math.floor(rating);
  const half = rating % 1 >= 0.5;
  return "⭐".repeat(full) + (half ? "✩" : "");
}

// ======== Quantity ========
qtyInc.onclick = () => (qtyInput.value = +qtyInput.value + 1);
qtyDec.onclick = () => (qtyInput.value = Math.max(1, +qtyInput.value - 1));

// ======== Confirm / Cancel Order ========
confirmOrderBtn.onclick = () => {
  const qty = +qtyInput.value;
  if (!selectedProduct) return;
  const { name, price } = selectedProduct;
  cart[name] = cart[name] ? { price, qty: cart[name].qty + qty } : { price, qty };
  saveCart();

  // Toast Notification
  const toast = document.createElement("div");
  toast.className = "toast";
  toast.innerText = `✔️ ${qty} x ${name} added to cart`;
  document.body.appendChild(toast);
  setTimeout(() => toast.classList.add("show"), 10);
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 2500);

  closeModal(orderModal);
};

cancelOrderBtn.onclick = () => closeModal(orderModal);
orderClose.onclick = () => closeModal(orderModal);

// ======== Cart Buttons ========
cartButton.onclick = () => openModal(cartModal);
cartClose.onclick = () => closeModal(cartModal);

clearCartBtn.onclick = () => {
  if (confirm("Clear all items?")) {
    cart = {};
    saveCart();
    closeModal(cartModal);
  }
};

// 🟢 UPDATE: Langsung ke halaman checkout.html
checkoutBtn.onclick = () => {
  if (Object.keys(cart).length === 0) {
    alert("Cart is empty!");
    return;
  }
  localStorage.setItem("checkout_data", JSON.stringify(cart));
  window.location.href = "checkout.html";
};

// ======== Modal Helper ========
function openModal(m) {
  m.style.display = "flex";
  m.setAttribute("aria-hidden", "false");
  document.body.style.overflow = "hidden";
  if (m === cartModal) renderCartItems();
}

function closeModal(m) {
  m.style.display = "none";
  m.setAttribute("aria-hidden", "true");
  document.body.style.overflow = "";
}

document.querySelectorAll(".modal").forEach((m) =>
  m.addEventListener("click", (e) => e.target === m && closeModal(m))
);

// ======== Load Cart saat Awal ========
loadCart();
