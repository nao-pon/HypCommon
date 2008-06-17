<?php

if (isset($_GET['l'])) {
	$url = $_GET['l'];
	$google = 'http://www.google.co.jp/gwt/n?u=' . rawurlencode($url);
	$url = str_replace('&amp;', '&',htmlspecialchars($_GET['l']));
	header('Content-type: text/html; charset=Shift_JIS');
	echo '<html><head><title>外部へ移動</title></head><body>外部サイトへ移動します。<br><br><a href="'.$url.'">'.$url.'</a><br><br><a href="'.$google.'">Google の携帯変換を使う</a></body></html>';
}