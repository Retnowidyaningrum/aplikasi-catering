<?php
// =============================================
// HELPERS SENTRAL - Panggil setelah config/database.php
// =============================================

// ========== CSRF PROTECTION ==========
// Generate token CSRF
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Output hidden input CSRF
if (!function_exists('csrfField')) {
    function csrfField() {
        $token = generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

// Verifikasi token CSRF
if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken() {
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}

// ========== STATUS BADGE ==========
if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        if ($status == 'pending') {
            return '<span class="badge-pending"><i class="fas fa-clock"></i> Pending</span>';
        } elseif ($status == 'processing') {
            return '<span class="badge-processing"><i class="fas fa-cog"></i> Diproses</span>';
        } elseif ($status == 'completed') {
            return '<span class="badge-completed"><i class="fas fa-check"></i> Selesai</span>';
        } elseif ($status == 'cancelled') {
            return '<span class="badge-cancelled"><i class="fas fa-times"></i> Dibatalkan</span>';
        }
        return '<span class="badge-pending">' . htmlspecialchars($status) . '</span>';
    }
}

// ========== PAYMENT BADGE ==========
if (!function_exists('getPaymentBadge')) {
    function getPaymentBadge($method) {
        if ($method == 'cod') {
            return '<span class="badge-payment cod"><i class="fas fa-money-bill-wave"></i> COD</span>';
        } elseif ($method == 'transfer') {
            return '<span class="badge-payment transfer"><i class="fas fa-university"></i> Transfer</span>';
        }
        return '<span class="badge-payment">' . htmlspecialchars($method) . '</span>';
    }
}

// ========== STATUS BADGE UNTUK USER (public) ==========
if (!function_exists('getStatusBadgeUser')) {
    function getStatusBadgeUser($status) {
        if ($status == 'pending') {
            return '<span class="badge-status pending"><i class="fas fa-clock"></i> Pending</span>';
        } elseif ($status == 'processing') {
            return '<span class="badge-status processing"><i class="fas fa-cog"></i> Diproses</span>';
        } elseif ($status == 'completed') {
            return '<span class="badge-status completed"><i class="fas fa-check-circle"></i> Selesai</span>';
        } elseif ($status == 'cancelled') {
            return '<span class="badge-status cancelled"><i class="fas fa-times-circle"></i> Dibatalkan</span>';
        }
        return '<span class="badge-status pending"><i class="fas fa-clock"></i> Pending</span>';
    }
}

if (!function_exists('getPaymentBadgeUser')) {
    function getPaymentBadgeUser($method) {
        if ($method == 'cod') {
            return '<span class="badge-payment cod"><i class="fas fa-money-bill-wave"></i> COD (Bayar di Tempat)</span>';
        } elseif ($method == 'transfer') {
            return '<span class="badge-payment transfer"><i class="fas fa-university"></i> Transfer Bank</span>';
        }
        return '<span class="badge-payment">' . htmlspecialchars($method) . '</span>';
    }
}

if (!function_exists('getProgressStep')) {
    function getProgressStep($status) {
        switch ($status) {
            case 'pending': return 1;
            case 'processing': return 2;
            case 'completed': return 3;
            case 'cancelled': return 0;
            default: return 1;
        }
    }
}
