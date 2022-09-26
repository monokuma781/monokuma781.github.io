#!/usr/local/bin/tclsh8.4
source wwwutil.tcl

proc member {} {
    global g

    set fp [OpenData member.txt]
    append content [Header "³ÉÔ±"]

    while {![eof $fp]} {
        gets $fp Line
        set name [lindex $Line 0]
        set title [lindex $Line 1]
        set info [lindex $Line 2]
        set web [lindex $Line 3]

        if {[string equal $name "#"]} then {
            append tmp [BR][Table [Item [Para $title $g(font1)] "width='10%'"]]
        } else {
            set items [BlankCol 5%]\n
            if {[string length $web] > 0} then {
                set lname [Link $name $web "$g(font2) target='_blank'"]
            } else {
                set lname [Span $name $g(font2)]
            }
            append items [Item $lname "width='8%'"]\n
            append items [Item [Span $title $g(font2)] "width='8%'"]\n
            append items [Item [Span $info $g(font2)] "width='8%'"]\n
            append items [BlankCol 10%]\n

            append tmp [Table $items]\n
        }
    }
    append content [Table $tmp]\n[Tail]\n

    close $fp
    return $content
}

#puts [member]
WriteFile member.html [member]
