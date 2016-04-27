#!/usr/bin/php
<?php

/**
* © 2011 Ilja I. Averkov <admin@jsmart.web.id>
*/

$areas = array (
	// minx, miny, maxx, maxy
	//'jakarta' => array(106.698610, -6.435592, 107.105998, -6.070917),
	'ciamis' => array(108.337129, -7.335344, 108.367902, -7.319304),
	//'tokyo' => array(139.609208, 35.426063, 139.942845, 35.826929),
	//'moscow' => array(37.360165, 55.553771, 37.895260, 55.913671),
	//'denpasar' => array(115.132893, -8.761039, 115.298522, -8.604478),
	//'singapore' => array(103.611991, 1.239909, 104.015080, 1.418488),
	//'surabaya' => array(112.658943, -7.371848, 112.821734, -7.203904),
	//'batam' => array(103.893180, 1.017434, 104.163438, 1.200168),
	//'samarinda' => array(117.120355, -0.513384, 117.174191, -0.466086),
	//'yogyakarta' => array(110.321685, -7.843759, 110.433950, -7.744504),
	//'bandung' => array(107.518872, -6.980648, 107.667058, -6.856315),
	//'abu-dhabi' => array(54.286731, 24.384378,  54.543604,  24.504157),
	//'barcelona' => array(2.096127, 41.371378, 2.267092, 41.459927),
);

final class Точка
{
	private $x = 0.0;
	private $y = 0.0;
	
	public function __construct($x, $y) {
		$this->x = $x;
		$this->y = $y;
	}
	
	public function & x() {
		return $this->x;
	}
	
	public function & y() {
		return $this->y;
	}
}

final class Загрузчик
{
	private $areas = array();
	private $blacklist = array();
	
	public function __construct($areas) {
		$this->areas = $areas;
		$this->blacklist = file(__DIR__ . '/blacklist.txt');
		array_walk($this->blacklist, 'trim');
		@ set_time_limit(0);
	}
	
	private function сообщение($текст) {
		echo '[', date('H:i:s', time()), "] $текст\n";
	}
	
	private function выпуклыйУгол(Точка $foo, Точка $bar, Точка $baz) {
		return (($bar->x() - $foo->x()) * ($baz->y() - $foo->y()) - ($bar->y() - $foo->y()) * ($baz->x() - $foo->x())) > 0;
	}
	
	private function ∈(Точка $тест, Точка $a, Точка $b, Точка $c) {
		$первое = $this->выпуклыйУгол($тест, $a, $b);
		$второе = $this->выпуклыйУгол($тест, $b, $c);
		$третье = $this->выпуклыйУгол($тест, $c, $a);
		return ($первое === $второе) && ($второе === $третье);
	}
	
	public function грузи() {
		$context = stream_context_create(array('http' => array('timeout' => 10)));
		$this->сообщение('starting in ' . __DIR__);
		foreach($this->areas as $place => $coordinates) {
			$this->сообщение("starting to process <$place>");
			if(! @ is_dir($dir = __DIR__ . "/$place")) {
				if(! @ mkdir($dir)) {
					$this->сообщение("failed to create directory <$place>");
					break;
				} else {
					$this->сообщение("created directory <$place>");
				}
			}
			if(!is_writable($dir)) {
				$this->сообщение("access denied to directory <$place>");
				continue;
			}
			$iterator = 0;
			do {
				if(!$data = @ json_decode(@ file_get_contents("http://www.panoramio.com/map/get_panoramas.php?set=full&from=$iterator&to=" . ($iterator + 99) . "&size=original&minx={$coordinates[0]}&miny={$coordinates[1]}&maxx={$coordinates[2]}&maxy={$coordinates[3]}&size=original&mapfilter=false"))) {
					$this->сообщение("processing <$place> failed: got bad data");
					break;
				}
				foreach($data->photos as $photo) {
					$iterator ++;
					$filename = "$dir/{$photo->photo_id}.jpg";
					if(in_array($photo->photo_id, $this->blacklist)) {
						$this->сообщение("<$place> blacklisted file: {$photo->photo_id}.jpg");
						@ unlink($filename);
						continue;
					}
					if(file_exists($filename)) {
						$this->сообщение("<$place> file exists, skipping: {$photo->photo_id}.jpg");
					} else {
						$this->сообщение("<$place> {$photo->photo_id}.jpg ({$iterator} from {$data->count}) [{$photo->width}×{$photo->height}]");
						$retry = 5;
						while(true) {
							$retry --;
							$photo_data = @ file_get_contents($photo->photo_file_url, false, $context);
							if(!$photo_data) {
								if($retry == 0) {
									$this->сообщение("downloading {$photo->photo_id}.jpg failed, no more retries, skipping");
									break;
								}
								$this->сообщение("downloading {$photo->photo_id}.jpg failed, retrying <$retry attempts remain>");
								continue;
							}
							@ file_put_contents($filename, $photo_data);
							break;
						}
					}
				}
			} while(@ $data->has_more);
		}
		$this->сообщение('finished');
	}
}

if(!isset($argv)) {
	die('This is a command-line tool');
}

$загрузчик = new Загрузчик($areas);
$загрузчик->грузи();

?>