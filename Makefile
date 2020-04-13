$ERRORS="-w"

comp:
	gcc log.c  ${ERRORS} -o log -lm -li2c

clean:
	rm *.o > /dev/null 2>&1 &
