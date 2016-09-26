<?php

require __DIR__ . '/vendor/autoload.php';

use GDText\Box;
use GDText\Color;

$texts = [
	[60, "Dissolving a Raspberry Pi in royal water"],
	[40, "Royal water is mix of concentrated hydrochloric and nitric acids.\nIt’s also called “aqua regia” sometimes."],
	[40, "I don’t recommend you doing it youselves (unless you know well what you are doing), because the mentioned acids are extremely corrosive and can badly corrupt your skin.\nThe other danger is nitrogen dioxide gas, which is produced during the reaction\n(and it is a very bad idea to breathe it).\n\nI’d also like to notice that my Raspberry Pi was broken.\nI also apologize for the absense of a tripod. Actually, I got one, but sadly, it’s far away from me right today."],
	[40, "Well… It looks so royal, doesn’t it?"],
	[40, "I left it for some more time, then washed the board in water."],
];

$font = '/home/wst/.fonts/20081021_vag_round_cyr.ttf';
//$font = '/home/wst/Downloads/Cuprum.otf';

$i = 0;
foreach($texts as $text) {
	$i ++;
	$im = imagecreatetruecolor(1920, 1080);
	$background = imagecolorallocate($im, 0, 0, 0);
	imagefill($im, 0, 0, $background);

	$box = new Box($im);
	$box->setFontFace($font);
	$box->setFontColor(new Color(255, 255, 255));
	//$box->setTextShadow(new Color(0x33, 0x33, 0x33, 50), 2, 2);
	$box->setFontSize($text[0]);
	$box->setBox(100, 100, 1720, 880);
	$box->setTextAlign('center', 'center');
	$box->draw($text[1]);

	imagepng($im, __DIR__ . "/out_$i.png");
}
