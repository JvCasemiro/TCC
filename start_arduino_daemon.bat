@echo off
title Arduino Daemon - Controlador de LEDs
echo ========================================
echo Arduino Daemon - Iniciando...
echo ========================================
echo.

cd /d "%~dp0"

REM Verificar se já está rodando
if exist "arduino_daemon.pid" (
    echo [AVISO] Daemon pode estar rodando!
    echo Arquivo PID encontrado: arduino_daemon.pid
    echo.
    echo Deseja parar o daemon anterior primeiro? (S/N)
    choice /C SN /N
    if errorlevel 2 goto :start
    if errorlevel 1 call stop_arduino_daemon.bat
)

:start
echo Iniciando daemon Python...
echo.
python python\arduino_daemon.py

if errorlevel 1 (
    echo.
    echo [ERRO] Falha ao iniciar daemon!
    echo.
    pause
    exit /b 1
)

pause
