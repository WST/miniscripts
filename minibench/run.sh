#!/bin/bash

#
gcc -O3 -ffast-math -flto benchmark.c -lm -o benchmark

echo -n "Pure C: "
./benchmark
rm benchmark

echo -n "Python: "
python benchmark.py

echo -n "Ruby: "
ruby benchmark.rb

echo -n "Lua: "
lua benchmark.lua

echo -n "PHP: "
php benchmark.php
