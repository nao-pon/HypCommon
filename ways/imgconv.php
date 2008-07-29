<?php
/*
 * Created on 2008/07/24 by nao-pon http://hypweb.net/
 * License: GPL v2 or (at your option) any later version
 * $Id: imgconv.php,v 1.1 2008/07/29 14:35:40 nao-pon Exp $
 */

$url = (isset($_GET['u']))? $_GET['u'] : '';
$mode = (isset($_GET['m']))? $_GET['m'] : '';

switch($mode) {
	case 'i2g':
		if ($url) {
			$TTL = 10 * 3600; // 10days
			if (isset($_GET['c'])) $TTL = 0;
			
			$basename = md5($url) . '.gif';
			$file = $cachepath . '/' .  $basename;
			
			if (file_exists($file) && filemtime($file) + $TTL > time()) {
				if (filesize($file)) {
					header('Location: ' . $cacheurl . '/' .  $basename);
				} else {
					header('Location: ' . $url);
				}
				exit();
			}

			if (! class_exists('HypCommonFunc')) {
				include($trustpath . '/class/hyp_common/hyp_common_func.php');
			}
			
			$h = new Hyp_HTTP_Request();
			
			$h->url = $url;
			$h->get();
			if ($h->rc === 200) {
				
				if ($fp = fopen($file, "wb")) {
					flock($fp,  LOCK_EX);
					fwrite($fp, $h->data);
					fclose($fp);
				} else {
					header('Location: ' . $url);
					exit();
				}
		
				if (HypCommonFunc::img2gif($file)) {
					clearstatcache();
					header('Content-Type: image/gif');
					header('Content-Length: ' . filesize($file));
					readfile($file);
					exit();
				}
			}
			if ($fp = fopen($file, "wb")) {
				flock($fp,  LOCK_EX);
				fwrite($fp, '');
				fclose($fp);
			}
			header('Location: ' . $url);
			exit();
		}
		break;
}
