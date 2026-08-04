Set WshShell = CreateObject("WScript.Shell")
' Run the start.bat silently (0 = hide window) and pass "admin/login" as the argument
WshShell.Run chr(34) & "d:\LEMS-Laravel\start.bat" & Chr(34) & " admin/login", 0
Set WshShell = Nothing
