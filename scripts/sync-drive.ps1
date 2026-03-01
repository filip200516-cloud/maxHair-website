# Stažení složky MaxHair z Google Drive (gdown)
# Odkaz: https://drive.google.com/drive/folders/1IN_Ezd0H_dy2UA9xbzVjM4UfLWlnw7Qk
# Po stažení jsou soubory v assets/drive-materials/

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
if (-not (Test-Path $ProjectRoot)) {
    $ProjectRoot = $PSScriptRoot
}
$OutDir = Join-Path $ProjectRoot "assets\drive-materials"
$DriveUrl = "https://drive.google.com/drive/folders/1IN_Ezd0H_dy2UA9xbzVjM4UfLWlnw7Qk"

if (-not (Test-Path (Join-Path $ProjectRoot "assets"))) {
    New-Item -ItemType Directory -Path (Join-Path $ProjectRoot "assets") -Force
}
if (-not (Test-Path $OutDir)) {
    New-Item -ItemType Directory -Path $OutDir -Force
}

$env:PYTHONIOENCODING = "utf-8"
Push-Location $ProjectRoot
try {
    python -m gdown --folder $DriveUrl -O $OutDir --remaining-ok
    Write-Host "Hotovo. Slozka: $OutDir"
} finally {
    Pop-Location
}
