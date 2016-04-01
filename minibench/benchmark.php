<?php

$start = microtime(true);

$foo = 0;
$bar = '';

for($i = 0; $i < 1000000; $i ++) {
	$foo += $i / 10;
	if((floor($foo) % 3) == 2) {
		$foo -= ($i - round(2.0 + sqrt($i)));
		$bar .= (string) $i;
	}
}

$end = microtime(true);
$diff = $end - $start;

printf("%.6f seconds\n", $diff);
