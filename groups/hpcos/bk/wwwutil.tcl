#!/usr/local/bin/tclsh8.4

set g(width) "'80%'"
set g(font1) "style='font-size:16;font-family:黑体'"
set g(font2) "style='font-size:11pt;font-family:宋体'"
set g(font3) "style='font-size:10.0pt'"
set g(font4) "style='font-size:9.0pt'"
set g(datapath) "/home/majie/web/hpcos/data"
set g(webpath) "/home/majie/public_html/hpcos"

proc Link {title link {largs ""}} {
    return "<a $largs href='$link'>$title</a>"
}

proc Image {file {largs ""}} {
    return "<img $largs src='$file'>"
}

proc Table {content {largs ""}} {
    global g
    return "<table align='center' width=$g(width) $largs>\n$content\n</table>"
}

proc Item {content {largs {}}} {
    return "<td $largs>\n$content\n</td>"
}

proc BR {} {
    return "<br>"
}

proc BlankCol {{w 1%}} {
    return [Item "" "width='$w'"]
}

proc Span {content {largs ""}} {
    return "<span $largs>\n$content\n</span>"
}

proc Center {content {largs ""}} {
    return "<center $largs>\n$content\n</center>"
}

proc Para {content {largs ""}} {
    return "<p $largs>\n$content\n</p>"
}

proc IncludeFile {filename {largs ""}} {
    global g

    set fp [open $g(datapath)/$filename r]
    append tmp "<p $largs>&nbsp;&nbsp;&nbsp;&nbsp;"
    while {![eof $fp]} {
        set Line [string trim [gets $fp]]
        if {[string length $Line] <= 0} then {
            set cnt 0
            append tmp "</p>\n<p $largs>&nbsp;&nbsp;&nbsp;&nbsp;"
        } else {
            append tmp "$Line"
        }
    }
    append tmp "</p>"
    close $fp
    return $tmp
}

proc WriteFile {filename content} {
    global g

    set fp [open $g(webpath)/$filename w]
    puts $fp $content
    close $fp
}

proc OpenData {filename} {
    global g

    set fp [open $g(datapath)/$filename r]
    return $fp
}

proc Header {{title {}}} {
    global g

    append tmp "<html>"
    append tmp "\n<meta http-equiv='Content-Type' content='text/html; charset=gb2312'>"
    append tmp "\n<meta name=Generator content='Ma Jie'>"
    append tmp "\n<meta name=Originator content='Ma Jie'>"
    if {[string length $title] > 0} then {
        append tmp "\n<head>"
        append tmp "\n<title>$title</title>"
        append tmp "\n</head>"
    }
    append tmp "\n<body background='images/background.jpg' bgproperties='fixed'>"

    return $tmp
}

proc Tail {} {
    global g

    append tmp "<table width=$g(width) align=center>"
    append tmp "\n<tr>"
    append tmp "\n<td bgcolor='#28549E'>"
    set mydate [exec date "+%Y年%m月%d日"]
    append tmp "\n<p style='font-size:9.0pt;color:white'>最后更新日期：$mydate</p>"
    append tmp "\n</td>"
    append tmp "\n</tr>"
    append tmp "\n<tr>"
    append tmp "\n<td align=center>"
    append tmp "\n<br>"
    append tmp "\n<p> <span style='font-size:9.0pt;color:gray'>"
    append tmp "\n本站版权归智能中心操作系统组所有"
    append tmp "\n</span><br>"
    append tmp "\n<span style='font-size:9.0pt;color:gray;font-family:Arial'>"
    append tmp "\nCOPYRIGHT &copy for HPCOS, NCIC All Rights Reserved"
    append tmp "\n</span></p>"
    append tmp "\n</td>"
    append tmp "\n</tr>"
    append tmp "\n</table>"
    append tmp "\n</body>"
    append tmp "\n</html>"

    return $tmp
}

