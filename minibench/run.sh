#!/bin/bash

#
gcc -O3 -ffast-math -flto benchmark.c -lm -o benchmark
echo -n "GCC: "
./benchmark
rm benchmark

pcc -O3 -ffast-math -flto benchmark.c -lm -o benchmark
echo -n "PCC: "
./benchmark
rm benchmark

bcc -O3 -ffast-math -flto benchmark.c -lm -o benchmark
echo -n "BCC: "
./benchmark
rm benchmark

clang -O3 -ffast-math -flto benchmark.c -lm -o benchmark
echo -n "clang: "
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

echo -n "PyPy: "
pypy benchmark.py
