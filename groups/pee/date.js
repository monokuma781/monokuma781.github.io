<!--
currentDate=new Date()
with (currentDate)
{year=getYear()
day=getDay()
 month=getMonth()+1
 if(year >= 2000)
   document.write('<font style=color:#000000>'+getYear()+'年'+month+'月'+getDate()+'日'+'</font>')
 if(year <= 99)
   document.write('<font style=color:#000000>'+'19'+getYear()+'年'+month+'月'+getDate()+'日'+'</font>')
 if(year >= 100 && year < 2000)
    {year=year-100+2000
     document.write('<font style=color:#000000>'+year+'年'+month+'月'+getDate()+'日'+'</font>')}}
 if(day==1){document.write('<font style=color:#000000>'+' 星期一'+'</font>')}
 if(day==2){document.write('<font style=color:#000000>'+' 星期二'+'</font>')}
 if(day==3){document.write('<font style=color:#000000>'+' 星期三'+'</font>')}
 if(day==4){document.write('<font style=color:#000000>'+' 星期四'+'</font>')}
 if(day==5){document.write('<font style=color:#000000>'+' 星期五'+'</font>')}
 if(day==6){document.write('<font style=color:#000000>'+' 星期六'+'</font>')}
 if(day==0){document.write('<font style=color:#000000>'+' 星期日'+'</font>')}
//document.write('<br><br>')
//document.write('<font style=font-size:12pt;color:#ffffff>'+' 星期日'+'</font>')
//document.write("<font style=font-size:9pt;color:#ffffff>"+(new Date().getMonth()+1)+"月"+new Date().getDate()+"日"+week+"</font>");
//document.writeln("<marquee direction=\"up\" id=\"cl\" onmouseout=\"cl.start()\" onmouseover=\"cl.stop()\" scrollAmount=\"1\" scrollDelay=\"60\" height=\"95\" width=\"120\" align=\"center\"> 　　<font color=000099>今天白天，晴，北转南风二三级，降水概率０，最高气温８℃。　　今天夜间，晴，南转北风一二级，降水概率０，最低气温零下４℃。</font></marquee>");
//-->