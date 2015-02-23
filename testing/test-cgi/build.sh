#!/bin/bash

gcc main.c -o app.cgi -lccgi -lsqlite3
strip app.cgi
