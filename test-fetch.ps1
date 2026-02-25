try {
    $r = Invoke-WebRequest -Uri 'http://localhost:3000/assets/proces-1-konzultace.png' -TimeoutSec 10
    Write-Host "Status: $($r.StatusCode)"
    Write-Host "Length: $($r.RawContentLength)"
} catch {
    Write-Host "Error: $($_.Exception.Message)"
}
