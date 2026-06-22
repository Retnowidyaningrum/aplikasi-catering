<?php
include '../config/database.php';

// ========== KONFIGURASI ONGKIR ==========
$ONGKIR_TETAP = 15000;
$MIN_ORDER_GRATIS = 500000;

$menus = $db->query("SELECT * FROM menus ORDER BY name ASC")->fetchAll();

if(isset($_POST['simpan'])) {
    // Simpan data pelanggan
    $cekCustomer = $db->prepare("SELECT id FROM customers WHERE phone = ?");
    $cekCustomer->execute([$_POST['customer_phone']]);
    $existing = $cekCustomer->fetch();
    
    if($existing) {
        $customer_id = $existing['id'];
    } else {
        $save = $db->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?,?,?,?)");
        $save->execute([$_POST['customer_name'], $_POST['customer_phone'], $_POST['customer_email'], $_POST['customer_address']]);
        $customer_id = $db->lastInsertId();
    }
    
    // Hitung subtotal
    $subtotal = 0;
    for($i = 0; $i < count($_POST['menu_id']); $i++) {
        $get = $db->prepare("SELECT price FROM menus WHERE id = ?");
        $get->execute([$_POST['menu_id'][$i]]);
        $price = (int)$get->fetchColumn();
        $qty = (int)$_POST['qty'][$i];
        $subtotal += $price * $qty;
    }
    
    // Hitung ongkir berdasarkan pilihan pengiriman
    $delivery_method = $_POST['delivery_method'];
    if($delivery_method == 'pickup') {
        $delivery_fee = 0;
    } else {
        if($subtotal >= $MIN_ORDER_GRATIS) {
            $delivery_fee = 0;
        } else {
            $delivery_fee = $ONGKIR_TETAP;
        }
    }
    
    // Metode pembayaran
    $payment_method = $_POST['payment_method'];
    
    $total = $subtotal + $delivery_fee;
    
    $invoice = 'INV/' . date('Ymd') . '/' . rand(100,999);
    
    // ========== QUERY INSERT YANG BENAR (DENGAN PAYMENT_METHOD) ==========
    $stmt = $db->prepare("INSERT INTO orders (invoice_no, customer_id, order_date, delivery_date, delivery_fee, payment_method, total, notes, status) VALUES (?,?,?,?,?,?,?,?, 'pending')");
    $stmt->execute([$invoice, $customer_id, $_POST['order_date'], $_POST['delivery_date'], $delivery_fee, $payment_method, $total, $_POST['notes']]);
    $order_id = $db->lastInsertId();
    
    for($i = 0; $i < count($_POST['menu_id']); $i++) {
        $getMenu = $db->prepare("SELECT name, price FROM menus WHERE id = ?");
        $getMenu->execute([$_POST['menu_id'][$i]]);
        $menu = $getMenu->fetch();
        $qty = (int)$_POST['qty'][$i];
        $detail = $db->prepare("INSERT INTO order_items (order_id, menu_name, quantity, price, subtotal) VALUES (?,?,?,?,?)");
        $detail->execute([$order_id, $menu['name'], $qty, $menu['price'], $menu['price'] * $qty]);
    }
    
    $method_text = ($delivery_method == 'pickup') ? 'Ambil di Tempat' : 'Antar ke Rumah';
    $payment_text = ($payment_method == 'cod') ? 'COD (Bayar di Tempat)' : 'Transfer Bank';
    
    // Tampilkan halaman sukses
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Pesanan Berhasil - Cateringku</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <style>
            body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
            .invoice-card {
                max-width: 500px;
                margin: 80px auto;
                background: white;
                border-radius: 20px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            .invoice-code {
                background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
                color: white;
                padding: 15px;
                border-radius: 12px;
                font-size: 24px;
                font-weight: bold;
                letter-spacing: 2px;
                margin: 20px 0;
            }
            .btn-action {
                background: #0d9488;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                text-align: center;
                flex: 1;
            }
            .btn-action:hover {
                background: #0f766e;
                color: white;
            }
            .btn-action-wrapper {
                display: flex;
                gap: 15px;
                justify-content: center;
                margin-top: 20px;
            }
            .btn-home {
                background: #27ae60;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
                font-weight: 500;
                margin-top: 15px;
            }
            .btn-home:hover {
                background: #219a52;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class='invoice-card'>
            <div class='mb-3'>
                <i class='fas fa-check-circle' style='font-size: 60px; color: #27ae60;'></i>
            </div>
            <h3>✅ Pesanan Berhasil!</h3>
            <p>Terima kasih telah memesan di Cateringku</p>
            
            <div class='invoice-code'>
                <i class='fas fa-receipt'></i> $invoice
            </div>
            
            <table class='table table-borderless text-start'>
                <tr><td><strong>Metode Pengiriman</strong></td><td>$method_text</td></tr>
                <tr><td><strong>Metode Pembayaran</strong></td><td>$payment_text</td></tr>
                <tr><td><strong>Total Pembayaran</strong></td><td class='text-success fw-bold'>" . rp($total) . "</td></tr>
            </table>
            
            <div class='alert alert-info'>
                <i class='fas fa-info-circle'></i> Simpan nomor invoice untuk melacak pesanan Anda.
            </div>
            
            <div class='btn-action-wrapper'>
                <button class='btn-action' onclick='copyInvoice()'>
                    <i class='fas fa-copy'></i> Salin Invoice
                </button>
                <a href='tracking.php' class='btn-action'>
                    <i class='fas fa-search'></i> Lacak Pesanan
                </a>
            </div>
            
            <a href='index.php' class='btn-home'>
                <i class='fas fa-home'></i> Kembali ke Beranda
            </a>
        </div>
        
        <script>
            function copyInvoice() {
                navigator.clipboard.writeText('$invoice');
                alert('Invoice berhasil disalin!');
            }
        </script>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit();
}

$selected_menu_id = isset($_GET['menu_id']) ? $_GET['menu_id'] : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cateringku - Form Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        
        .navbar { 
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); 
            padding: 12px 0; 
        }
        .navbar-brand { color: white !important; font-weight: bold; }
        .btn-light { background: white; color: #0d9488; border: none; }
        .btn-light:hover { background: #f0f2f5; color: #0d9488; }
        
        .form-container { max-width: 800px; margin: 40px auto; }
        .card { border-radius: 16px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-header { background: white; border-bottom: 2px solid #f0f2f5; font-weight: 600; }
        .card-header i { color: #0d9488; }
        
        .btn-submit { 
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); 
            color: white; 
            border: none; 
            border-radius: 10px; 
            padding: 12px; 
            width: 100%; 
            font-weight: bold; 
        }
        .btn-submit:hover { 
            transform: translateY(-2px); 
            transition: 0.3s; 
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        }
        
        .cart-item { background: #f8f9fa; padding: 10px; margin-bottom: 8px; border-radius: 8px; border-left: 3px solid #27ae60; }
        .empty-cart { text-align: center; padding: 20px; color: #999; }
        .total-price { font-size: 24px; font-weight: bold; color: #27ae60; }
        
        .btn-success { background: #27ae60; border: none; }
        .btn-success:hover { background: #219a52; }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 0.2rem rgba(13,148,136,0.25);
        }
        
        .info-ongkir {
            background: #e8f4fd;
            border-radius: 10px;
            padding: 12px;
            margin-top: 10px;
            font-size: 13px;
        }
        
        .delivery-option, .payment-option {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .delivery-option:hover, .payment-option:hover {
            border-color: #0d9488;
            background: #f0fdfa;
        }
        .delivery-option.active, .payment-option.active {
            border-color: #0d9488;
            background: #f0fdfa;
        }
        .delivery-option input, .payment-option input {
            margin-right: 10px;
        }
        .delivery-title, .payment-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .delivery-desc, .payment-desc {
            font-size: 12px;
            color: #666;
            margin-left: 25px;
        }
        
        .bank-info {
            background: #fef9e7;
            border-radius: 10px;
            padding: 12px;
            margin-top: 10px;
            font-size: 13px;
            border-left: 3px solid #f39c12;
        }
        
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-utensils"></i> Cateringku</a>
        <div>
            <a href="tracking.php" class="btn btn-light btn-sm me-2">
                <i class="fas fa-search"></i> Lacak
            </a>
            <a href="index.php" class="btn btn-light btn-sm">
                <i class="fas fa-home"></i> Kembali
            </a>
        </div>
    </div>
</nav>

<div class="form-container">
    <h3 class="text-center mb-4"><i class="fas fa-shopping-cart"></i> Form Pemesanan</h3>
    
    <form method="POST" id="orderForm">
        <div class="card">
            <div class="card-header"><i class="fas fa-user"></i> Data Diri <span class="text-danger">*Wajib diisi</span></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label required">Nomor Telepon</label>
                        <input type="text" name="customer_phone" class="form-control" placeholder="Nomor Telepon (aktif)" required>
                        <small class="text-muted">Contoh: 081234567890</small>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-control" placeholder="Email (opsional)">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label required" id="alamatLabel">Alamat Lengkap</label>
                        <textarea name="customer_address" id="customer_address" class="form-control" rows="2" placeholder="Alamat lengkap" required></textarea>
                        <small class="text-muted">*Wajib diisi untuk pengiriman, opsional jika ambil di tempat</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><i class="fas fa-utensils"></i> Pilih Menu</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-7">
                        <select id="menuSelect" class="form-select">
                            <option value="">-- Pilih Menu --</option>
                            <?php foreach($menus as $m): ?>
                            <option value="<?= $m['id'] ?>" data-price="<?= $m['price'] ?>" data-name="<?= $m['name'] ?>" <?= ($selected_menu_id == $m['id']) ? 'selected' : '' ?>><?= $m['name'] ?> - <?= rp($m['price']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-3"><input type="number" id="menuQty" class="form-control" value="1" min="1"></div>
                    <div class="col-2"><button type="button" class="btn btn-success w-100" onclick="addToCart()"><i class="fas fa-plus"></i></button></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><i class="fas fa-shopping-cart"></i> Keranjang <span id="cartCount" class="badge bg-danger" style="display:none">0</span></div>
            <div class="card-body" id="cartContainer" style="max-height:300px;overflow:auto"><div class="empty-cart">Keranjang kosong</div></div>
        </div>
        
        <div class="card">
            <div class="card-header"><i class="fas fa-truck"></i> Metode Pengiriman</div>
            <div class="card-body">
                <div class="delivery-option" onclick="selectDelivery('delivery')" id="deliveryOption">
                    <input type="radio" name="delivery_method" id="delivery_method_delivery" value="delivery" checked>
                    <span class="delivery-title"><i class="fas fa-truck"></i> Antar ke Rumah</span>
                    <div class="delivery-desc">
                        Pesanan akan diantar ke alamat Anda. 
                        Ongkir <?= rp($ONGKIR_TETAP) ?> (Gratis jika belanja minimal <?= rp($MIN_ORDER_GRATIS) ?>)
                    </div>
                </div>
                
                <div class="delivery-option" onclick="selectDelivery('pickup')" id="pickupOption">
                    <input type="radio" name="delivery_method" id="delivery_method_pickup" value="pickup">
                    <span class="delivery-title"><i class="fas fa-store"></i> Ambil di Tempat</span>
                    <div class="delivery-desc">
                        Ambil sendiri pesanan Anda di lokasi kami. <strong class="text-success">Gratis (Tanpa Ongkir)</strong>
                    </div>
                </div>
                
                <div id="storeAddress" class="info-ongkir mt-3" style="display: none;">
                    <i class="fas fa-map-marker-alt"></i> <strong>Alamat Pengambilan:</strong><br>
                    Jl. Mawar Desa Bojongwetan, Kecamatan Bojong, Kabupaten Pekalongan<br>
                    <small>Jam operasional: 08.00 - 17.00</small>
                </div>
            </div>
        </div>
        
        <!-- METODE PEMBAYARAN -->
        <div class="card">
            <div class="card-header"><i class="fas fa-credit-card"></i> Metode Pembayaran</div>
            <div class="card-body">
                <div class="payment-option" onclick="selectPayment('cod')" id="codOption">
                    <input type="radio" name="payment_method" id="payment_method_cod" value="cod" checked>
                    <span class="payment-title"><i class="fas fa-money-bill-wave"></i> COD (Cash On Delivery)</span>
                    <div class="payment-desc">
                        Bayar langsung saat pesanan sampai di tempat Anda.
                    </div>
                </div>
                
                <div class="payment-option" onclick="selectPayment('transfer')" id="transferOption">
                    <input type="radio" name="payment_method" id="payment_method_transfer" value="transfer">
                    <span class="payment-title"><i class="fas fa-university"></i> Transfer Bank</span>
                    <div class="payment-desc">
                        Bayar via transfer bank. Pesanan akan diproses setelah pembayaran dikonfirmasi.
                    </div>
                </div>
                
                <div id="bankInfo" class="bank-info" style="display: none;">
                    <i class="fas fa-info-circle"></i> <strong>Informasi Rekening:</strong><br>
                    <strong>Bank BCA</strong><br>
                    No. Rekening: 1234 5678 9012 3456<br>
                    Atas Nama: Cateringku<br>
                    <small class="text-muted">*Setelah transfer, harap konfirmasi ke admin via WhatsApp</small>
                </div>
            </div>
        </div>
        
        <div class="card" id="deliveryDateCard">
            <div class="card-header"><i class="fas fa-calendar"></i> Jadwal Pengiriman</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Tanggal Pesanan</label>
                        <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Tanggal Pengiriman/Pengambilan</label>
                        <input type="date" name="delivery_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                </div>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan..."></textarea>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><i class="fas fa-calculator"></i> Ringkasan</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><td class="ps-0"><strong>Subtotal</strong></td><td class="text-end pe-0" id="subtotalDisplay">Rp 0</span></td>
                    <tr><td class="ps-0"><strong>Ongkos Kirim</strong> <br><small class="text-muted" id="ongkirDetail"><?= rp($ONGKIR_TETAP) ?></small></td>
                        <td class="text-end pe-0" id="ongkirDisplay"><?= rp($ONGKIR_TETAP) ?></td>
                    </tr>
                    <tr class="border-top"><td class="ps-0 pt-2"><strong>TOTAL</strong></td>
                    <td class="text-end pe-0 pt-2"><h4 class="total-price" id="totalDisplay">Rp 0</h4></td>
                </table>
                <input type="hidden" name="delivery_fee" id="deliveryFeeHidden" value="<?= $ONGKIR_TETAP ?>">
                <input type="hidden" name="total" id="totalHidden">
                <button type="submit" name="simpan" class="btn-submit" id="simpanBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Kirim Pesanan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let cart = [];
const ONGKIR_TETAP = <?= $ONGKIR_TETAP ?>;
const MIN_ORDER_GRATIS = <?= $MIN_ORDER_GRATIS ?>;

<?php if($selected_menu_id): 
    $menu = $db->prepare("SELECT id, name, price FROM menus WHERE id = ?");
    $menu->execute([$selected_menu_id]);
    $m = $menu->fetch();
    if($m): ?>
    cart.push({id: <?= $m['id'] ?>, name: '<?= addslashes($m['name']) ?>', price: <?= $m['price'] ?>, qty: 1});
    <?php endif; 
endif; ?>

function selectDelivery(method) {
    let deliveryOption = document.getElementById('deliveryOption');
    let pickupOption = document.getElementById('pickupOption');
    let addressField = document.getElementById('customer_address');
    let storeAddressDiv = document.getElementById('storeAddress');
    let deliveryDateCard = document.getElementById('deliveryDateCard');
    let alamatLabel = document.getElementById('alamatLabel');
    
    if(method == 'pickup') {
        deliveryOption.classList.remove('active');
        pickupOption.classList.add('active');
        document.getElementById('delivery_method_pickup').checked = true;
        addressField.removeAttribute('required');
        addressField.placeholder = 'Opsional (isi jika perlu)';
        storeAddressDiv.style.display = 'block';
        deliveryDateCard.querySelector('.card-header').innerHTML = '<i class="fas fa-calendar"></i> Jadwal Pengambilan';
        alamatLabel.innerHTML = 'Alamat Lengkap <small class="text-muted">(Opsional untuk ambil di tempat)</small>';
    } else {
        deliveryOption.classList.add('active');
        pickupOption.classList.remove('active');
        document.getElementById('delivery_method_delivery').checked = true;
        addressField.setAttribute('required', 'required');
        addressField.placeholder = 'Alamat lengkap *';
        storeAddressDiv.style.display = 'none';
        deliveryDateCard.querySelector('.card-header').innerHTML = '<i class="fas fa-calendar"></i> Jadwal Pengiriman';
        alamatLabel.innerHTML = 'Alamat Lengkap <span class="text-danger">*</span>';
    }
    hitungTotal();
}

function selectPayment(method) {
    let codOption = document.getElementById('codOption');
    let transferOption = document.getElementById('transferOption');
    let bankInfo = document.getElementById('bankInfo');
    
    if(method == 'transfer') {
        codOption.classList.remove('active');
        transferOption.classList.add('active');
        document.getElementById('payment_method_transfer').checked = true;
        bankInfo.style.display = 'block';
    } else {
        transferOption.classList.remove('active');
        codOption.classList.add('active');
        document.getElementById('payment_method_cod').checked = true;
        bankInfo.style.display = 'none';
    }
}

function addToCart() {
    let select = document.getElementById('menuSelect');
    let opt = select.options[select.selectedIndex];
    if(!select.value) { alert('Pilih menu!'); return; }
    let id = select.value, name = opt.dataset.name, price = parseInt(opt.dataset.price), qty = parseInt(document.getElementById('menuQty').value);
    let idx = cart.findIndex(i => i.id == id);
    if(idx !== -1) cart[idx].qty += qty;
    else cart.push({id:id, name:name, price:price, qty:qty});
    updateCart();
    select.value = '';
    document.getElementById('menuQty').value = 1;
}

function updateCartQty(i, q) { if(q < 1) q = 1; cart[i].qty = q; updateCart(); }
function removeItem(i) { cart.splice(i, 1); updateCart(); }

function updateCart() {
    let container = document.getElementById('cartContainer');
    let count = document.getElementById('cartCount');
    let btn = document.getElementById('simpanBtn');
    if(cart.length == 0) {
        container.innerHTML = '<div class="empty-cart">Keranjang kosong</div>';
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
        let sub = cart[i].price * cart[i].qty;
        html += `<div class="cart-item"><div class="row align-items-center">
            <div class="col-6"><b>${cart[i].name}</b><br><small>${formatRupiah(cart[i].price)}</small></div>
            <div class="col-3">
                <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQty(${i}, ${cart[i].qty-1})">-</button>
                <span class="mx-1">${cart[i].qty}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQty(${i}, ${cart[i].qty+1})">+</button>
            </div>
            <div class="col-2 text-end"><b>${formatRupiah(sub)}</b></div>
            <div class="col-1 text-end"><button class="btn btn-sm btn-danger" onclick="removeItem(${i})"><i class="fas fa-trash"></i></button></div>
        </div></div>`;
    }
    html += `<div id="hiddenInputs"></div>`;
    container.innerHTML = html;
    let hiddenDiv = document.getElementById('hiddenInputs');
    hiddenDiv.innerHTML = '';
    for(let i = 0; i < cart.length; i++) {
        hiddenDiv.innerHTML += `<input type="hidden" name="menu_id[]" value="${cart[i].id}"><input type="hidden" name="qty[]" value="${cart[i].qty}">`;
    }
    hitungTotal();
}

function hitungTotal() {
    let subtotal = 0;
    for(let i = 0; i < cart.length; i++) {
        subtotal += cart[i].price * cart[i].qty;
    }
    
    let deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
    let ongkir = 0;
    let ongkirText = '';
    
    if(deliveryMethod == 'pickup') {
        ongkir = 0;
        ongkirText = 'Gratis (Ambil di Tempat)';
    } else {
        if(subtotal >= MIN_ORDER_GRATIS) {
            ongkir = 0;
            ongkirText = 'Gratis (Minimal order)';
        } else {
            ongkir = ONGKIR_TETAP;
            ongkirText = formatRupiah(ONGKIR_TETAP);
        }
    }
    
    let total = subtotal + ongkir;
    
    document.getElementById('subtotalDisplay').innerHTML = formatRupiah(subtotal);
    document.getElementById('ongkirDisplay').innerHTML = ongkirText;
    document.getElementById('ongkirDetail').innerHTML = ongkirText;
    document.getElementById('totalDisplay').innerHTML = formatRupiah(total);
    document.getElementById('totalHidden').value = total;
    document.getElementById('deliveryFeeHidden').value = ongkir;
}

function formatRupiah(a) { return 'Rp ' + a.toLocaleString('id-ID'); }

document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if(this.value == 'pickup') {
            selectDelivery('pickup');
        } else {
            selectDelivery('delivery');
        }
    });
});

// Validasi tambahan sebelum submit
document.getElementById('orderForm').addEventListener('submit', function(e) {
    let name = document.querySelector('input[name="customer_name"]').value.trim();
    let phone = document.querySelector('input[name="customer_phone"]').value.trim();
    let deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
    let address = document.getElementById('customer_address').value.trim();
    
    if(name === '') {
        e.preventDefault();
        alert('❌ Nama lengkap wajib diisi!');
        return false;
    }
    
    if(phone === '') {
        e.preventDefault();
        alert('❌ Nomor telepon wajib diisi!');
        return false;
    }
    
    let phoneRegex = /^[0-9]{10,13}$/;
    if(!phoneRegex.test(phone.replace(/[^0-9]/g, ''))) {
        e.preventDefault();
        alert('❌ Nomor telepon tidak valid! Masukkan 10-13 digit angka.');
        return false;
    }
    
    if(deliveryMethod === 'delivery' && address === '') {
        e.preventDefault();
        alert('❌ Alamat wajib diisi untuk pengiriman ke rumah!');
        return false;
    }
    
    if(cart.length === 0) {
        e.preventDefault();
        alert('❌ Silakan tambahkan menu ke keranjang terlebih dahulu!');
        return false;
    }
    
    return true;
});

// Inisialisasi
if(cart.length > 0) updateCart();
selectDelivery('delivery');
selectPayment('cod');
</script>
</body>
</html>