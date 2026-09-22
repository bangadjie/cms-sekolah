<?php
session_start();
include "koneksi.php";

// Pengecekan Login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Cek Apakah User Yang Login Adalah M4570KO
$username_login = strtolower($_SESSION['username'] ?? '');
$is_m4570ko      = ($username_login === 'm4570ko');

$tab = $_GET['tab'] ?? 'beranda';

// Jika user biasa mencoba akses tab profil via URL secara paksa, alihkan ke beranda
if ($tab == 'profil' && !$is_m4570ko) {
    echo "<script>alert('Akses Ditolak! Menu Identitas Sekolah hanya untuk M4570KO.'); window.location='admin.php';</script>";
    exit();
}

// ==========================================
// PROSES CRUD (BACKEND)
// ==========================================

// 1. SIMPAN IDENTITAS SEKOLAH (Khusus M4570KO)
if (isset($_POST['simpan_profil'])) {
    if (!$is_m4570ko) {
        echo "<script>alert('Akses Ditolak!'); window.location='admin.php';</script>";
        exit();
    }

    $nama_sekolah = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    if (!empty($_FILES['logo']['name'])) {
        $logo_name = time() . '_' . $_FILES['logo']['name'];
        if (move_uploaded_file($_FILES['logo']['tmp_name'], "uploads/" . $logo_name)) {
            $query = "UPDATE profil_sekolah SET nama_sekolah='$nama_sekolah', alamat='$alamat', logo='$logo_name' WHERE id=1";
        } else {
            $query = "UPDATE profil_sekolah SET nama_sekolah='$nama_sekolah', alamat='$alamat' WHERE id=1";
        }
    } else {
        $query = "UPDATE profil_sekolah SET nama_sekolah='$nama_sekolah', alamat='$alamat' WHERE id=1";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Identitas Sekolah Berhasil Diperbarui!'); window.location='admin.php?tab=profil';</script>";
    }
}


// 2. SIMPAN HALAMAN DINAMIS
if (isset($_POST['simpan_halaman'])) {

    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $judul    = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $konten   = mysqli_real_escape_string($koneksi, $_POST['konten']);

    // Cek apakah kategori sudah ada di database
    $cek = mysqli_query(
        $koneksi,
        "SELECT * FROM halaman WHERE kategori='$kategori'"
    );

    $data_lama = mysqli_fetch_assoc($cek);

    // Gunakan gambar lama jika tidak upload gambar baru
    $gambar_name = $data_lama['gambar'] ?? '';

    // Jika admin memilih gambar baru
    if (
        isset($_FILES['gambar']) &&
        $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        // Cek apakah upload berhasil diterima PHP
        if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            die("Upload gambar gagal. Kode error: " . $_FILES['gambar']['error']);
        }

        // Folder penyimpanan gambar
        $folder = "uploads/";

        // Buat folder jika belum tersedia
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // Nama file unik
        $gambar_name = time() . "_" . basename($_FILES['gambar']['name']);

        // Pindahkan gambar ke folder uploads
        if (!move_uploaded_file(
            $_FILES['gambar']['tmp_name'],
            $folder . $gambar_name
        )) {
            die("Gagal memindahkan gambar ke folder uploads.");
        }
    }
    // Jika data kategori sudah ada, UPDATE
    if ($data_lama) {

        $q = "UPDATE halaman SET
                judul='$judul',
                konten='$konten',
                gambar='$gambar_name'
              WHERE kategori='$kategori'";
    } else {

        // Jika belum ada, INSERT
        $q = "INSERT INTO halaman
                (kategori, judul, konten, gambar)
              VALUES
                ('$kategori', '$judul', '$konten', '$gambar_name')";
    }
    // Eksekusi query
    if (mysqli_query($koneksi, $q)) {

        echo "<script>
            alert('Halaman Berhasil Disimpan!');
            window.location='admin.php?tab=$kategori';
        </script>";
    } else {
        echo "Gagal menyimpan halaman: " . mysqli_error($koneksi);
    }
}

// 3. TAMBAH BERITA
if (isset($_POST['tambah_berita'])) {
    $judul  = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi    = mysqli_real_escape_string($koneksi, $_POST['isi']);
    $tgl    = date('Y-m-d');
    $gambar = '';
    $slug = strtolower(trim($judul));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . '_' . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/" . $gambar);
    }
    mysqli_query($koneksi, "INSERT INTO berita (judul, isi, tanggal, gambar, slug) VALUES ('$judul', '$isi', '$tgl', '$gambar', '$slug')");
    echo "<script>alert('Berita Berhasil Ditambahkan!'); window.location='admin.php?tab=berita';</script>";
}

// 4. TAMBAH GALERI
// if (isset($_POST['tambah_galeri'])) {
//     $judul  = mysqli_real_escape_string($koneksi, $_POST['judul']);
//     $file_gambar = time() . '_' . $_FILES['gambar']['name'];
//     move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/" . $file_gambar);

//     mysqli_query($koneksi, "INSERT INTO galeri (judul, file_gambar) VALUES ('$judul', '$file_gambar')");
//     echo "<script>alert('Foto Galeri Berhasil Ditambahkan!'); window.location='admin.php?tab=galeri';</script>";
// }

// 4. TAMBAH GALERI
if (isset($_POST['tambah_galeri'])) {

    $judul = mysqli_real_escape_string(
        $koneksi,
        $_POST['judul']
    );

    // Membuat nama file unik
    $file_gambar = time() . '_' . $_FILES['gambar']['name'];

    $lokasi_file = "uploads/" . $file_gambar;

    // Proses upload gambar
    if (move_uploaded_file(
        $_FILES['gambar']['tmp_name'],
        $lokasi_file
    )) {

        // Isi kedua kolom gambar dengan nama file yang sama
        $sql = "INSERT INTO galeri
                (judul, gambar, file_gambar)
                VALUES
                ('$judul', '$file_gambar', '$file_gambar')";

        if (mysqli_query($koneksi, $sql)) {

            echo "<script>
                alert('Foto Galeri Berhasil Ditambahkan!');
                window.location='admin.php?tab=galeri';
            </script>";
        } else {
            echo "Gagal menyimpan data galeri: "
                . mysqli_error($koneksi);
        }
    } else {
        echo "<script>
            alert('Upload gambar gagal!');
            window.location='admin.php?tab=galeri';
        </script>";
    }
}

// 5. TAMBAH ALUMNI
// if (isset($_POST['tambah_alumni'])) {
//     $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
//     $tahun  = mysqli_real_escape_string($koneksi, $_POST['tahun_lulus']);
//     $kesan  = mysqli_real_escape_string($koneksi, $_POST['kesan']);
//     $tahun_lulus = $_POST['tahun_lulus'];

//     @mysqli_query($koneksi, "INSERT INTO alumni (nama, tahun_lulus, kesan, tahun_lulus) VALUES ('$nama', '$tahun', '$kesan', '')");
//     echo "<script>alert('Data Alumni Berhasil Ditambahkan!'); window.location='admin.php?tab=alumni';</script>";
// }

// 5. TAMBAH ALUMNI
if (isset($_POST['tambah_alumni'])) {

    $nama       = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $pendidikan = mysqli_real_escape_string($koneksi, $_POST['pendidikan']);
    $alamat     = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $kesan      = mysqli_real_escape_string($koneksi, $_POST['kesan']);
    $tahun      = mysqli_real_escape_string($koneksi, $_POST['tahun_lulus']);

    // Proses upload foto
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];

    // Folder upload
    $folder = "uploads/alumni/";

    // Buat folder jika belum ada
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // Membuat nama file unik
    $nama_foto = time() . "_" . basename($foto);

    // Upload foto
    if (move_uploaded_file($tmp, $folder . $nama_foto)) {

        $nama_foto_db = mysqli_real_escape_string($koneksi, $nama_foto);

        // Simpan data ke database
        $query = mysqli_query($koneksi, "
            INSERT INTO alumni 
            (nama, pendidikan, alamat, kesan_pesan, foto, tahun_lulus)
            VALUES 
            ('$nama', '$pendidikan', '$alamat', '$kesan', '$nama_foto_db', '$tahun')
        ");

        if ($query) {
            echo "<script>
                alert('Data Alumni Berhasil Ditambahkan!');
                window.location='admin.php?tab=alumni';
            </script>";
        } else {
            echo "<script>
                alert('Data Alumni Gagal Ditambahkan!');
            </script>";
        }
    } else {
        echo "<script>
            alert('Foto gagal diupload!');
        </script>";
    }
}

// 6. TAMBAH USER ADMIN
if (isset($_POST['tambah_user'])) {

    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $user         = mysqli_real_escape_string($koneksi, $_POST['username']);
    $pass         = md5($_POST['password']);
    $level        = mysqli_real_escape_string($koneksi, $_POST['level']);

    $query = mysqli_query($koneksi, "
        INSERT INTO users 
        (nama_lengkap, username, password, level)
        VALUES 
        ('$nama_lengkap', '$user', '$pass', '$level')
    ");

    if ($query) {
        echo "<script>
            alert('User Admin Berhasil Ditambahkan!');
            window.location='admin.php?tab=users';
        </script>";
    } else {
        echo "<script>
            alert('User Admin Gagal Ditambahkan!');
        </script>";
    }
}

// 7. PROSES RESET PASSWORD USER (Khusus M4570KO)
if (isset($_POST['reset_password'])) {
    if (!$is_m4570ko) {
        echo "<script>alert('Akses Ditolak!'); window.location='admin.php';</script>";
        exit();
    }

    $id_user   = (int)$_POST['id_user'];
    $pass_baru = md5($_POST['password_baru']);

    mysqli_query($koneksi, "UPDATE users SET password='$pass_baru' WHERE id=$id_user");
    echo "<script>alert('Password User Berhasil Diubah!'); window.location='admin.php?tab=users';</script>";
}

// 8. PROSES HAPUS DATA (Dengan Proteksi Whitelist Tabel)
if (isset($_GET['hapus']) && isset($_GET['from'])) {
    $id  = (int)$_GET['hapus'];
    $tbl = $_GET['from'];

    // Whitelist tabel untuk mencegah kelemahan keamanan
    $allowed_tables = ['berita', 'galeri', 'alumni', 'users'];

    if (in_array($tbl, $allowed_tables)) {
        mysqli_query($koneksi, "DELETE FROM `$tbl` WHERE id=$id");
        echo "<script>alert('Data Berhasil Dihapus!'); window.location='admin.php?tab=$tbl';</script>";
    } else {
        echo "<script>alert('Tabel tidak valid!'); window.location='admin.php';</script>";
    }
}

// AMBIL DATA PROFIL SEKOLAH
$q_profil = mysqli_query($koneksi, "SELECT * FROM profil_sekolah WHERE id=1");
$profil   = mysqli_fetch_assoc($q_profil);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin CMS Sekolah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <!-- NAVBAR ATAS -->
    <nav class="navbar navbar-dark bg-success shadow-sm py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold fs-5 d-flex align-items-center" href="admin.php">
                <i class="bi bi-speedometer2 me-2"></i> Panel Admin CMS
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid my-4 px-4">
        <div class="row">

            <!-- SIDEBAR MENU UTAMA -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-list me-2"></i> Menu Utama
                    </div>
                    <div class="list-group list-group-flush">

                        <!-- MENU IDENTITAS SEKOLAH KHUSUS M4570KO -->
                        <?php if ($is_m4570ko): ?>
                            <a href="admin.php?tab=profil" class="list-group-item list-group-item-action <?= ($tab == 'profil') ? 'active fw-bold' : ''; ?>">
                                <i class="bi bi-building me-2"></i> Identitas Sekolah
                            </a>
                        <?php endif; ?>

                        <a href="admin.php?tab=struktur" class="list-group-item list-group-item-action <?= ($tab == 'struktur') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-diagram-3 me-2"></i> Struktur Organisasi
                        </a>
                        <a href="admin.php?tab=sarana" class="list-group-item list-group-item-action <?= ($tab == 'sarana') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-building-gear me-2"></i> Sarana & Prasarana
                        </a>
                        <a href="admin.php?tab=kegiatan" class="list-group-item list-group-item-action <?= ($tab == 'kegiatan') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-calendar-event me-2"></i> Kegiatan Sekolah
                        </a>
                        <a href="admin.php?tab=berita" class="list-group-item list-group-item-action <?= ($tab == 'berita') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-newspaper me-2"></i> Kelola Berita
                        </a>
                        <a href="admin.php?tab=galeri" class="list-group-item list-group-item-action <?= ($tab == 'galeri') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-images me-2"></i> Kelola Galeri Foto
                        </a>
                        <a href="admin.php?tab=alumni" class="list-group-item list-group-item-action <?= ($tab == 'alumni') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-mortarboard me-2"></i> Data Alumni
                        </a>
                        <a href="admin.php?tab=kontak" class="list-group-item list-group-item-action <?= ($tab == 'kontak') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-telephone me-2"></i> Kontak & Informasi
                        </a>
                        <a href="admin.php?tab=users" class="list-group-item list-group-item-action <?= ($tab == 'users') ? 'active fw-bold' : ''; ?>">
                            <i class="bi bi-people me-2"></i> Kelola User Admin
                        </a>
                        <a href="index.php" target="_blank" class="list-group-item list-group-item-action text-primary">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Lihat Halaman Depan
                        </a>
                    </div>
                </div>
            </div>

            <!-- KONTEN UTAMA -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">

                    <?php
                    // 1. IDENTITAS SEKOLAH (DIBATASI HANYA UNTUK M4570KO)
                    if ($tab == 'profil' && $is_m4570ko):
                    ?>
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-4"><i class="bi bi-pencil-square me-2"></i> Edit Identitas & Header Sekolah</h4>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Lembaga / Sekolah</label>
                                <input type="text" name="nama_sekolah" class="form-control" value="<?= htmlspecialchars($profil['nama_sekolah'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alamat Sekolah</label>
                                <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($profil['alamat'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Logo Sekolah</label>
                                <?php if (!empty($profil['logo']) && file_exists('uploads/' . $profil['logo'])): ?>
                                    <div class="mb-2 p-2 border rounded bg-light d-block">
                                        <img src="uploads/<?= $profil['logo']; ?>" alt="Logo Current" style="height: 60px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" name="simpan_profil" class="btn btn-success fw-bold px-4 py-2 mt-2"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                        </form>

                    <?php
                    // 2. HALAMAN DINAMIS
                    elseif (in_array($tab, ['struktur', 'sarana', 'kegiatan', 'kontak'])):
                        $q_hal = mysqli_query($koneksi, "SELECT * FROM halaman WHERE kategori='$tab'");
                        $d_hal = mysqli_fetch_assoc($q_hal);
                        $judul_hal = [
                            'struktur' => 'Struktur Organisasi',
                            'sarana'   => 'Sarana & Prasarana',
                            'kegiatan' => 'Kegiatan Sekolah',
                            'kontak'   => 'Kontak & Informasi'
                        ];
                    ?>
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-4">Kelola Halaman: <?= $judul_hal[$tab] ?? 'Halaman'; ?></h4>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="kategori" value="<?= $tab; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Judul Halaman</label>
                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($d_hal['judul'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Upload Gambar Utama (.jpg, .png)</label>
                                <input type="file" name="gambar" class="form-control" accept="image/*">
                                <?php if (!empty($d_hal['gambar']) && file_exists('uploads/' . $d_hal['gambar'])): ?>
                                    <div class="mt-2"><img src="uploads/<?= $d_hal['gambar']; ?>" class="img-thumbnail" style="max-height: 150px;"></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi / Konten Teks Tambahan</label>
                                <textarea name="konten" class="form-control" rows="8"><?= htmlspecialchars($d_hal['konten'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="simpan_halaman" class="btn btn-success fw-bold px-4 py-2"><i class="bi bi-save me-1"></i> Simpan Halaman</button>
                        </form>

                    <?php
                    // 3. KELOLA BERITA
                    elseif ($tab == 'berita'):
                    ?>
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-4">Kelola Berita Sekolah</h4>
                        <form action="" method="POST" enctype="multipart/form-data" class="mb-4 p-3 border rounded bg-light">
                            <h6 class="fw-bold mb-3">Tambah Berita Baru</h6>
                            <div class="mb-2"><input type="text" name="judul" class="form-control" placeholder="Judul Berita" required></div>
                            <div class="mb-2"><textarea name="isi" class="form-control" rows="4" placeholder="Isi Berita" required></textarea></div>
                            <div class="mb-2"><input type="file" name="gambar" class="form-control" accept="image/*"></div>
                            <button type="submit" name="tambah_berita" class="btn btn-success btn-sm fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Berita</button>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY id DESC");
                                    while ($d = mysqli_fetch_assoc($q)):
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($d['judul'] ?? ''); ?></td>
                                            <td><?= htmlspecialchars($d['tanggal'] ?? '-'); ?></td>
                                            <td><a href="admin.php?tab=berita&hapus=<?= $d['id']; ?>&from=berita" class="btn btn-danger btn-sm" onclick="return confirm('Hapus berita ini?')"><i class="bi bi-trash"></i></a></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php
                    // 4. KELOLA GALERI FOTO
                    elseif ($tab == 'galeri'):
                    ?>
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-4">Kelola Galeri Foto</h4>
                        <form action="" method="POST" enctype="multipart/form-data" class="mb-4 p-3 border rounded bg-light">
                            <h6 class="fw-bold mb-3">Upload Foto Baru</h6>
                            <div class="mb-2"><input type="text" name="judul" class="form-control" placeholder="Keterangan Foto" required></div>
                            <div class="mb-2"><input type="file" name="gambar" class="form-control" accept="image/*" required></div>
                            <button type="submit" name="tambah_galeri" class="btn btn-success btn-sm fw-bold"><i class="bi bi-upload me-1"></i> Upload Foto</button>
                        </form>
                        <div class="row">
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM galeri ORDER BY id DESC");
                            while ($d = mysqli_fetch_assoc($q)):
                            ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <img src="uploads/<?= $d['gambar']; ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                                        <div class="card-body p-2 text-center">
                                            <small class="d-block fw-bold mb-2"><?= htmlspecialchars($d['judul'] ?? ''); ?></small>
                                            <a href="admin.php?tab=galeri&hapus=<?= $d['id']; ?>&from=galeri" class="btn btn-danger btn-sm w-100" onclick="return confirm('Hapus foto ini?')"><i class="bi bi-trash me-1"></i> Hapus</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                    <?php
                    // 5. DATA ALUMNI
                    elseif ($tab == 'alumni'):
                    ?>
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-4">Kelola Data Alumni</h4>
                        <form action="" method="POST" enctype="multipart/form-data" class="mb-4 p-3 border rounded bg-light">

                            <h6 class="fw-bold mb-3">Tambah Data Alumni</h6>

                            <!-- Nama + Tahun Lulus -->
                            <div class="row g-2 mb-2">

                                <div class="col-md-8">
                                    <input
                                        type="text"
                                        name="nama"
                                        class="form-control"
                                        placeholder="Nama Alumni"
                                        required>
                                </div>

                                <div class="col-md-4">
                                    <input
                                        type="number"
                                        name="tahun_lulus"
                                        class="form-control"
                                        placeholder="Tahun Lulus"
                                        min="1900"
                                        max="2100"
                                        required>
                                </div>

                            </div>

                            <!-- Pendidikan -->
                            <div class="mb-2">
                                <input
                                    type="text"
                                    name="pendidikan"
                                    class="form-control"
                                    placeholder="Pendidikan Terakhir (contoh: S1 Informatika)"
                                    required>
                            </div>

                            <!-- Alamat -->
                            <div class="mb-2">
                                <textarea
                                    name="alamat"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Alamat Alumni"
                                    required></textarea>
                            </div>

                            <!-- Kesan / Pesan -->
                            <div class="mb-2">
                                <textarea
                                    name="kesan"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Kesan / Pesan Alumni"></textarea>
                            </div>

                            <!-- Foto -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto Alumni</label>

                                <input
                                    type="file"
                                    name="foto"
                                    class="form-control"
                                    accept="image/*"
                                    required>

                                <small class="text-muted">
                                    Format: JPG, JPEG, PNG. Pilih foto alumni.
                                </small>
                            </div>

                            <!-- Tombol -->
                            <button
                                type="submit"
                                name="tambah_alumni"
                                class="btn btn-success btn-sm fw-bold">
                                <i class="bi bi-plus-circle me-1"></i>
                                Tambah Alumni
                            </button>

                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Foto</th>
                                        <th>Nama</th>
                                        <th>Pendidikan</th>
                                        <th>Alamat</th>
                                        <th>Tahun Lulus</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $no = 1;

                                    $q = mysqli_query(
                                        $koneksi,
                                        "SELECT * FROM alumni ORDER BY id DESC"
                                    );

                                    while ($d = mysqli_fetch_assoc($q)):
                                    ?>

                                        <tr>
                                            <!-- Nomor -->
                                            <td>
                                                <?= $no++; ?>
                                            </td>

                                            <!-- Foto -->
                                            <td class="text-center">
                                                <?php if (!empty($d['foto'])): ?>

                                                    <img
                                                        src="uploads/alumni/<?= htmlspecialchars($d['foto']); ?>"
                                                        alt="Foto <?= htmlspecialchars($d['nama']); ?>"
                                                        width="70"
                                                        height="70"
                                                        style="object-fit: cover; border-radius: 8px;">

                                                <?php else: ?>

                                                    <span class="text-muted">
                                                        Tidak ada foto
                                                    </span>

                                                <?php endif; ?>
                                            </td>

                                            <!-- Nama -->
                                            <td>
                                                <?= htmlspecialchars($d['nama'] ?? ''); ?>
                                            </td>

                                            <!-- Pendidikan -->
                                            <td>
                                                <?= htmlspecialchars($d['pendidikan'] ?? '-'); ?>
                                            </td>

                                            <!-- Alamat -->
                                            <td>
                                                <?= htmlspecialchars($d['alamat'] ?? '-'); ?>
                                            </td>

                                            <!-- Tahun Lulus -->
                                            <td>
                                                <?= htmlspecialchars($d['tahun_lulus'] ?? '-'); ?>
                                            </td>

                                            <!-- Aksi -->
                                            <td>
                                                <a
                                                    href="admin.php?tab=alumni&hapus=<?= $d['id']; ?>&from=alumni"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Hapus data alumni ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                    // 6. KELOLA USER ADMIN
                    elseif ($tab == 'users'):
                    ?>
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-4">Kelola User Admin</h4>

                        <form action="" method="POST" class="mb-4 p-3 border rounded bg-light">

                            <h6 class="fw-bold mb-3">Tambah Admin Baru</h6>

                            <div class="row g-2 mb-2">

                                <!-- Nama Lengkap -->
                                <div class="col-md-4">
                                    <input
                                        type="text"
                                        name="nama_lengkap"
                                        class="form-control"
                                        placeholder="Nama Lengkap"
                                        required>
                                </div>

                                <!-- Username -->
                                <div class="col-md-3">
                                    <input
                                        type="text"
                                        name="username"
                                        class="form-control"
                                        placeholder="Username"
                                        required>
                                </div>

                                <!-- Password -->
                                <div class="col-md-3">
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Password"
                                        required>
                                </div>

                                <!-- Level -->
                                <input
                                    type="hidden"
                                    name="level"
                                    value="admin">

                                <!-- Tombol -->
                                <div class="col-md-2">
                                    <button
                                        type="submit"
                                        name="tambah_user"
                                        class="btn btn-success w-100 fw-bold">
                                        <i class="bi bi-person-plus me-1"></i>
                                        Tambah
                                    </button>
                                </div>

                            </div>

                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Lengkap</th>
                                        <th>Username</th>
                                        <th>Level</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC");
                                    while ($d = mysqli_fetch_assoc($q)):
                                        $lvl = $d['level'] ?? $d['role'] ?? 'admin';
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>

                                            <td>
                                                <?= htmlspecialchars($d['nama_lengkap'] ?? '-'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($d['username'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($lvl); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if (strtolower($d['username'] ?? '') !== 'm4570ko'): ?>

                                                    <!-- Tombol Reset Password -->
                                                    <?php if ($is_m4570ko): ?>

                                                        <button
                                                            type="button"
                                                            class="btn btn-warning btn-sm me-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalReset<?= $d['id']; ?>">
                                                            <i class="bi bi-key me-1"></i>
                                                            Reset Password
                                                        </button>

                                                        <!-- Modal Reset Password -->
                                                        <div
                                                            class="modal fade"
                                                            id="modalReset<?= $d['id']; ?>"
                                                            tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">

                                                                    <form action="" method="POST">

                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">
                                                                                Reset Password -
                                                                                <?= htmlspecialchars($d['username']); ?>
                                                                            </h5>

                                                                            <button
                                                                                type="button"
                                                                                class="btn-close"
                                                                                data-bs-dismiss="modal"></button>
                                                                        </div>

                                                                        <div class="modal-body text-start">

                                                                            <input
                                                                                type="hidden"
                                                                                name="id_user"
                                                                                value="<?= $d['id']; ?>">

                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-semibold">
                                                                                    Masukkan Password Baru
                                                                                </label>

                                                                                <input
                                                                                    type="password"
                                                                                    name="password_baru"
                                                                                    class="form-control"
                                                                                    required
                                                                                    placeholder="Password baru...">
                                                                            </div>

                                                                        </div>

                                                                        <div class="modal-footer">

                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-secondary btn-sm"
                                                                                data-bs-dismiss="modal">
                                                                                Batal
                                                                            </button>

                                                                            <button
                                                                                type="submit"
                                                                                name="reset_password"
                                                                                class="btn btn-success btn-sm">
                                                                                Simpan Password Baru
                                                                            </button>

                                                                        </div>

                                                                    </form>

                                                                </div>
                                                            </div>
                                                        </div>

                                                    <?php endif; ?>

                                                    <!-- Hapus User -->
                                                    <a
                                                        href="admin.php?tab=users&hapus=<?= $d['id']; ?>&from=users"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Hapus user ini?')">
                                                        <i class="bi bi-trash"></i>
                                                        Hapus
                                                    </a>

                                                <?php else: ?>

                                                    <small class="text-muted">
                                                        Utama
                                                    </small>

                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php
                    // 7. DEFAULT BERANDA
                    else:
                    ?>
                        <div class="p-4 text-center">
                            <h3 class="text-success fw-bold mb-3">Selamat Datang di Panel Admin</h3>
                            <p class="text-muted fs-5">Silakan pilih menu di sebelah kiri untuk mengelola konten website sekolah.</p>
                            <hr class="my-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light p-3">
                                        <i class="bi bi-newspaper fs-1 text-success mb-2"></i>
                                        <h5>Kelola Berita</h5>
                                        <p class="small text-muted mb-0">Atur postingan dan informasi kegiatan terbaru.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light p-3">
                                        <i class="bi bi-images fs-1 text-success mb-2"></i>
                                        <h5>Galeri Foto</h5>
                                        <p class="small text-muted mb-0">Upload dokumentasi kegiatan sekolah.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light p-3">
                                        <i class="bi bi-mortarboard fs-1 text-success mb-2"></i>
                                        <h5>Data Alumni</h5>
                                        <p class="small text-muted mb-0">Kelola jejak alumni dan lulusan sekolah.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <!-- JS Bootstrap untuk mendukung interaktivitas Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>