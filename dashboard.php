<?php
session_start();
if(!isset($_SESSION['login'])) header('Location: index.php');
include 'config/database.php';

$total_menu = $db->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$total_customer = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_order = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$orders = $db->query("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.id DESC LIMIT 5")->fetchAll();

// Fungsi untuk mendapatkan badge status dalam Bahasa Indonesia
function getStatusBadge($status) {
    if($status == 'pending') {
        return '<span class="badge-pending"><i class="fas fa-clock"></i> Pending</span>';
    } elseif($status == 'processing') {
        return '<span class="badge-processing"><i class="fas fa-cog"></i> Diproses</span>';
    } elseif($status == 'completed') {
        return '<span class="badge-completed"><i class="fas fa-check"></i> Selesai</span>';
    } else {
        return '<span class="badge-cancelled"><i class="fas fa-times"></i> Dibatalkan</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sistem Pemesanan Cateringku - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* BADGES STATUS */
        .badge-pending { background: #f39c12; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .badge-processing { background: #3498db; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .badge-completed { background: #27ae60; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .badge-cancelled { background: #e74c3c; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        
        /* STATS */
        .stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 20px; }
        .stat-box { background: white; padding: 20px; border-radius: 15px; text-align: center; border-left: 4px solid; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-box h3 { font-size: 28px; font-weight: bold; margin: 0; }
        
        /* WELCOME BANNER - WARNA BARU BIRU TOSCA */
        .welcome-banner { 
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); 
            color: white; 
            border-radius: 15px; 
            padding: 20px; 
            margin-bottom: 20px; 
        }
        
        /* TABEL */
        .table th, .table td { vertical-align: middle; padding: 12px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        
        /* CARD */
        .card { background: white; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
        .card-header-custom { background: white; padding: 15px 20px; border-bottom: 2px solid #f0f0f0; font-weight: 600; }
        .card-header-custom i { color: #0d9488; margin-right: 8px; }
        .card-body { padding: 20px; }
        
        /* TOP BAR */
        .top-bar { background: white; padding: 15px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .page-title { font-size: 1.5rem; font-weight: 600; color: #1a1a2e; margin: 0; }
        .page-title i { color: #0d9488; margin-right: 10px; }
    </style>
</head>
<body>

<?php include 'inc/sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><i class="fas fa-home"></i> Dashboard</h1>
        <div><i class="fas fa-user-circle"></i> <?= $_SESSION['user'] ?? 'Admin' ?></div>
    </div>
    
    <div class="welcome-banner">
        <h4><i class="fas fa-check-circle"></i> Selamat Datang di Cateringku</h4>
        <p>Sistem ini membantu Anda mengelola pemesanan catering dengan mudah. Data tersimpan di database dan tidak akan hilang.</p>
    </div>
    
    <div class="stats">
        <div class="stat-box" style="border-left-color: #3498db;"><h3><?= $total_order ?></h3><p>Total Pesanan</p></div>
        <div class="stat-box" style="border-left-color: #27ae60;"><h3><?= $total_menu ?></h3><p>Total Menu</p></div>
        <div class="stat-box" style="border-left-color: #f39c12;"><h3><?= $total_customer ?></h3><p>Total Pelanggan</p></div>
        <div class="stat-box" style="border-left-color: #e74c3c;"><h3><?= $pending ?></h3><p>Pending</p></div>
    </div>
    
    <div class="card">
        <div class="card-header-custom"><i class="fas fa-history"></i> Pesanan Terbaru</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $o): ?>
                        <tr>
                            <td><strong><?= $o['invoice_no'] ?></strong></td>
                            <td><?= htmlspecialchars($o['customer_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($o['order_date'])) ?></td>
                            <td class="text-success fw-bold"><?= rp($o['total']) ?></td>
                            <td><?= getStatusBadge($o['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada pesanan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>