<?php
/*
 * Created on 2008/02/11 by nao-pon http://hypweb.net/
 * $Id: favicon.php,v 1.4 2008/04/29 11:22:38 nao-pon Exp $
 */

/**
 * favicon.php - Outputs the cached favicon with proper headers
 *
 * @author      revulo
 * @licence     http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version     1.2
 * @link        http://www.revulo.com/PukiWiki/Plugin/Favicon.html
 */

ignore_user_abort(FALSE);
error_reporting(0);

define('FAVICON_TRUST_PATH' , dirname(__FILE__));

if (file_exists(FAVICON_TRUST_PATH . '/conf.php')) {
	include FAVICON_TRUST_PATH . '/conf.php';
} else {
	define('FAVICON_DEFAULT_IMAGE', FAVICON_TRUST_PATH . '/images/world_go.png');
	define('FAVICON_ERROR_IMAGE',   FAVICON_TRUST_PATH . '/images/link_break.png');
	define('FAVICON_CACHE_DIR',     FAVICON_TRUST_PATH . '/cache/');
	define('FAVICON_CACHE_TTL',     2592000);  // 60 * 60 * 24 * 30 [sec.] (1 month)
}

function get_favicon_url($url)
{
    if (! is_url($url)) return false;
    if (time() <= get_timestamp($url) + FAVICON_CACHE_TTL) {
        $cache = get_url_filename($url);
        return file_get_contents($cache);
    } else {
        return update_cache($url);
    }
}

function get_timestamp($url)
{
    static $time;

    if (empty($time)) {
        $filename = get_url_filename($url);
        $time     = (int)filemtime($filename);
    }
    return $time;
}

function get_url_filename($url)
{
    static $filename;

    if (empty($filename)) {
        $url = preg_replace('/^https?:\/\//', '', $url);
        $url = preg_replace('/index\.[a-z]+/i', '', $url);
        $url = rtrim($url, '/');
        $filename = FAVICON_CACHE_DIR . substr(rawurlencode($url), 0, 250) . '.url';
    }
    return $filename;
}

function get_image_filename($url)
{
    static $filename;

    if (empty($filename)) {
        if ($url === 'DefaultIcon') {
        	$filename = FAVICON_DEFAULT_IMAGE;
        } else if ($url === 'ErrorIcon') {
        	$filename = FAVICON_ERROR_IMAGE;
        } else {
            $url      = preg_replace('/^https?:\/\//', '', $url);
            $filename = FAVICON_CACHE_DIR . substr(rawurlencode($url), 0, 254);
        }
    }
    return $filename;
}

function if_modified_since()
{
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $str = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
        if (($pos = strpos($str, ';')) !== false) {
            $str = substr($str, 0, $pos);
        }
        if (strpos($str, ',') === false) {
            $str .= ' GMT';
        }
        $time = strtotime($str);
    }

    if (isset($time) && is_int($time)) {
        return $time;
    } else {
        return -1;
    }
}

function output_image($url, $time = 0)
{
    $filename = get_image_filename($url);
    if (function_exists('mb_http_output')) {
        mb_http_output('pass');
    }

    if ($time) {
        header('Expires: ' . gmdate('D, d M Y H:i:s', $time + FAVICON_CACHE_TTL) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $time) . ' GMT');
        header('Cache-Control: public, max-age=' . FAVICON_CACHE_TTL);
    }
    header('Content-Disposition: inline');
    header('Content-Length: ' . filesize($filename));
    header('Content-Type: image/x-icon');
    readfile($filename);
}


function update_cache($url)
{
    $html = http_get_contents($url, 4096);
    if ($html === false) {        // connection failed or timed out
        $favicon = 'DefaultIcon';
        //return false;
    } else if ($html === null) {  // 404 status code or unsupported scheme
        $favicon = 'ErrorIcon';
    } else {
        $url  = parse_url($url);
        $base = $url['scheme'] . '://' . $url['host'] . (isset($url['port']) ? ':' . $url['port'] : '');
        $url  = $base . (isset($url['path']) ? $url['path'] : '/');

        if (preg_match('/<link ([^>]*)rel=[\'"]?(?:shortcut )?icon[\'"]?([^>]*)/si', $html, $matches)) {
            $link = implode(' ', $matches);

            if (preg_match('/href=[\'"]?(https?:\/\/)?([^\'" ]+)/si', $link, $matches)) {
                $favicon = $matches[2];

                if ($matches[1]) {
                    $favicon = $matches[1] . $favicon;
                } else if ($favicon[0] === '/') {
                    $favicon = $base . $favicon;
                } else if (substr($url, -1) === '/') {
                    $favicon = $url . $favicon;
                } else {
                    $favicon = dirname($url) . '/' . $favicon;
                }
                str_replace('/./', '/', $favicon);
                while(preg_match('#[^/]+/\.\./#', $favicon)) {
                	$favicon = preg_replace('#[^/]+/\.\./#', '', $favicon);
                }
            }
        }
        if (empty($favicon)) {
            $favicon = $base . '/favicon.ico';
        }

        $data = http_get_contents($favicon);
        if ($data === false) {                   // connection failed or timed out
            return false;
        } else if (is_image($data) === false) {  // no favicon or unknown format
            $favicon = 'DefaultIcon';
        } else {
            $image = get_image_filename($favicon);
            if (file_put_contents($image, $data) === FALSE) {
            	$favicon = 'ErrorIcon';
            }
        }
    }

    $filename = get_url_filename($url);
    file_put_contents($filename, $favicon);
    return $favicon;
}

function http_get_contents(& $url, $size = 0)
{
    file_put_contents(get_url_filename($url), 'DefaultIcon');

	include_once dirname(dirname(__FILE__)) . '/hyp_common_func.php';
	
	$ht = new Hyp_HTTP_Request();
	$ht->init();
	$ht->url = $url;
	if ($size) $ht->getSize = $size;
	$ht->ua = 'Mozilla/5.0';
	$ht->connect_timeout = 2;
	$ht->read_timeout = 5;
	$ht->get();
	if ($size) $url = $ht->url;
	return ($ht->rc == 404 || $ht->rc == 410 || $ht->rc > 600 || $ht->rc < 100)? null : $ht->data;
}

function is_image($data)
{
    if (strncmp("\x00\x00\x01\x00", $data, 4) === 0) {
        // ICO
        return true;
    } else if (strncmp("\x89PNG\x0d\x0a\x1a\x0a", $data, 8) === 0) {
        // PNG
        return true;
    } else if (strncmp('BM', $data, 2) === 0) {
        // BMP
        return true;
    } else if (strncmp('GIF87a', $data, 6) === 0 || strncmp('GIF89a', $data, 6) === 0) {
        // GIF
        return true;
    } else if (strncmp("\xff\xd8", $data, 2) === 0) {
        // JPEG
        return true;
    } else {
        return false;
    }
}

function is_url(& $url)
{
	$url = preg_replace('/(\?|#).*/', '', $url);
	
	if ($url{0} === '/') {
		$p_url  = parse_url(XOOPS_URL);
        $base = $p_url['scheme'] . '://' . $p_url['host'] . (isset($p_url['port']) ? ':' . $p_url['port'] : '');
        $url  = $base . $url;
	} else {
		$_hosts = @ file(FAVICON_TRUST_PATH . '/group.def.hosts');
		if (file_exists(FAVICON_TRUST_PATH . '/group.hosts')) {
			$_hosts = array_merge($_hosts, file(FAVICON_TRUST_PATH . '/group.hosts'));
		}
		if ($_hosts) {
			foreach($_hosts as $host) {
				list($from, $to) = explode(' ', $host);
				$hosts[trim($to)] = trim($from);
			}
			$p_url = parse_url($url);
			if ($match = array_search($p_url['host'], $hosts)) {
				$url = $match;
			}
		}
	}
	$url = preg_replace('/([" \x80-\xff]+)/e', 'rawurlencode("$1")', $url);
	return (preg_match('/(?:https?|ftp|news):\/\/[!~*\'();\/?:\@&=+\$,%#\w.-]+/', $url));
}

function redirect_icon($url)
{
	$p_url  = parse_url(XOOPS_URL);
    $base = $p_url['scheme'] . '://' . $p_url['host'] . (isset($p_url['port']) ? ':' . $p_url['port'] : '');
	$uri = preg_replace('/url=[^&]+/', 'icon=' . rawurlencode($url), $_SERVER['REQUEST_URI']);
	header('Cache-Control: public, max-age=' . FAVICON_CACHE_TTL );
	header('Location: '.$base . $uri);
	exit();
}

function output_icon($icon) {

	if (in_array($icon, array('DefaultIcon', 'ErrorIcon'))) {
		$time = time();
	} else {
		$time = filemtime(get_image_filename($icon));
	}

	if ($time <= if_modified_since()) {
	    header('HTTP/1.1 304 Not Modified');
	    header('Cache-Control: public, max-age=' . FAVICON_CACHE_TTL );
	    exit;
	}

	output_image($icon, $time);
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data)
    {
        $fp = fopen($filename, file_exists($filename) ? 'r+b' : 'wb');
        if ($fp === false) {
            return false;
        }
        flock($fp, LOCK_EX);
        rewind($fp);
        $bytes = fwrite($fp, $data);
        fflush($fp);
        ftruncate($fp, ftell($fp));
        flock($fp, LOCK_UN);
        fclose($fp);
        return $bytes;
    }
}

if (isset($_GET['icon'])) {
	output_icon($_GET['icon']);
	exit;
}

@ set_time_limit(5);
$url = get_favicon_url(rawurldecode(@ $_GET['url']));

if ($url === false) {
    output_image(FAVICON_ERROR_IMAGE);
    exit;
}

redirect_icon($url);
exit;
