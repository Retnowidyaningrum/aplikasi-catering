<?php
session_start();
if(!isset($_SESSION['login'])) header('Location: index.php');
include 'config/database.php';

if(isset($_POST['tambah'])) {
    if (!verifyCsrfToken()) { die('Token CSRF tidak valid!'); }
    $db->prepare("INSERT INTO customers (name,phone,email,address) VALUES (?,?,?,?)")->execute([$_POST['name'],$_POST['phone'],$_POST['email'],$_POST['address']]);
    echo "<script>alert('Pelanggan ditambahkan!'); location.href='customer.php';</script>";
}

if(isset($_POST['edit'])) {
    if (!verifyCsrfToken()) { die('Token CSRF tidak valid!'); }
    $db->prepare("UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=?")->execute([$_POST['name'],$_POST['phone'],$_POST['email'],$_POST['address'],$_POST['id']]);
    echo "<script>alert('Pelanggan diupdate!'); location.href='customer.php';</script>";
}

if(isset($_GET['hapus'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) { die('Token CSRF tidak valid!'); }
    $db->prepare("DELETE FROM customers WHERE id=?")->execute([$_GET['hapus']]);
    echo "<script>alert('Pelanggan dihapus!'); location.href='customer.php';</script>";
}

$customers = $db->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title> Cateringku - Kelola Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* TABEL */
        .table th, .table td { vertical-align: middle; padding: 12px; }
        .table th { background: #f8f9fa; font-weight: 600; white-space: nowrap; }
        
        /* BUTTON - UKURAN SAMA */
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 6px; margin: 0 3px; display: inline-flex; align-items: center; gap: 5px; }
        .btn-warning { background: #f39c12; border: none; color: white; }
        .btn-warning:hover { background: #e67e22; }
        .btn-danger { background: #e74c3c; border: none; color: white; }
        .btn-danger:hover { background: #c0392b; }
        
        /* TOMBOL TAMBAH PELANGGAN - WARNA TOSCA (SAMA SEPERTI TAMBAH MENU) */
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
        
        /* ACTION BUTTONS - SEJAJAR */
        .action-buttons { display: flex; gap: 5px; align-items: center; justify-content: center; flex-wrap: nowrap; }
        
        /* CARD */
        .card { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header-custom { padding: 18px 22px; background: white; border-bottom: 2px solid #f0f2f5; font-weight: 600; font-size: 16px; }
        .card-header-custom i { color: #0d9488; margin-right: 8px; }
        .card-body { padding: 20px; }
        
        /* TOP BAR */
        .top-bar { background: white; padding: 18px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; margin: 0; }
        .page-title i { color: #0d9488; margin-right: 10px; }
        
        /* MODAL */
        .modal-header { border-bottom: 2px solid #f0f2f5; padding: 18px 22px; }
        .modal-body { padding: 22px; }
        .form-label { font-weight: 500; margin-bottom: 6px; font-size: 13px; color: #495057; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #ddd; padding: 10px 14px; font-size: 14px; }
        .form-control:focus, .form-select:focus { border-color: #0d9488; box-shadow: 0 0 0 0.2rem rgba(13,148,136,0.25); }
        
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
        <h1 class="page-title"><i class="fas fa-users"></i> Kelola Pelanggan</h1>
        <!-- TOMBOL TAMBAH PELANGGAN WARNA TOSCA -->
        <button class="btn-tosca" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus"></i> Tambah Pelanggan
        </button>
    </div>
    
    <div class="card">
        <div class="card-header-custom">
            <i class="fas fa-list"></i> Daftar Pelanggan
            <span class="badge bg-secondary ms-2">Total: <?= count($customers) ?> Pelanggan</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($customers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                Belum ada data pelanggan. Silakan tambah pelanggan!
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $no=1; foreach($customers as $c): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['phone']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td style="max-width: 200px; word-wrap: break-word;"><?= htmlspecialchars($c['address']) ?></td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button class="btn btn-warning btn-sm" onclick="editCustomer(
                                            <?= $c['id'] ?>,
                                            '<?= addslashes($c['name']) ?>',
                                            '<?= addslashes($c['phone']) ?>',
                                            '<?= addslashes($c['email']) ?>',
                                            '<?= addslashes($c['address']) ?>'
                                        )">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $c['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pelanggan <?= addslashes($c['name']) ?>?')">
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
    
    <!-- Informasi -->
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle"></i> <strong>Informasi:</strong>
        <ul class="mb-0 mt-2">
            <li>✏️ <strong>Edit</strong> - Klik tombol Edit untuk mengubah data pelanggan</li>
            <li>🗑️ <strong>Hapus</strong> - Menghapus data pelanggan dari sistem</li>
            <li>➕ <strong>Tambah Pelanggan</strong> - Menambahkan pelanggan baru</li>
        </ul>
    </div>
</div>

<!-- Modal Tambah Pelanggan -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <div class="modal-header" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah Pelanggan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border: none;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editCustomer(id, name, phone, email, address) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_address').value = address;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>