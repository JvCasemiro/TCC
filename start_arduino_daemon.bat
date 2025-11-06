@echo off
title Arduino Daemon - Controlador de LEDs
echo ========================================
echo Controle do Arduino Daemon
echo ========================================
echo.

cd /d "%~dp0"

REM Verificar se já está rodando
if exist "arduino_daemon.pid" (
    echo [AVISO] Daemon pode estar rodando!
    echo Arquivo PID encontrado: arduino_daemon.pid
    echo.
    echo Deseja parar o daemon? (S/N)
    choice /C SN /N
    if errorlevel 2 goto :end
    if errorlevel 1 call stop_arduino_daemon.bat
) else (
    echo Nenhuma instância do daemon em execução encontrada.
)

:end
echo.
echo Use o comando: python python\arduino_daemon.py para iniciar manualmente.
echo.
pause
