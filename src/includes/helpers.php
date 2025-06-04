<?php
// src/includes/helpers.php

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Pastikan sesi dimulai jika belum
}

function formatTanggal($tanggalInput) {
    if (empty($tanggalInput) || $tanggalInput === '0000-00-00' || $tanggalInput === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    try {
        // Coba format dari Y-m-d H:i:s dulu (misal dari tanggal_lamaran)
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $tanggalInput);
        if ($date === false) {
            // Jika gagal, coba format Y-m-d (misal dari tanggal_batas, tanggal_lahir)
            $date = DateTime::createFromFormat('Y-m-d', $tanggalInput);
        }
        if ($date === false) {
            // Jika masih gagal, coba parsing langsung, mungkin formatnya sudah benar
            $date = new DateTime($tanggalInput);
        }

        $date->setTimezone(new DateTimeZone('Asia/Jakarta'));

        if (class_exists('IntlDateFormatter')) {
            $formatter = new IntlDateFormatter(
                'id_ID',
                IntlDateFormatter::LONG, // Full month name
                IntlDateFormatter::NONE, // No time
                'Asia/Jakarta',
                IntlDateFormatter::GREGORIAN,
                'd MMMM yyyy' // Contoh: 18 Mei 2025
            );
            return $formatter->format($date);
        } else {
            return strftime('%d %B %Y', $date->getTimestamp()); // Fallback if Intl not available
        }
    } catch (Exception $e) {
        error_log("Error formatting date '$tanggalInput': " . $e->getMessage());
        return $tanggalInput; // Kembalikan input asli jika ada error
    }
}

function potongDeskripsi($teks, $jumlahKata = 20) {
    $kata = explode(" ", strip_tags($teks));
    if (count($kata) > $jumlahKata) {
        $kata = array_slice($kata, 0, $jumlahKata);
        return implode(" ", $kata) . "...";
    } else {
        return $teks;
    }
}

function generateBreadcrumb() {
    $path = $_SERVER['PHP_SELF'];
    $pathParts = explode('/', trim($path, '/'));
    // Karena semua file ada di public/, kita bisa hapus 'public' dari breadcrumb jika muncul
    if (isset($pathParts[0]) && strtolower($pathParts[0]) === 'public') {
        array_shift($pathParts); // Hapus 'public'
    }

    $breadcrumb = '<div class="breadcrumb"><p>';
    $link = '/'; // Mulai dari root web

    foreach ($pathParts as $index => $part) {
        // Jika path part adalah nama file, jangan sertakan dalam link path selanjutnya
        if (strpos($part, '.php') !== false && $index < count($pathParts) -1 ) {
             $link .= $part;
        } else if (strpos($part, '.php') === false) {
            $link .= $part . '/';
        }


        $name = ($part === 'index.php') ? 'Home' : ucfirst(str_replace(['.php', '-', '_'], ['', ' ', ' '], $part));

        if ($index < count($pathParts) - 1) {
            // Pastikan link benar, jika file ada di root public, linknya langsung nama file
            $current_link = (count($pathParts) == 1 || $index == 0 && strpos($pathParts[$index+1], '.php') !== false) ? $part : rtrim($link, '/');
             if ($part === 'index.php') $current_link = '/'; // Home selalu ke root
            $breadcrumb .= '<a href="' . ($current_link ?: '/') . '">' . $name . '</a> / ';
        } else {
            $breadcrumb .= $name; // Bagian terakhir sebagai teks
        }
    }
    $breadcrumb .= '</p></div>';
    return $breadcrumb;
}


function getTanggalSekarang() {
    date_default_timezone_set('Asia/Jakarta');
    return date('Y-m-d');
}

function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return '';
        case UPLOAD_ERR_INI_SIZE:
            return "Ukuran file melebihi batas unggah server (php.ini directive).";
        case UPLOAD_ERR_FORM_SIZE:
            return "Ukuran file melebihi batas yang ditentukan dalam form HTML.";
        case UPLOAD_ERR_PARTIAL:
            return "File hanya terunggah sebagian.";
        case UPLOAD_ERR_NO_FILE:
            return "Tidak ada file yang diunggah.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Folder sementara untuk unggahan tidak ditemukan.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Gagal menulis file ke disk.";
        case UPLOAD_ERR_EXTENSION:
            return "Unggahan file dihentikan oleh ekstensi PHP.";
        default:
            return "Terjadi error tidak diketahui saat unggah file.";
    }
}

function simpanFile($file, $maxSize, $desiredFilename, array $allowedExtensions, array $allowedMimeTypes) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE), 'path' => null];
    }

    if ($file['size'] > $maxSize) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar. Maksimal ' . ($maxSize / 1000000) . 'MB.', 'path' => null];
    }

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['status' => false, 'message' => 'Jenis file tidak diizinkan. Hanya file dengan ekstensi: ' . implode(', ', $allowedExtensions) . ' yang diperbolehkan.', 'path' => null];
    }

    $mimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($file['tmp_name']);
    } else {
        return ['status' => false, 'message' => 'Tidak dapat memverifikasi tipe file (fungsi MIME tidak tersedia).', 'path' => null];
    }

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return ['status' => false, 'message' => 'Tipe file (MIME) tidak valid (' . htmlspecialchars($mimeType) . '). Pastikan file tidak korup dan sesuai dengan ekstensinya.', 'path' => null];
    }

    // $targetDir adalah relatif terhadap root proyek, karena kita akan menggunakannya dari file di public/
    // Pastikan ini mengarah ke 'public/uploads/' dari root proyek.
    // Karena skrip akan berada di public/, targetnya adalah 'uploads/' relatif terhadap skrip tersebut.
    $targetDirRelativeFromScript = 'uploads/';
    // Untuk path yang disimpan di DB, kita ingin itu relatif dari public/ juga.
    $pathForDb = 'uploads/' . $desiredFilename;


    // Path absolut ke direktori uploads dari root file system server
    // __DIR__ akan menjadi public/namafile.php, jadi __DIR__ . '/../uploads/' akan menjadi public/../uploads/
    // yang salah. kita ingin __DIR__ . '/uploads/' yang berarti public/uploads/
    $absoluteTargetDir = __DIR__ . '/' . $targetDirRelativeFromScript; // Jika helpers.php di src/includes, ini salah
                                                                     // Seharusnya path dihitung dari skrip yang memanggil simpanFile

    // KOREKSI: $targetDir harus relatif dari skrip yang memanggil `simpanFile`.
    // Jika `apply.php` ada di `public/apply.php`, maka `$targetDir = 'uploads/'` akan menjadi `public/uploads/`. Ini benar.
    // Path yang disimpan di DB juga harus `uploads/filename.ext`.
    $targetDirForMove = 'uploads/'; // Relatif dari skrip di public/
    $finalTargetPath = $targetDirForMove . $desiredFilename;


    if (!is_dir($targetDirForMove)) {
        if (!mkdir($targetDirForMove, 0755, true)) {
             return ['status' => false, 'message' => 'Gagal membuat direktori uploads: ' . $targetDirForMove, 'path' => null];
        }
    }

    if (move_uploaded_file($file['tmp_name'], $finalTargetPath)) {
        return ['status' => true, 'message' => 'File berhasil disimpan.', 'path' => $finalTargetPath]; // Path yang disimpan di DB
    } else {
        // Tambahkan debugging error
        $error = error_get_last();
        $message = 'Gagal menyimpan file.';
        if ($error) {
            $message .= ' Pesan sistem: ' . $error['message'];
        }
        return ['status' => false, 'message' => $message , 'path' => null];
    }
}

?>