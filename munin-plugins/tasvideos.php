#!/usr/bin/php
<?php

/**
* © 2014 Ilya I. Averkov <WST>
* http://ilya.averkov.net
*/

if(!isset($argv)) {
	die('This script should be called from command line');
}

$graph_caption = 'Sonic tool-assisting speedrunners';
$players = ['marzojr', 'WST'];
$retries = 1;

function getPoints($username) {
	do {
		$data = @ file_get_contents('http://tasvideos.org/playerinfo/' . urlencode($username) . '.json');
		if($data) {
			$information = @ json_decode($data);
			if($information === false) continue;
			return $information->points;
		}
	} while($retries --);
}

function encodeName($username) {
	return str_replace(' ', '_', strtolower($username));
}

if(@ $argv[1]){
	$label = 'points';
	if(!(rand() % 10)) $label = 'ponies';
	echo "{$graph_caption}\ngraph_vlabel {$label}\ngraph_category tasvideos\n";
	foreach($players as $player) {
		echo encodeName($player). ".label {$player}\n";
	}
	die();
}

@ ini_set('default_socket_timeout', 30);

foreach($players as $player) {
	$player_name = encodeName($player);
	$player_points = getPoints($player);
	echo "{$player_name}.value {$player_points}\n";
}
