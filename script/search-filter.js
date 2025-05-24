document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-job-title-input');
    const searchErrorMessage = document.getElementById('search-job-title-error');
    // Tentukan selector yang benar untuk job container dan job box berdasarkan halaman
    // Untuk index.php dan dashboard-company.php, .job-box ada di dalam .job-container
    const jobContainer = document.querySelector('.job-container');

    if (searchInput && jobContainer) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const jobBoxes = jobContainer.querySelectorAll('.job-box'); // Ambil semua job-box di dalam container
            let found = false;

            jobBoxes.forEach(function (jobBox) {
                const jobTitleElement = jobBox.querySelector('.job-title');
                if (jobTitleElement) {
                    const jobTitle = jobTitleElement.textContent.toLowerCase();
                    if (jobTitle.includes(searchTerm)) {
                        jobBox.style.display = ''; // Atau 'flex', 'block' sesuai display asli
                        found = true;
                    } else {
                        jobBox.style.display = 'none';
                    }
                }
            });

            if (!found && searchTerm !== '') {
                if (searchErrorMessage) {
                    searchErrorMessage.textContent = 'Tidak ditemukan pekerjaan ini!';
                    searchErrorMessage.style.display = 'block';
                }
                this.classList.add('input-error');
            } else {
                if (searchErrorMessage) {
                    searchErrorMessage.style.display = 'none';
                }
                this.classList.remove('input-error');
            }

            // Jika input kosong, pastikan semua job box terlihat dan error hilang
            if (searchTerm === '') {
                jobBoxes.forEach(function (jobBox) {
                    jobBox.style.display = '';
                });
            }
        });
    }
});