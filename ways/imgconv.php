<?php
/*
 * Created on 2008/07/24 by nao-pon http://hypweb.net/
 * License: GPL v2 or (at your option) any later version
 * $Id: imgconv.php,v 1.2 2008/08/20 04:26:12 nao-pon Exp $
 */

$url = (isset($_GET['u']))? $_GET['u'] : '';
$mode = (isset($_GET['m']))? $_GET['m'] : '';
$maxsize = (isset($_GET['s']))? intval($_GET['s']) : 0;
$png = (isset($_GET['p']))? 1 : 0;
if (! $maxsize) $maxsize = 200;

switch($mode) {
	case 'i4k':
		$maxage = 86400; // TTL 1day
		if ($url) {
			$TTL = 10 * 3600; // 10days
			if (isset($_GET['c'])) $TTL = 0;
			
			$basename = md5(join("\t", array($url, $maxsize, $png))) . '.i4k';
			$file = $cachepath . '/' .  $basename;
			
			if (is_file($file) && filemtime($file) + $TTL > time()) {
				if (filesize($file)) {
					$size = getimagesize($file);
					if (isset($size['mime'])) {
						$mime = $size['mime'];
					}
					header('Content-Type: ' . $mime);
					header('Content-Length: ' . filesize($file));
					header('Cache-Control:max-age=' . $maxage);
					readfile($file);
					exit();
				} else {
					header('HTTP/1.1 301 Moved Permanently');
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
		
				if (HypCommonFunc::img4ktai($file, $maxsize, $png)) {
					$size = getimagesize($file);
					if (isset($size['mime'])) {
						$mime = $size['mime'];
					}
					header('Content-Type: ' . $mime);
					header('Content-Length: ' . filesize($file));
					header('Cache-Control:max-age=' . $maxage);
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
