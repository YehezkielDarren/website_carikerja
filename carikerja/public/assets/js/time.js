function updateDate() {
  const now = new Date();
  const options = {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  };
  const formattedDate = now.toLocaleDateString("id-ID", options);
  document.getElementById("date").textContent = formattedDate;
}
function updateClock() {
  const now = new Date();

  let jam = now.getHours().toString().padStart(2, "0");
  let menit = now.getMinutes().toString().padStart(2, "0");
  let detik = now.getSeconds().toString().padStart(2, "0");

  const waktu = jam + ":" + menit + ":" + detik;
  document.getElementById("clock").textContent = waktu;
}
setInterval(updateClock, 1000);
setInterval(updateDate, 1000);
updateClock();
updateDate();
