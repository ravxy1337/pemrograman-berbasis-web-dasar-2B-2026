<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Blog Developer</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>Blog Reflektif Developer</h1>

    <div class="kotak-kutipan">
        <strong>Kutipan Hari Ini:</strong><br>
        <?php
        $kutipan = array(
            "Kode yang baik adalah kode yang bisa dibaca orang lain.",
            "Jangan takut error, itu artinya kamu sedang belajar.",
        );
        $index_acak = array_rand($kutipan);
        echo $kutipan[$index_acak];
        ?>
    </div>
    <br>

    <h2>Daftar Artikel</h2>
    <?php
    $artikel = array(
        "html" => array(
            "judul" => "Belajar HTML Pertama Kali",
            "tanggal" => "05 April 2026",
            "isi" => "Pertama kali belajar HTML rasanya campur aduk. Bingung kenapa tag tidak mau tertutup, lupa pakai tanda kurung siku, dan hasilnya berantakan. Tapi setelah pelan-pelan mencoba, akhirnya berhasil membuat halaman sederhana yang bisa dibuka di browser",
            "gambar" => "img/html.jpg",
            "link" => "https://www.w3schools.com/html/"
        ),
        "error" => array(
            "judul" => "Error Pertama Saat Coding",
            "tanggal" => "06 April 2026",
            "isi" => "Error pertama yang saya temui adalah undefined variable di PHP. Saya panik karena tidak tahu artinya. Setelah searching di Google, ternyata variabelnya salah nama. Dari sini saya belajar pentingnya teliti dalam menulis kode.",
            "gambar" => "img/error.jpg",
            "link" => "https://www.php.net/manual/en/language.variables.php"
        ),
        "proyek" => array(
            "judul" => "Proyek Website Pertama",
            "tanggal" => "25 April 2026",
            "isi" => "Proyek pertama saya adalah website profil sederhana menggunakan HTML, CSS, dan PHP. Prosesnya tidak mudah, banyak bug dan tampilan yang tidak sesuai harapan. Namun setelah selesai, saya merasa bangga bisa membuat sesuatu dari nol.",
            "gambar" => "img/proyek.jpg",
            "link" => "https://www.w3schools.com/php/"
        )
    );

    foreach ($artikel as $key => $data) { ?>
        <a class="link-artikel" href="blog.php?artikel=<?php echo $key; ?>">
            <?php echo $data['judul']; ?>
        </a>

    <?php } ?>

    <?php
    $artikel_dipilih = null;
    if (isset($_GET['artikel']) && array_key_exists($_GET['artikel'], $artikel)) {
        $artikel_dipilih = $artikel[$_GET['artikel']];  
    }

    if ($artikel_dipilih != null) { ?>

        <div class="kotak-artikel">
            <h2><?php echo $artikel_dipilih['judul']; ?></h2>
            <p><em>Tanggal: <?php echo $artikel_dipilih['tanggal']; ?></em></p>

            <img src="<?php echo $artikel_dipilih['gambar']; ?>" alt="ilustrasi" width="300"><br><br>

            <p><?php echo $artikel_dipilih['isi']; ?></p>

            <p>Referensi: <a href="<?php echo $artikel_dipilih['link']; ?>"
                    target="_blank"><?php echo $artikel_dipilih['link']; ?></a></p>
        </div>

    <?php } ?>

    <div class="nav">
        <a href="timeline.php">Ke Timeline</a>
        <a href="index.php">Ke Profil</a>
    </div>

</body>

</html>