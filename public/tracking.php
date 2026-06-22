<?php
include '../config/database.php';

$message = '';
$order = null;
$items = [];

if(isset($_POST['cek'])) {
    $invoice = $_POST['invoice'];
    
    $stmt = $db->prepare("SELECT o.*, c.name as customer_name, c.phone, c.address 
                          FROM orders o 
                          LEFT JOIN customers c ON o.customer_id = c.id 
                          WHERE o.invoice_no = ?");
    $stmt->execute([$invoice]);
    $order = $stmt->fetch();
    
    if($order) {
        $itemStmt = $db->prepare("SELECT menu_name, quantity, price, subtotal FROM order_items WHERE order_id = ?");
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
    } else {
        $message = "Maaf, pesanan dengan invoice <strong>$invoice</strong> tidak ditemukan.";
    }
}

function getStatusBadgeUser($status) {
    if($status == 'pending') {
        return '<span class="badge-status pending"><i class="fas fa-clock"></i> Pending</span>';
    } elseif($status == 'processing') {
        return '<span class="badge-status processing"><i class="fas fa-cog"></i> Diproses</span>';
    } elseif($status == 'completed') {
        return '<span class="badge-status completed"><i class="fas fa-check-circle"></i> Selesai</span>';
    } elseif($status == 'cancelled') {
        return '<span class="badge-status cancelled"><i class="fas fa-times-circle"></i> Dibatalkan</span>';
    } else {
        return '<span class="badge-status pending"><i class="fas fa-clock"></i> Pending</span>';
    }
}

function getPaymentBadgeUser($method) {
    if($method == 'cod') {
        return '<span class="badge-payment cod"><i class="fas fa-money-bill-wave"></i> COD (Bayar di Tempat)</span>';
    } elseif($method == 'transfer') {
        return '<span class="badge-payment transfer"><i class="fas fa-university"></i> Transfer Bank</span>';
    } else {
        return '<span class="badge-payment">' . $method . '</span>';
    }
}

function getProgressStep($status) {
    if($status == 'pending') {
        return 1;
    } elseif($status == 'processing') {
        return 2;
    } elseif($status == 'completed') {
        return 3;
    } elseif($status == 'cancelled') {
        return 0;
    } else {
        return 1;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cateringku - Lacak Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .navbar {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            padding: 15px 0;
        }
        .navbar-brand { color: white !important; font-weight: bold; font-size: 24px; }
        .navbar-brand i { margin-right: 10px; }
        
        .tracking-container {
            max-width: 900px;
            margin: 40px auto;
        }
        
        .search-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .result-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .badge-status {
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-status.pending { background: #f39c12; color: white; }
        .badge-status.processing { background: #3498db; color: white; }
        .badge-status.completed { background: #27ae60; color: white; }
        .badge-status.cancelled { background: #e74c3c; color: white; }
        
        .badge-payment {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .badge-payment.cod {
            background: #e8f4fd;
            color: #3498db;
            border: 1px solid #3498db;
        }
        .badge-payment.transfer {
            background: #fef9e7;
            color: #f39c12;
            border: 1px solid #f39c12;
        }
        
        .progress-step {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        .progress-step::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e0e0e0;
            z-index: 1;
        }
        .step {
            text-align: center;
            z-index: 2;
            background: white;
            flex: 1;
        }
        .step-icon {
            width: 50px;
            height: 50px;
            background: #e0e0e0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            transition: all 0.3s;
        }
        .step.active .step-icon {
            background: #0d9488;
            transform: scale(1.1);
        }
        .step.completed .step-icon {
            background: #27ae60;
        }
        .step.cancelled .step-icon {
            background: #e74c3c;
        }
        .step-label {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        .step.active .step-label {
            color: #0d9488;
            font-weight: bold;
        }
        .step.completed .step-label {
            color: #27ae60;
            font-weight: bold;
        }
        .step.cancelled .step-label {
            color: #e74c3c;
        }
        
        .invoice-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .items-list {
            list-style: none;
            padding: 0;
        }
        .items-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            border: none;
            padding: 12px 30px;
            font-weight: bold;
        }
        .btn-search:hover {
            transform: translateY(-2px);
            transition: 0.3s;
        }
        
        .btn-light-custom {
            background: white;
            color: #0d9488;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-light-custom:hover {
            background: #f0f2f5;
            color: #0d9488;
            transform: translateY(-2px);
            transition: 0.3s;
        }
        
        .btn-confirm {
            background: #f39c12;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }
        .btn-confirm:hover {
            background: #e67e22;
            color: white;
            transform: translateY(-2px);
            transition: 0.3s;
        }
        
        .btn-review {
            background: #ffc107;
            color: #856404;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-review:hover {
            background: #e0a800;
            color: white;
            transform: translateY(-2px);
            transition: 0.3s;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        .check-green {
            color: #27ae60;
            font-size: 16px;
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .step-icon { width: 40px; height: 40px; font-size: 16px; }
            .step-label { font-size: 10px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-utensils"></i> Cateringku</a>
        <div>
            <a href="index.php" class="btn-light-custom">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</nav>

<div class="tracking-container">
    <div class="search-card">
        <h4 class="text-center mb-4">
            <i class="fas fa-search"></i> Lacak Pesanan Anda
        </h4>
        <p class="text-center text-muted mb-4">
            Masukkan nomor invoice yang Anda terima saat memesan
        </p>
        
        <form method="POST">
            <div class="row g-2">
                <div class="col-10">
                    <input type="text" name="invoice" class="form-control form-control-lg" 
                           placeholder="Contoh: INV/20241220/123" required>
                </div>
                <div class="col-2">
                    <button type="submit" name="cek" class="btn btn-search btn-lg w-100">
                        <i class="fas fa-search"></i> Cek
                    </button>
                </div>
            </div>
        </form>
        
        <?php if($message): ?>
            <div class="alert alert-danger mt-4 text-center">
                <i class="fas fa-exclamation-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if($order): ?>
    <div class="result-card">
        <!-- Status Badge -->
        <div class="text-center mb-4">
            <?= getStatusBadgeUser($order['status']) ?>
        </div>
        
        <!-- Progress Step -->
        <div class="progress-step">
            <?php if($order['status'] == 'cancelled'): ?>
                <div class="step cancelled active" style="width: 100%;">
                    <div class="step-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="step-label">Pesanan Dibatalkan</div>
                </div>
            <?php else: ?>
                <div class="step <?= getProgressStep($order['status']) >= 1 ? 'completed' : '' ?> <?= getProgressStep($order['status']) == 1 ? 'active' : '' ?>">
                    <div class="step-icon"><i class="fas fa-receipt"></i></div>
                    <div class="step-label">
                        Pending
                        <?php if(getProgressStep($order['status']) >= 1): ?>
                            <i class="fas fa-check-circle check-green"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="step <?= getProgressStep($order['status']) >= 2 ? 'completed' : '' ?> <?= getProgressStep($order['status']) == 2 ? 'active' : '' ?>">
                    <div class="step-icon"><i class="fas fa-cog"></i></div>
                    <div class="step-label">
                        Diproses
                        <?php if(getProgressStep($order['status']) >= 2): ?>
                            <i class="fas fa-check-circle check-green"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="step <?= getProgressStep($order['status']) >= 3 ? 'completed' : '' ?>">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="step-label">
                        Selesai
                        <?php if(getProgressStep($order['status']) >= 3): ?>
                            <i class="fas fa-check-circle check-green"></i>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Informasi Invoice & Pembayaran -->
        <div class="invoice-info">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-receipt"></i> Invoice:</strong> <?= $order['invoice_no'] ?></p>
                    <p><strong><i class="fas fa-calendar"></i> Tanggal Pesan:</strong> <?= date('d/m/Y', strtotime($order['order_date'])) ?></p>
                    <p><strong><i class="fas fa-credit-card"></i> Metode Pembayaran:</strong> <?= getPaymentBadgeUser($order['payment_method'] ?? 'cod') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-truck"></i> Tanggal Kirim:</strong> <?= $order['delivery_date'] ? date('d/m/Y', strtotime($order['delivery_date'])) : 'Belum ditentukan' ?></p>
                    <p><strong><i class="fas fa-money-bill"></i> Total Bayar:</strong> <?= rp($order['total']) ?></p>
                    <p><strong><i class="fas fa-store"></i> Metode Pengiriman:</strong> 
                        <?= $order['delivery_fee'] > 0 ? '<span class="badge-payment cod"><i class="fas fa-truck"></i> Antar ke Rumah</span>' : '<span class="badge-payment transfer"><i class="fas fa-store"></i> Ambil di Tempat</span>' ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Informasi Pembayaran Transfer -->
        <?php if(($order['payment_method'] ?? 'cod') == 'transfer' && $order['payment_confirmed'] != 1): ?>
        <div class="alert alert-warning mt-3" style="background: #fef9e7; border: 1px solid #f39c12;">
            <i class="fas fa-info-circle"></i> <strong>Informasi Pembayaran Transfer:</strong><br>
            Silakan transfer ke rekening berikut:<br>
            <strong>Bank BCA</strong> - 1234 5678 9012 3456 a.n. Cateringku<br>
            <strong>Total: <?= rp($order['total']) ?></strong><br>
            <hr>
            <a href="confirm_payment.php?invoice=<?= $order['invoice_no'] ?>" class="btn-confirm">
                <i class="fas fa-upload"></i> Konfirmasi Pembayaran
            </a>
        </div>
        <?php elseif(($order['payment_method'] ?? 'cod') == 'transfer' && $order['payment_confirmed'] == 1): ?>
        <div class="alert alert-success mt-3">
            <i class="fas fa-check-circle"></i> <strong>Pembayaran sudah dikonfirmasi!</strong><br>
            Terima kasih. Pesanan Anda akan segera diproses.
        </div>
        <?php endif; ?>
        
        <?php if(($order['payment_method'] ?? 'cod') == 'cod'): ?>
        <div class="alert alert-info mt-3" style="background: #e8f4fd; border: 1px solid #3498db;">
            <i class="fas fa-info-circle"></i> <strong>Informasi Pembayaran COD:</strong><br>
            Pembayaran dilakukan secara tunai saat pesanan sampai di tempat Anda.
        </div>
        <?php endif; ?>
        
        <!-- Detail Menu -->
        <h6 class="mb-3"><i class="fas fa-utensils"></i> Detail Pesanan</h6>
        <ul class="items-list">
            <?php foreach($items as $item): ?>
            <li>
                <span><?= htmlspecialchars($item['menu_name']) ?> <strong>x<?= $item['quantity'] ?></strong></span>
                <span class="text-success"><?= rp($item['subtotal']) ?></span>
            </li>
            <?php endforeach; ?>
            <li class="border-top pt-2 mt-2">
                <strong>Subtotal</strong>
                <strong><?= rp($order['total'] - $order['delivery_fee']) ?></strong>
            </li>
            <li>
                <span>Ongkos Kirim</span>
                <span><?= rp($order['delivery_fee']) ?></span>
            </li>
            <li class="border-top pt-2">
                <strong>TOTAL</strong>
                <strong class="text-success"><?= rp($order['total']) ?></strong>
            </li>
        </ul>
        
        <!-- Informasi Pelanggan -->
        <div class="mt-4 p-3" style="background: #e8f4fd; border-radius: 12px;">
            <h6 class="mb-2"><i class="fas fa-user"></i> Informasi Pelanggan</h6>
            <p class="mb-1"><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
            <p class="mb-1"><i class="fas fa-phone"></i> <?= $order['phone'] ?? '-' ?></p>
            <p class="mb-0"><i class="fas fa-map-marker-alt"></i> <?= $order['address'] ?? '-' ?></p>
        </div>
        
        <!-- Catatan -->
        <?php if($order['notes']): ?>
        <div class="mt-3 p-3" style="background: #fff3e0; border-radius: 12px;">
            <h6 class="mb-1"><i class="fas fa-pen"></i> Catatan Pesanan</h6>
            <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
        </div>
        <?php endif; ?>
        
        <!-- ========== TOMBOL ULASAN & RATING ========== -->
        <?php if($order['status'] == 'completed'): ?>
            <?php
            // Cek apakah sudah pernah review
            $cekReview = $db->prepare("SELECT id FROM reviews WHERE order_id = ?");
            $cekReview->execute([$order['id']]);
            $sudahReview = $cekReview->fetch();
            ?>
            <?php if(!$sudahReview): ?>
                <div class="alert alert-warning mt-3 text-center" style="background: #fff3cd; border: 1px solid #ffc107;">
                    <i class="fas fa-star"></i> <strong>Pesanan Anda sudah selesai!</strong><br>
                    Bagikan pengalaman Anda dengan memberi rating dan ulasan.
                    <hr>
                    <a href="review.php?invoice=<?= $order['invoice_no'] ?>" class="btn-review">
                        <i class="fas fa-star"></i> Beri Ulasan & Rating
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-success mt-3 text-center">
                    <i class="fas fa-check-circle"></i> <strong>Terima kasih!</strong><br>
                    Anda sudah memberi ulasan untuk pesanan ini.
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Status Info -->
        <div class="alert alert-info mt-4 mb-0">
            <i class="fas fa-info-circle"></i> 
            <?php if($order['status'] == 'pending'): ?>
                Pesanan Anda sedang <strong>Pending</strong>. Kami akan segera memproses pesanan Anda.
            <?php elseif($order['status'] == 'processing'): ?>
                Pesanan Anda sedang <strong>Diproses</strong> oleh tim dapur. Kami akan segera mengirimkan pesanan Anda.
            <?php elseif($order['status'] == 'completed'): ?>
                🎉 <strong>Selamat! Pesanan Anda telah Selesai</strong> 🎉<br>
                Pesanan Anda sudah dikirim. Terima kasih telah memesan di Cateringku!
            <?php elseif($order['status'] == 'cancelled'): ?>
                Pesanan Anda telah <strong>Dibatalkan</strong>. Silakan hubungi admin untuk informasi lebih lanjut.
            <?php else: ?>
                Status pesanan Anda: <strong><?= $order['status'] ?></strong>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Cateringku. All rights reserved.</p>
        <p class="small">Sistem Pemesanan Catering Online</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>