' === Excel Import Script ===
' Run this VBScript to automatically import all CSV files into Excel

Dim ExcelApp
Dim ExcelWorkbook
Dim ExcelSheet
Dim fso
Dim folder
Dim file

Set ExcelApp = CreateObject("Excel.Application")
ExcelApp.Visible = True
Set ExcelWorkbook = ExcelApp.Workbooks.Add

Set fso = CreateObject("Scripting.FileSystemObject")
Set folder = fso.GetFolder("/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38")

' Import Accreditation Bodies
Set ExcelSheet = ExcelWorkbook.Sheets(1)
ExcelSheet.Name = "Accreditation Bodies"
With ExcelWorkbook.Sheets("Accreditation Bodies").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Accreditation_Bodies.csv", _
    Destination:=ExcelWorkbook.Sheets("Accreditation Bodies").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Program Types
Set ExcelSheet = ExcelWorkbook.Sheets(2)
ExcelSheet.Name = "Program Types"
With ExcelWorkbook.Sheets("Program Types").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Program_Types.csv", _
    Destination:=ExcelWorkbook.Sheets("Program Types").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Course Categories
Set ExcelSheet = ExcelWorkbook.Sheets(3)
ExcelSheet.Name = "Course Categories"
With ExcelWorkbook.Sheets("Course Categories").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Course_Categories.csv", _
    Destination:=ExcelWorkbook.Sheets("Course Categories").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Programs
Set ExcelSheet = ExcelWorkbook.Sheets(4)
ExcelSheet.Name = "Programs"
With ExcelWorkbook.Sheets("Programs").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Programs.csv", _
    Destination:=ExcelWorkbook.Sheets("Programs").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Faculties
Set ExcelSheet = ExcelWorkbook.Sheets(5)
ExcelSheet.Name = "Faculties"
With ExcelWorkbook.Sheets("Faculties").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Faculties.csv", _
    Destination:=ExcelWorkbook.Sheets("Faculties").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Program Levels
Set ExcelSheet = ExcelWorkbook.Sheets(6)
ExcelSheet.Name = "Program Levels"
With ExcelWorkbook.Sheets("Program Levels").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Program_Levels.csv", _
    Destination:=ExcelWorkbook.Sheets("Program Levels").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Courses
Set ExcelSheet = ExcelWorkbook.Sheets(7)
ExcelSheet.Name = "Courses"
With ExcelWorkbook.Sheets("Courses").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Courses.csv", _
    Destination:=ExcelWorkbook.Sheets("Courses").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Course Prerequisites
Set ExcelSheet = ExcelWorkbook.Sheets(8)
ExcelSheet.Name = "Course Prerequisites"
With ExcelWorkbook.Sheets("Course Prerequisites").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Course_Prerequisites.csv", _
    Destination:=ExcelWorkbook.Sheets("Course Prerequisites").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Program Accreditations
Set ExcelSheet = ExcelWorkbook.Sheets(9)
ExcelSheet.Name = "Program Accreditations"
With ExcelWorkbook.Sheets("Program Accreditations").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Program_Accreditations.csv", _
    Destination:=ExcelWorkbook.Sheets("Program Accreditations").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Course Accreditations
Set ExcelSheet = ExcelWorkbook.Sheets(10)
ExcelSheet.Name = "Course Accreditations"
With ExcelWorkbook.Sheets("Course Accreditations").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Course_Accreditations.csv", _
    Destination:=ExcelWorkbook.Sheets("Course Accreditations").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Academic Years
Set ExcelSheet = ExcelWorkbook.Sheets(11)
ExcelSheet.Name = "Academic Years"
With ExcelWorkbook.Sheets("Academic Years").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Academic_Years.csv", _
    Destination:=ExcelWorkbook.Sheets("Academic Years").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Semesters
Set ExcelSheet = ExcelWorkbook.Sheets(12)
ExcelSheet.Name = "Semesters"
With ExcelWorkbook.Sheets("Semesters").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Semesters.csv", _
    Destination:=ExcelWorkbook.Sheets("Semesters").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Course Schedules
Set ExcelSheet = ExcelWorkbook.Sheets(13)
ExcelSheet.Name = "Course Schedules"
With ExcelWorkbook.Sheets("Course Schedules").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Course_Schedules.csv", _
    Destination:=ExcelWorkbook.Sheets("Course Schedules").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Staff
Set ExcelSheet = ExcelWorkbook.Sheets(14)
ExcelSheet.Name = "Staff"
With ExcelWorkbook.Sheets("Staff").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Staff.csv", _
    Destination:=ExcelWorkbook.Sheets("Staff").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

' Import Students
Set ExcelSheet = ExcelWorkbook.Sheets(15)
ExcelSheet.Name = "Students"
With ExcelWorkbook.Sheets("Students").QueryTables.Add(Connection:= _
    "TEXT;/Applications/MAMP/htdocs/nds/wp-content/plugins/nds-school-main/database-export-2026-02-05-18-57-38\\Students.csv", _
    Destination:=ExcelWorkbook.Sheets("Students").Range("$A$1"))
    .TextFileParseType = xlDelimited
    .TextFileCommaDelimiter = True
    .Refresh
End With

MsgBox "CSV files imported successfully! Follow the EXCEL_SETUP_GUIDE.txt to configure dropdowns."
