#include "bmp180.h"
#include <unistd.h>
#include <stdio.h>
#include <time.h>

const char *I2C_DEVICE = "/dev/i2c-1";
const int ADDRESS = 0x77;
const char* SAVE_FILE = "/home/pi/logging/save.csv";
const char* OUT_FORMAT = "%f,%f,%lu\n";

const int DELAY = 10; // mins

struct Readings{
	float temperature;
	float pressure;
};

void* init(void* bmp){
	bmp = bmp180_init(ADDRESS, I2C_DEVICE);
	
	bmp180_eprom_t eprom;
	bmp180_dump_eprom(bmp, &eprom);
	
	bmp180_set_oss(bmp, 1);
	return bmp;
}


struct Readings get_readings(void* bmp){
	struct Readings readings;
	
	readings.temperature = bmp180_temperature(bmp);
	readings.pressure = bmp180_pressure(bmp) / 100.0; // using integer division here. We want to return millibar, not pascal
	
	return readings;
}

int write_data(struct Readings readings){
	FILE *file;
	if( access( SAVE_FILE, F_OK ) != -1 ) {
		file = fopen(SAVE_FILE, "a");
	} else {
		file = fopen(SAVE_FILE, "w");
	}
	time_t secs = time(NULL);
	
	fprintf( file, OUT_FORMAT, readings.temperature, readings.pressure, secs );
	printf( OUT_FORMAT, readings.temperature, readings.pressure, secs );
	
	return fclose(file);
}

void sleep_secs(int secs){
	usleep(secs * 1000*1000); // secs multiplied by microseconds
}

int main(int argc, char **argv){
	void* bmp;
	bmp = init(bmp);
	
	while(1){
		struct Readings readings = get_readings(bmp);
		
		int stat = write_data(readings);
		
		sleep_secs(  DELAY*60 );
	}

	bmp180_close(bmp);
	
	return 0;
}
