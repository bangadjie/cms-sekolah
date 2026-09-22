    <?php
    include "koneksi.php";

    // Ambil data profil sekolah untuk header
    $q_profil = mysqli_query($koneksi, "SELECT * FROM profil_sekolah WHERE id=1");
    $profil   = mysqli_fetch_assoc($q_profil);

    $nama_sekolah = $profil['nama_sekolah'] ?? 'SDN SUMBERSUKO';
    $alamat       = $profil['alamat'] ?? 'Jl. Raya Pakis No. 123, Malang';
    $logo         = !empty($profil['logo']) && file_exists('uploads/' . $profil['logo']) ? 'uploads/' . $profil['logo'] : '';

    // LOGIKA UTAMA: Ambil kategori dari URL ($_GET), jika kosong default ke 'struktur'
    $kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : 'struktur';

    // Kueri berdasarkan kategori yang diklik
    $query = mysqli_query($koneksi, "SELECT * FROM halaman WHERE kategori='$kategori'");
    $data  = mysqli_fetch_assoc($query);

    // Set judul, gambar, dan konten dinamis
    $judul  = !empty($data['judul']) ? $data['judul'] : ucfirst($kategori);
    $gambar = !empty($data['gambar']) ? $data['gambar'] : '';
    $konten = !empty($data['konten']) ? $data['konten'] : '<p class="text-muted">Konten untuk halaman ini belum diisi oleh operator.</p>';
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($judul); ?> - <?= htmlspecialchars($nama_sekolah); ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </head>

    <body class="bg-light d-flex flex-column min-vh-100">

        <!-- NAVBAR HEADER -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                    <?php if (!empty($logo)): ?>
                        <img src="<?= $logo; ?>" alt="Logo" height="40" class="me-2 rounded bg-white p-1">
                    <?php else: ?>
                        <i class="bi bi-building-fill fs-3 me-2"></i>
                    <?php endif; ?>
                    <div>
                        <?= htmlspecialchars($nama_sekolah); ?><br>
                        <small style="font-size: 10px; font-weight: normal;" class="d-block text-white-50">
                            <?= htmlspecialchars($alamat); ?>
                        </small>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($kategori == 'struktur') ? 'active fw-bold' : ''; ?>" href="halaman.php?kategori=struktur">Struktur Organisasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($kategori == 'sarana') ? 'active fw-bold' : ''; ?>" href="halaman.php?kategori=sarana">Sarana & Prasarana</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($kategori == 'kegiatan') ? 'active fw-bold' : ''; ?>" href="halaman.php?kategori=kegiatan">Kegiatan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="galeri.php">Galeri</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kontak.php">Kontak</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- KONTEN HALAMAN DINAMIS -->
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                        <h3 class="text-success fw-bold border-bottom pb-2 mb-4">
                            <?= htmlspecialchars($judul); ?>
                        </h3>


                        <?php
                        // Pastikan nama file gambar tersedia
                        $path_gambar = "uploads/" . basename($gambar);
                        ?>

                        <?php if (!empty($gambar) && file_exists($path_gambar)): ?>

                            <div class="text-center mb-4">
                                <img
                                    src="<?= htmlspecialchars($path_gambar); ?>"
                                    alt="<?= htmlspecialchars($judul); ?>"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-height: 400px; object-fit: cover;">
                            </div>

                        <?php elseif (!empty($gambar)): ?>

                            <div class="alert alert-warning">
                                File gambar tidak ditemukan:
                                <?= htmlspecialchars($gambar); ?>
                            </div>

                        <?php endif; ?>

                        <div class="content-area lh-lg fs-6">
                            <?= $konten; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="bg-dark text-white text-center py-3 mt-auto">
            <small>&copy; 2026 Website Resmi Sekolah. All Rights Reserved.</small>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>