
import time
import math

start = time.time()

foo = 0
bar = ''
for i in range(0, 1000000):
	foo += i / 10
	if (math.floor(foo) % 3) == 2:
		foo -= (i - round(2.0 + math.sqrt(i)))
		bar += str(i)

end = time.time()
diff = end - start

print("%.6f seconds" % diff)
