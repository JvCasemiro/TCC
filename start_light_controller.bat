@echo off
title Arduino Daemon Controller
cd /d "%~dp0"

REM Verificar se já está rodando
if exist "arduino_daemon.pid" (
    echo Daemon ja esta rodando!
    pause
    exit /b 0
)

echo Iniciando Arduino Daemon...
python python\arduino_daemon.py
