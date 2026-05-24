@echo off
setlocal
set "PHP_EXE=c:\wamp64\bin\php\php8.3.14\php.exe"
if not exist "%PHP_EXE%" (
    echo PHP 8.3 not found at %PHP_EXE%
    echo Switch WAMP to PHP 8.3 or update PHP_EXE in artisan.bat
    exit /b 1
)
"%PHP_EXE%" "%~dp0artisan" %*
