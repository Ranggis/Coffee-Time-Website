document.getElementById("loginForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  const errorMsg = document.getElementById("errorMsg");

  const dummyAdmin = { username: "admin", password: "12345" };
  const dummyUser = { username: "user", password: "123456" };

  if (username === dummyAdmin.username && password === dummyAdmin.password) {
    window.location.href = "dashboard.html";
  } else {
    errorMsg.textContent = "Username atau password salah!";
  }
  if (username === dummyUser.username && password === dummyUser.password) {
    window.location.href = "DashboardUser.html";
  } else {
    errorMsg.textContent = "Username atau password salah!";
  }
});