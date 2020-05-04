ERRORS=-w
STD=11

main:
	export LD_LIBRARY_PATH=.:$LD_LIBRARY_PATH
	g++ -L. -std=c${STD} main.cpp  ${ERRORS} -o log -lbmp180

link:
	gcc -c bmp180.c -o bmp180.o
	gcc -shared -o libbmp180.so bmp180.o  -Wl,--no-undefined -li2c -lm

clean:
	rm *.o > /dev/null 2>&1 &
