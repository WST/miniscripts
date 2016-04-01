
#include <stdio.h>
#include <string.h>
#include <math.h>
#include <time.h>

char bar[2000000];
double foo = 0.0;
struct timespec tstart = {0, 0}, tend = {0, 0};
unsigned long pos = 0;

int main(int argc, char *argv[]) {
	clock_gettime(CLOCK_MONOTONIC, & tstart);

	unsigned long i = 0;
	for(i = 0; i < 1000000; i ++) {
		foo += i / 10;
		if(((int) floor(foo) % 3) == 2) {
			foo -= (i - round(2.0 + sqrt(i)));
			char buf[10];
			sprintf(buf, "%d", i);
			short buf_length = strlen(buf);
			memcpy(&bar[pos], buf, buf_length);
			pos += buf_length;
		}
	}

	clock_gettime(CLOCK_MONOTONIC, & tend);

	double diff = (double)(1.0e-9 * tend.tv_nsec + tend.tv_sec) - (double)(1.0e-9 * tstart.tv_nsec + tstart.tv_sec);

	printf("%.6f seconds\n", diff);
	return 0;
}
