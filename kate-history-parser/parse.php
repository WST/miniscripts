#!/usr/bin/php
<?php

/*
* Иногда очень хочется сделать бэкап истории переписки с кем-то из ВК,
* Но встроенная функция бэкапа Kate Mobile умеет делать только неудобные
* текстовые файлы. Эта хрень предназначена для того, чтобы сконвертировать
* такой файл в нормальную веб-страницу
* TODO: поддержка вложений
*/

if(count($argv) != 3) {
	die("Использование: parse.php <filename> <target_dir>\n");
}

$filename = $argv[1];
$target_dir = $argv[2];

if(!file_exists($filename)) {
	die("Файл не существует: <$filename>\n");
}

if(!is_readable($filename)) {
	die("Не удалось открыть файл: <$filename>\n");
}

$size = round(filesize($filename) / 1048576.0, 2);

echo "Начинаем работу\nРазмер: {$size}M\n";


class HistoryParser
{
	private $db;
	private $statements = [];

	public function __construct($filename, $target_dir) {
		$this->filename = $filename;
		$this->db = new PDO("sqlite:messages.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS messages (m_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, m_when INTEGER NOT NULL, m_who TEXT NOT NULL, m_what TEXT NOT NULL)");
		$this->statements['new_message'] = $this->db->prepare("INSERT INTO messages (m_when, m_who, m_what) VALUES (?, ?, ?)");
	}

	function parseDate($date_string) {
		return 0;
	}

	function parseHead($head) {
		$m = [];
		if(preg_match('#^([^\\(]+) \\(([^)]+)\\):$#U', trim($head), $m)) {
			return [$this->parseDate($m[2]), trim($m[1])];
		} else {
			return false;
			var_dump($head);
			var_dump($m);
			//die();
		}
	}

	function processMessage($head, $body) {
		$this->statements['new_message']->bindParam(1, $head[0], PDO::PARAM_INT);
		$this->statements['new_message']->bindParam(2, $head[1], PDO::PARAM_STR);
		$this->statements['new_message']->bindParam(3, $body, PDO::PARAM_STR);
		$this->statements['new_message']->execute();
	}

	private function parse() {
		$raw = file_get_contents($this->filename);

		$this->db->beginTransaction();
		$this->db->exec("DELETE FROM messages");

		// Цикл по «сырым» сообщениям
		$i = 0;
		$position = 0;
		while(true) {
			// Сначала нужно найти позицию окончания сообщения. В лоб искать "\n\n" не вариант, ведь сообщения сами могут содержать переводы строк.
			$supposed_message_end = strpos($raw, "\n\n", $position);

			while(true) {
				$next_line_end = strpos($raw, "\n", $supposed_message_end + 2);
				$next_line_length = $next_line_end - $supposed_message_end - 2;
				$next_line = substr($raw, $supposed_message_end + 2, $next_line_length);

				//var_dump($next_line);
				//usleep(5000);

				if($head_info = $this->parseHead($next_line)) {
					$message_end = $supposed_message_end;
					break;
				} else {
					$supposed_message_end = strpos($raw, "\n\n", $supposed_message_end + 2);
				}
			}



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
			
			$this->processMessage($head_info, $body);

			if($message_end === false) break;
			$i ++;
		}

		$this->db->commit();
	}

	private function renderPage() {
		$messages = '<!DOCTYPE html><html><head><title>Диалог</title><meta http-equiv="content-type" content="text/html;charset=utf-8" /></head><body><table border="1"><tr><th>Автор</th><th>Сообщение</th></tr>';
		$statement = $this->db->prepare("SELECT * FROM messages ORDER BY m_when ASC");
		$statement->execute();
		while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$messages .= '<tr><td>' . htmlspecialchars($row['m_who']) . '</td><td>' . htmlspecialchars($row['m_what']) . '</td></tr>';
		}
		$statement->closeCursor();
		$messages .= '</table></body></html>';
		file_put_contents("out.html", $messages);
	}

	public function run() {
		$this->parse();
		$this->renderPage();
	}
}

$parser = new HistoryParser($filename, $target_dir);
$parser->run();
