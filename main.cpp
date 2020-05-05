#include <iostream>
#include <unistd.h>
#include <time.h>
#include <fstream>
#include "json.hpp"

extern "C"{
  #include "bmp180.h"
}

const char *I2C_DEVICE = "/dev/i2c-1";
int ADDRESS;// = 0x77;
const char* SAVE_FILE = "/home/pi/logging/save.csv";
char* CONF_FILE = "/home/pi/logging/html/config.json";

struct Data{
	float temperature;
	float pressure;
	long seconds;
};

using namespace std;
using json = nlohmann::json;

void* init(void* bmp){
	bmp = bmp180_init(ADDRESS, I2C_DEVICE);

	bmp180_eprom_t eprom;
	bmp180_dump_eprom(bmp, &eprom);

	bmp180_set_oss(bmp, 1);
	return bmp;
}

void write_data(Data data, bool create=false){
	ofstream file;
	file.open(SAVE_FILE, ios_base::app); // append instead of overwrite
	if(file.fail()){
		cout << "File does not exist!" << endl;
		exit(1);
	}
	file << data.temperature << "," << data.pressure << "," << data.seconds << endl;
	file.close();
}

Data get_readings(void* bmp){
	Data data;

	data.temperature = bmp180_temperature(bmp);
	data.pressure = bmp180_pressure(bmp) / 100.0; // using integer division here. We want to return millibar, not pascal

	return data;
}

json read_json(char* fileName){
	ifstream file(fileName);
	json j;
	if(file.is_open()){
		file >> j;
	}
	file.close();
	return j;
}

int main(){
	json config = read_json(CONF_FILE);
	int delay = config["delay"];
	delay *= 60; // to make it mins, not secs
	ADDRESS = config["i2c_address"];
	void* bmp;
	bmp = init(bmp);


	unsigned long next_time = time(NULL)+delay;
	unsigned long cur_time;

	Data data;

	while(1){
		if((cur_time = time(NULL)) == next_time){

			if(fork() == 0){ // child
				data = get_readings(bmp);
				data.seconds = cur_time;
				cout << data.temperature << "," << data.pressure << "," << data.seconds << endl;
				write_data(data);
				return 0;
			}

			next_time = cur_time + delay;
		}
	}

  

	write_data(data);

	bmp180_close(bmp);
}
