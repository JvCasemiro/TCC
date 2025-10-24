@echo off
REM Inicia o daemon em background (sem janela visível)

cd /d "%~dp0"

REM Verificar se já está rodando
if exist "arduino_daemon.pid" (
    exit /b 0
)

REM Iniciar daemon em background sem mostrar janela
start /B pythonw python\arduino_daemon.py > nul 2>&1

REM Aguardar daemon inicializar (2 segundos)
timeout /t 2 /nobreak > nul

REM Verificar se iniciou com sucesso
if exist "arduino_daemon.pid" (
    echo Daemon iniciado com sucesso em background
) else (
    echo Erro ao iniciar daemon
    exit /b 1
)

exit /b 0
