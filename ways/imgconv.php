<?php
/*
 * Created on 2008/07/24 by nao-pon http://hypweb.net/
 * License: GPL v2 or (at your option) any later version
 * $Id: imgconv.php,v 1.6 2009/06/25 23:42:56 nao-pon Exp $
 */

// clear output buffer
while( ob_get_level() ) {
	ob_end_clean() ;
}

$url = (isset($_GET['u']))? $_GET['u'] : '';
$mode = (isset($_GET['m']))? $_GET['m'] : '';
$maxsize = (isset($_GET['s']))? intval($_GET['s']) : 0;
$png = (isset($_GET['p']))? 1 : 0;
$gc = (isset($_GET['gc']));
if (! $maxsize) $maxsize = 200;
define('UNIX_TIME', (isset($_SERVER['REQUEST_TIME'])? $_SERVER['REQUEST_TIME'] : time()));

switch($mode) {
	case 'i4k':
		$maxage = 86400; // TTL 1day
		$TTL = 10 * 86400; // 10days
		if ($url) {
			if (isset($_GET['c'])) $TTL = 0;
			
			$basename = md5(join("\t", array($url, $maxsize, $png))) . '.i4k';
			$file = $cachepath . '/' .  $basename;
			$size_file = $file . 's';
			
			if (is_file($size_file) && is_file($file) && filemtime($file) + $TTL > UNIX_TIME) {
				if (filesize($file)) {
					$size = getimagesize($file);
					if (isset($size['mime'])) {
						$mime = $size['mime'];
					}
					header('Content-Type: ' . $mime);
					header('Content-Length: ' . filesize($file));
					header('Cache-Control:max-age=' . $maxage);
					header('Expires: ' . gmdate( "D, d M Y H:i:s", UNIX_TIME + $maxage ) . ' GMT');
					readfile($file);
					exit();
				} else {
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: ' . $url);
				}
				exit();
			}
			
			// GC
			$gc = $cachepath . '/i4k.gc';
			if (! is_file($gc) || filemtime($gc) < UNIX_TIME - $maxage) {
				GC_i4k($cachepath, $TTL);
			}
			
			include_once($trustpath . '/class/hyp_common/hyp_common_func.php');
			
			$h = new Hyp_HTTP_Request();
			
			$h->url = $url;
			$h->get();
			if ($h->rc === 200) {
				if ($fp = fopen($file, "wb")) {
					flock($fp,  LOCK_EX);
					fwrite($fp, $h->data);
					fclose($fp);
					clearstatcache();
				} else {
					header('Location: ' . $url);
					exit();
				}
				$org_size = getimagesize($file);
				if ($org_size && $fp = fopen($size_file, "wb")) {
					flock($fp,  LOCK_EX);
					fwrite($fp, $org_size[0] . 'x' . $org_size[1]);
					fclose($fp);
				}
				
				if ($org_size) {
					$quality = 50;
					if ($maxsize >= 300 && $org_size[0] >= 300) $quality = 30;
					if ($maxsize >= 400 && $org_size[0] >= 400) $quality = 15;
				}

				$notImageHeader = (! preg_match('#^Content-Type: *image/(?:gif|jpeg|png)#mi', $h->header));
				if (HypCommonFunc::img4ktai($file, $maxsize, $png, $notImageHeader, $quality)) {
					$size = getimagesize($file);
					
					if ($size && $fp = fopen($size_file, "wb")) {
						flock($fp,  LOCK_EX);
						fwrite($fp, $size[0] . 'x' . $size[1]);
						fclose($fp);
					}

					if (isset($size['mime'])) {
						$mime = $size['mime'];
					}
					header('Content-Type: ' . $mime);
					header('Content-Length: ' . filesize($file));
					header('Cache-Control:max-age=' . $maxage);
					header('Expires: ' . gmdate( "D, d M Y H:i:s", UNIX_TIME + $maxage ) . ' GMT');
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
		} else if ($gc) {
			GC_i4k($cachepath, $TTL, TRUE);
		}
		break;
}

function GC_i4k($cachepath, $TTL, $showResult = FALSE) {
	touch($cachepath . '/i4k.gc');
	$i = 0;
	$i2 = 0;
	if ($handle = opendir($cachepath)) {
		while (false !== ($file = readdir($handle))) {
			if (substr($file, -4) === '.i4k' || substr($file, -5) === '.i4s') {
				$i2++;
				$target = $cachepath . '/' . $file;
				if (filemtime($target) < UNIX_TIME - $TTL) {
					unlink($target);
					$i++;
					//echo $file . '<br />';
				}
			}	
		}
		closedir($handle);
	}
	if ($showResult) echo $i . '/' . $i2 . ' files removed.';
}