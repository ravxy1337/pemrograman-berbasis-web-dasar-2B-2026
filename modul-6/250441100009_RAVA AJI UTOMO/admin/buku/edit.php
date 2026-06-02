
<!--  -->
 <?php 
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$pesan = '';
$tipe_pesan = '';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_buku = (int) $_GET['id'];
$q_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id = $id_buku");

if (mysqli_num_rows($q_buku) == 0) {
    header('Location: index.php');
    exit;
}

$buku = mysqli_fetch_assoc($q_buku);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = trim($_POST['isbn']);
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $deskripsi = trim($_POST['deskripsi']);
    $cover_buku = $buku['cover_buku']; // Default cover lama

    if (empty($judul) || empty($pengarang) || empty($penerbit) || empty($tahun_terbit) || empty($kategori)) {
        $pesan = 'Semua kolom yang wajib harus diisi.';
        $tipe_pesan = 'danger';
    } elseif ($stok < 0) {
        $pesan = 'Stok tidak boleh negatif.';
        $tipe_pesan = 'danger';
    } else {
        // Upload Cover
        if (isset($_FILES['cover_buku']) && $_FILES['cover_buku']['error'] === UPLOAD_ERR_OK) {
            $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
            $nama_file = $_FILES['cover_buku']['name'];
            $ukuran = $_FILES['cover_buku']['size'];
            $tmp = $_FILES['cover_buku']['tmp_name'];

            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

            if (!in_array($ekstensi, $ekstensi_diizinkan)) {
                $pesan = 'Apa? mau up shell? ngehek dulu yang bener deck';
                $tipe_pesan = 'danger';
            } elseif ($ukuran > 2097152) { // 2MB
                $pesan = 'Ukuran file maksimal 2MB.';
                $tipe_pesan = 'danger';
            } else {
                $nama_baru = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $nama_file);
                $path = '../../assets/uploads/cover/' . $nama_baru;

                if (move_uploaded_file($tmp, $path)) {
                    if (!empty($buku['cover_buku']) && file_exists('../../assets/uploads/cover/' . $buku['cover_buku'])) {
                        unlink('../../assets/uploads/cover/' . $buku['cover_buku']);
                    }
                    $cover_buku = $nama_baru;
                } else {
                    $pesan = 'Gagal mengunggah file cover.';
                    $tipe_pesan = 'danger';
                }
            }
        }

        if (empty($pesan)) {
            $isbn = empty($isbn) ? null : $isbn;
            $stmt = mysqli_prepare($koneksi, "UPDATE buku SET judul=?, pengarang=?, penerbit=?, tahun_terbit=?, isbn=?, kategori=?, stok=?, deskripsi=?, cover_buku=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssssissi", $judul, $pengarang, $penerbit, $tahun_terbit, $isbn, $kategori, $stok, $deskripsi, $cover_buku, $id_buku);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['pesan'] = 'Data buku berhasil diperbarui.';
                $_SESSION['tipe_pesan'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                if (mysqli_errno($koneksi) == 1062) { // Duplicate entry
                    $pesan = 'ISBN sudah terdaftar pada buku lain.';
                } else {
                    $pesan = 'Terjadi kesalahan saat menyimpan data: ' . mysqli_error($koneksi);
                }
                $tipe_pesan = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$judul_halaman = 'Edit Buku';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Edit Data Buku</span>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
        <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="formEdit">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="judul" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul"
                        value="<?= htmlspecialchars($buku['judul']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pengarang" class="form-label">Pengarang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pengarang" name="pengarang"
                        value="<?= htmlspecialchars($buku['pengarang']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="penerbit" class="form-label">Penerbit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="penerbit" name="penerbit"
                        value="<?= htmlspecialchars($buku['penerbit']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tahun_terbit" class="form-label">Tahun Terbit <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" min="1900"
                        max="<?= date('Y') ?>" value="<?= htmlspecialchars($buku['tahun_terbit']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok" name="stok" min="0"
                        value="<?= htmlspecialchars($buku['stok']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input type="text" class="form-control" id="isbn" name="isbn" maxlength="13"
                        value="<?= htmlspecialchars((string) $buku['isbn']) ?>">
                    <div class="form-text">Biarkan kosong jika tidak ada (Maksimal 13 karakter).</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select" id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        $kategori_list = ['Fiksi', 'Non-Fiksi', 'Sains', 'Sejarah', 'Teknologi', 'Sastra', 'Agama', 'Lainnya'];
                        foreach ($kategori_list as $kat) {
                            $selected = ($buku['kategori'] == $kat) ? 'selected' : '';
                            echo "<option value=\"$kat\" $selected>$kat</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi"
                        rows="4"><?= htmlspecialchars($buku['deskripsi']) ?></textarea>
                </div>
                <div class="col-md-12 mb-4">
                    <label class="form-label">Cover Saat Ini</label>
                    <div>
                        <?php if (!empty($buku['cover_buku'])): ?>
                            <img src="<?= BASE_URL ?>/assets/uploads/cover/<?= $buku['cover_buku'] ?>" alt="Cover"
                                style="max-height: 150px; border-radius: 4px; border: 1px solid #ddd; padding: 2px;">
                        <?php else: ?>
                            <span class="text-muted">Tidak ada cover.</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <label for="cover_buku" class="form-label">Ganti Cover Buku (Opsional)</label>
                    <input class="form-control" type="file" id="cover_buku" name="cover_buku"
                        accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">Biarkan kosong jika tidak ingin mengganti cover. Maksimal ukuran file 2MB.
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formEdit').addEventListener('submit', function (e) {
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

<!-- hapus.php -->
 <?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $q_cover = mysqli_query($koneksi, "SELECT cover_buku FROM buku WHERE id = $id");
    if (mysqli_num_rows($q_cover) > 0) {
        $cover = mysqli_fetch_assoc($q_cover)['cover_buku'];

        $stmt = mysqli_prepare($koneksi, "DELETE FROM buku WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            // Hapus file cover jika ada
            if (!empty($cover) && file_exists('../../assets/uploads/cover/' . $cover)) {
                unlink('../../assets/uploads/cover/' . $cover);
            }
            $_SESSION['pesan'] = 'Buku berhasil dihapus.';
            $_SESSION['tipe_pesan'] = 'success';
        } else {
            $_SESSION['pesan'] = 'Gagal menghapus buku: ' . mysqli_error($koneksi);
            $_SESSION['tipe_pesan'] = 'danger';
        }
        mysqli_stmt_close($stmt);
    }
}

header('Location: index.php');
exit;
?>


<!-- index.php -->
 <?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$judul_halaman = 'Manajemen Buku';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Buku</span>
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Buku
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-<?= $_SESSION['tipe_pesan'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['pesan']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            unset($_SESSION['pesan']);
            unset($_SESSION['tipe_pesan']);
            ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover ">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id DESC");
                    $no = 1;
                    if (mysqli_num_rows($query) > 0):
                        while ($row = mysqli_fetch_assoc($query)):
                            $cover = !empty($row['cover_buku']) ? BASE_URL . '/assets/uploads/cover/' . $row['cover_buku'] : BASE_URL . '/assets/uploads/cover/default.jpg';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><img src="<?= $cover ?>" alt="Cover" style="width: 50px; height: 70px; object-fit: cover;">
                                </td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['pengarang']) ?></td>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td>
                                    <?php if ($row['stok'] > 0): ?>
                                        <span class="badge bg-success"><?= $row['stok'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="konfirmasiHapus(<?= $row['id'] ?>)" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data buku.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus buku ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="linkHapus" class="btn btn-danger">Ya, Hapus</a>
            </div>
        </div>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function konfirmasiHapus(id) {
        document.getElementById('linkHapus').href = 'hapus.php?id=' + id;
        var modal = new bootstrap.Modal(document.getElementById('modalHapus'));
        modal.show();
    }
</script>
</body>

</html>

<!-- tambah.php -->
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
