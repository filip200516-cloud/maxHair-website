<#
.SYNOPSIS
    Extracts device recording frames at 3 fps for Cursor to analyze.

.DESCRIPTION
    Parses a video (e.g. screen recording of your phone/tablet) into individual
    frames at 3 per second. Outputs to device-frames/ in your project so Cursor
    can read them as visual context for website development.

.PARAMETER Video
    Path to the video file (drag & drop or full path)

.PARAMETER OutputDir
    Output folder (default: device-frames, relative to project root)

.PARAMETER Fps
    Frames per second (default: 3)

.EXAMPLE
    .\device-to-cursor.ps1 -Video "C:\Users\me\Videos\phone-recording.mp4"
    .\device-to-cursor.ps1 "recording.mp4"
#>

param(
    [Parameter(Mandatory = $true, Position = 0)]
    [string]$Video,

    [string]$OutputDir = "device-frames",

    [double]$Fps = 3
)

$ErrorActionPreference = "Stop"

# Find project root (where we run from, or workspace root)
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = if (Test-Path (Join-Path $scriptDir "..\package.json")) {
    (Resolve-Path (Join-Path $scriptDir "..")).Path
} else {
    (Get-Location).Path
}

# Check FFmpeg
$ffmpeg = Get-Command ffmpeg -ErrorAction SilentlyContinue
if (-not $ffmpeg) {
    Write-Host "FFmpeg not found. Install with: winget install FFmpeg" -ForegroundColor Red
    exit 1
}

# Resolve input
$inputPath = $PSCmdlet.SessionState.Path.GetUnresolvedProviderPathFromPSPath($Video)
if (-not (Test-Path $inputPath)) {
    Write-Host "Video not found: $Video" -ForegroundColor Red
    exit 1
}

# Output in project root so Cursor sees it
$outPath = Join-Path $projectRoot $OutputDir
if (Test-Path $outPath) {
    Remove-Item -Path (Join-Path $outPath "*") -Force -ErrorAction SilentlyContinue
} else {
    New-Item -ItemType Directory -Path $outPath -Force | Out-Null
}

$outputPattern = Join-Path $outPath "frame_%04d.jpg"
$videoName = [System.IO.Path]::GetFileNameWithoutExtension($inputPath)
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm"

Write-Host ""
Write-Host "  Device-to-Cursor" -ForegroundColor Cyan
Write-Host "  ================" -ForegroundColor Cyan
Write-Host "  Video:  $videoName" -ForegroundColor Gray
Write-Host "  Rate:   $Fps frames/sec" -ForegroundColor Gray
Write-Host "  Output: $outPath" -ForegroundColor Gray
Write-Host ""

# Extract frames (q:v 2 = high quality JPEG)
# -loglevel error suppresses version banner/progress so PowerShell doesn't treat stderr as failure
$ffmpegArgs = @("-loglevel", "error", "-i", $inputPath, "-vf", "fps=$Fps", "-q:v", "2", $outputPattern, "-y")
& ffmpeg $ffmpegArgs 2>$null

$frames = Get-ChildItem -Path $outPath -Filter "frame_*.jpg" | Sort-Object Name
$frameCount = $frames.Count

# Build frame index with timestamps (each frame = 1/Fps seconds)
$frameList = @()
for ($i = 0; $i -lt $frameCount; $i++) {
    $sec = [math]::Round($i / $Fps, 1)
    $frameList += "  - frame_$($i.ToString('0000')).jpg (${sec}s)"
}

# Create context file for Cursor
$contextContent = @"
# Device Recording – Visual Context for Cursor

**Source:** $videoName  
**Extracted:** $timestamp  
**Frames:** $frameCount at $Fps fps  

## Purpose
These frames show how the website/app looks on the actual device (phone, tablet, etc.). Use them to:
- Match the layout to the real device appearance
- Fix responsive/breakpoint issues
- Recreate UI elements accurately
- Debug visual bugs

## Frame Index (time → file)
$($frameList -join "`n")

## How to use in Cursor
1. **Drag this folder** into Cursor Chat, or
2. **@ mention** this folder: \`@device-frames\`
3. **Prompt example:** "These frames show my site on a real device. Fix the layout to match frame_0015.jpg" or "Recreate the header from these device frames."

"@

$contextContent | Out-File -FilePath (Join-Path $outPath "_CONTEXT.md") -Encoding UTF8

# Create prompt template
$promptTemplate = @"
---
Copy this into Cursor Chat after extraction:
---

@device-frames These frames are from a screen recording of my website on a real device (3 fps). Use them as visual reference to:
1. Understand the actual layout and appearance
2. Fix any responsive/breakpoint issues
3. Match the design to what users see on device

[Add your specific request here, e.g.: "Fix the header overlap in frame_0020.jpg" or "The mobile menu doesn't match - update the CSS"]
"@

$promptTemplate | Out-File -FilePath (Join-Path $outPath "_PROMPT_TEMPLATE.txt") -Encoding UTF8

# Summary
Write-Host "  Done. Extracted $frameCount frames." -ForegroundColor Green
Write-Host ""
Write-Host "  Next steps:" -ForegroundColor Yellow
Write-Host "  1. In Cursor: type @device-frames and select the folder" -ForegroundColor White
Write-Host "  2. Or drag the device-frames folder into Chat" -ForegroundColor White
Write-Host "  3. Prompt: 'These frames show my site on a real device. Fix the layout to match.'" -ForegroundColor White
Write-Host ""
Write-Host "  Context file: $outPath\_CONTEXT.md" -ForegroundColor Gray
Write-Host ""
