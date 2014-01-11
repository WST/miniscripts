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

if(@ $argv[1]){
	echo "graph_title Sonic TAS players\ngraph_vlabel points\ngraph_category tasvideos\n";
	foreach($players as $player) {
		echo strtolower($player). ".label {$player}\n";
	}
	die();
}

foreach($players as $player) {
	$data = @ file_get_contents("http://tasvideos.org/playerinfo/{$player}.json");
	if($data === false) {
		// Handle the error
		continue;
	} else {
		$information = json_decode($data);
		$player_name = strtolower($information->username);
		echo "{$player_name}.value {$information->points}\n";
	}
}
