#!/usr/local/bin/tclsh8.4
source wwwutil.tcl

proc top {} {
    global g

# 1. Header
    append content [Header "国家智能计算机研究开发中心 操作系统组"]

# 2. Top Lines
    append content "\n"
    append content [Table [Item [Image "images/HPCOS.jpg"]] "width='80%'"]

# 3. Menu
    append tmp [Item \
           [Link "首页" "main.html" "$g(font3) target='main'"] \
            "width='5%'"]
    append tmp "\n"

    append tmp [Item \
           [Link "成员" "member.html" "$g(font3) target='main'"] \
           "width='5%'"]
    append tmp "\n"

    append tmp [Item \
            [Link "科研工作" "research/" "$g(font3) target='main'"] \
            "width='8%'"]
    append tmp "\n"

    append tmp [Item \
        [Link "论文资料" "paper.html" "$g(font3) target='main'"] \
        "width='8%'"]
    append tmp "\n"

    append tmp [Item \
        [Link "小组活动" "life/" "$g(font3) target='main'"] \
        "width='8%'"]
    append tmp "\n"

    append tmp [Item \
        [Link "联系我们" "mailto: majie@ncic.ac.cn" "$g(font3) target='main'"] \
        "width='8%'"]
    append tmp "\n"

    append tmp [Item \
        [Link "智能中心主页" "http://www.ncic.ac.cn" "$g(font3) target='top'"] \
        "align='right' width='50%'"]

    set tmp "[BlankCol]$tmp[BlankCol]"
    append content "\n[Table $tmp "width='80%'"]"
    append content "[BR][BR][BR][Tail]"

    return $content
}

WriteFile top.html [top]
