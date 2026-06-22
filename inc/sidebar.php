<?php
// =============================================
// SIDEBAR - TERPUSAT UNTUK SEMUA HALAMAN
// =============================================

// Ambil nama file saat ini untuk menentukan menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* SIDEBAR STYLE - KONSISTEN UNTUK SEMUA HALAMAN */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100%;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        padding: 20px;
        overflow-y: auto;
        z-index: 1000;
    }
    
    .sidebar-header {
        text-align: center;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }
    
    .sidebar-header h3 i {
        font-size: 28px;
        display: block;
        margin-bottom: 10px;
    }
    
    .sidebar-header p {
        margin: 5px 0 0;
        font-size: 11px;
        opacity: 0.7;
    }
    
    .sidebar a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        display: block;
        padding: 12px 15px;
        margin: 5px 0;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .sidebar a:hover {
        background: rgba(255,255,255,0.1);
        color: white;
    }
    
    .sidebar a.active {
        background: rgba(255,255,255,0.15);
        color: white;
        border-left: 3px solid #667eea;
    }
    
    .sidebar a i {
        width: 25px;
        margin-right: 10px;
        text-align: center;
    }
    
    .sidebar hr {
        margin: 15px 0;
        border-color: rgba(255,255,255,0.1);
    }
    
    /* MAIN CONTENT OFFSET */
    .main-content {
        margin-left: 260px;
        padding: 20px;
        min-height: 100vh;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .sidebar { width: 70px; padding: 10px; }
        .sidebar-header h3 { font-size: 10px; }
        .sidebar-header h3 i { font-size: 20px; }
        .sidebar-header p { display: none; }
        .sidebar a span { display: none; }
        .sidebar a i { margin-right: 0; font-size: 18px; }
        .main-content { margin-left: 70px; }
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>
            <i class="fas fa-utensils"></i>
            Cateringku
        </h3>
        <p>Sistem Pemesanan Catering</p>
    </div>
    
    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
    </a>
    
    <a href="menu.php" class="<?= $current_page == 'menu.php' ? 'active' : '' ?>">
        <i class="fas fa-utensils"></i> <span>Kelola Menu</span>
    </a>
    
    <a href="customer.php" class="<?= $current_page == 'customer.php' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> <span>Kelola Pelanggan</span>
    </a>
    
    <a href="order.php" class="<?= $current_page == 'order.php' ? 'active' : '' ?>">
        <i class="fas fa-shopping-cart"></i> <span>Daftar Pesanan</span>
    </a>
    
    <a href="order_create.php" class="<?= $current_page == 'order_create.php' ? 'active' : '' ?>">
        <i class="fas fa-plus-circle"></i> <span>Input Pesanan</span>
    </a>
    
    <a href="report.php" class="<?= $current_page == 'report.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i> <span>Laporan</span>
    </a>
    
    <hr>
    
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
    </a>
</div>

<!-- Style untuk content -->
<style>
    .top-bar {
        background: white;
        padding: 15px 25px;
        border-radius: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
    }
    .page-title i {
        color: #667eea;
        margin-right: 10px;
    }
    .card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .card-header-custom {
        background: white;
        padding: 15px 20px;
        border-bottom: 2px solid #f0f0f0;
        font-weight: 600;
    }
    .card-body {
        padding: 20px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 8px 20px;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        transition: 0.3s;
    }
</style>