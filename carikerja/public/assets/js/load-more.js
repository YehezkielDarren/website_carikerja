document.addEventListener("DOMContentLoaded", function () {
  const loadMoreBtn = document.getElementById("loadMoreBtn");
  const jobContainer = document.querySelector(".job-container");

  // Jika tombol tidak ditemukan, hentikan eksekusi script
  if (!loadMoreBtn) {
    return;
  }

  // Fungsi untuk memeriksa apakah tombol 'Load More' harus ditampilkan
  const checkButtonVisibility = () => {
    const hiddenBoxes = jobContainer.querySelectorAll(
      ".job-box.job-box-hidden"
    );
    if (hiddenBoxes.length === 0) {
      // Jika tidak ada lagi lowongan yang tersembunyi, sembunyikan tombol
      loadMoreBtn.classList.add("hidden");
    } else {
      // Jika masih ada, pastikan tombol terlihat
      loadMoreBtn.classList.remove("hidden");
    }
  };

  // Tambahkan event listener untuk tombol 'click'
  loadMoreBtn.addEventListener("click", function () {
    // Ambil 5 elemen pertama yang masih tersembunyi
    const hiddenBoxes = jobContainer.querySelectorAll(
      ".job-box.job-box-hidden"
    );
    const boxesToShow = Array.from(hiddenBoxes).slice(0, 5);

    // Hapus class 'job-box-hidden' untuk menampilkannya
    boxesToShow.forEach((box) => {
      box.classList.remove("job-box-hidden");
    });

    // Setelah menampilkan, periksa kembali visibilitas tombol
    checkButtonVisibility();
  });

  // Jalankan pemeriksaan saat halaman pertama kali dimuat
  checkButtonVisibility();
});
