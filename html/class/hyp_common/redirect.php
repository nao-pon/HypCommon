<?php

if (isset($_GET['l'])) {
	$url = $_GET['l'];
	$google = 'http://www.google.co.jp/gwt/n?u=' . rawurlencode($url);
	$url = str_replace('&amp;', '&',htmlspecialchars($_GET['l']));
	header('Content-type: text/html; charset=Shift_JIS');
	echo '<html><head><title>�O���ֈړ�</title></head><body>�O���T�C�g�ֈړ����܂��B<br><br><a href="'.$url.'">'.$url.'</a><br><br><a href="'.$google.'">Google �̌g�ѕϊ����g��</a></body></html>';
}