#!/usr/local/bin/tclsh8.4
source wwwutil.tcl

proc main {} {
    global g

    append content [Header "国家智能计算机研究开发中心 操作系统组"]

# 1. Headerline News and News.
    set headline [Item [genHeadline] "width=214 valign=top"]
    set news [Item [genNews] "valign=top"]
    append content [Table $headline[BlankCol 5%]$news]

#2. Introduction and Projects.
    set research [Item [genResearch] "width=120 align='center' valign=top"]
    set intro [Item \
              [IncludeFile "intro.txt" $g(font2)] \
              "width=70% valign=top"]
    set life [Item [genLife] "width=120 align='center' valign=top"]

    set items [BlankCol]\n
    append items $research\n
    append items [BlankCol 5%]\n
    append items $intro\n
    append items [BlankCol 5%]\n
    append items $life\n

    append content [BR]\n[Table $items]\n[Tail]

    return $content
}

proc genHeadline {} {
    global g

    set fp [OpenData news.txt]
    while {![eof $fp]} {
        set Line [string trim [gets $fp]]
        if {[llength $Line] > 2} then {
            break
        }
    }
    set img [Image "images/news/[lindex $Line 2]"]
    set link [Link [lindex $Line 1] [lindex $Line 3] \
                   "$g(font1) target='_blank'"]
    set iheadline [Center $img\n$link]
    close $fp
    return $iheadline
}

proc genNews {} {
    global g

    set fp [OpenData news.txt]
    append news [Link "动态信息" "news/index.html" "$g(font1)"]
    set tmp ""
    for {set i 0} {$i < 7} {incr i} {
        if {[eof $fp]} {
            break
        }
        set Line [string trim [gets $fp]]
        set date [lindex $Line 0]
        set content [lindex $Line 1]
        set link [lindex $Line 3]
        if {[string length $link] <= 0} then {
            append tmp "&nbsp;&nbsp;$date&nbsp;$content[BR]"
        } else {
            append tmp "&nbsp;&nbsp;$date&nbsp;[Link $content $link][BR]"
        }
    }

    append news [Para $tmp $g(font3)]

    if {![eof $fp]} {
        set tmp [Link "更多..." "news/index.html" $g(font4)]
        append news [Table [BlankCol 85%][Item $tmp]]
    }

    close $fp

    return $news
}

proc genResearch {} {
    global g

    set fp [OpenData research.txt]
    for {set i 0} {$i < 5} {incr i} {
        if {[eof $fp]} {
            break
        }
        set Line [string trim [gets $fp]]
        if {[string length $Line] <= 0} then {
            incr i -1
            continue
        }
        if {[string index $Line 0] != "\["} then {
            incr i -1
            continue
        }
        set date [lindex $Line 0]
        set title [lindex $Line 1]
        set pic [lindex $Line 2]
        set link [lindex $Line 3]
        if {[string length $link] <= 0} then {
            append tmp [Image "images/R/$pic"]\n
            append tmp [Span $title $g(font3)]\n
        } else {
            append tmp [Image "images/R/$pic"]\n
            append tmp [Link [Span $title $g(font3)] $link "target='_blank'"]\n
        }
    }

    append res $tmp

    if {![eof $fp]} {
        append res "[BR][BR]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        append res [Link "更多..." "research/index.html" $g(font4)]
    }

    close $fp

    return $res
}

proc genLife {} {
    global g

    set fp [OpenData life.txt]
    for {set i 0} {$i < 5} {incr i} {
        if {[eof $fp]} {
            break
        }
        set Line [string trim [gets $fp]]
        if {[string length $Line] <= 0} then {
            incr i -1
            continue
        }
        if {[string index $Line 0] != "\["} then {
            incr i -1
            continue
        }
        set date [lindex $Line 0]
        set title [lindex $Line 1]
        set pic [lindex $Line 2]
        set link [lindex $Line 3]
        if {[string length $link] <= 0} then {
            append tmp [Image "images/A/$pic"]\n
            append tmp [Span $title $g(font3)]\n
        } else {
            append tmp [Image "images/A/$pic"]\n
            append tmp [Link [Span $title $g(font3)] $link "target='_blank'"]\n
        }
    }

    append life $tmp

    if {![eof $fp]} {
        append life "[BR][BR]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        append life [Link "更多..." "life/index.html" $g(font4)]
#        append life [Table [BlankCol 85%][Item $tmp]]
    }

    close $fp

    return $life
}

#puts [main]
WriteFile main.html [main]

