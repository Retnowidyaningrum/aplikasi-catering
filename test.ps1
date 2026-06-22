# Cateringku - Local Test Script (PowerShell)
# Jalankan sebelum commit untuk memverifikasi tidak ada error sintaks PHP

Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "      CATERINGKU - LOCAL TEST SUITE" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

# Cari PHP executable secara otomatis
$phpExe = "php"
if (-not (Get-Command $phpExe -ErrorAction SilentlyContinue)) {
    $xamppPaths = @(
        "C:\xampp\php\php.exe",
        "C:\xampp72\php\php.exe",
        "C:\xampp80\php\php.exe"
    )
    $phpExe = $null
    foreach ($p in $xamppPaths) {
        if (Test-Path -LiteralPath $p) { $phpExe = $p; break }
    }
    if (-not $phpExe) {
        Write-Host "[ERROR] PHP tidak ditemukan di PATH atau di direktori XAMPP." -ForegroundColor Red
        Write-Host "        Install XAMPP atau tambahkan PHP ke PATH." -ForegroundColor Yellow
        exit 1
    }
}
Write-Host "  PHP : $phpExe" -ForegroundColor Gray
Write-Host ""

$totalErrors = 0
$totalFiles = 0

# 1. PHP Syntax Check
Write-Host "--- PHP SYNTAX CHECK (php -l) ---" -ForegroundColor Yellow
Write-Host ""

$phpFiles = Get-ChildItem -Path ".\" -Recurse -Filter "*.php" -File | Where-Object { $_.FullName -notmatch '\\.github\\' }

foreach ($file in $phpFiles) {
    $totalFiles++
    $result = & $phpExe -l $file.FullName 2>&1
    $output = $result -join "`n"
    if ($output -match "No syntax errors detected") {
        Write-Host "  [PASS] $($file.FullName)" -ForegroundColor Green
    } else {
        Write-Host "  [FAIL] $($file.FullName)" -ForegroundColor Red
        Write-Host "     $output" -ForegroundColor Red
        $totalErrors++
    }
}

Write-Host ""
Write-Host "-----------------------------------------------" -ForegroundColor Gray

# 2. Git Status Check
Write-Host ""
Write-Host "--- GIT STATUS ---" -ForegroundColor Yellow
Write-Host ""
if (Test-Path -LiteralPath ".\\.git") {
    git status
} else {
    Write-Host "  [WARN] Belum ada git repository." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "-----------------------------------------------" -ForegroundColor Gray

# Summary
Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "  SUMMARY" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Files checked : $totalFiles" -ForegroundColor White
Write-Host "  Errors found  : $totalErrors" -ForegroundColor White
Write-Host ""

if ($totalErrors -eq 0) {
    Write-Host "  [PASS] ALL CHECKS PASSED - Siap untuk commit!" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] $totalErrors ERROR(S) DITEMUKAN - Perbaiki sebelum commit!" -ForegroundColor Red
}

Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

if ($totalErrors -gt 0) { exit 1 } else { exit 0 }
