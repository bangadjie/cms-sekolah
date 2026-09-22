<?php
// Panggil Header Dinamis (Navigasi, Koneksi, Logo & Floating WA)
include 'header.php';

// 1. Fetch 1 Berita Utama
$query_utama = mysqli_query($koneksi, "SELECT * FROM berita WHERE kategori='utama' ORDER BY id DESC LIMIT 1");
$berita_utama = mysqli_fetch_assoc($query_utama);

// 2. Fetch 8 Berita Terkini (Untuk Slider Berita)
$query_cuplikan = mysqli_query($koneksi, "SELECT * FROM berita WHERE id != '" . ($berita_utama['id'] ?? 0) . "' ORDER BY id DESC LIMIT 8");

// 3. Fetch Data Alumni dari Database MySQL
$query_alumni = mysqli_query($koneksi, "SELECT * FROM alumni ORDER BY id DESC");
?>

<style>
    /* CSS Slider Berita & Alumni 4 Kolom */
    .news-slider-container,
    .alumni-slider-container {
        position: relative;
        overflow: hidden;
        padding: 10px 0;
    }

    .news-slider-track,
    .alumni-slider-track {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 10px;
    }

    .news-slider-track::-webkit-scrollbar,
    .alumni-slider-track::-webkit-scrollbar {
        display: none;
    }

    .news-card-item,
    .alumni-card-item {
        flex: 0 0 calc(25% - 12px);
        /* Tampil Presisi 4 Kolom dalam 1 Baris */
        min-width: 250px;
        backface-visibility: hidden;
        transform: translateZ(0);
    }

    @media (max-width: 992px) {

        .news-card-item,
        .alumni-card-item {
            flex: 0 0 calc(50% - 10px);
            /* 2 Kolom untuk Tablet */
        }
    }

    @media (max-width: 576px) {

        .news-card-item,
        .alumni-card-item {
            flex: 0 0 100%;
            /* 1 Kolom untuk HP */
        }
    }

    /* Style Foto Alumni Bulat (Dioptimasi agar tidak flicker) */
    .img-alumni {
        width: 85px;
        height: 85px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #198754;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }
</style>

<!-- CONTAINER UTAMA HALAMAN INDEX -->
<div class="container my-4">
    <h3 class="border-bottom pb-2 mb-4 text-success">Berita & Informasi Terkini</h3>

    <!-- 1. BAGIAN BERITA UTAMA -->
    <?php if ($berita_utama): ?>
        <div class="card mb-5 border-0 shadow-sm overflow-hidden">
            <div class="row g-0 align-items-center bg-light">
                <div class="col-md-5">
                    <img src="uploads/<?= $berita_utama['gambar']; ?>" class="img-fluid p-3 w-100" style="height: 260px; object-fit: contain;" alt="Berita Utama">
                </div>
                <div class="col-md-7">
                    <div class="card-body">
                        <span class="badge bg-danger mb-2">Berita Utama</span>
                        <h3 class="card-title text-dark"><?= $berita_utama['judul']; ?></h3>
                        <p class="text-muted small"><?= date('d M Y', strtotime($berita_utama['tanggal'])); ?></p>
                        <p class="card-text"><?= substr(strip_tags($berita_utama['isi']), 0, 200); ?>...</p>
                        <a href="detail_berita.php?id=<?= $berita_utama['id']; ?>" class="btn btn-success btn-sm mt-2">Baca Selengkapnya &raquo;</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. BAGIAN SLIDER 4 BERITA TERKINI (GERAK AUTOMATIS 3 DETIK) -->
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h4 class="text-secondary mb-0">Berita Terkini Lainnya</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" onclick="scrollNews(-1)">&laquo; Prev</button>
            <button class="btn btn-sm btn-outline-success" onclick="scrollNews(1)">Next &raquo;</button>
        </div>
    </div>

    <div class="news-slider-container">
        <div class="news-slider-track" id="newsTrack">
            <?php
            if ($query_cuplikan && mysqli_num_rows($query_cuplikan) > 0):
                while ($row = mysqli_fetch_assoc($query_cuplikan)):
            ?>
                    <div class="news-card-item">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="uploads/<?= $row['gambar']; ?>" class="card-img-top p-2" style="height: 160px; object-fit: contain;" alt="<?= $row['judul']; ?>">
                            <div class="card-body d-flex flex-column p-3">
                                <span class="text-muted small mb-1" style="font-size: 11px;"><?= date('d M Y', strtotime($row['tanggal'])); ?></span>
                                <h6 class="card-title text-dark font-weight-bold mb-2" style="font-size: 14px; line-height: 1.3; height: 36px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <?= $row['judul']; ?>
                                </h6>
                                <p class="card-text small text-secondary mb-3" style="font-size: 12px; height: 48px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                    <?= substr(strip_tags($row['isi']), 0, 80); ?>...
                                </p>
                                <a href="detail_berita.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-success w-100 mt-auto">Baca Detail</a>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <p class="text-muted small ms-2">Belum ada berita lainnya.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. BAGIAN SLIDER 4 KESAN & PESAN ALUMNI (DATABASE DINAMIS GERAK 2 DETIK) -->
    <hr class="my-5">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h4 class="text-success mb-0"><i class="bi bi-people-fill me-2"></i>Kesan & Pesan Alumni</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" onclick="scrollAlumni(-1)">&laquo; Prev</button>
            <button class="btn btn-sm btn-outline-success" onclick="scrollAlumni(1)">Next &raquo;</button>
        </div>
    </div>

    <div class="alumni-slider-container">
        <div class="alumni-slider-track" id="alumniTrack">

            <?php
            if ($query_alumni && mysqli_num_rows($query_alumni) > 0):
                while ($alumni = mysqli_fetch_assoc($query_alumni)):
            ?>

                    <div class="alumni-card-item">
                        <div class="card h-100 shadow-sm border-0 text-center p-3">

                            <!-- Foto Alumni -->
                            <div class="mb-2">

                                <?php if (!empty($alumni['foto'])): ?>

                                    <img
                                        src="uploads/alumni/<?= htmlspecialchars($alumni['foto']); ?>"
                                        class="img-alumni shadow-sm"
                                        alt="Foto <?= htmlspecialchars($alumni['nama']); ?>">

                                <?php else: ?>

                                    <div class="text-muted small">
                                        Tidak ada foto
                                    </div>

                                <?php endif; ?>

                            </div>

                            <!-- Nama -->
                            <h6
                                class="fw-bold text-dark mb-1"
                                style="font-size: 14px;">
                                <?= htmlspecialchars($alumni['nama']); ?>
                            </h6>

                            <!-- Pendidikan -->
                            <span
                                class="badge bg-success-subtle text-success mb-1"
                                style="font-size: 10px;">
                                <?= htmlspecialchars($alumni['pendidikan']); ?>
                            </span>

                            <!-- Alamat -->
                            <p
                                class="text-muted small mb-2"
                                style="font-size: 11px;">
                                <i class="bi bi-geo-alt"></i>
                                <?= htmlspecialchars($alumni['alamat']); ?>
                            </p>

                            <!-- Kesan / Pesan -->
                            <p
                                class="card-text text-secondary small"
                                style="font-size: 12px; line-height: 1.4;">
                                <?= htmlspecialchars($alumni['kesan_pesan']); ?>
                            </p>

                        </div>
                    </div>

                <?php
                endwhile;
            else:
                ?>

                <p class="text-muted small ms-2">
                    Belum ada kesan & pesan alumni yang ditambahkan.
                </p>

            <?php endif; ?>

        </div>
    </div>

</div>

<!-- FOOTER WEB -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <small>copy right by Tim IT binaprof 2026</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JAVASCRIPT SLIDER AUTOMATIC -->
<script>
    // 1. SLIDER BERITA (BERGERAK OTOMATIS SETIAP 3 DETIK)
    const newsTrack = document.getElementById('newsTrack');

    function scrollNews(direction) {
        if (newsTrack) {
            const width = newsTrack.querySelector('.news-card-item')?.offsetWidth + 16 || newsTrack.clientWidth;
            newsTrack.scrollBy({
                left: direction * width,
                behavior: 'smooth'
            });
        }
    }
    let autoNews = setInterval(() => {
        if (newsTrack) {
            if (Math.ceil(newsTrack.scrollLeft + newsTrack.clientWidth) >= newsTrack.scrollWidth - 5) {
                newsTrack.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
            } else {
                scrollNews(1);
            }
        }
    }, 3000);

    // 2. SLIDER ALUMNI (BERGERAK OTOMATIS SETIAP 2 DETIK)
    const alumniTrack = document.getElementById('alumniTrack');

    function scrollAlumni(direction) {
        if (alumniTrack) {
            const width = alumniTrack.querySelector('.alumni-card-item')?.offsetWidth + 16 || alumniTrack.clientWidth;
            alumniTrack.scrollBy({
                left: direction * width,
                behavior: 'smooth'
            });
        }
    }
    let autoAlumni = setInterval(() => {
        if (alumniTrack) {
            if (Math.ceil(alumniTrack.scrollLeft + alumniTrack.clientWidth) >= alumniTrack.scrollWidth - 5) {
                alumniTrack.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
            } else {
                scrollAlumni(1);
            }
        }
    }, 2000);

    // JEDA SAAT HOVER MOUSE
    if (newsTrack) {
        newsTrack.addEventListener('mouseenter', () => clearInterval(autoNews));
        newsTrack.addEventListener('mouseleave', () => autoNews = setInterval(() => {
            if (Math.ceil(newsTrack.scrollLeft + newsTrack.clientWidth) >= newsTrack.scrollWidth - 5) {
                newsTrack.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
            } else {
                scrollNews(1);
            }
        }, 3000));
    }

    if (alumniTrack) {
        alumniTrack.addEventListener('mouseenter', () => clearInterval(autoAlumni));
        alumniTrack.addEventListener('mouseleave', () => autoAlumni = setInterval(() => {
            if (Math.ceil(alumniTrack.scrollLeft + alumniTrack.clientWidth) >= alumniTrack.scrollWidth - 5) {
                alumniTrack.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
            } else {
                scrollAlumni(1);
            }
        }, 2000));
    }
</script>

</body>

</html>