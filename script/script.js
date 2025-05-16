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
