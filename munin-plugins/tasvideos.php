#!/usr/bin/php
<?php

/**
* © 2014 Ilya I. Averkov <WST>
* http://ilya.averkov.net
*/

if(!isset($argv)) {
	die('This script should be called from command line');
}

$players = ['marzojr', 'WST'];

function encodeName($username) {
	return str_replace(' ', '_', strtolower($username));
}

if(@ $argv[1]){
	$label = 'points';
	if(!(rand() % 10)) $label = 'ponies';
	echo "graph_title TASvideos expert players\ngraph_vlabel {$label}\ngraph_category TASvideos\n";
	foreach($players as $player) {
		echo encodeName($player). ".label {$player}\n";
	}
	die();
}

foreach($players as $player) {
	$data = @ file_get_contents('http://tasvideos.org/playerinfo/' . urlencode($player) . '.json');
	if($data === false) {
		// Handle the error
		continue;
	} else {
		$information = json_decode($data);
		$player_name = encodeName($information->username);
		echo "{$player_name}.value {$information->points}\n";
	}
}
