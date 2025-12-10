document.addEventListener("DOMContentLoaded", () => {
  const checkoutContainer = document.getElementById("checkout-items");
  const totalElem = document.getElementById("checkout-total");
  const checkoutData = JSON.parse(localStorage.getItem("checkout_data")) || [];

  if (checkoutData.length === 0) {
    checkoutContainer.innerHTML = "<p>Your cart is empty.</p>";
    totalElem.textContent = "Rp0";
    return;
  }

  let total = 0;
  checkoutData.forEach(item => {
    const subtotal = item.price * item.qty;
    total += subtotal;

    const div = document.createElement("div");
    div.className = "checkout-item";
    div.innerHTML = `
      <div class="item-info">
        <img src="${item.img}" width="60" height="60" style="border-radius:8px;">
        <div>
          <h4>${item.name}</h4>
          <p>Rp${item.price.toLocaleString()} × ${item.qty}</p>
        </div>
      </div>
      <div class="subtotal">Rp${subtotal.toLocaleString()}</div>
    `;
    checkoutContainer.appendChild(div);
  });

  totalElem.textContent = `Rp${total.toLocaleString()}`;

  document.getElementById("payment-form").addEventListener("submit", e => {
    e.preventDefault();
    const name = document.getElementById("fullname").value;
    const method = document.getElementById("payment-method").value;

    if (!name || !method) {
      alert("Please fill in all fields.");
      return;
    }

    // Clear cart after checkout
    localStorage.removeItem("coffee_cart");
    localStorage.removeItem("checkout_data");

    // Show success message
    alert(`✅ Payment successful!\nThank you, ${name}! Your order will be processed via ${method.toUpperCase()}.`);
    window.location.href = "index.html";
  });
});
