<?php
require_once '../config/database.php';

// Ambil parameter filter kategori
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Query menu dengan filter jika ada
if ($selectedCategory && $selectedCategory != '') {
    $menuQuery = $db->prepare("SELECT * FROM menus WHERE category = ? ORDER BY category ASC, name ASC");
    $menuQuery->execute([$selectedCategory]);
    $menus = $menuQuery->fetchAll();
} else {
    $menus = $db->query("SELECT * FROM menus ORDER BY category ASC, name ASC")->fetchAll();
}

// Ambil semua kategori untuk dropdown filter
$categories = $db->query("SELECT DISTINCT category FROM menus ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

// Group menu by kategori
$groupedMenus = [];
foreach($menus as $menu) {
    $menu_id = $menu['id'];
    
    // Ambil rata-rata rating untuk setiap menu
    $ratingStmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM reviews WHERE menu_id = ?");
    $ratingStmt->execute([$menu_id]);
    $ratingData = $ratingStmt->fetch();
    
    $menu['avg_rating'] = round($ratingData['avg_rating'] ?? 0, 1);
    $menu['total_reviews'] = $ratingData['total_reviews'] ?? 0;
    
    // Ambil 2 ulasan terbaru untuk menu ini - QUERY YANG BENAR
    $reviewStmt = $db->prepare("
        SELECT r.*, 
               COALESCE(c.name, 'Pelanggan') as customer_name 
        FROM reviews r 
        LEFT JOIN customers c ON r.customer_id = c.id 
        WHERE r.menu_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 2
    ");
    $reviewStmt->execute([$menu_id]);
    $menu['reviews'] = $reviewStmt->fetchAll();
    
    $groupedMenus[$menu['category']][] = $menu;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cateringku - Pemesanan Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .navbar {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            padding: 10px 0;
        }
        .navbar-brand { color: white !important; font-weight: bold; font-size: 18px; }
        .navbar-brand i { font-size: 20px; margin-right: 8px; }
        .btn-lacak {
            background: white;
            color: #0d9488;
            border: none;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-lacak:hover { background: #f0f2f5; color: #0d9488; }
        
        .hero {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        .hero h1 { font-size: 28px; margin-bottom: 8px; }
        .hero h1 i { font-size: 32px; }
        .hero p { font-size: 13px; opacity: 0.9; margin-bottom: 15px; }
        .hero .btn-light {
            background: white;
            color: #0d9488;
            border: none;
            border-radius: 20px;
            padding: 6px 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .filter-section {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 10px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .filter-label { font-weight: 600; color: #2c3e50; margin-right: 10px; font-size: 14px; }
        .filter-select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 8px 30px 8px 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        .btn-filter-reset {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 14px;
            margin-left: 10px;
            text-decoration: none;
        }
        .active-filter-badge {
            background: #0d9488;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .menu-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
            height: 100%;
        }
        .menu-card:hover { transform: translateY(-3px); }
        
        .menu-image { width: 100%; height: 150px; object-fit: cover; }
        .menu-image-placeholder {
            width: 100%;
            height: 150px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
        }
        .menu-image-placeholder i { font-size: 40px; }
        
        .menu-card-body { padding: 12px; }
        .menu-name { font-size: 15px; font-weight: 600; margin: 0 0 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .menu-category { font-size: 10px; color: #6c757d; margin-bottom: 5px; }
        .menu-price { font-size: 18px; font-weight: bold; color: #27ae60; margin: 5px 0; }
        
        .rating-stars { display: inline-block; font-size: 11px; margin-bottom: 5px; }
        .rating-stars i { color: #ffc107; margin-right: 1px; }
        .rating-stars i.far, .rating-stars i.text-muted { color: #e0e0e0; }
        .rating-count { font-size: 10px; color: #6c757d; margin-left: 3px; }
        
        .review-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px;
            margin-top: 8px;
            font-size: 11px;
        }
        .review-customer { font-weight: 600; color: #0d9488; font-size: 10px; }
        .review-text { color: #555; margin: 3px 0; font-size: 10px; line-height: 1.4; }
        .review-date { font-size: 9px; color: #999; }
        
        .btn-order {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            width: 100%;
            margin-top: 8px;
        }
        .btn-order:hover { transform: scale(1.02); }
        
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            font-size: 12px;
        }
        
        .category-title {
            font-size: 20px;
            font-weight: 600;
            margin: 20px 0 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #0d9488;
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 22px; }
            .menu-image { height: 120px; }
            .menu-name { font-size: 13px; }
            .menu-price { font-size: 15px; }
            .category-title { font-size: 18px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-utensils"></i> Cateringku</a>
        <div>
            <a href="tracking.php" class="btn-lacak"><i class="fas fa-search"></i> Lacak Pesanan</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <h1><i class="fas fa-utensils"></i> Cateringku</h1>
        <p>Nikmati hidangan lezat untuk acara Anda | Pemesanan mudah & cepat</p>
        <a href="order.php" class="btn btn-light"><i class="fas fa-shopping-cart"></i> Pesan Sekarang</a>
    </div>
</section>

<div class="filter-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center flex-wrap">
                    <span class="filter-label"><i class="fas fa-filter"></i> Filter Kategori:</span>
                    <form method="GET" action="" class="d-inline-flex">
                        <select name="category" class="filter-select" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <?php foreach($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= $selectedCategory == $category ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if($selectedCategory): ?>
                        <a href="index.php" class="btn-filter-reset"><i class="fas fa-times"></i> Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if($selectedCategory): ?>
                <div class="active-filter-badge"><i class="fas fa-tag"></i> <?= htmlspecialchars($selectedCategory) ?></div>
                <?php else: ?>
                <small class="text-muted"><i class="fas fa-utensils"></i> Semua menu</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container py-3">
    <?php if(!empty($groupedMenus)): ?>
        <?php foreach($groupedMenus as $kategori => $items): ?>
        <div class="text-center">
            <h2 class="category-title"><i class="fas fa-tag"></i> <?= htmlspecialchars($kategori) ?></h2>
        </div>
        <div class="row">
            <?php foreach($items as $menu): ?>
            <div class="col-md-3 col-sm-6">
                <div class="menu-card">
                    <?php if(!empty($menu['image']) && file_exists('../uploads/menus/' . $menu['image'])): ?>
                        <img src="../uploads/menus/<?= $menu['image'] ?>" class="menu-image" alt="<?= htmlspecialchars($menu['name']) ?>">
                    <?php else: ?>
                        <div class="menu-image-placeholder"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    
                    <div class="menu-card-body">
                        <div class="menu-name"><?= htmlspecialchars($menu['name']) ?></div>
                        <div class="menu-category"><i class="fas fa-tag"></i> <?= htmlspecialchars($menu['category']) ?></div>
                        
                        <div class="rating-stars">
                            <?php 
                            $rating = $menu['avg_rating'];
                            $fullStars = floor($rating);
                            for($i=1; $i<=5; $i++): 
                                if($i <= $fullStars): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-muted"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="rating-count">(<?= $menu['total_reviews'] ?>)</span>
                        </div>
                        
                        <div class="menu-price"><?= rp($menu['price']) ?></div>
                        
                        <?php if(!empty($menu['reviews'])): ?>
                            <?php foreach($menu['reviews'] as $review): ?>
                            <div class="review-item">
                                <div class="review-customer">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($review['customer_name'] ?? 'Pelanggan') ?>
                                    <small class="float-end">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>" style="font-size: 8px;"></i>
                                        <?php endfor; ?>
                                    </small>
                                </div>
                                <div class="review-text">
                                    "<?= htmlspecialchars(substr($review['comment'], 0, 40)) . (strlen($review['comment']) > 40 ? '...' : '') ?>"
                                </div>
                                <div class="review-date">
                                    <i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="review-item text-muted text-center">
                                <i class="fas fa-comment"></i> Belum ada ulasan
                            </div>
                        <?php endif; ?>
                        
                        <a href="order.php?menu_id=<?= $menu['id'] ?>" class="btn btn-order">
                            <i class="fas fa-shopping-cart"></i> Pesan
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Tidak ada menu dalam kategori ini</h5>
        <a href="index.php" class="btn btn-order mt-3" style="width: auto; padding: 8px 30px;"><i class="fas fa-arrow-left"></i> Lihat Semua Menu</a>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Cateringku. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>