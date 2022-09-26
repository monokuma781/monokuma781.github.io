#
# Makefile to compile the assign.c program
#
# Do not forget to include the path-to-mysql-library
# in the LD_LIBRARY_PATH variable, if compiled in dynamic mode
# Example: export LD_LIBRARY_PATH=/usr/local/mysql/lib/mysql
#

MYSQL_HOME = /usr/local/mysql
MYSQL_LIB=  -L${MYSQL_HOME}/lib/mysql/ -lmysqlclient
CC=gcc
# CCOPT=-static

all: assign

assign: assign.c
	${CC} ${CCOPT} assign.c -I${MYSQL_HOME}/include ${MYSQL_LIB} -lz -o assign





