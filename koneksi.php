<?php
$host = "154.19.37.34";
$user = "cms_sekolah";
$pass = "avT**E_2EWh@8mVD";
$db   = "cms-sekolah";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>