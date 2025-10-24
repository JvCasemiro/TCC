Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)

' Verificar se já está rodando
If CreateObject("Scripting.FileSystemObject").FileExists("arduino_daemon.pid") Then
    WScript.Quit 0
End If

' Executar daemon em background (janela oculta)
WshShell.Run "python python\arduino_daemon.py", 0, False

WScript.Quit 0
