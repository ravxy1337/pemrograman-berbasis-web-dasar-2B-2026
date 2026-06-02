<?php

$timeline = array(
    array(
        "tahun"      => "Agustus 2025",
        "judul"      => "Awal Masuk Kuliah",
        "keterangan" => "Baru masuk kuliah di semester 1 dan mulai mengenal dunia IT.",
        "penting"    => true
    ),
    array(
        "tahun"      => "2025",
        "judul"      => "Algoritma Pemrograman Dasar",
        "keterangan" => "Belajar dasar algoritma dan logika pemrograman menggunakan bahasa Python selama satu semester penuh.",
        "penting"    => false
    ),
    array(
        "tahun"      => "Awal 2026",
        "judul"      => "Masuk Semester 2",
        "keterangan" => "Melanjutkan studi ke semester 2 dengan materi yang lebih fokus.",
        "penting"    => true
    ),
    array(
        "tahun"      => "2026",
        "judul"      => "Pemrograman Berbasis Web",
        "keterangan" => "Mulai belajar membangun halaman web menggunakan HTML, CSS, JavaScript, dan PHP.",
        "penting"    => false
    ),
    array(
        "tahun"      => "2026",
        "judul"      => "Pengantar Basis Data",
        "keterangan" => "Mempelajari konsep database, mencakup materi DML, DDL, query menggunakan JOIN antar tabel, fungsi agregasi, dll.",
        "penting"    => false
    )
);

function formatTahun($tahun, $penting) {
    if ($penting == true) {
        echo "<strong style='color: #000080;'>" . $tahun . " •</strong>";
    } else {
        echo "<strong>" . $tahun . "</strong>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Timeline Belajar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Timeline Perjalanan Belajar Coding</h1>

<?php foreach ($timeline as $item) { ?>
    <div class="kotak-timeline">
        <?php formatTahun($item['tahun'], $item['penting']); ?>
        <h3><?php echo $item['judul']; ?></h3>
        <p><?php echo $item['keterangan']; ?></p>
    </div>
<?php } ?>

<div class="nav">
    <a href="index.php">Kembali ke Profil</a>
    <a href="blog.php">Ke Blog Developer</a>
</div>

</body>
</html>