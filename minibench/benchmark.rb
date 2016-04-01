
start_time = Time.now

foo = 0
bar = ''

1000000.times do |i|
	foo += i / 10.0
	if((foo.floor() % 3) == 2)
		foo -= (i - (2.0 + Math.sqrt(i)).round)
		bar.concat(i.to_s)
	end
end

end_time = Time.now
diff = end_time - start_time

puts sprintf("%.6f seconds\n", diff)
