function checkLogin() {
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const errorDiv = document.getElementById("errorMsg");

  if (username === "" || password === "") {
    errorDiv.innerText = "Username dan password tidak boleh kosong.";
    errorDiv.style.display = "block";
    return false;
  }

  return true;
}
function showAlert(message) {
  document.getElementById("alertMessage").textContent = message;
  document.getElementById("customAlert").classList.add("show");
}

function closeAlert() {
  document.getElementById("customAlert").classList.remove("show");
}

function showPassword() {
  const passwordInput = document.getElementById("password");
  const toggleIcon = document.querySelector(".toggle-password");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    toggleIcon.classList.remove("bx-show");
    toggleIcon.classList.add("bx-hide");
  } else {
    passwordInput.type = "password";
    toggleIcon.classList.remove("bx-hide");
    toggleIcon.classList.add("bx-show");
  }
}
