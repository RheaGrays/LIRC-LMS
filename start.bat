@echo off
cd /d "%~dp0"
set PATH=C:\xampp\php;%PATH%

:: Check if Laravel server is already running on port 8000
netstat -ano | findstr :8000 >nul
if %errorlevel% neq 0 (
    :: Not running, start it in the background
    start /b php artisan serve --port=8000
    
    :: Wait 3 seconds for the server to fully spin up
    timeout /t 3 /nobreak >nul
)

:: Launch Google Chrome in borderless "App" mode for a native desktop feel
start chrome.exe --app="http://127.0.0.1:8000/%~1"
