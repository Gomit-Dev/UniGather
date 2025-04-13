const users = {
  "alice": { password: "1234", role: "admin" },
  "bob": { password: "abcd", role: "user" }
};

document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  const result = document.getElementById("result");

  if (users[username] && users[username].password === password) {
    const role = users[username].role;
    if (role === "admin") {
      result.innerHTML = "👑 Welcome Admin!";
      // You can also redirect: window.location.href = "admin.html";
    } else {
      result.innerHTML = "👤 Welcome User!";
      // Or redirect: window.location.href = "user.html";
    }
  } else {
    result.textContent = "❌ Invalid username or password.";
  }
});
