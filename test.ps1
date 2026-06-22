# Cateringku - Local Test Script (PowerShell)
# Jalankan sebelum commit untuk memverifikasi tidak ada error sintaks PHP

Write-Host "══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "      CATERINGKU - LOCAL TEST SUITE" -ForegroundColor Cyan
Write-Host "══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$totalErrors = 0
$totalFiles = 0

# 1. PHP Syntax Check
Write-Host "┌─────────────────────────────────────────────┐" -ForegroundColor Yellow
Write-Host "│  PHP SYNTAX CHECK (php -l)                 │" -ForegroundColor Yellow
Write-Host "└─────────────────────────────────────────────┘" -ForegroundColor Yellow
Write-Host ""

$phpFiles = Get-ChildItem -Path ".\" -Recurse -Filter "*.php" -File | Where-Object { $_.FullName -notmatch '\\.github\\' }

foreach ($file in $phpFiles) {
    $totalFiles++
    $result = php -l $file.FullName 2>&1
    $output = $result -join "`n"
    if ($output -match "No syntax errors detected") {
        Write-Host "  ✅ $($file.FullName)" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $($file.FullName)" -ForegroundColor Red
        Write-Host "     $output" -ForegroundColor Red
        $totalErrors++
    }
}

Write-Host ""
Write-Host "───────────────────────────────────────────────" -ForegroundColor Gray

# 2. Git Status Check
Write-Host ""
Write-Host "┌─────────────────────────────────────────────┐" -ForegroundColor Yellow
Write-Host "│  GIT STATUS                                │" -ForegroundColor Yellow
Write-Host "└─────────────────────────────────────────────┘" -ForegroundColor Yellow
Write-Host ""
if (Test-Path -LiteralPath ".\\.git") {
    git status
} else {
    Write-Host "  ⚠️  Belum ada git repository." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "───────────────────────────────────────────────" -ForegroundColor Gray

# Summary
Write-Host ""
Write-Host "══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  SUMMARY" -ForegroundColor Cyan
Write-Host "══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Files checked : $totalFiles" -ForegroundColor White
Write-Host "  Errors found  : $totalErrors" -ForegroundColor White
Write-Host ""

if ($totalErrors -eq 0) {
    Write-Host "  ✅ ALL CHECKS PASSED - Siap untuk commit!" -ForegroundColor Green
} else {
    Write-Host "  ❌ $totalErrors ERROR(S) DITEMUKAN - Perbaiki sebelum commit!" -ForegroundColor Red
}

Write-Host ""
Write-Host "══════════════════════════════════════════════" -EdgeColor Cyan
Write-Host ""

if ($totalErrors -gt 0) { exit 1 } else { exit 0 }
