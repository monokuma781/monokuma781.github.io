WEB = /home/majie/public_html/hpcos
DATA = ../data

MAIN        = $(WEB)/main.html
MEMBER      = $(WEB)/member.html
TOP         = $(WEB)/top.html
LIFE        = $(WEB)/life/index.html
NEWS        = $(WEB)/news/index.html
PAPER       = $(WEB)/paper/index.html
PROJECT     = $(WEB)/project/index.html
RESEARCH    = $(WEB)/research/index.html
README      = $(WEB)/readme.html

RES_FILE = $(DATA)/hpcos_cn.res
COMMON_FILE = $(RES_FILE) wwwutil.tcl

D_MAIN      = $(COMMON_FILE) $(DATA)/intro.txt $(DATA)/news.txt \
              $(DATA)/research.txt $(DATA)/project.txt \
              $(DATA)/life.txt $(DATA)/link.txt
D_MEMBER    = $(COMMON_FILE) $(DATA)/member.txt
D_TOP       = $(COMMON_FILE)
D_LIFE      = $(COMMON_FILE) $(DATA)/life.txt
D_NEWS      = $(COMMON_FILE) $(DATA)/news.txt
D_PAPER     = $(COMMON_FILE) $(DATA)/paper.txt
D_PROJECT   = $(COMMON_FILE) $(DATA)/project.txt
D_RESEARCH  = $(COMMON_FILE) $(DATA)/research.txt
D_README    = $(COMMON_FILE) $(DATA)/README

all: $(MAIN) $(MEMBER) $(TOP) $(LIFE) $(NEWS) $(PAPER) $(PROJECT) $(RESEARCH) \
     $(README)

$(MAIN): cmain.tcl $(D_MAIN)
	./cmain.tcl

$(MEMBER): cmember.tcl $(D_MEMBER)
	./cmember.tcl

$(TOP): ctop.tcl $(D_TOP)
	./ctop.tcl

$(LIFE): clife.tcl $(D_LIFE)
	./clife.tcl

$(NEWS): cnews.tcl $(D_NEWS)
	./cnews.tcl

$(PAPER): cpaper.tcl $(D_PAPER)
	./cpaper.tcl

$(PROJECT): cproject.tcl $(D_PROJECT)
	./cproject.tcl

$(RESEARCH): cresearch.tcl $(D_RESEARCH)
	./cresearch.tcl

$(README): creadme.tcl $(D_README)
	./creadme.tcl

install:
	cd $(WEB)
	scp -r * mud:hpcos/.

#The end.
