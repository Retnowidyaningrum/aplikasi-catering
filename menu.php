<?php
session_start();
if(!isset($_SESSION['login'])) header('Location: index.php');
include 'config/database.php';

// ========== PROSES TAMBAH MENU ==========
if(isset($_POST['tambah'])) {
    if (!verifyCsrfToken()) { die('Token CSRF tidak valid!'); }
    
    $price = str_replace('.', '', $_POST['price']);
    
    // Upload gambar
    $imageName = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if(!in_array($ext, $allowed)) {
            echo "<script>alert('Format file tidak didukung! (JPG, PNG, WEBP, GIF)'); location.href='menu.php';</script>";
            exit();
        }
        if($_FILES['image']['size'] > $maxSize) {
            echo "<script>alert('Ukuran file maksimal 2MB!'); location.href='menu.php';</script>";
            exit();
        }
        
        $imageName = time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = __DIR__ . '/uploads/menus/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
    }
    
    $stmt = $db->prepare("INSERT INTO menus (name, category, price, image) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['name'], $_POST['category'], $price, $imageName]);
    echo "<script>alert('Menu berhasil ditambahkan!'); location.href='menu.php';</script>";
}

// ========== PROSES EDIT MENU ==========
if(isset($_POST['edit'])) {
    if (!verifyCsrfToken()) { die('Token CSRF tidak valid!'); }
    $price = str_replace('.', '', $_POST['price']);
    
    // Ambil data menu lama
    $getOld = $db->prepare("SELECT image FROM menus WHERE id = ?");
    $getOld->execute([$_POST['id']]);
    $oldImage = $getOld->fetchColumn();
    
    $imageName = $oldImage;
    
    // Upload gambar baru jika ada
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $maxSize = 2 * 1024 * 1024;
        
        if(!in_array($ext, $allowed)) {
            echo "<script>alert('Format file tidak didukung!'); location.href='menu.php';</script>";
            exit();
        }
        if($_FILES['image']['size'] > $maxSize) {
            echo "<script>alert('Ukuran file maksimal 2MB!'); location.href='menu.php';</script>";
            exit();
        }
        
        // Hapus gambar lama jika ada
        if($oldImage && file_exists(__DIR__ . '/uploads/menus/' . $oldImage)) {
            unlink(__DIR__ . '/uploads/menus/' . $oldImage);
        }
        
        $imageName = time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = __DIR__ . '/uploads/menus/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
    }
    
    $stmt = $db->prepare("UPDATE menus SET name=?, category=?, price=?, image=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['category'], $price, $imageName, $_POST['id']]);
    echo "<script>alert('Menu berhasil diupdate!'); location.href='menu.php';</script>";
}

// ========== PROSES HAPUS MENU ==========
if(isset($_GET['hapus'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die('Token CSRF tidak valid!');
    }
    
    // Ambil nama gambar
    $getImage = $db->prepare("SELECT image FROM menus WHERE id = ?");
    $getImage->execute([$_GET['hapus']]);
    $image = $getImage->fetchColumn();
    
    // Hapus file gambar jika ada
    if($image && file_exists(__DIR__ . '/uploads/menus/' . $image)) {
        unlink(__DIR__ . '/uploads/menus/' . $image);
    }
    
    $db->prepare("DELETE FROM menus WHERE id=?")->execute([$_GET['hapus']]);
    echo "<script>alert('Menu dihapus!'); location.href='menu.php';</script>";
}

// ========== AMBIL SEMUA DATA MENU ==========
$menus = $db->query("SELECT * FROM menus ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cateringku - Kelola Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .table th, .table td { vertical-align: middle; padding: 12px; }
        .table th { background: #f8f9fa; font-weight: 600; white-space: nowrap; text-align: center; }
        
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 6px; margin: 0 3px; display: inline-flex; align-items: center; gap: 5px; }
        .btn-warning { background: #f39c12; border: none; color: white; }
        .btn-warning:hover { background: #e67e22; }
        .btn-danger { background: #e74c3c; border: none; color: white; }
        .btn-danger:hover { background: #c0392b; }
        
        /* WARNA TOMBOL TAMBAH MENU - TOSCA */
        .btn-tosca { 
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); 
            border: none; 
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-tosca:hover { 
            transform: translateY(-2px); 
            transition: 0.3s; 
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            color: white;
        }
        
        .action-buttons { display: flex; gap: 5px; align-items: center; justify-content: center; flex-wrap: nowrap; }
        
        .card { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header-custom { padding: 18px 22px; background: white; border-bottom: 2px solid #f0f2f5; font-weight: 600; font-size: 16px; }
        .card-header-custom i { color: #0d9488; margin-right: 8px; }
        .card-body { padding: 20px; }
        
        .top-bar { background: white; padding: 18px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; margin: 0; }
        .page-title i { color: #0d9488; margin-right: 10px; }
        
        .modal-header { border-bottom: 2px solid #f0f2f5; padding: 18px 22px; }
        .modal-body { padding: 22px; }
        .form-label { font-weight: 500; margin-bottom: 6px; font-size: 13px; color: #495057; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #ddd; padding: 10px 14px; font-size: 14px; }
        .form-control:focus, .form-select:focus { border-color: #0d9488; box-shadow: 0 0 0 0.2rem rgba(13,148,136,0.25); }
        
        .price-format { font-weight: bold; color: #27ae60; }
        .menu-image { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .menu-image-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 10px; margin-top: 10px; }
        
        @media (max-width: 768px) {
            .action-buttons { flex-direction: column; gap: 5px; }
            .btn-sm { width: 100%; }
        }
    </style>
</head>
<body>

<?php include 'inc/sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><i class="fas fa-utensils"></i> Kelola Menu Cateringku</h1>
        <!-- TOMBOL TAMBAH MENU WARNA TOSCA -->
        <button class="btn-tosca" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus"></i> Tambah Menu
        </button>
    </div>
    
    <div class="card">
        <div class="card-header-custom">
            <i class="fas fa-list"></i> Daftar Menu
            <span class="badge bg-secondary ms-2">Total: <?= count($menus) ?> Menu</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th>Gambar</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($menus)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                Belum ada data menu. Silakan tambah menu!
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $no=1; foreach($menus as $m): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td class="text-center">
                                    <?php if($m['image'] && file_exists('uploads/menus/' . $m['image'])): ?>
                                        <img src="uploads/menus/<?= $m['image'] ?>" class="menu-image" alt="<?= $m['name'] ?>">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 8px; margin: 0 auto;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= $m['category'] ?></span></td>
                                <td class="price-format"><?= rp($m['price']) ?></td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button class="btn btn-warning btn-sm" onclick="editMenu(
                                            <?= $m['id'] ?>,
                                            '<?= addslashes($m['name']) ?>',
                                            '<?= $m['category'] ?>',
                                            <?= $m['price'] ?>,
                                            '<?= $m['image'] ?>'
                                        )">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $m['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus menu <?= addslashes($m['name']) ?>?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle"></i> <strong>Informasi:</strong>
        <ul class="mb-0 mt-2">
            <li>🖼️ <strong>Gambar</strong> - Upload gambar menu (format: JPG, PNG, WEBP, GIF)</li>
            <li>✏️ <strong>Edit</strong> - Klik tombol Edit untuk mengubah nama, kategori, harga, atau gambar menu</li>
            <li>🗑️ <strong>Hapus</strong> - Menghapus menu dari sistem (termasuk gambar)</li>
        </ul>
    </div>
</div>

<!-- ========== MODAL TAMBAH MENU ========== -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-header" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Menu Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Nasi Box Ayam Goreng" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Paket Hemat">Paket Hemat</option>
                            <option value="Prasmanan">Prasmanan</option>
                            <option value="Snack">Snack</option>
                            <option value="Box">Box / Nasi Kotak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="price" id="priceInput" class="form-control" placeholder="Contoh: 25000" required onkeyup="formatRupiah(this)" onblur="formatRupiah(this)">
                            <span class="input-group-text">.00</span>
                        </div>
                        <small class="text-muted">Ketik angka (contoh: 25000) maka otomatis menjadi 25.000</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Menu</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewImage(this, 'previewTambah')">
                        <small class="text-muted">Format: JPG, PNG, WEBP, GIF. Maksimal 2MB.</small>
                        <div id="previewTambah" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border: none;">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL EDIT MENU ========== -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="old_image" id="edit_old_image">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="Paket Hemat">Paket Hemat</option>
                            <option value="Prasmanan">Prasmanan</option>
                            <option value="Snack">Snack</option>
                            <option value="Box">Box / Nasi Kotak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="price" id="edit_price" class="form-control" required onkeyup="formatRupiah(this)" onblur="formatRupiah(this)">
                            <span class="input-group-text">.00</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Menu</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewImage(this, 'previewEdit')">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
                        <div id="previewEdit" class="mt-2"></div>
                        <div id="currentImage" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-warning">Update Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function formatRupiah(element) {
        let value = element.value;
        let number = value.replace(/[^,\d]/g, '');
        number = number.replace(/\./g, '');
        let numeric = parseInt(number);
        if (isNaN(numeric)) {
            element.value = '';
            return;
        }
        element.value = numeric.toLocaleString('id-ID');
    }
    
    function previewImage(input, previewId) {
        let preview = document.getElementById(previewId);
        if(input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="menu-image-preview">';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.innerHTML = '';
        }
    }
    
    function formatNumberWithDots(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    function editMenu(id, name, category, price, image) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_price').value = formatNumberWithDots(price);
        document.getElementById('edit_old_image').value = image;
        
        let currentImageDiv = document.getElementById('currentImage');
        if(image) {
            currentImageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-image"></i> Gambar saat ini:<br><img src="uploads/menus/' + image + '" class="menu-image-preview mt-2"></div>';
        } else {
            currentImageDiv.innerHTML = '<div class="alert alert-secondary"><i class="fas fa-info-circle"></i> Belum ada gambar</div>';
        }
        
        document.getElementById('previewEdit').innerHTML = '';
        
        var modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
        modalEdit.show();
    }
</script>
</body>
</html>