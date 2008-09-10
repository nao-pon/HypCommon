<?php
/*
 * Created on 2008/09/04 by nao-pon http://hypweb.net/
 * License: GPL v2 or (at your option) any later version
 * $Id: redirect.php,v 1.1 2008/09/10 04:27:10 nao-pon Exp $
 */

// clear output buffer
while( ob_get_level() ) {
	ob_end_clean() ;
}

if (isset($_GET['l'])) {
	$url = $_GET['l'];
	$info = get_url_info($url);

	$mobile = $info['handheld']? '<p>Mobile: <a href="'.$info['handheld'].'">'.$info['handheld'].'</a><p>' : '';
	
	$type = $size = '';
	if ($info['header']) {
		if (preg_match('/^Content-Type:\s*(.+)$/mi', $info['header'], $match)) {
			$type = '<p>Content type: ' . htmlspecialchars($match[1]) . '</p>';
		} 
		if (preg_match('/^Content-Length:\s*([\d]+)/mi', $info['header'], $match)) {
			$size = '<p>Content size: ' . floor($match[1] / 1024) . 'KB' . '</p>';
		} 
	}
	
	$google = 'http://www.google.co.jp/gwt/n?u=' . rawurlencode($url);
	$url = str_replace('&amp;', '&',htmlspecialchars($_GET['l']));
	
	$lang = XOOPS_TRUST_PATH . '/class/hyp_common/language/' . $xoopsConfig['language'] . '/redirect.lng.php';
	if (!is_file($lang)) {
		$lang = XOOPS_TRUST_PATH . '/class/hyp_common/language/english/redirect.lng.php';
	}
	include_once $lang;
	
	header('Content-type: text/html; charset=Shift_JIS');
	echo '<html><head><title>' . HYP_LANG_REDIRECT_TITLE . '</title></head>' .
			'<body>' .
			'<p>' . HYP_LANG_REDIRECT_DESC . '</p>' .
			'<p><a href="'.$url.'">'.$url.'</a></p>' .
			$type .
			$size .
			$mobile .
			'<a href="'.$google.'">' . HYP_LANG_REDIRECT_USE_GOOGLE . '</a>' .
			'</body></html>';
}

function get_url_info ($url) {

	$ttl = 60 * 60 * 24; // 1day
	$cache = XOOPS_ROOT_PATH . '/class/hyp_common/cache/' . md5($url) . '.rdi';
	
	if (is_file($cache) && filemtime($cache) + $ttl > time()) {
		return unserialize(file_get_contents($cache));
	}
	
	include_once XOOPS_TRUST_PATH . '/class/hyp_common/hyp_common_func.php';
	
	$h = new Hyp_HTTP_Request();
	$h->url = $url;
	$h->getSize = 4096;
	$h->get();
	
	$ret = array(
		'header' => '',
		'handheld' => '',
	);
	if ($h->rc === 200 || $h->rc === 206) {
		$html = $h->data;
		$ret['header'] = $h->header;
		if (strpos($html, '<body') !== FALSE) {
			list($head, $dum) = explode('<body', $html, 2);
			if (preg_match_all('/<link [^>]*?rel=(\'|")alternate\\1[^>]*?>/i', $head, $match)) {
				foreach ($match[0] as $rel) {
					if (preg_match('/media=(\'|")handheld\\1/i', $rel) && preg_match('/href=(\'|")(.+)?\\1/i', $rel, $link)) {
						$ret['handheld'] = str_replace('&amp;', '&', $link[2]);
					}
				}
			}
		}
	}
	
	if ($fp = fopen($cache, 'wb')) {
		fwrite($fp, serialize($ret));
		fclose($fp);
	}
	
	return $ret;
}
