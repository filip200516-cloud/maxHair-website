@echo off
REM Device-to-Cursor: Extract 3 fps from video for Cursor
REM Usage: Double-click and enter path, or: device-to-cursor.bat "video.mp4"

cd /d "%~dp0"

if "%~1"=="" (
    set /p VIDEO="Drag video here or paste path: "
) else (
    set "VIDEO=%~1"
)

powershell -ExecutionPolicy Bypass -File "%~dp0device-to-cursor.ps1" -Video "%VIDEO%"
pause
