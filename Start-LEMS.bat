@echo off
title LEMS Launcher
echo Starting Library Entrance Monitoring System (LEMS)...

:: 1. Ensure MySQL service is running
net start MySQL >nul 2>&1
if %errorlevel% neq 0 (
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "" /min "C:\xampp\mysql\bin\mysqld.exe"
    )
)

:: 2. Launch LEMS Backend Server silently in background
start "LEMS Server" /min php artisan serve --host=0.0.0.0 --port=8000

:: 3. Wait 2 seconds and open Kiosk automatically
timeout /t 2 /nobreak >nul
start http://localhost:8000/kiosk

echo ===================================================
echo   LEMS System is now RUNNING!
echo   Do not close this window while using the system.
echo ===================================================
