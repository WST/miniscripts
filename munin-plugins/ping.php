#!/usr/bin/php
<?php

if(!isset($argv)) {
	die('This script should be called from command line');
}

$graph_caption = 'Average ping';
$hosts = [
	'ardhosting.com' => 'ArdHosting',
	'andalan.net' => 'Andalan.net',
	'www.idc.co.id' => 'IDC website',
	'unpad.ac.id' => 'Universitas Padjadjaran',
	'itb.ac.id' => 'ITB',
];
$attempts = 10;
$delay = 1;

function ping($host, $timeout = 1) {
	/* ICMP ping packet with a pre-calculated checksum */
	$package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
	$socket  = socket_create(AF_INET, SOCK_RAW, 1);
	socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
	socket_connect($socket, $host, null);
	$ts = microtime(true);
	socket_send($socket, $package, strLen($package), 0);
	if(socket_read($socket, 255))
		$result = microtime(true) - $ts;
	else
		$result = false;
	
	socket_close($socket);
	return $result;
}

function cyclePing($host, $iterations = 10, $delay = 1) {
	$sum = 0;
	$successful_attempts = 0;
	for($i = 0; $i < $iterations; $i ++) {
		if($result = ping($host)) {
			$successful_attempts ++;
			$sum += $result;
		}
		sleep($delay);
	}

	return $sum / $successful_attempts;
}

function encodeName($name) {
	return str_replace('.', '_', $name);
}

if(@ $argv[1]){
	echo "graph_title {$graph_caption}\ngraph_vlabel seconds\ngraph_category network\n";
	foreach($hosts as $key => $value) {
		echo encodeName($key). ".label {$value}\n";
	}
	die();
}

foreach($hosts as $key => $value) {
	echo encodeName($key). ".value " . cyclePing($key, $attempts, $delay) . "\n";
}
