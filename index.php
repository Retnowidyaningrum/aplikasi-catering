<?php
session_start();
include 'config/database.php';

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if($user) {
        $_SESSION['login'] = true;
        $_SESSION['user'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}

// Proses reset password (demo - hanya menampilkan pesan)
if(isset($_POST['reset_password'])) {
    $reset_email = $_POST['reset_email'];
    // Demo: hanya menampilkan alert
    echo "<script>alert('Link reset password telah dikirim ke $reset_email\\n\\nUsername: admin\\nPassword: admin123');</script>";
}

if(isset($_SESSION['login'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cateringku - Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo i {
            font-size: 60px;
            color: #0d9488;
            background: #e6f7f5;
            padding: 20px;
            border-radius: 50%;
        }
        
        .logo h3 {
            margin-top: 15px;
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .logo p {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: #495057;
            font-size: 13px;
        }
        
        .form-group label i {
            margin-right: 6px;
            color: #0d9488;
        }
        
        /* Password input with toggle */
        .password-wrapper {
            position: relative;
        }
        
        .password-wrapper .form-control {
            padding-right: 45px;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            background: none;
            border: none;
            font-size: 16px;
        }
        
        .toggle-password:hover {
            color: #0d9488;
        }
        
        .form-control {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13,148,136,0.1);
            outline: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13,148,136,0.3);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        /* Links */
        .form-links {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 13px;
        }
        
        .form-links a {
            color: #0d9488;
            text-decoration: none;
        }
        
        .form-links a:hover {
            text-decoration: underline;
        }
        
        .demo-info {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .demo-info small {
            color: #6c757d;
            font-size: 12px;
        }
        
        .demo-info strong {
            color: #0d9488;
        }
        
        .alert {
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 12px 15px;
            font-size: 13px;
        }
        
        /* Modal Reset Password */
        .modal-content {
            border-radius: 16px;
        }
        
        .modal-header {
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .btn-reset {
            background: #0d9488;
            border: none;
            border-radius: 10px;
            padding: 10px;
        }
        
        .btn-reset:hover {
            background: #0f766e;
        }
        
        @media (max-width: 480px) {
            .login-card { padding: 30px 25px; margin: 20px; }
            .logo i { font-size: 45px; padding: 15px; }
            .logo h3 { font-size: 20px; }
            .form-links { flex-direction: column; gap: 8px; text-align: center; }
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">
        <i class="fas fa-utensils"></i>
        <h3>Cateringku</h3>
        <p>Sistem Pemesanan Catering Online</p>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>
        </div>
        
        <div class="form-links">
            <a href="#" data-bs-toggle="modal" data-bs-target="#modalLupaSandi">
                <i class="fas fa-question-circle"></i> Lupa Password?
            </a>
        </div>
        
        <button type="submit" name="login" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>
    
    <div class="demo-info">
        <small>
            <i class="fas fa-info-circle"></i> Demo: 
            <strong>admin</strong> / <strong>admin123</strong>
        </small>
    </div>
</div>

<!-- Modal Lupa Password -->
<div class="modal fade" id="modalLupaSandi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Masukkan email atau username Anda untuk mereset password.</p>
                    <div class="mb-3">
                        <label class="form-label">Email / Username</label>
                        <input type="text" name="reset_email" class="form-control" placeholder="Masukkan email atau username" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Demo: Gunakan <strong>admin</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reset_password" class="btn btn-reset text-white">Kirim Link Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Fungsi toggle password (lihat/sembunyikan password)
    function togglePassword() {
        var passwordInput = document.getElementById('password');
        var toggleBtn = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.classList.remove('fa-eye-slash');
            toggleBtn.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleBtn.classList.remove('fa-eye');
            toggleBtn.classList.add('fa-eye-slash');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>