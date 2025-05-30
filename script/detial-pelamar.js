document.addEventListener("DOMContentLoaded", function () {
  const detailButtons = document.querySelectorAll(".btn-detail");
  const detailsPlaceholder = document.getElementById("detailsPlaceholder");
  const detailsView = document.getElementById("detailsView");

  const detailLogo = document.getElementById("detail-logo");
  const detailNamaLengkap = document.getElementById("detail-nama-lengkap");
  const detailUsername = document.getElementById("detail-username");
  const detailEmail = document.getElementById("detail-email");
  const detailNomorTelepon = document.getElementById("detail-nomor-telepon");
  const detailTanggalLahir = document.getElementById("detail-tanggal-lahir");

  const detailCvLink = document.getElementById("detail-cv-link");
  const cvNoFile = document.getElementById("cv-no-file");
  const detailPortofolioLink = document.getElementById(
    "detail-portofolio-link"
  );
  const portofolioNoFile = document.getElementById("portofolio-no-file");
  const detailSuratLamaranLink = document.getElementById(
    "detail-surat-lamaran-link"
  );
  const suratLamaranNoFile = document.getElementById("surat-lamaran-no-file");

  detailButtons.forEach((button) => {
    button.addEventListener("click", function () {
      detailsPlaceholder.style.display = "none";
      detailsView.style.display = "block";

      detailLogo.src = this.dataset.logo_akun || "img/ProfilePicture.jpg";
      detailNamaLengkap.textContent = this.dataset.nama_lengkap;
      detailUsername.textContent = this.dataset.username;
      detailEmail.textContent = this.dataset.email;
      detailNomorTelepon.textContent = this.dataset.nomor_telepon;

      // Format tanggal lahir
      const tglLahir = new Date(this.dataset.tanggal_lahir);
      const options = { year: "numeric", month: "long", day: "numeric" };
      detailTanggalLahir.textContent = tglLahir.toLocaleDateString(
        "id-ID",
        options
      );

      // Handle CV file
      if (this.dataset.cv_file) {
        detailCvLink.href = this.dataset.cv_file;
        detailCvLink.style.display = "inline";
        cvNoFile.style.display = "none";
      } else {
        detailCvLink.style.display = "none";
        cvNoFile.style.display = "inline";
      }

      // Handle Portofolio file
      if (this.dataset.portofolio_file) {
        detailPortofolioLink.href = this.dataset.portofolio_file;
        detailPortofolioLink.style.display = "inline";
        portofolioNoFile.style.display = "none";
      } else {
        detailPortofolioLink.style.display = "none";
        portofolioNoFile.style.display = "inline";
      }

      // Handle Surat Lamaran file
      if (this.dataset.surat_lamaran_file) {
        detailSuratLamaranLink.href = this.dataset.surat_lamaran_file;
        detailSuratLamaranLink.style.display = "inline";
        suratLamaranNoFile.style.display = "none";
      } else {
        detailSuratLamaranLink.style.display = "none";
        suratLamaranNoFile.style.display = "inline";
      }
    });
  });
});
