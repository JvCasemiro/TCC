@echo off
title Arduino Daemon - Parar Daemon
echo ========================================
echo Parando Arduino Daemon...
echo ========================================
echo.

cd /d "%~dp0"

if not exist "arduino_daemon.pid" (
    echo [AVISO] Arquivo PID nao encontrado.
    echo O daemon pode nao estar rodando.
    echo.
    pause
    exit /b 0
)

REM Ler PID
set /p DAEMON_PID=<arduino_daemon.pid

echo PID do daemon: %DAEMON_PID%
echo Encerrando processo...

REM Tentar encerrar o processo
taskkill /PID %DAEMON_PID% /F >nul 2>&1

if errorlevel 1 (
    echo [AVISO] Nao foi possivel encerrar o processo (pode ja estar fechado)
) else (
    echo [OK] Processo encerrado com sucesso
)

REM Remover arquivo PID
if exist "arduino_daemon.pid" (
    del "arduino_daemon.pid"
    echo [OK] Arquivo PID removido
)

REM Limpar fila
if exist "arduino_queue.json" (
    echo [] > arduino_queue.json
    echo [OK] Fila de comandos limpa
)

echo.
echo Daemon parado.
pause
