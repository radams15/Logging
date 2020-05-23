ERRORS=-w
STD=11

CPP=g++
C=gcc

main:
	# export LD_LIBRARY_PATH=/home/pi/logging:$LD_LIBRARY_PATH # put this in bashrc, needed to run program
	${CPP} -L/home/pi/logging -std=c${STD} main.cpp  ${ERRORS} -o log -lbmp180

link:
	${C} -c bmp180.c -o bmp180.o
	${C} -shared -o libbmp180.so bmp180.o  -Wl,--no-undefined -li2c -lm

cross:
	CPP=arm-linux-gnueabihf-g++
	C=arm-linux-gnueabihf-gcc

clean:
	rm *.o > /dev/null 2>&1 &
