@echo off
echo Stopping PHP servers and MySQL...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM mysqld.exe >nul 2>&1
echo All servers stopped.
timeout /t 2 >nul