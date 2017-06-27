#!/usr/bin/php
<?php

/*
* Иногда очень хочется сделать бэкап истории переписки с кем-то из ВК,
* Но встроенная функция бэкапа Kate Mobile умеет делать только неудобные
* текстовые файлы. Эта хрень предназначена для того, чтобы сконвертировать
* такой файл в нормальную веб-страницу
* TODO: поддержка вложений
*/

function parseDate($date_string) {
	return $date_string;
}

if(count($argv) != 3) {
	die("Использование: parse.php <filename> <target_dir>\n");
}

$filename = $argv[1];
$target = $argv[2];

if(!file_exists($filename)) {
	die("Файл не существует: <$filename>\n");
}

if(!is_readable($filename)) {
	die("Не удалось открыть файл: <$filename>\n");
}

$size = round(filesize($filename) / 1048576.0, 2);

echo "Начинаем работу\nРазмер: {$size}M\n";

$raw = file_get_contents($filename);
$messages = [];

/*
preg_match_all('#^([^\\(]+?) \\(([^\\)]+)\\):\n(.+?)\n$#Um', $raw, $messages, PREG_SET_ORDER);

foreach($messages as $message) {
	$from = trim($message[1]);
	$when = parseDate($message[2]);
	$text = trim($message[3]);

	echo "<$from>: $text\n";
}
*/

// Цикл по «сырым» сообщениям
$i = 0;
$position = 0;
while(true) {
	$message_end = strpos($raw, "\n\n", $position);
	$head_end = strpos($raw, "\n", $position);
	$head = substr($raw, $position, $head_end - $position);
	if($message_end == $head_end) {
		$position = $message_end + 2;
		$body = '';
	} else {
		$body_start = $head_end + 1;
		$body = substr($raw, $body_start, $message_end - $body_start);
	}
	
	$position = $message_end + 2;
	echo "$body\n";

	if($message_end === false) break;
	$i ++;
}
