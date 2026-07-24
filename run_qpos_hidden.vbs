Set WshShell = CreateObject("WScript.Shell")
' This runs your start_app.bat without a visible window
WshShell.Run chr(34) & "C:\xampp\htdocs\qpos\qpos\start_app.bat" & chr(34), 0
Set WshShell = Nothing