<?php
session_start();
if(!isset($_SESSION['login'])) header('Location: index.php');
include 'config/database.php';

// PERUBAHAN: Hapus WHERE stock > 0
$menus = $db->query("SELECT * FROM menus ORDER BY name ASC")->fetchAll();
$customers = $db->query("SELECT id, name, phone, address FROM customers ORDER BY name ASC")->fetchAll();

if(isset($_POST['simpan'])) {
    $customer_id = $_POST['customer_id'];
    $order_date = $_POST['order_date'];
    $delivery_date = $_POST['delivery_date'];
    $delivery_fee = $_POST['delivery_fee'];
    $total = $_POST['total'];
    $notes = $_POST['notes'];
    $menu_ids = $_POST['menu_id'];
    $qtys = $_POST['qty'];
    
    $invoice = 'INV/' . date('Ymd') . '/' . rand(100,999);
    
    $stmt = $db->prepare("INSERT INTO orders (invoice_no, customer_id, order_date, delivery_date, delivery_fee, total, notes, status) VALUES (?,?,?,?,?,?,?, 'pending')");
    $stmt->execute([$invoice, $customer_id, $order_date, $delivery_date, $delivery_fee, $total, $notes]);
    $order_id = $db->lastInsertId();
    
    for($i = 0; $i < count($menu_ids); $i++) {
        $menu_id = $menu_ids[$i];
        $qty = $qtys[$i];
        
        $getMenu = $db->prepare("SELECT name, price FROM menus WHERE id = ?");
        $getMenu->execute([$menu_id]);
        $menu = $getMenu->fetch();
        
        $detail = $db->prepare("INSERT INTO order_items (order_id, menu_name, quantity, price, subtotal) VALUES (?,?,?,?,?)");
        $detail->execute([$order_id, $menu['name'], $qty, $menu['price'], $menu['price'] * $qty]);
    }
    
    echo "<script>alert('Pesanan berhasil disimpan! Invoice: $invoice'); location.href='order.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title> Cateringku - Input Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { background: #f0f2f5; }
        
        /* CARD */
        .card { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-header-custom { 
            background: #f8f9fa; 
            padding: 12px 15px; 
            border-bottom: 1px solid #e9ecef; 
            font-weight: 600; 
            font-size: 14px;
            text-align: center;
        }
        .card-header-custom i { margin-right: 5px; color: #667eea; }
        .card-body { padding: 15px; }
        
        /* LABEL - DITENGAH */
        .form-label { 
            font-weight: 500; 
            margin-bottom: 8px; 
            display: block; 
            text-align: center; 
            font-size: 13px;
        }
        
        /* FORM CONTROL */
        .form-control, .form-select { 
            border-radius: 8px; 
            border: 1px solid #ddd; 
            padding: 8px 12px; 
            font-size: 14px;
        }
        
        /* BUTTON TAMBAH - DIPERKECIL */
        .btn-add-menu {
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-add-menu:hover { background: #219a52; }
        
        /* BUTTON SIMPAN */
        .btn-simpan {
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: bold;
            width: 100%;
        }
        .btn-simpan:hover { background: #219a52; }
        
        /* BUTTON TAMBAH KE KERANJANG */
        .btn-tambah-keranjang {
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            width: 100%;
        }
        .btn-tambah-keranjang:hover { background: #219a52; }
        
        /* KERANJANG */
        .cart-item { background: #f8f9fa; padding: 10px; margin-bottom: 8px; border-radius: 8px; border-left: 3px solid #27ae60; }
        .empty-cart { text-align: center; padding: 30px; color: #999; }
        .qty-btn { background: #e9ecef; border: none; width: 26px; height: 26px; border-radius: 5px; font-size: 12px; }
        .qty-input { width: 45px; text-align: center; border: 1px solid #dee2e6; border-radius: 5px; margin: 0 3px; padding: 4px; }
        .total-price { font-size: 24px; font-weight: bold; color: #27ae60; }
        
        /* CUSTOMER INFO CARD */
        .customer-info-card { 
            background: #e8f4fd; 
            border-radius: 8px; 
            padding: 10px; 
            margin-top: 10px; 
            border-left: 3px solid #3498db;
            font-size: 13px;
        }
        
        /* SELECT2 */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px;
            min-height: 38px;
            border-color: #ddd;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }
        
        /* INFORMASI PESANAN */
        .info-pesanan { font-size: 13px; }
        
        @media (max-width: 768px) {
            .card-header-custom { font-size: 12px; }
        }
    </style>
</head>
<body>

<?php include 'inc/sidebar.php'; ?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><i class="fas fa-plus-circle"></i> Input Pesanan Baru</h1>
    </div>
    
    <form method="POST" id="orderForm">
        <div class="row">
            <div class="col-md-5">
                <!-- Data Pelanggan -->
                <div class="card">
                    <div class="card-header-custom"><i class="fas fa-user"></i> Data Pelanggan</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cari / Pilih Pelanggan <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select" required style="width:100%">
                                <option value="">-- Ketik nama atau telepon pelanggan --</option>
                                <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>" data-phone="<?= htmlspecialchars($c['phone']) ?>" data-address="<?= htmlspecialchars($c['address']) ?>">
                                    <?= htmlspecialchars($c['name']) ?> - <?= htmlspecialchars($c['phone']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="text-center mt-1">
                                <small class="text-muted"><i class="fas fa-search"></i> Ketik nama atau nomor telepon untuk mencari</small>
                            </div>
                        </div>
                        
                        <!-- Informasi Pelanggan yang dipilih -->
                        <div id="customerInfo" class="customer-info-card" style="display: none;">
                            <i class="fas fa-check-circle text-primary"></i> <strong id="selectedCustomerName"></strong><br>
                            <small><i class="fas fa-phone"></i> <span id="selectedCustomerPhone"></span></small><br>
                            <small><i class="fas fa-map-marker-alt"></i> <span id="selectedCustomerAddress"></span></small>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggan">
                                <i class="fas fa-plus"></i> Tambah Pelanggan Baru
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Informasi Pesanan -->
                <div class="card">
                    <div class="card-header-custom"><i class="fas fa-calendar-alt"></i> Informasi Pesanan</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold">Tanggal Pesanan</label>
                                <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold">Tanggal Pengiriman</label>
                                <input type="date" name="delivery_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold"><i class="fas fa-pen"></i> Catatan Khusus</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Jangan pakai sambal, Tolong diantar jam 12 siang..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <!-- Pilih Menu -->
                <div class="card">
                    <div class="card-header-custom"><i class="fas fa-utensils"></i> Pilih Menu</div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label fw-bold">Cari / Pilih Menu</label>
                            <select id="menuSelect" class="form-select" style="width:100%">
                                <option value="">-- Ketik nama menu untuk mencari --</option>
                                <?php foreach($menus as $m): ?>
                                <option value="<?= $m['id'] ?>" data-price="<?= $m['price'] ?>" data-name="<?= $m['name'] ?>">
                                    <?= htmlspecialchars($m['name']) ?> - <?= rp($m['price']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="text-center mt-1">
                                <small class="text-muted"><i class="fas fa-search"></i> Ketik nama menu untuk mencari</small>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-8">
                                <input type="number" id="menuQty" class="form-control" value="1" min="1" placeholder="Jumlah">
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn-tambah-keranjang" onclick="addToCart()">
                                    <i class="fas fa-cart-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Keranjang -->
                <div class="card">
                    <div class="card-header-custom"><i class="fas fa-shopping-cart"></i> Keranjang Pesanan <span id="cartCount" class="badge bg-danger" style="display:none">0</span></div>
                    <div class="card-body" id="cartContainer" style="max-height:280px;overflow:auto">
                        <div class="empty-cart"><i class="fas fa-shopping-cart"></i> Keranjang kosong</div>
                    </div>
                </div>
                
                <!-- Ringkasan -->
                <div class="card">
                    <div class="card-header-custom"><i class="fas fa-calculator"></i> Ringkasan Pesanan</div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="ps-0"><strong>Subtotal Menu</strong></td>
                                <td class="text-end pe-0" id="subtotalDisplay">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="ps-0">
                                    <strong><i class="fas fa-motorcycle"></i> Biaya Pengiriman</strong>
                                </td>
                                <td class="text-end pe-0">
                                    <input type="number" name="delivery_fee" id="deliveryFee" class="form-control text-end" style="width:120px; display: inline-block;" value="0" oninput="hitungTotal()">
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td class="ps-0 pt-2"><strong class="fs-5">TOTAL PESANAN</strong></td>
                                <td class="text-end pe-0 pt-2"><h4 class="total-price mb-0" id="totalDisplay">Rp 0</h4></td>
                            </tr>
                        </table>
                        <input type="hidden" name="total" id="totalHidden">
                        <button type="submit" name="simpan" class="btn-simpan mt-3" id="simpanBtn" disabled>
                            <i class="fas fa-save"></i> Simpan Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Tambah Pelanggan -->
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="customer.php" target="_blank">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label>Nama Lengkap</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-2"><label>Nomor Telepon</label><input type="text" name="phone" class="form-control" placeholder="Contoh: 081234567890"></div>
                    <div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="mb-2"><label>Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
            <div class="modal-footer border-0 pt-0">
                <small class="text-muted">* Setelah menyimpan, refresh halaman dan cari pelanggan baru</small>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let cart = [];

$(document).ready(function() {
    $('#customerSelect').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Ketik nama atau telepon pelanggan --',
        allowClear: true,
        width: '100%'
    });
    
    $('#menuSelect').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Ketik nama menu untuk mencari --',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() { return "Menu tidak ditemukan"; },
            searching: function() { return "Mencari..."; }
        }
    });
    
    $('#customerSelect').on('change', function() {
        var selected = $(this).find('option:selected');
        if(selected.val()) {
            var name = selected.text().split(' - ')[0];
            var phone = selected.data('phone') || '-';
            var address = selected.data('address') || '-';
            
            $('#selectedCustomerName').text(name);
            $('#selectedCustomerPhone').text(phone);
            $('#selectedCustomerAddress').text(address);
            $('#customerInfo').show();
        } else {
            $('#customerInfo').hide();
        }
    });
});

function addToCart() {
    let select = document.getElementById('menuSelect');
    let selectedOption = $(select).find('option:selected');
    
    if(!select.value) {
        alert('Pilih menu terlebih dahulu!');
        return;
    }
    
    let id = select.value;
    let name = selectedOption.data('name');
    let price = parseInt(selectedOption.data('price'));
    let qty = parseInt(document.getElementById('menuQty').value);
    
    let idx = cart.findIndex(i => i.id == id);
    if(idx !== -1) {
        cart[idx].qty += qty;
    } else {
        cart.push({id:id, name:name, price:price, qty:qty});
    }
    
    updateCart();
    $('#menuSelect').val(null).trigger('change');
    document.getElementById('menuQty').value = 1;
}

function updateCartQty(i, q) {
    if(q < 1) q = 1;
    cart[i].qty = q;
    updateCart();
}

function removeItem(i) {
    cart.splice(i, 1);
    updateCart();
}

function updateCart() {
    let container = document.getElementById('cartContainer');
    let count = document.getElementById('cartCount');
    let btn = document.getElementById('simpanBtn');
    
    if(cart.length == 0) {
        container.innerHTML = '<div class="empty-cart"><i class="fas fa-shopping-cart"></i> Keranjang kosong</div>';
        count.style.display = 'none';
        btn.disabled = true;
        hitungTotal();
        return;
    }
    
    btn.disabled = false;
    count.style.display = 'inline-block';
    count.innerText = cart.length;
    
    let html = '';
    for(let i = 0; i < cart.length; i++) {
        let item = cart[i];
        let sub = item.price * item.qty;
        html += `<div class="cart-item">
            <div class="row align-items-center">
                <div class="col-5"><b>${item.name}</b><br><small>${formatRupiah(item.price)}</small></div>
                <div class="col-4">
                    <button class="qty-btn" onclick="updateCartQty(${i}, ${item.qty-1})">-</button>
                    <input type="number" class="qty-input" value="${item.qty}" onchange="updateCartQty(${i}, parseInt(this.value))">
                    <button class="qty-btn" onclick="updateCartQty(${i}, ${item.qty+1})">+</button>
                </div>
                <div class="col-3 text-end"><b>${formatRupiah(sub)}</b><br><button class="btn btn-sm btn-danger mt-1" onclick="removeItem(${i})"><i class="fas fa-trash"></i></button></div>
            </div>
        </div>`;
    }
    
    html += `<div id="hiddenInputs"></div>`;
    container.innerHTML = html;
    
    let hiddenDiv = document.getElementById('hiddenInputs');
    hiddenDiv.innerHTML = '';
    for(let i = 0; i < cart.length; i++) {
        hiddenDiv.innerHTML += `<input type="hidden" name="menu_id[]" value="${cart[i].id}">`;
        hiddenDiv.innerHTML += `<input type="hidden" name="qty[]" value="${cart[i].qty}">`;
    }
    
    hitungTotal();
}

function hitungTotal() {
    let sub = 0;
    for(let i = 0; i < cart.length; i++) {
        sub += cart[i].price * cart[i].qty;
    }
    let ongkir = parseInt(document.getElementById('deliveryFee').value) || 0;
    let total = sub + ongkir;
    document.getElementById('subtotalDisplay').innerHTML = formatRupiah(sub);
    document.getElementById('totalDisplay').innerHTML = formatRupiah(total);
    document.getElementById('totalHidden').value = total;
}

function formatRupiah(angka) {
    return 'Rp ' + angka.toLocaleString('id-ID');
}

document.getElementById('orderForm').addEventListener('submit', function(e) {
    if(!document.getElementById('customerSelect').value) {
        e.preventDefault();
        alert('Pilih pelanggan!');
    } else if(cart.length == 0) {
        e.preventDefault();
        alert('Tambah menu ke keranjang!');
    }
});
</script>
</body>
</html>