@echo off
title Arduino Daemon - Status
echo ========================================
echo Arduino Daemon - Verificando Status
echo ========================================
echo.

cd /d "%~dp0"

if not exist "arduino_daemon.pid" (
    echo [STATUS] Daemon NAO esta rodando
    echo Arquivo PID nao encontrado.
    echo.
    echo Execute start_arduino_daemon.bat para iniciar
    echo.
    pause
    exit /b 1
)

REM Ler PID
set /p DAEMON_PID=<arduino_daemon.pid

echo [PID] %DAEMON_PID%

REM Verificar se o processo está rodando
tasklist /FI "PID eq %DAEMON_PID%" 2>nul | find /I /N "python.exe">nul
if "%ERRORLEVEL%"=="0" (
    echo [STATUS] Daemon ESTA RODANDO
    echo.
    echo Processo Python encontrado e ativo.
) else (
    echo [STATUS] Daemon NAO esta rodando
    echo.
    echo Arquivo PID existe mas o processo nao foi encontrado.
    echo Pode ter sido encerrado inesperadamente.
    echo.
    echo Execute start_arduino_daemon.bat para reiniciar
)

echo.

REM Verificar fila
if exist "arduino_queue.json" (
    echo [FILA] Arquivo de fila encontrado
    for %%A in (arduino_queue.json) do echo [FILA] Tamanho: %%~zA bytes
) else (
    echo [FILA] Arquivo de fila nao encontrado
)

echo.
pause
