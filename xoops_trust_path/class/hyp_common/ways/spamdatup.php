<?php
ignore_user_abort(true);

// clear output buffer
while( ob_get_level() ) {
	ob_end_clean() ;
}
header('Content-Type: text/javascript; charset=UTF-8');
header('Content-Length: 0');
header('Connection: close');
flush();

include_once $trustpath . '/class/hyp_common/hyp_common_func.php';
$ht = new Hyp_HTTP_Request();
$files = array('spamsites.dat', 'spamwords.dat');
foreach($files as $file) {
	$target = $trustpath . '/uploads/hyp_common/' . $file;
	if (filemtime($target) + 600 < time()) {
		$ht->init();
		$ht->url = 'http://nao-pon.github.io/HypContents/spamdat/' . $file;
		$ht->get();
		if ($ht->rc == 200 && $ht->data) {
			$data = $ht->data;
			if (md5($data) !== @ md5_file($target)) {
				file_put_contents($target, $data);
			}
		}
	}
}

exit('');