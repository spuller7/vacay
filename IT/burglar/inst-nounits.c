#include <phidget22.h>
#include <stdio.h>

static void CCONV onSensorChange(PhidgetVoltageRatioInputHandle ch, void * ctx, double sensorValue, Phidget_UnitInfo * sensorUnit)
{
	printf("SensorValue: %lf\n", sensorValue);
	printf("SensorUnit: %s\n", sensorUnit->symbol);
	printf("----------\n");
}

int main()
{
	double current = 0.0;
	PhidgetVoltageRatioInputHandle voltageRatioInput0;

	PhidgetVoltageRatioInput_create(&voltageRatioInput0);

	//PhidgetVoltageRatioInput_setOnSensorChangeHandler(voltageRatioInput0, onSensorChange, NULL);

	Phidget_openWaitForAttachment((PhidgetHandle)voltageRatioInput0, 5000);

	PhidgetVoltageRatioInput_setSensorType(voltageRatioInput0, SENSOR_TYPE_1122_DC);
	//Other valid sensor types for this sensor include: SENSOR_TYPE_1122_AC

	//Wait until Enter has been pressed before exiting
	//getchar();

	PhidgetVoltageRatioInput_getSensorValue(voltageRatioInput0, &current);

	Phidget_close((PhidgetHandle)voltageRatioInput0);
	printf("%lf", current);

	PhidgetVoltageRatioInput_delete(&voltageRatioInput0);
}
