@echo off
title Listing ERP Server
echo ====================================================
echo          Starting Listing ERP (Supabase Connected)
echo ====================================================
echo.
echo Website URL: http://127.0.0.1:8080
echo.
echo Opening browser...
start http://127.0.0.1:8080
echo.
echo Press Ctrl + C to stop the server anytime.
echo ====================================================
echo.

.\php-local\php.exe -S 127.0.0.1:8080 -t public
pause
