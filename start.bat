@echo off
title Visual Schedule System Launcher
color 0A

echo ===================================================
echo   Starting Visual Schedule and Task Management...
echo ===================================================

:: 1. Automatically start XAMPP MySQL in the background (if not already running)
echo [1/4] Starting MySQL Database...
start "" /min "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone

:: 2. Start the PHP Backend REST API on Port 5000
echo [2/4] Starting Backend Server (http://localhost:5000)...
start "Backend API - Port 5000" cmd /k "cd /d "%~dp0backend" && C:\xampp\php\php.exe -S localhost:5000"

:: 3. Start the Frontend Server on Port 3000
echo [3/4] Starting Frontend Server (http://localhost:3000)...
start "Frontend UI - Port 3000" cmd /k "cd /d "%~dp0frontend" && C:\xampp\php\php.exe -S localhost:3000"

:: 4. Wait 2 seconds for servers to initialize, then open the browser
echo [4/4] Opening Browser...
timeout /t 2 /nobreak >nul
start http://localhost:3000/index.html