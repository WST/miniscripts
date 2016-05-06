
#include "main.h"

CGI_varlist *varlist;
sqlite3 *database;

void sendImage() {
	gdImagePtr image;
	image = gdImageCreate(88, 31);
	int background = gdImageColorAllocate(image, 0xFF, 0xFF, 0xFF);
	fputs("Content-Type: image/png\n\n", stdout);
	gdImagePng(image, stdout);
	gdImageDestroy(image);
}

void sendContent(char *text) {
	fputs("Content-Type: text/html;charset=utf-8\n\n", stdout);
	fputs("<html><body><h1>CGI app</h1>", stdout);
	fputs(text, stdout);
	fputs("</body></html>\n", stdout);
}

void initialize() {
	varlist = CGI_get_all(0);
	int status = sqlite3_open("data.db", &database);
	if(status != SQLITE_OK) {
		fputs("Failed to open data.db", stderr);
		exit(1);
	}
}

void finalize() {
	sqlite3_close(database);
	CGI_free_varlist(varlist);
}

int main(int argc, char *argv[]) {
	initialize();

	sendImage();

	finalize();
	return 0;
}
