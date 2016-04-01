
start_time = os.clock();

foo = 0;
bar = {};

for i = 0, 999999 do
	foo = foo + (i / 10);
	if((math.floor(foo) % 3) == 2) then
		foo = foo - (i - math.floor(2.5 + math.sqrt(i)));
		bar[#bar + 1] = tostring(i);
	end;
end;

bar = table.concat(bar);

end_time = os.clock();

diff = end_time - start_time;
print(string.format("%.6f seconds", diff));
