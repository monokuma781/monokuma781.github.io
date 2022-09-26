#!/usr/local/bin/tclsh8.4
source wwwutil.tcl

proc research {} {
    global g

    set fp [OpenData research.txt]
    append content [Header "科研工作"]

    set itemcnt 0
    set item2 ""
    set order 1
    while {![eof $fp]} {
        set Line [string trim [gets $fp]]
        if {[string length $Line] <= 0} then {
            append item2 "<p>&nbsp;&nbsp;&nbsp;&nbsp;"
            continue
        }
        if {[string index $Line 0] != "\["} then {
            append item2 $Line
        } else {
            set tmp ""
            set date [lindex $Line 0]
            set title [lindex $Line 1]
            set pic [lindex $Line 2]
            set link [lindex $Line 3]
            if {[string length $link] <= 0} then {
                append tmp [Image "../images/R/$pic"]\n
                set tmptitle [Span "$title" $g(font1)]\n
            } else {
#                append tmp [Image "../images/R/$pic"]\n
                append tmp [Link [Image "../images/R/$pic"] $link "target='_blank'"]\n
                set tmptitle [Link [Span "$title" $g(font1)] $link "target='_blank'"]\n
            }
            if {$itemcnt == 0} then {
                set item1 $tmp
                incr itemcnt
            } else {
                set part1 [Item [Center $item1] "width=120 valign=top"]\n
                set part2 [Item [Span $item2 $g(font3)] "valign=top"]\n
                if {$order} then {
                    append body [Table [BlankCol 5%]$part1[BlankCol 5%]$part2[BlankCol 5%]]\n
                    set order 0
                } else {
                    append body [Table [BlankCol 5%]$part2[BlankCol 5%]$part1[BlankCol 5%]]\n
                    set order 1
                }
                append body "<HR width=70%>"
                set item1 $tmp
                set item2 ""
            }
            set item2 "$tmptitle<p>&nbsp;&nbsp;&nbsp;&nbsp;"
#            set item2 "&nbsp;&nbsp;&nbsp;&nbsp;"
        }
    }

    set part1 [Item [Center $item1] "width=120 valign=top"]\n
    set part2 [Item [Span $item2 $g(font3)] "valign=top"]\n
    if {$order} then {
        append body [Table [BlankCol 5%]$part1[BlankCol 5%]$part2[BlankCol 5%]]\n
        set order 0
    } else {
        append body [Table [BlankCol 5%]$part2[BlankCol 5%]$part1[BlankCol 5%]]\n
        set order 1
    }

    append content [Table $body]\n[Tail]\n
    close $fp
    return $content
}

WriteFile research/index.html [research]
