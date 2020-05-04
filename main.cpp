#include <iostream>

struct Data{
  float temperature;
  float pressure;
};

using namespace std;

extern "C"{
  #include "bmp180.h"
}

Data get_temp(){
  Data data;
  void* bmp = bmp180_init(0x77, "/dev/i2c-1");

  bmp180_eprom_t eprom;
  bmp180_dump_eprom(bmp, &eprom);

  bmp180_set_oss(bmp, 1);

  data.temperature = bmp180_temperature(bmp);

  data.pressure = bmp180_pressure(bmp);

  bmp180_close(bmp);

  return data;
}

int main(){

  Data data = get_temp();
}
