@echo off
cd /d %~dp0
start "" /B python python/light_controller_service.py
exit 0
