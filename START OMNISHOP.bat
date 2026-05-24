@echo off
title OmniShop - Starting...
color 0A
echo.
echo  ================================================
echo    OmniShop - OmniSpace 3D Events
echo    Solar and Storage Live Kenya 2026
echo  ================================================
echo.

REM Check if PHP is installed
php -v >nul 2>&1
if errorlevel 1 (
    echo  ERROR: PHP is not installed or not in your PATH.
    echo  Please ensure WAMP is running or PHP is installed.
    echo.
    pause
    exit
)

echo  Step 1 of 1: Starting your website...
echo.
echo  ================================================
echo   Your website is now running!
echo.
echo   Open your browser and go to:
echo   http://localhost:8080/solarandstorage
echo.
echo   Admin panel:
echo   http://localhost:8080/admin
echo   Password: Silversky#10
echo.
echo   Keep this window open while using the site.
echo   Close this window to stop the server.
echo  ================================================
echo.

REM Open the browser automatically
start http://localhost:8080/solarandstorage

REM Start the server
echo  Starting PHP server on http://localhost:8080...
php -S localhost:8080 index.php

pause
