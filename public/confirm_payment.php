<?php
include '../config/database.php';

$message = '';
$order = null;

if(isset($_GET['invoice'])) {
    $invoice = $_GET['invoice'];
    $stmt = $db->prepare("SELECT * FROM orders WHERE invoice_no = ?");
    $stmt->execute([$invoice]);
    $order = $stmt->fetch();
}

if(isset($_POST['confirm'])) {
    if (!verifyCsrfToken()) { die('Token CSRF tidak valid!'); }
    
    $invoice = $_POST['invoice'];
    
    // Buat folder jika belum ada
    $uploadDir = '../uploads/payments/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if(isset($_FILES['proof']) && $_FILES['proof']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if(!in_array($ext, $allowed)) {
            $message = "Format file tidak didukung! (JPG, PNG, WEBP, GIF)";
        } elseif($_FILES['proof']['size'] > $maxSize) {
            $message = "Ukuran file maksimal 2MB!";
        } else {
            $newName = time() . '_' . rand(1000,9999) . '.' . $ext;
            $uploadPath = $uploadDir . $newName;
            
            if(move_uploaded_file($_FILES['proof']['tmp_name'], $uploadPath)) {
                $update = $db->prepare("UPDATE orders SET payment_proof = ?, payment_confirmed = 1, payment_confirmed_at = NOW() WHERE invoice_no = ?");
                $update->execute([$newName, $invoice]);
                $message = "Bukti transfer berhasil dikirim!";
                
                // Redirect ke tracking
                header("Location: tracking.php?invoice=" . $invoice);
                exit();
            } else {
                $message = "Gagal upload file!";
            }
        }
    } else {
        $message = "Pilih file terlebih dahulu!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container" style="max-width: 500px; margin-top: 50px;">
    <div class="card">
        <div class="card-header bg-primary text-white">Konfirmasi Pembayaran</div>
        <div class="card-body">
            <?php if(isset($_GET['invoice']) && !$order): ?>
                <div class="alert alert-danger">Invoice tidak ditemukan!</div>
                <a href="tracking.php" class="btn btn-primary">Kembali</a>
            <?php elseif($order && $order['payment_confirmed'] == 1): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Pembayaran sudah dikonfirmasi!
                </div>
                <a href="tracking.php?invoice=<?= $order['invoice_no'] ?>" class="btn btn-primary">Lacak Pesanan</a>
            <?php elseif($order): ?>
                <div class="alert alert-info">
                    <strong>Invoice:</strong> <?= $order['invoice_no'] ?><br>
                    <strong>Total:</strong> <?= rp($order['total']) ?><br>
                    <strong>Bank BCA:</strong> 1234 5678 9012 3456 a.n. Cateringku
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="invoice" value="<?= $order['invoice_no'] ?>">
                    <div class="mb-3">
                        <label>Upload Bukti Transfer</label>
                        <input type="file" name="proof" class="form-control" required accept="image/jpeg,image/png,image/webp,image/gif">
                        <small class="text-muted">Format: JPG, PNG, WEBP, GIF. Maksimal 2MB.</small>
                    </div>
                    <button type="submit" name="confirm" class="btn btn-success w-100">Kirim</button>
                </form>
                <?php if($message): ?>
                    <div class="alert alert-danger mt-3"><?= $message ?></div>
                <?php endif; ?>
            <?php else: ?>
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="invoice" class="form-control" placeholder="Masukkan Invoice" required>
                        <button type="submit" class="btn btn-primary">Cek</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>