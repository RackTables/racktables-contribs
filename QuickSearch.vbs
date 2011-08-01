'======================================================================
'Racktables QuickSearch VBScript (Windows only)
'By goran.tornqvist@cypoint.se
'License: Freeware - Do what you please with it :)
'Description: Performs a quick search for an object or a hostname in racktables and displays its ip address.
'INSTALL:
'- Download and install MySQL Connector/ODBC: http://dev.mysql.com/downloads/connector/odbc/
'- Put the script on your harddrive and rename it from .txt to .vbs
'- Create a shortcut on your desktop to "wscript.exe c:\path\to\script.vbs"
'- Right click the shortcut and pin it to the taskbar for a quick way to start the script
'- Change the strDSN variable below (server/database/user/password).
'  The mysql user should preferably be a read only user and only allow SELECT on specific columns, see strSQL below to tighten permissions.
'  SECURITY NOTE: Be aware - Script bypasses racktables authentication so make sure you don´t break your company´s security policy ;)
'======================================================================

Const strDSN = "Driver={MySQL ODBC 5.1 Driver};Server=server;Database=database; User=user;Password=password;Option=3;"

SearchString = InputBox("Enter object name or host name","Racktables QuickSearch","") 

If SearchString <> "" Then

strSQL = "select " & _
	"	RackObject.name as ipaddr_name, " & _
	"	CONVERT(INET_NTOA(IPv4Allocation.ip), CHAR) as ipaddr, " & _
	"	CONCAT(IFNULL(IPv4Allocation.name, 'unknown'), '/', IPv4Allocation.type) as comment " & _
	"from IPv4Allocation " & _
	"	left join RackObject on IPv4Allocation.object_id = RackObject.id " & _
	"where  RackObject.name like '%" & SearchString & "%'" & _
	"union " & _
	"select " & _
	"	IPv4Address.name as ipaddr_name, " & _
	"	CONVERT(INET_NTOA(IPv4Address.ip), CHAR) as ipaddr, " & _
	"	CONCAT('reserved: ', IPv4Address.reserved) as comment " & _
	"from IPv4Address " & _
	"where IPv4Address.name like '%" & SearchString & "%'"


Set objConn = CreateObject("ADODB.Connection")
objConn.Open strDSN

Set objRs = objConn.Execute(strSQL)

If (objRs.BOF AND objRs.EOF) Then
	Matches = "No matches on search string " & SearchString & "."
Else

	Do While NOT objRs.EOF
		If objRs.Fields("comment").Value <> "" Then
			Comment = " (" & objRs.Fields("comment").Value & ")"
		Else
			Comment = ""
		End If
		Chars = 35 - Len(objRs.Fields("ipaddr_name").Value)
		StrTab = ""
		For i = 0 To Chars
			StrTab = StrTab & " "
		Next
		Matches = Matches & objRs.Fields("ipaddr_name").Value & StrTab & objRs.Fields("ipaddr").Value & Comment & vbCrLf 


		objRs.MoveNext
	Loop

End If

objRs.Close
Set objRs = Nothing

objConn.Close
Set objConn = Nothing

MsgBox Matches,,"Racktables QuickSearch"

Else

MsgBox "Search aborted!",,"Racktables QuickSearch"

End If