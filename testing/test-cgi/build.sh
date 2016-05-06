#!/bin/bash

gcc main.c -o app.cgi -lccgi -lsqlite3 -lgd
strip app.cgi
