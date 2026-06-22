<?php
// =============================================
// DASHBOARD - SISTEM PEMESANAN CATERING UMKM
// =============================================

session_start();

// Cek login, jika belum redirect ke login
if(!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

// Panggil konfigurasi database
include 'config/database.php';

// Ambil data dari database
$total_menu = $db->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$total_customer = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_order = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

// Ambil 5 pesanan terbaru
$orders = $db->query("SELECT o.*, c.name as customer_name 
                      FROM orders o 
                      LEFT JOIN customers c ON o.customer_id = c.id 
                      ORDER BY o.id DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SISTEM_NAME; ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        
        /* SIDEBAR */
        .sidebar { 
            position: fixed; left: 0; top: 0; width: 280px; height: 100%; 
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white; padding: 20px; overflow-y: auto;
        }
        .sidebar-header { 
            text-align: center; padding-bottom: 20px; margin-bottom: 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h2 { margin: 0; font-size: 20px; font-weight: bold; }
        .sidebar-header h2 i { font-size: 32px; display: block; margin-bottom: 10px; }
        .sidebar-header p { margin: 5px 0 0; font-size: 11px; opacity: 0.7; }
        
        .sidebar a { 
            color: rgba(255,255,255,0.8); text-decoration: none; display: block; 
            padding: 12px 15px; margin: 5px 0; border-radius: 10px; transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }
        .sidebar a i { width: 25px; margin-right: 10px; }
        
        /* MAIN CONTENT */
        .content { margin-left: 280px; padding: 20px; min-height: 100vh; }
        
        /* TOP BAR */
        .top-bar {
            background: white; padding: 15px 25px; border-radius: 15px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .page-title { font-size: 1.5rem; font-weight: 600; color: #1a1a2e; margin: 0; }
        .page-title i { color: #667eea; margin-right: 10px; }
        .user-info { 
            background: #f0f2f5; padding: 8px 15px; border-radius: 25px; 
            font-size: 14px; color: #333;
        }
        .user-info i { margin-right: 8px; color: #667eea; }
        
        /* STATS CARDS */
        .stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 20px; }
        .stat-box {
            background: white; padding: 20px; border-radius: 15px; text-align: center;
            border-left: 4px solid; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-box:hover { transform: translateY(-5px); }
        .stat-box h3 { font-size: 32px; font-weight: bold; margin: 0; }
        .stat-box p { margin: 5px 0 0; color: #666; }
        
        /* CARD */
        .card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .card-title i { margin-right: 8px; color: #667eea; }
        
        /* BADGES */
        .badge-pending { background: #f39c12; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
        .badge-processing { background: #3498db; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
        .badge-completed { background: #27ae60; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
        .badge-cancelled { background: #e74c3c; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
        
        /* WELCOME BANNER */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border-radius: 15px; padding: 20px; margin-bottom: 20px;
        }
        .welcome-banner h4 { margin: 0 0 5px; }
        .welcome-banner p { margin: 0; opacity: 0.9; font-size: 14px; }
        
        table { width: 100%; }
        th, td { padding: 12px; vertical-align: middle; }
        .table thead th { background: #f8f9fa; }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 10px; }
            .sidebar-header h2 { font-size: 12px; }
            .sidebar-header p { display: none; }
            .sidebar a span { display: none; }
            .sidebar a i { margin-right: 0; font-size: 18px; }
            .content { margin-left: 70px; }
            .stats { grid-template-columns: repeat(2,1fr); }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-utensils"></i> <?php echo SISTEM_SHORT; ?></h2>
        <p><?php echo SISTEM_TAGLINE; ?></p>
    </div>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
    <a href="menu.php"><i class="fas fa-utensils"></i> <span>Kelola Menu</span></a>
    <a href="customer.php"><i class="fas fa-users"></i> <span>Kelola Pelanggan</span></a>
    <a href="order.php"><i class="fas fa-shopping-cart"></i> <span>Daftar Pesanan</span></a>
    <a href="order_create.php"><i class="fas fa-plus-circle"></i> <span>Input Pesanan</span></a>
    <a href="report.php"><i class="fas fa-chart-line"></i> <span>Laporan</span></a>
    <hr style="border-color: rgba(255,255,255,0.1);">
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <!-- TOP BAR -->
    <div class="top-bar">
        <h1 class="page-title"><i class="fas fa-home"></i> Dashboard</h1>
        <div class="user-info">
            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user'] ?? 'Admin'; ?>
        </div>
    </div>
    
    <!-- WELCOME BANNER -->
    <div class="welcome-banner">
        <h4><i class="fas fa-check-circle"></i> Selamat Datang di <?php echo SISTEM_NAME; ?></h4>
        <p>Sistem ini membantu Anda mengelola pemesanan catering dengan mudah. Data tersimpan di database dan tidak akan hilang meskipun logout.</p>
    </div>
    
    <!-- STATISTIK -->
    <div class="stats">
        <div class="stat-box" style="border-left-color: #3498db;">
            <h3><?= $total_order ?></h3>
            <p><i class="fas fa-shopping-cart"></i> Total Pesanan</p>
        </div>
        <div class="stat-box" style="border-left-color: #27ae60;">
            <h3><?= $total_menu ?></h3>
            <p><i class="fas fa-utensils"></i> Total Menu</p>
        </div>
        <div class="stat-box" style="border-left-color: #f39c12;">
            <h3><?= $total_customer ?></h3>
            <p><i class="fas fa-users"></i> Total Pelanggan</p>
        </div>
        <div class="stat-box" style="border-left-color: #e74c3c;">
            <h3><?= $pending ?></h3>
            <p><i class="fas fa-clock"></i> Pesanan Pending</p>
        </div>
    </div>
    
    <!-- PESANAN TERBARU -->
    <div class="card">
        <div class="card-title">
            <i class="fas fa-history"></i> Pesanan Terbaru
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada pesanan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                        <tr>
                            <td><strong><?= $o['invoice_no'] ?></strong></td>
                            <td><?= $o['customer_name'] ?></td>
                            <td><?= date('d/m/Y', strtotime($o['order_date'])) ?></td>
                            <td class="text-success fw-bold"><?= rp($o['total']) ?></td>
                            <td>
                                <?php if($o['status'] == 'pending'): ?>
                                    <span class="badge-pending">Pending</span>
                                <?php elseif($o['status'] == 'processing'): ?>
                                    <span class="badge-processing">Diproses</span>
                                <?php elseif($o['status'] == 'completed'): ?>
                                    <span class="badge-completed">Selesai</span>
                                <?php else: ?>
                                    <span class="badge-cancelled"><?= $o['status'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>