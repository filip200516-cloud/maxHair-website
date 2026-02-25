<#
.SYNOPSIS
    Installs llm and llm-video-frames for video analysis with GPT-4o.
#>

Write-Host "Installing llm and llm-video-frames..." -ForegroundColor Cyan
pip install llm llm-video-frames

if ($LASTEXITCODE -eq 0) {
    Write-Host "`nDone. Next steps:" -ForegroundColor Green
    Write-Host "  1. Set OpenAI key: llm keys set openai" -ForegroundColor Yellow
    Write-Host "  2. Run: llm -f video-frames:your-video.mp4 'describe this video' -m gpt-4o" -ForegroundColor Yellow
} else {
    Write-Host "Installation failed. Ensure FFmpeg is installed: winget install FFmpeg" -ForegroundColor Red
}
