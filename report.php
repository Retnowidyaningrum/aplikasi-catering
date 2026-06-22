<?php
include 'config/database.php';
if(!isset($_SESSION['login'])) header('Location: index.php');

// ========== FILTER LAPORAN ==========
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Build query with prepared statements
$where = "1=1";
$params = [];
$title = "Semua Laporan";

if($filter == 'daily') {
    $where = "order_date = ?";
    $params[] = $date;
    $title = "Laporan Harian - " . date('d/m/Y', strtotime($date));
} elseif($filter == 'weekly') {
    $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
    $where = "order_date BETWEEN ? AND ?";
    $params[] = $week_start;
    $params[] = $week_end;
    $title = "Laporan Mingguan - " . date('d/m/Y', strtotime($week_start)) . " s/d " . date('d/m/Y', strtotime($week_end));
} elseif($filter == 'monthly') {
    $where = "DATE_FORMAT(order_date, '%Y-%m') = ?";
    $params[] = $month;
    $title = "Laporan Bulanan - " . date('F Y', strtotime($month . '-01'));
}

// Ambil data pesanan sesuai filter (menggunakan prepared statement)
$sql = "SELECT orders.*, customers.name as customer_name 
        FROM orders 
        LEFT JOIN customers ON orders.customer_id = customers.id 
        WHERE $where
        ORDER BY orders.order_date DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Hitung total pendapatan (prepared statement)
$sqlTotal = "SELECT COALESCE(SUM(total),0) FROM orders WHERE status='completed' AND $where";
$stmtTotal = $db->prepare($sqlTotal);
$stmtTotal->execute($params);
$total_revenue = $stmtTotal->fetchColumn();
$total_orders = count($orders);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cateringku - Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        

        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stats-card:hover { transform: translateY(-5px); }
        .stats-card h2 { font-size: 32px; font-weight: 700; margin: 0; color: #2c3e50; }
        .stats-card p { margin: 5px 0 0; color: #6c757d; font-size: 14px; }
        .stats-card i { font-size: 40px; color: #0d9488; margin-bottom: 10px; display: block; }
        
        .table th, .table td { vertical-align: middle; padding: 12px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        
        .btn-print { background: #27ae60; border: none; border-radius: 8px; padding: 8px 20px; color: white; }
        .btn-print:hover { background: #219a52; }
        
        /* TOMBOL TAMPILKAN - WARNA TOSCA */
        .btn-tosca { 
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); 
            border: none; 
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
        }
        .btn-tosca:hover { 
            transform: translateY(-2px); 
            transition: 0.3s; 
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            color: white;
        }
        
        .filter-box { background: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        
        @media print {
            .sidebar, .top-bar, .btn-print, .filter-box, .alert { display: none; }
            .main-content { margin-left: 0; padding: 0; }
            .card { box-shadow: none; }
            body { background: white; }
        }
    </style>
</head>
<body>

<?php include 'inc/sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><i class="fas fa-chart-line"></i> Laporan</h1>
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Laporan</button>
    </div>
    
    <!-- FILTER LAPORAN -->
    <div class="filter-box">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Filter Laporan</label>
                <select id="filterSelect" class="form-select" onchange="changeFilter()">
                    <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Semua Laporan</option>
                    <option value="daily" <?= $filter == 'daily' ? 'selected' : '' ?>>Harian</option>
                    <option value="weekly" <?= $filter == 'weekly' ? 'selected' : '' ?>>Mingguan</option>
                    <option value="monthly" <?= $filter == 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                </select>
            </div>
            <div class="col-md-3" id="dailyDiv" style="display: <?= $filter == 'daily' ? 'block' : 'none' ?>;">
                <label class="form-label fw-bold">Pilih Tanggal</label>
                <input type="date" id="dailyDate" class="form-control" value="<?= $date ?>">
            </div>
            <div class="col-md-3" id="weeklyDiv" style="display: <?= $filter == 'weekly' ? 'block' : 'none' ?>;">
                <label class="form-label fw-bold">Pilih Minggu</label>
                <input type="week" id="weeklyDate" class="form-control" value="<?= date('Y-\WW', strtotime($date)) ?>">
            </div>
            <div class="col-md-3" id="monthlyDiv" style="display: <?= $filter == 'monthly' ? 'block' : 'none' ?>;">
                <label class="form-label fw-bold">Pilih Bulan</label>
                <input type="month" id="monthlyDate" class="form-control" value="<?= $month ?>">
            </div>
            <div class="col-md-2">
                <!-- TOMBOL TAMPILKAN WARNA TOSCA -->
                <button class="btn-tosca" onclick="applyFilter()"><i class="fas fa-search"></i> Tampilkan</button>
            </div>
        </div>
    </div>
    
    <!-- JUDUL LAPORAN -->
    <div class="alert alert-primary text-center" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; border: none;">
        <h5 class="mb-0"><i class="fas fa-chart-line"></i> <?= $title ?></h5>
    </div>
    
    <!-- STATISTIK -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stats-card">
                <i class="fas fa-money-bill-wave"></i>
                <h2><?= rp($total_revenue) ?></h2>
                <p>Total Pendapatan</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stats-card">
                <i class="fas fa-shopping-cart"></i>
                <h2><?= $total_orders ?></h2>
                <p>Total Pesanan</p>
            </div>
        </div>
    </div>
    
    <!-- TABEL SEMUA PESANAN -->
    <div class="card">
        <div class="card-header-custom">
            <i class="fas fa-list"></i> Daftar Pesanan
            <span class="badge bg-secondary ms-2">Total: <?= $total_orders ?> Pesanan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                Tidak ada data pesanan untuk periode ini.
                            <tr>
                        </tr>
                        <?php else: ?>
                            <?php foreach($orders as $o): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($o['order_date'])) ?></td>
                                <td><strong><?= $o['invoice_no'] ?></strong></td>
                                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                                <td class="text-end text-success fw-bold"><?= rp($o['total']) ?></td>
                                <td class="text-center">
                                    <?= getStatusBadge($o['status']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total Pendapatan:</th>
                            <th class="text-end"><?= rp($total_revenue) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- INFORMASI -->
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle"></i> <strong>Informasi:</strong>
        <ul class="mb-0 mt-2">
            <li>📅 <strong>Filter Laporan</strong> - Pilih Harian, Mingguan, atau Bulanan untuk melihat laporan spesifik</li>
            <li>📊 <strong>Total Pendapatan</strong> - Dihitung dari pesanan dengan status <strong>Selesai</strong></li>
            <li>🖨️ <strong>Cetak Laporan</strong> - Klik tombol cetak untuk mencetak laporan</li>
        </ul>
    </div>
</div>

<script>
function changeFilter() {
    var filter = document.getElementById('filterSelect').value;
    document.getElementById('dailyDiv').style.display = 'none';
    document.getElementById('weeklyDiv').style.display = 'none';
    document.getElementById('monthlyDiv').style.display = 'none';
    
    if(filter == 'daily') {
        document.getElementById('dailyDiv').style.display = 'block';
    } else if(filter == 'weekly') {
        document.getElementById('weeklyDiv').style.display = 'block';
    } else if(filter == 'monthly') {
        document.getElementById('monthlyDiv').style.display = 'block';
    }
}

function applyFilter() {
    var filter = document.getElementById('filterSelect').value;
    var url = '?filter=' + filter;
    
    if(filter == 'daily') {
        var date = document.getElementById('dailyDate').value;
        if(date) url += '&date=' + date;
    } else if(filter == 'weekly') {
        var week = document.getElementById('weeklyDate').value;
        if(week) url += '&date=' + week;
    } else if(filter == 'monthly') {
        var month = document.getElementById('monthlyDate').value;
        if(month) url += '&month=' + month;
    }
    
    window.location.href = url;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>