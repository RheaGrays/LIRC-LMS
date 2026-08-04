Set WshShell = CreateObject("WScript.Shell")
' Run the start.bat silently (0 = hide window) and pass "kiosk" as the argument
WshShell.Run chr(34) & "d:\LEMS-Laravel\start.bat" & Chr(34) & " kiosk", 0
Set WshShell = Nothing
