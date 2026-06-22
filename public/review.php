<?php
include '../config/database.php';

$message = '';
$order = null;
$items = [];
$sudah_review = false;
$error = '';

if(isset($_GET['invoice'])) {
    $invoice = $_GET['invoice'];
    
    $stmt = $db->prepare("SELECT o.*, c.name as customer_name, c.id as customer_id 
                          FROM orders o 
                          LEFT JOIN customers c ON o.customer_id = c.id 
                          WHERE o.invoice_no = ?");
    $stmt->execute([$invoice]);
    $order = $stmt->fetch();
    
    if($order) {
        // Cek apakah sudah review
        $cekReview = $db->prepare("SELECT id FROM reviews WHERE order_id = ?");
        $cekReview->execute([$order['id']]);
        if($cekReview->fetch()) {
            $sudah_review = true;
        }
        
        // Ambil detail menu
        $itemStmt = $db->prepare("SELECT menu_name, quantity, price, subtotal FROM order_items WHERE order_id = ?");
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
    } else {
        $message = "Invoice tidak ditemukan!";
    }
}

if(isset($_POST['submit_review']) && $order && !$sudah_review) {
    if (!verifyCsrfToken()) { die('Token CSRF tidak valid!'); }
    
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if(empty($comment)) {
        $error = "Ulasan tidak boleh kosong!";
    } else {
        // Simpan review tanpa menu_id dulu
        $insert = $db->prepare("INSERT INTO reviews (order_id, customer_id, rating, comment) VALUES (?,?,?,?)");
        $insert->execute([$order['id'], $order['customer_id'], $rating, $comment]);
        
        // Update status sudah_review di orders
        $update = $db->prepare("UPDATE orders SET sudah_review = 1 WHERE id = ?");
        $update->execute([$order['id']]);
        
        $success = true;
        $message = "Terima kasih! Ulasan Anda telah disimpan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Ulasan - Cateringku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        
        .navbar {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            padding: 12px 0;
        }
        .navbar-brand { color: white !important; font-weight: bold; font-size: 20px; }
        .navbar-brand i { margin-right: 8px; }
        
        .container-custom { max-width: 700px; margin: 40px auto; }
        .card { border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: white; border-bottom: 2px solid #f0f2f5; font-weight: 600; padding: 15px 20px; }
        .card-header i { color: #0d9488; margin-right: 8px; }
        .card-body { padding: 25px; }
        
        .rating-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .rating input {
            display: none;
        }
        .rating label {
            font-size: 40px;
            color: #ddd;
            cursor: pointer;
            transition: 0.2s;
        }
        .rating label:hover,
        .rating label:hover ~ label,
        .rating input:checked ~ label {
            color: #ffc107;
        }
        .rating-label {
            font-size: 14px;
            color: #666;
            display: block;
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .order-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .item-list {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .item-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
        }
        .item-list li:last-child {
            border-bottom: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); }
        
        textarea.form-control {
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 14px;
        }
        
        @media (max-width: 576px) {
            .rating label { font-size: 30px; }
            .card-body { padding: 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-utensils"></i> Cateringku</a>
        <a href="tracking.php" class="btn btn-light btn-sm"><i class="fas fa-search"></i> Lacak Pesanan</a>
    </div>
</nav>

<div class="container-custom">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-star"></i> Beri Ulasan & Rating
        </div>
        <div class="card-body">
            
            <?php if(isset($_GET['invoice']) && !$order): ?>
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                    <?= $message ?>
                </div>
                <div class="text-center">
                    <a href="tracking.php" class="btn btn-secondary">Kembali ke Tracking</a>
                </div>
                
            <?php elseif($order && $sudah_review): ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>Terima kasih!</h5>
                    <p>Anda sudah memberikan ulasan untuk pesanan ini.</p>
                    <hr>
                    <a href="tracking.php?invoice=<?= $order['invoice_no'] ?>" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lihat Tracking Pesanan
                    </a>
                </div>
                
            <?php elseif(isset($success)): ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>Ulasan berhasil disimpan!</h5>
                    <p><?= $message ?></p>
                    <hr>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Kembali ke Beranda</a>
                        <a href="tracking.php?invoice=<?= $order['invoice_no'] ?>" class="btn btn-info text-white"><i class="fas fa-search"></i> Lihat Tracking</a>
                    </div>
                </div>
                
            <?php elseif($order && $order['status'] == 'completed'): ?>
                <div class="order-info">
                    <p><strong><i class="fas fa-receipt"></i> Invoice:</strong> <?= $order['invoice_no'] ?></p>
                    <p><strong><i class="fas fa-calendar"></i> Tanggal Pesan:</strong> <?= date('d/m/Y', strtotime($order['order_date'])) ?></p>
                    <p><strong><i class="fas fa-money-bill"></i> Total:</strong> <?= rp($order['total']) ?></p>
                </div>
                
                <div class="item-list">
                    <h6 class="mb-2"><i class="fas fa-utensils"></i> Pesanan Anda:</h6>
                    <?php foreach($items as $item): ?>
                    <li>
                        <span><?= htmlspecialchars($item['menu_name']) ?> <strong>x<?= $item['quantity'] ?></strong></span>
                        <span><?= rp($item['subtotal']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-success text-center mb-4">
                    <i class="fas fa-check-circle"></i> Pesanan Anda sudah selesai!<br>
                    <small>Bagikan pengalaman Anda menikmati pesanan dari Cateringku</small>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    
                    <div class="rating-container">
                        <label class="form-label fw-bold mb-2">Rating Anda</label>
                        <div class="rating">
                            <input type="radio" name="rating" value="5" id="star5" required><label for="star5">★</label>
                            <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                            <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                            <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                            <input type="radio" name="rating" value="1" id="star1"><label for="star1">★</label>
                        </div>
                        <span class="rating-label">Klik bintang untuk memberi rating</span>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="fas fa-pen"></i> Ulasan Anda</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Tulis pengalaman Anda menikmati pesanan dari Cateringku..." required></textarea>
                        <small class="text-muted">*Ulasan Anda akan membantu pelanggan lain memilih menu</small>
                    </div>
                    
                    <button type="submit" name="submit_review" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Kirim Ulasan
                    </button>
                </form>
                
            <?php elseif($order && $order['status'] != 'completed'): ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>Pesanan Belum Selesai</h5>
                    <p>Anda hanya bisa memberi ulasan setelah pesanan <strong>selesai</strong>.</p>
                    <a href="tracking.php?invoice=<?= $order['invoice_no'] ?>" class="btn btn-primary">Lacak Pesanan</a>
                </div>
                
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-star fa-3x text-warning mb-3 d-block"></i>
                    <h5 class="mb-3">Cek Pesanan Anda</h5>
                    <p class="text-muted mb-4">Masukkan nomor invoice untuk memberi ulasan dan rating</p>
                    
                    <form method="GET" class="mt-2">
                        <div class="input-group">
                            <input type="text" name="invoice" class="form-control" placeholder="Contoh: INV/20250417/123" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cek Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>