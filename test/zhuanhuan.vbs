Dim FileName, Find, ReplaceWith, FileContents, dFileContents   
Find = WScript.Arguments(0)   
ReplaceWith = WScript.Arguments(1)   
FileName = WScript.Arguments(2)   
 
'读取文件   
FileContents = GetFile(FileName)   
 
'用“替换内容”替换文件中所有“查找内容”   
dFileContents = replace(FileContents, Find, ReplaceWith, 1, -1, 1)   
 
'比较源文件和替换后的文件   
if dFileContents <> FileContents Then   
'保存替换后的文件   
WriteFile FileName, dFileContents   
 
Wscript.Echo "Replace done."   
If Len(ReplaceWith) <> Len(Find) Then   
'计算替换总数   
Wscript.Echo _   
( (Len(dFileContents) - Len(FileContents)) / (Len(ReplaceWith)-Len(Find)) ) & _   
" replacements."   
End If   
Else   
Wscript.Echo "Searched string Not In the source file"   
End If   
 
'读取文件   
function GetFile(FileName)   
If FileName<>"" Then   
Dim FS, FileStream   
Set FS = CreateObject("Scripting.FileSystemObject")   
on error resume Next   
Set FileStream = FS.OpenTextFile(FileName)   
GetFile = FileStream.ReadAll   
End If   
End Function   
 
'写文件   
function WriteFile(FileName, Contents)   
Dim OutStream, FS   
 
on error resume Next   
Set FS = CreateObject("Scripting.FileSystemObject")   
Set OutStream = FS.OpenTextFile(FileName, 2, True)   
OutStream.Write Contents   
End Function