Option Explicit

' Configurações
Const APP_NAME = "ArduinoDaemon"
Const LOG_FILE = "arduino_daemon.log"
Const PYTHON_SCRIPT = "python\\arduino_daemon.py"

' Objetos
Dim fso, WshShell, currentDir, logFile

' Inicialização
Set fso = CreateObject("Scripting.FileSystemObject")
Set WshShell = CreateObject("WScript.Shell")
currentDir = fso.GetParentFolderName(WScript.ScriptFullName)
WScript.Echo "Diretório atual: " & currentDir
WshShell.CurrentDirectory = currentDir

' Função para registrar logs
Sub LogMessage(message)
    On Error Resume Next
    Dim ts, logStream
    Set logStream = fso.OpenTextFile(currentDir & "\" & LOG_FILE, 8, True) ' 8 = ForAppending, True = Create if not exists
    ts = "[" & Now & "] [VBS] "
    logStream.WriteLine ts & message
    logStream.Close
    WScript.Echo message
    If Err.Number <> 0 Then
        WScript.Echo "Erro ao escrever no log: " & Err.Description
        Err.Clear
    End If
    On Error Goto 0
End Sub

' Verificar se o script Python existe
If Not fso.FileExists(fso.BuildPath(currentDir, PYTHON_SCRIPT)) Then
    LogMessage "ERRO: Arquivo do daemon não encontrado: " & PYTHON_SCRIPT
    WScript.Quit 1
End If

' Verificar se já está rodando
If fso.FileExists("arduino_daemon.pid") Then
    On Error Resume Next
    Dim pidFile, pid
    Set pidFile = fso.OpenTextFile("arduino_daemon.pid", 1) ' 1 = ForReading
    If Err.Number = 0 Then
        pid = pidFile.ReadLine
        pidFile.Close
        
        ' Verificar se o processo ainda está ativo
        Dim process, processList, processName
        Set processList = GetObject("winmgmts:").ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId = " & pid)
        
        For Each process In processList
            processName = LCase(process.Name)
            If InStr(processName, "python") > 0 Then
                Dim cmdLine : cmdLine = LCase(process.CommandLine)
                If InStr(cmdLine, LCase("arduino_daemon.py")) > 0 Then
                    LogMessage "O daemon já está em execução (PID: " & pid & ")"
                    WScript.Quit 0
                End If
            End If
        Next
        
        ' Se chegou aqui, o PID existe mas o processo não está mais ativo
        fso.DeleteFile "arduino_daemon.pid"
        LogMessage "Removido PID de um processo antigo: " & pid
    Else
        LogMessage "Aviso: Não foi possível ler o arquivo PID: " & Err.Description
        Err.Clear
    End If
    On Error Goto 0
End If

' Registrar início da execução
LogMessage "Iniciando " & APP_NAME

' Encontrar o Python
Dim pythonPath, pythonPaths(3), i, path
pythonPaths(0) = "python"  # Tenta o python do PATH
pythonPaths(1) = "C:\Python313\python.exe"  # Python 3.13
pythonPaths(2) = "C:\Python312\python.exe"  # Python 3.12
pythonPaths(3) = "C:\Python311\python.exe"  # Python 3.11

pythonPath = ""
For i = 0 To UBound(pythonPaths)
    On Error Resume Next
    path = WshShell.ExpandEnvironmentStrings(pythonPaths(i))
    If fso.FileExists(path) Then
        pythonPath = path
        Exit For
    ElseIf i = 0 Then  # Para 'python' no PATH, tenta executar para verificar
        Set exec = WshShell.Exec("cmd /c where python")
        Do While exec.Status = 0
            WScript.Sleep 100
        Loop
        If exec.ExitCode = 0 Then
            pythonPath = "python"
            Exit For
        End If
    End If
    On Error Goto 0
Next

If pythonPath = "" Then
    ' Tenta encontrar no registro
    On Error Resume Next
    pythonPath = WshShell.RegRead("HKEY_LOCAL_MACHINE\SOFTWARE\Python\PythonCore\3.13\InstallPath\") & "python.exe"
    If Err.Number <> 0 Then
        Err.Clear
        pythonPath = WshShell.RegRead("HKEY_LOCAL_MACHINE\SOFTWARE\Python\PythonCore\3.12\InstallPath\") & "python.exe"
        If Err.Number <> 0 Then
            Err.Clear
            pythonPath = WshShell.RegRead("HKEY_LOCAL_MACHINE\SOFTWARE\Python\PythonCore\3.11\InstallPath\") & "python.exe"
            If Err.Number <> 0 Then
                LogMessage "ERRO: Python não encontrado. Certifique-se de que o Python está instalado e no PATH."
                WScript.Quit 1
            End If
        End If
    End If
    On Error Goto 0
End If

LogMessage "Usando Python em: " & pythonPath

' Verificar se o Python está acessível
On Error Resume Next
Set exec = WshShell.Exec(""""" & pythonPath & """ --version")
Do While exec.Status = 0
    WScript.Sleep 100
Loop
If exec.ExitCode <> 0 Then
    LogMessage "ERRO: Não foi possível executar o Python. Verifique a instalação."
    WScript.Quit 1
End If
On Error Goto 0

' Montar o comando
Dim command, exec, processId, exitCode
command = """" & pythonPath & """ """ & fso.BuildPath(currentDir, PYTHON_SCRIPT) & """"
LogMessage "Executando: " & command

' Executar o comando em uma janela oculta
Set exec = WshShell.Exec("cmd /c " & command & " && echo %ERRORLEVEL% > " & currentDir & "\arduino_daemon.exitcode")
processId = exec.ProcessID

' Salvar o PID
On Error Resume Next
Set pidFile = fso.CreateTextFile("arduino_daemon.pid", True)
pidFile.WriteLine processId
pidFile.Close
If Err.Number <> 0 Then
    LogMessage "AVISO: Não foi possível salvar o arquivo PID: " & Err.Description
    Err.Clear
End If
On Error Goto 0

' Verificar se o processo foi iniciado
On Error Resume Next
Set processList = GetObject("winmgmts:").ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId = " & processId)
If processList.Count > 0 Then
    LogMessage "Daemon iniciado com sucesso (PID: " & processId & ")"
    WScript.Quit 0
Else
    LogMessage "ERRO: Falha ao iniciar o daemon. Verifique o arquivo de log para mais detalhes."
    WScript.Quit 1
End If
