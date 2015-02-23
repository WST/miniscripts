
// Стандартная библиотека
#include <stdio.h>
#include <stdlib.h>

// Библиотека для работы с CGI
#include <ccgi.h>

// СУБД
#include <sqlite3.h>

CGI_varlist *varlist;
sqlite3 *database;

void sendHeaders() {
	fputs("Content-Type: text/html;charset=utf-8\n\n", stdout);
}

void sendContent(char *text) {
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

	sendHeaders();

	sendContent("<p>Hello, world!</p>");

	finalize();
	return 0;
}
