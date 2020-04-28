#include <unistd.h>
#include <stdio.h>
#include <time.h>

#include "bmp180.h"
#include "jsmn.h"

const char *I2C_DEVICE = "/dev/i2c-1";
const int ADDRESS = 0x77;
const char* SAVE_FILE = "/home/pi/logging/save.csv";
const char* OUT_FORMAT = "%f,%f,%lu\n";
char* CONF_FILE = "/home/pi/logging/html/config.json";

unsigned int delay; // in secs

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

	return fclose(file);
}

char *readFile(char *filename) {
    FILE *f = fopen(filename, "rt");
    fseek(f, 0, SEEK_END);
    long length = ftell(f);
    fseek(f, 0, SEEK_SET);
    char *buffer = (char *) malloc(length + 1);
    buffer[length] = '\0';
    fread(buffer, 1, length, f);
    fclose(f);
    return buffer;
}

static int jsoneq(const char *json, jsmntok_t *tok, const char *s) {
  if (tok->type == JSMN_STRING && (int)strlen(s) == tok->end - tok->start &&
      strncmp(json + tok->start, s, tok->end - tok->start) == 0) {
    return 0;
  }
  return -1;
}

void sleep_secs(int secs){
	usleep(secs * 1000*1000); // secs multiplied by microseconds
}

int main(int argc, char **argv){
	void* bmp;
	bmp = init(bmp);

	jsmn_parser p;
	jsmntok_t t[32]; // 32 max tokens


	char* json_file_data = readFile(CONF_FILE);

	jsmn_init(&p);
	int r = jsmn_parse(&p, json_file_data, strlen(json_file_data), t, sizeof(t) / sizeof(t[0]));

	for (int i = 1; i < r; i++) {
		if (jsoneq(json_file_data, &t[i], "delay") == 0) {
			delay = atoi(json_file_data + t[i + 1].start)*60;
      i++;
    }
	}

	unsigned long next_time = time(NULL)+delay;
	unsigned long cur_time;

	while(1){
		if((cur_time = time(NULL)) == next_time){

			if(fork() == 0){ // child
				struct Readings readings = get_readings(bmp);
				printf( OUT_FORMAT, readings.temperature, readings.pressure, cur_time);
				int stat = write_data(readings);
				return 0;
			}

			next_time = cur_time + delay;
		}
	}

	bmp180_close(bmp);

	return 0;
}
