<#
.SYNOPSIS
    Extracts frames from a video file for use with Cursor's vision models.

.DESCRIPTION
    Uses FFmpeg to extract frames at a specified rate. Drag the resulting
    images into Cursor Chat/Composer for analysis.

.PARAMETER InputVideo
    Path to the input video file (.mp4, .mov, etc.)

.PARAMETER OutputDir
    Directory for output frames (default: frames)

.PARAMETER Fps
    Frames per second to extract (default: 1 = 1 frame/sec)

.EXAMPLE
    .\extract-frames.ps1 -InputVideo "bug-recording.mp4"
    .\extract-frames.ps1 -InputVideo "demo.mp4" -Fps 2 -OutputDir "demo_frames"
#>

param(
    [Parameter(Mandatory = $true)]
    [string]$InputVideo,

    [string]$OutputDir = "frames",

    [double]$Fps = 1
)

# Check FFmpeg
$ffmpeg = Get-Command ffmpeg -ErrorAction SilentlyContinue
if (-not $ffmpeg) {
    Write-Error "FFmpeg not found. Install with: winget install FFmpeg"
    exit 1
}

# Resolve paths
$inputPath = $PSCmdlet.SessionState.Path.GetUnresolvedProviderPathFromPSPath($InputVideo)
if (-not (Test-Path $inputPath)) {
    Write-Error "Input video not found: $InputVideo"
    exit 1
}

$outPath = Join-Path (Get-Location) $OutputDir
if (-not (Test-Path $outPath)) {
    New-Item -ItemType Directory -Path $outPath -Force | Out-Null
}

$outputPattern = Join-Path $outPath "frame_%04d.jpg"

Write-Host "Extracting frames from: $inputPath" -ForegroundColor Cyan
Write-Host "Output: $outPath ($Fps fps)" -ForegroundColor Cyan

& ffmpeg -loglevel error -i $inputPath -vf "fps=$Fps" -q:v 2 $outputPattern -y 2>$null

$frameCount = (Get-ChildItem -Path $outPath -Filter "frame_*.jpg").Count
Write-Host "Done. Extracted $frameCount frames to $outPath" -ForegroundColor Green
Write-Host "Drag these images into Cursor Chat and ask: 'Based on these frames, analyze/recreate this layout.'" -ForegroundColor Yellow
