<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$judul_halaman = 'Tambah Buku';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Tambah Buku Baru</span>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
        <?php
        $pesan = '';
        $tipe_pesan = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $judul = trim($_POST['judul']);
            $pengarang = trim($_POST['pengarang']);
            $penerbit = trim($_POST['penerbit']);
            $tahun_terbit = $_POST['tahun_terbit'];
            $isbn = empty(trim($_POST['isbn'])) ? null : trim($_POST['isbn']);
            $kategori = $_POST['kategori'];
            $stok = (int) $_POST['stok'];
            $deskripsi = trim($_POST['deskripsi']);
            $cover_buku = null;

            if (empty($judul) || empty($pengarang) || empty($penerbit) || empty($tahun_terbit) || empty($kategori)) {
                $pesan = 'Semua kolom yang wajib harus diisi.';
                $tipe_pesan = 'danger';
            } elseif ($stok < 0) {
                $pesan = 'Stok tidak boleh negatif.';
                $tipe_pesan = 'danger';
            } else {
                if (isset($_FILES['cover_buku']) && $_FILES['cover_buku']['error'] === UPLOAD_ERR_OK) {
                    $nama_file = $_FILES['cover_buku']['name'];
                    $tmp = $_FILES['cover_buku']['tmp_name'];
                    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

                    if (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $pesan = 'Format file tidak valid.';
                        $tipe_pesan = 'danger';
                    } else {
                        $nama_baru = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $nama_file);
                        move_uploaded_file($tmp, '../../assets/uploads/cover/' . $nama_baru);
                        $cover_buku = $nama_baru;
                    }
                }

                if (empty($pesan)) {
                    $cek_isbn = false;
                    if ($isbn) {
                        $q_isbn = mysqli_query($koneksi, "SELECT id FROM buku WHERE isbn='$isbn'");
                        if (mysqli_num_rows($q_isbn) > 0) {
                            $pesan = 'ISBN sudah terdaftar pada buku lain.';
                            $tipe_pesan = 'danger';
                            $cek_isbn = true;
                        }
                    }
                    
                    if (!$cek_isbn) {
                        $val_isbn = $isbn ? "'$isbn'" : "NULL";
                        $val_cover = $cover_buku ? "'$cover_buku'" : "NULL";
                        $query = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, isbn, kategori, stok, deskripsi, cover_buku) 
                                  VALUES ('$judul', '$pengarang', '$penerbit', '$tahun_terbit', $val_isbn, '$kategori', $stok, '$deskripsi', $val_cover)";
                        
                        if (mysqli_query($koneksi, $query)) {
                            $_SESSION['pesan'] = 'Buku berhasil ditambahkan.';
                            $_SESSION['tipe_pesan'] = 'success';
                            echo "<meta http-equiv='refresh' content='0;url=index.php'>";
                            exit;
                        } else {
                            $pesan = 'Terjadi kesalahan sistem: ' . mysqli_error($koneksi);
                            $tipe_pesan = 'danger';
                        }
                    }
                }
            }
        }
        ?>
        <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="formTambah">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="judul" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul"
                        value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pengarang" class="form-label">Pengarang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pengarang" name="pengarang"
                        value="<?= isset($_POST['pengarang']) ? htmlspecialchars($_POST['pengarang']) : '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="penerbit" class="form-label">Penerbit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="penerbit" name="penerbit"
                        value="<?= isset($_POST['penerbit']) ? htmlspecialchars($_POST['penerbit']) : '' ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tahun_terbit" class="form-label">Tahun Terbit <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" min="1900"
                        max="<?= date('Y') ?>"
                        value="<?= isset($_POST['tahun_terbit']) ? htmlspecialchars($_POST['tahun_terbit']) : '' ?>"
                        required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok" name="stok" min="0"
                        value="<?= isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : '0' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input type="text" class="form-control" id="isbn" name="isbn" maxlength="13"
                        value="<?= isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : '' ?>">
                    <div class="form-text">Biarkan kosong jika tidak ada (Maksimal 13 karakter).</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select" id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        $kategori_list = ['Fiksi', 'Non-Fiksi', 'Sains', 'Sejarah', 'Teknologi', 'Sastra', 'Agama', 'Lainnya'];
                        foreach ($kategori_list as $kat) {
                            $selected = (isset($_POST['kategori']) && $_POST['kategori'] == $kat) ? 'selected' : '';
                            echo "<option value=\"$kat\" $selected>$kat</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi"
                        rows="4"><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                </div>
                <div class="col-md-12 mb-4">
                    <label for="cover_buku" class="form-label">Cover Buku</label>
                    <input class="form-control" type="file" id="cover_buku" name="cover_buku"
                        accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">Maksimal ukuran file 2MB. Format: JPG, PNG, WEBP.</div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Buku</button>
        </form>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formTambah').addEventListener('submit', function (e) {
        let valid = true;

        this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        this.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        function tampilkanError(elemen, pesan) {
            elemen.classList.add('is-invalid');
            let divError = document.createElement('div');
            divError.className = 'invalid-feedback';
            divError.textContent = pesan;
            elemen.parentNode.insertBefore(divError, elemen.nextSibling);
            valid = false;
        }

        const judul = document.getElementById('judul');
        if (judul.value.trim() === '') tampilkanError(judul, 'Judul wajib diisi');

        const stok = document.getElementById('stok');
        if (parseInt(stok.value) < 0) tampilkanError(stok, 'Stok tidak boleh negatif');

        const isbn = document.getElementById('isbn');
        if (isbn.value.trim() !== '' && isbn.value.length !== 13) {
            tampilkanError(isbn, 'ISBN harus 13 karakter');
        }

        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
