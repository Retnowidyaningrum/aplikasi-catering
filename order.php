<?php
session_start();
if(!isset($_SESSION['login'])) header('Location: index.php');
include 'config/database.php';

// Update status
if(isset($_GET['update'])) {
    $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$_GET['status'], $_GET['id']]);
    echo "<script>alert('Status pesanan berhasil diupdate!'); location.href='order.php';</script>";
}

// HAPUS PESANAN
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);
    echo "<script>alert('Pesanan berhasil dihapus!'); location.href='order.php';</script>";
}

// Ambil semua pesanan
$orders = $db->query("SELECT o.*, c.name as customer_name, c.phone, c.address 
                      FROM orders o 
                      LEFT JOIN customers c ON o.customer_id = c.id 
                      ORDER BY o.id DESC")->fetchAll();

foreach($orders as &$order) {
    $items = $db->prepare("SELECT menu_name, quantity, price, subtotal FROM order_items WHERE order_id = ?");
    $items->execute([$order['id']]);
    $order['items'] = $items->fetchAll();
}

function getPaymentBadge($method) {
    if($method == 'cod') {
        return '<span class="badge-payment cod"><i class="fas fa-money-bill-wave"></i> COD</span>';
    } elseif($method == 'transfer') {
        return '<span class="badge-payment transfer"><i class="fas fa-university"></i> Transfer</span>';
    } else {
        return '<span class="badge-payment">' . $method . '</span>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cateringku - Daftar Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .badge-pending { background: #f39c12; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .badge-processing { background: #3498db; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .badge-completed { background: #27ae60; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .badge-cancelled { background: #e74c3c; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; display: inline-block; }
        
        .badge-payment {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
        }
        .badge-payment.cod { background: #e8f4fd; color: #3498db; border: 1px solid #3498db; }
        .badge-payment.transfer { background: #fef9e7; color: #f39c12; border: 1px solid #f39c12; }
        
        .badge-proof {
            background: #17a2b8;
            color: white;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
            display: inline-block;
        }
        
        .order-row { cursor: pointer; transition: background 0.2s; }
        .order-row:hover { background: #f8f9fa; }
        
        .detail-row { background: #f8f9fa; display: none; }
        .detail-row.show { display: table-row; }
        .detail-content { padding: 15px; }
        .detail-card { background: white; border-radius: 10px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .detail-title { font-weight: 600; font-size: 12px; margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px solid #e9ecef; }
        .detail-title i { color: #0d9488; margin-right: 5px; }
        .items-list { list-style: none; padding: 0; margin: 0; }
        .items-list li { padding: 5px 0; border-bottom: 1px solid #eef2f6; display: flex; justify-content: space-between; font-size: 12px; }
        .items-list li:last-child { border-bottom: none; }
        .customer-info { background: #e8f4fd; border-radius: 10px; padding: 10px; margin-top: 5px; }
        .customer-info p { margin: 0; font-size: 12px; }
        .customer-info i { width: 18px; }
        
        .action-buttons { display: flex; gap: 5px; align-items: center; justify-content: center; }
        .btn-action { padding: 4px 8px; font-size: 11px; border-radius: 5px; width: 28px; text-align: center; }
        .btn-warning { background: #f39c12; border: none; color: white; }
        .btn-danger { background: #e74c3c; border: none; color: white; }
        
        .table { font-size: 13px; width: 100%; margin-bottom: 0; }
        .table th, .table td { padding: 10px 8px; vertical-align: middle; }
        .table th { background: #f8f9fa; font-weight: 600; white-space: nowrap; }
        .table td { border-bottom: 1px solid #e9ecef; }
        
        .proof-img { max-width: 100%; max-height: 150px; border-radius: 8px; cursor: pointer; }
        
        .rating-stars {
            display: inline-block;
            unicode-bidi: bidi-override;
            direction: rtl;
        }
        .rating-stars i {
            font-size: 16px;
            margin-right: 2px;
        }
        .rating-stars i.text-warning { color: #ffc107; }
        .rating-stars i.text-muted { color: #ddd; }
        
        .top-bar {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .page-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a1a2e;
            margin: 0;
        }
        .page-title i {
            color: #0d9488;
            margin-right: 10px;
        }
        .card-header-custom {
            background: white;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .table th, .table td { padding: 6px 4px; font-size: 11px; }
            .badge-payment { font-size: 9px; padding: 2px 6px; }
            .btn-action { padding: 3px 5px; width: 24px; font-size: 10px; }
        }
    </style>
</head>
<body>

<?php include 'inc/sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Daftar Pesanan</h1>
    </div>
    
    <div class="card">
        <div class="card-header-custom">
            <i class="fas fa-list"></i> Semua Pesanan
            <span class="badge bg-secondary ms-2"><?= count($orders) ?> Pesanan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Tgl Kirim</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Pembayaran</th>
                            <th class="text-center">Status</th>
                            <th width="70px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada pesanan.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $no=1; foreach($orders as $o): ?>
                            <tr class="order-row" onclick="toggleDetail(<?= $o['id'] ?>)">
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td><strong><?= $o['invoice_no'] ?></strong></td>
                                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                                <td class="text-center">
                                    <?= $o['delivery_date'] ? date('d/m/y', strtotime($o['delivery_date'])) : '-' ?>
                                </td>
                                <td class="text-end text-success fw-bold"><?= rp($o['total']) ?></td>
                                <td class="text-center">
                                    <?= getPaymentBadge($o['payment_method'] ?? 'cod') ?>
                                    <?php if(($o['payment_method'] ?? 'cod') == 'transfer' && !empty($o['payment_proof'])): ?>
                                        <span class="badge-proof">
                                            <i class="fas fa-image"></i> Bukti
                                        </span>
                                    <?php endif; ?>
                                 </span>
                                <td class="text-center">
                                    <?php if($o['status'] == 'pending'): ?>
                                        <span class="badge-pending">Pending</span>
                                    <?php elseif($o['status'] == 'processing'): ?>
                                        <span class="badge-processing">Diproses</span>
                                    <?php elseif($o['status'] == 'completed'): ?>
                                        <span class="badge-completed">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge-cancelled">Dibatalkan</span>
                                    <?php endif; ?>
                                 </span>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button class="btn-action btn-warning" onclick="editStatus(<?= $o['id'] ?>, '<?= $o['status'] ?>')" title="Edit Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?hapus=<?= $o['id'] ?>" class="btn-action btn-danger" onclick="return confirm('Hapus pesanan?')" title="Hapus Pesanan">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                 </span>
                             </tr>
                            <!-- Detail Row -->
                            <tr id="detail-<?= $o['id'] ?>" class="detail-row">
                                <td colspan="8">
                                    <div class="detail-content">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="detail-card">
                                                    <div class="detail-title"><i class="fas fa-receipt"></i> Detail Menu</div>
                                                    <?php if(!empty($o['items'])): ?>
                                                        <ul class="items-list">
                                                            <?php foreach($o['items'] as $item): ?>
                                                            <li>
                                                                <span><?= htmlspecialchars($item['menu_name']) ?> <strong>x<?= $item['quantity'] ?></strong></span>
                                                                <span><?= rp($item['subtotal']) ?></span>
                                                            </li>
                                                            <?php endforeach; ?>
                                                            <li class="border-top pt-1 mt-1">
                                                                <span class="fw-bold">Subtotal</span>
                                                                <span><?= rp($o['total'] - $o['delivery_fee']) ?></span>
                                                            </li>
                                                            <li>
                                                                <span>Ongkir</span>
                                                                <span><?= rp($o['delivery_fee']) ?></span>
                                                            </li>
                                                            <li class="border-top pt-1">
                                                                <span class="fw-bold">TOTAL</span>
                                                                <span class="text-success fw-bold"><?= rp($o['total']) ?></span>
                                                            </li>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p class="text-muted">Tidak ada detail item</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="detail-card">
                                                    <div class="detail-title"><i class="fas fa-user"></i> Pelanggan</div>
                                                    <div class="customer-info">
                                                        <p><i class="fas fa-user"></i> <strong><?= htmlspecialchars($o['customer_name']) ?></strong></p>
                                                        <p><i class="fas fa-phone"></i> <?= $o['phone'] ?? '-' ?></p>
                                                        <p><i class="fas fa-map-marker-alt"></i> <?= $o['address'] ?? '-' ?></p>
                                                    </div>
                                                </div>
                                                <div class="detail-card">
                                                    <div class="detail-title"><i class="fas fa-calendar"></i> Jadwal</div>
                                                    <div class="summary-row d-flex justify-content-between">
                                                        <span>Tgl Pesan:</span>
                                                        <span class="fw-bold"><?= date('d/m/Y', strtotime($o['order_date'])) ?></span>
                                                    </div>
                                                    <div class="summary-row d-flex justify-content-between">
                                                        <span>Tgl Kirim:</span>
                                                        <span class="fw-bold"><?= $o['delivery_date'] ? date('d/m/Y', strtotime($o['delivery_date'])) : '-' ?></span>
                                                    </div>
                                                    <div class="summary-row d-flex justify-content-between">
                                                        <span>Ongkir:</span>
                                                        <span class="fw-bold"><?= rp($o['delivery_fee']) ?></span>
                                                    </div>
                                                </div>
                                                <?php if(!empty($o['notes'])): ?>
                                                <div class="detail-card">
                                                    <div class="detail-title"><i class="fas fa-pen"></i> Catatan</div>
                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($o['notes'])) ?></p>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- ========== BUKTI TRANSFER ========== -->
                                                <?php if(($o['payment_method'] ?? 'cod') == 'transfer' && !empty($o['payment_proof'])): ?>
                                                <div class="detail-card">
                                                    <div class="detail-title"><i class="fas fa-image"></i> Bukti Transfer</div>
                                                    <?php 
                                                    $proofPath = 'uploads/payments/' . $o['payment_proof'];
                                                    if(file_exists(__DIR__ . '/' . $proofPath)):
                                                    ?>
                                                        <a href="<?= $proofPath ?>" target="_blank">
                                                            <img src="<?= $proofPath ?>" class="proof-img">
                                                        </a>
                                                        <div class="mt-2">
                                                            <span class="badge <?= $o['payment_confirmed'] ? 'bg-success' : 'bg-warning' ?>">
                                                                <?= $o['payment_confirmed'] ? '✓ Dikonfirmasi' : '⏳ Belum Dikonfirmasi' ?>
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-danger">File tidak ditemukan</div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- ========== ULASAN & RATING ========== -->
                                                <?php
                                                $reviewStmt = $db->prepare("SELECT rating, comment, created_at FROM reviews WHERE order_id = ?");
                                                $reviewStmt->execute([$o['id']]);
                                                $review = $reviewStmt->fetch();
                                                if($review):
                                                ?>
                                                <div class="detail-card">
                                                    <div class="detail-title"><i class="fas fa-star"></i> Ulasan Pelanggan</div>
                                                    <div class="mb-2">
                                                        <?php for($i=1; $i<=5; $i++): ?>
                                                            <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                        <?php endfor; ?>
                                                        <span class="ms-2">(<?= $review['rating'] ?>/5)</span>
                                                    </div>
                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                 </span>
                             </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info mt-3" style="font-size: 12px;">
        <i class="fas fa-info-circle"></i> <strong>Informasi:</strong>
        Klik pada baris pesanan untuk melihat detail menu, ongkir, bukti transfer, dan ulasan pelanggan.
    </div>
</div>

<!-- Modal Edit Status -->
<div class="modal fade" id="modalEditStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="#" class="btn btn-sm btn-warning btn-status" data-status="pending">Pending</a>
                    <a href="#" class="btn btn-sm btn-info btn-status" data-status="processing">Diproses</a>
                    <a href="#" class="btn btn-sm btn-success btn-status" data-status="completed">Selesai</a>
                    <a href="#" class="btn btn-sm btn-danger btn-status" data-status="cancelled">Batalkan</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentOrderId = null;

function toggleDetail(orderId) {
    var detailRow = document.getElementById('detail-' + orderId);
    if(detailRow.classList.contains('show')) {
        detailRow.classList.remove('show');
    } else {
        document.querySelectorAll('.detail-row.show').forEach(function(row) {
            row.classList.remove('show');
        });
        detailRow.classList.add('show');
    }
}

function editStatus(orderId, currentStatus) {
    currentOrderId = orderId;
    var modal = new bootstrap.Modal(document.getElementById('modalEditStatus'));
    modal.show();
}

document.querySelectorAll('.btn-status').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var newStatus = this.getAttribute('data-status');
        if(currentOrderId) {
            window.location.href = '?update&id=' + currentOrderId + '&status=' + newStatus;
        }
    });
});
</script>
</body>
</html>