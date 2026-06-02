<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Developer</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php
    function barisTabel($label, $nilai)
    {
        echo "<tr>";
        echo "<td>" . $label . "</td>";
        echo "<td>" . $nilai . "</td>";
        echo "</tr>";
    }
    ?>
    <h1>Profil Interaktif Developer Pemula</h1>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $framework = $_POST['framework'];
        $pengalaman = $_POST['pengalaman'];
        $tools = isset($_POST['tools']) ? $_POST['tools'] : [];
        $minat = isset($_POST['minat']) ? $_POST['minat'] : "";
        $skill = $_POST['skill'];

        $error = "";

        if ($framework == "") {
            $error .= "Framework wajib diisi! <br>";
        }
        if ($pengalaman == "") {
            $error .= "Pengalaman wajib diisi! <br>";
        }
        if (empty($tools)) {
            $error .= "Tools penunjang wajib dipilih! <br>";
        }
        if ($minat == "") {
            $error .= "Minat bidang wajib dipilih! <br>";
        }
        if ($skill == "") {
            $error .= "Tingkat skill wajib dipilih! <br>";
        }

        if ($error == "") {

            echo "<p class='sukses'>Data berhasil dikirim!</p>";

            $tools_dipilih = "";
            foreach ($tools as $t) {
                $tools_dipilih .= $t . ", ";
            }
            $tools_dipilih = rtrim($tools_dipilih, ", ");

            $array_framework = explode(",", $framework);
            $jumlah_framework = count($array_framework);

            if ($jumlah_framework > 2) {
                echo "<p class='sukses'>Skill Anda cukup luas di bidang development!</p>";
            }

            echo "<h2>Hasil Data Developer</h2>";
            echo "<table>";
            barisTabel("Framework/Tools", $framework);
            barisTabel("Tools", $tools_dipilih);
            barisTabel("Minat Bidang", $minat);
            barisTabel("Tingkat Skill", $skill);
            echo "</table>";

            echo "<h3>Pengalaman:</h3>";
            echo "<p>" . $pengalaman . "</p>";
            echo "<br><a href='index.php'>Kembali ke Form</a>";

        } else {
            echo "<p class='error'>" . $error . "</p>";
            echo "<a href='index.php'>Kembali</a>";
        }

    } else {
        ?>
        <h2>Data Pribadi</h2>
        <table>
            <?php barisTabel("Nama", "Rava Aji Utomo"); ?>
            <?php barisTabel("ID Developer", "DEV-2025-00009"); ?>
            <?php barisTabel("Kota / Tgl Lahir", "Lumajang / 23 Januari 2007"); ?>
            <?php barisTabel("Email", "ravaasek@email.com"); ?>
            <?php barisTabel("No. WhatsApp", "088119922000"); ?>
        </table>
        <br>

        <h2>Form Developer</h2>
        <form method="POST" action="index.php">
            <p>
                <label>Framework/Tools (pisah dengan koma):</label><br>
                <input type="text" name="framework" placeholder="contoh: Laravel, Vue, React">
            </p>

            <p>
                <label>Cerita Singkat Pengalaman:</label><br>
                <textarea name="pengalaman" placeholder="Ceritakan pengalamanmu..."></textarea>
            </p>
            <p>
                <label>Tools:</label><br>
                <input type="checkbox" name="tools[]" value="VS Code"> VS Code <br>
                <input type="checkbox" name="tools[]" value="GitHub"> GitHub <br>
                <input type="checkbox" name="tools[]" value="Figma"> Figma <br>
                <input type="checkbox" name="tools[]" value="Postman"> Postman <br>
            </p>
            <p>
                <label>Minat Bidang:</label><br>
                <input type="radio" name="minat" value="Frontend"> Frontend
                <input type="radio" name="minat" value="Backend"> Backend
                <input type="radio" name="minat" value="Fullstack"> Fullstack
            </p>

            <p>
                <label>Tingkat Skill Coding:</label><br>
                <select name="skill">
                    <option value="">-- Pilih --</option>
                    <option value="Dasar">Dasar</option>
                    <option value="Cukup">Cukup</option>
                    <option value="Profesional">Profesional</option>
                </select>
            </p>
            <input type="submit" value="Kirim Data">
        </form>
    <?php } ?>

    <div class="nav">
        <a href="timeline.php">Ke Timeline</a>
        <a href="blog.php">Ke Blog</a>
    </div>

</body>

</html>