<?php
/*
 * Created on 2008/06/17 by nao-pon http://hypweb.net/
 * License: GPL v2 or (at your option) any later version
 * $Id: hyp_ktai_render.php,v 1.18 2008/08/22 08:08:46 nao-pon Exp $
 */

if (! class_exists('HypKTaiRender')) {

//// mbstring ////
if (! extension_loaded('mbstring') && ! class_exists('HypMBString')) {
	require (dirname(dirname(__FILE__)) . '/mbemulator/mb-emulator.php');
}

class HypKTaiRender
{
	var $contents = array();
	var $outputMode = 'html';
	var $inputEncode = '';
	var $outputEncode = 'SJIS';
	var $myRoot = '';
	var $pagekey = '_p_';
	var $hashkey = '_h_';
	var $maxSize = 0;
	var $inputHtml = '';
	var $inputHead = '';
	var $inputBody = '';
	var $outputHtml = '';
	var $outputHead = '';
	var $outputBody = '';
	var $langcode = 'ja';
	var $xmlDocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	var $vars = array();
	var $keymap = array();
	var $keybutton = array();
	var $SERVER = array();
	var $Config_showImgHosts = array('amazon.com', 'yimg.jp', 'yimg.com', 'ba.afl.rakuten.co.jp', 'assoc-amazon.jp', 'ad.linksynergy.com');
	var $Config_directLinkHosts = array('amazon.co.jp', 'ck.jp.ap.valuecommerce.com');
	var $Config_redirect = '';
	var $Config_urlRewrites = array();
	var $Config_emojiDir = '';
	var $Config_icons = array();
	var $Config_imageConvert = FALSE;
	var $Config_encodeHintName = 'HypEncHint';
	var $Config_encodeHintWord = '';
	var $Config_hypCommonURL = '';
	var $Config_pictSizeMax = '200';
	
	function HypKTaiRender () {
		$this->SERVER = $_SERVER;
		
		$this->keymap['prev'] = '4';
		$this->keymap['next'] = '6';
		
		$this->Config_icons['first'] = '((s:465d))';
		$this->Config_icons['prev']  = '((s:465b))';
		$this->Config_icons['next']  = '((s:465a))';
		$this->Config_icons['last']  = '((s:465c))';
		
		$this->Config_icons['extLink'] = '((i:f8d9))';
		$this->Config_icons['hTag']    = '((i:f8e4))';
		
		$this->contents['header'] = '';
		$this->contents['body'] = '';
		$this->contents['footer'] = '';
		
		$this->myRoot = 'http' . (!empty($this->SERVER['HTTPS'])? 's' : '' ) . '://'
		         . $this->SERVER['SERVER_NAME'] . (($this->SERVER['SERVER_PORT'] == 80)? '' : ':'.$this->SERVER['SERVER_PORT']);
		$this->myRoot = rtrim($this->myRoot, '/');
		
		$this->inputEncode = mb_internal_encoding();
		
		$this->_uaSetup();

		// Amazon ECS DetailPageURL Rewrite
		$this->Config_urlRewrites['regex'][] = '#^(http://(?:www\.)?amazon\.[^/]+?)/(?:[^/]+?/)?dp/([a-z0-9]+).+?tag%3D([a-z0-9-]+).*$#iS';
		$this->Config_urlRewrites['tostr'][] = '$1/gp/aw/rd.html?ie=UTF8&amp;dl=1&amp;uid=NULLGWDOCOMO&amp;lc=msn&amp;a=$2&amp;at=$3&amp;url=%2Fgp%2Faw%2Fd.html';
	
		// Amazon Search results link Rewrite
		$this->Config_urlRewrites['regex'][] = '#^(http://(?:www\.)?amazon.[^/]+?)/gp/search\?.+?tag=([a-z0-9]+).+?keywords=([^& \'"]+).*$#iS';
		$this->Config_urlRewrites['tostr'][] = '$1/gp/aw/rd.html?ie=UTF8&amp;k=$3&amp;uid=NULLGWDOCOMO&amp;at=$2&amp;m=Blended&amp;url=%2Fgp%2Faw%2Fs.html&amp;lc=mqs';	
		$this->Config_urlRewrites['regex'][] = '#^(http://(?:www\.)?amazon.[^/]+?)/gp/search\?.+?keywords=([^& \'"]+).+?tag=([a-z0-9]+).*$#iS';
		$this->Config_urlRewrites['tostr'][] = '$1/gp/aw/rd.html?ie=UTF8&amp;k=$2&amp;uid=NULLGWDOCOMO&amp;at=$3&amp;m=Blended&amp;url=%2Fgp%2Faw%2Fs.html&amp;lc=mqs';	
		
	}
	
	function set_myRoot ($url) {
		$parsed_url = parse_url($url);
		$this->myRoot = $parsed_url['scheme'].'://'.$parsed_url['host'].(isset($parsed_url['port'])? ':' . $parsed_url['port'] : '');
	}
	
	function removeSID ($url) {
		return $this->removeQueryFromUrl($url, session_name());
	}

	function removeQueryFromUrl ($url, $keys) {
		if (! is_array($keys)) {
			$keys = array($keys);
		}
		foreach ($keys as $key) {
			$url = preg_replace('/(?:(\?)|&(?:amp;)?)' . $key . '(?:=[^&#>]+)?(&(?:amp;)?|$)/', '$1$2', $url);
		}
		$url = str_replace('?&', '?', $url);
		return $url;
	}
	
	function doOptimize () {

		
		if ($this->inputHtml) {
			$this->_extractHeadBody();
			$header = $footer = '';
			$body = $this->inputBody;
		} else {
			foreach(array('header', 'body', 'footer') as $key) {
				if (isset($this->contents[$key])) {
					$$key = $this->contents[$key];
				} else {
					$$key = '';
				}
			}
		}
		
		
		$header = mb_convert_encoding($this->html_diet_for_hp($header), $this->outputEncode, $this->inputEncode);
		$body = mb_convert_encoding($this->html_diet_for_hp($body), $this->outputEncode, $this->inputEncode);
		$footer = mb_convert_encoding($this->html_diet_for_hp($footer), $this->outputEncode, $this->inputEncode);
		
		foreach(array('header', 'body', 'footer') as $var) {
			$str =& $$var;
			if (preg_match('/\(\((?:e|i|s):[0-9a-f]{4}\)\)/S', $str)) {
				if (! isset($mpc)) {
					if (! class_exists('MobilePictogramConverter')) {
						HypCommonFunc::loadClass('MobilePictogramConverter');
					}
					$mpc =& MobilePictogramConverter::factory_common();
					$mpc->setImagePath($this->Config_emojiDir);
					$mpc->setFromCharset(MPC_FROM_CHARSET_SJIS);
				}
				$mpc->setString($str);
				$str = $mpc->autoConvertModKtai();
			}
		}
		
		$pager = '';
		
		$pnum = empty($_GET[$this->pagekey])? 0 : intval($_GET[$this->pagekey]);
		
		$extra_len = strlen($header . $footer);

		if ($this->maxSize && (strlen($body) + $extra_len) > $this->maxSize) {
			
			$margin = 200;
			$this->splitMaxSize = $this->maxSize - $extra_len - $margin;
			list($pages, $ids) = $this->html_split($body);

			if (isset($_GET[$this->hashkey]) && isset($ids[$_GET[$this->hashkey]])) {
				$pnum = $ids[$_GET[$this->hashkey]];
			}

			if ($header) {
				list(, $_ids) = $this->html_split($header, $pnum);
				$ids = array_merge($ids, $_ids);
			}
			if ($footer) {
				list(, $_ids) = $this->html_split($footer, $pnum);
				$ids = array_merge($ids, $_ids);
			}
			
			$pageids = array();
			if ($ids) {
				foreach($ids as $_h => $_p) {
					$pageids[$_p][] = $_h;
				}
			}
			
			$pagecount = count($pages);
			$pnum = max(0, min($pnum, $pagecount - 1));
			
			$body = $pages[$pnum];
			
			if (! empty($pageids[$pnum])) {
				rsort($pageids[$pnum]);
				$h_reg = array();
				foreach ($pageids[$pnum] as $_h) {
					$_h = preg_quote($_h, '/');
					$h_reg[] = $_h;
				}
				$h_reg = '(?:' . preg_quote($this->hashkey, '/') . '=(?:' . join('|', $h_reg) . '))';
			} else {
				$h_reg = '(?!)';
			}
			
			// Make page navigation
			$base = '?';
			$join = '';
			$querys = isset($this->SERVER['QUERY_STRING'])? $this->SERVER['QUERY_STRING'] : '';
			if ($querys) {
				$querys = preg_replace('/(?:^|&)' . preg_quote($this->pagekey, '/').'=[^&]+/', '', $querys);
				$querys = preg_replace('/(?:^|&)' . preg_quote($this->hashkey, '/').'=[^&]+/', '', $querys);
				$querys = preg_replace('/(?:^|&)' . preg_quote(session_name(), '/') . '=[^&]+/', '', $querys);
				if ($querys) {
					$base .= str_replace('&', '&amp;', $querys);
					$this->pagekey = '&amp;' . $this->pagekey;
				}
			}
			
			$accesskey = 'accesskey';
			
			$prev = $pnum - 1;
			$next = $pnum + 1;
			if ($pnum > 0) {
				if ($pnum !== 1) $pager[] = '<a href="' . $base . $this->pagekey . '=0' . '">' . $this->Config_icons['first'] . '</a>';
				$pager[] = '<a href="' . $base . $this->pagekey . '=' . $prev . '" ' . $accesskey . '="' . $this->keymap['prev'] . '">' . $this->keybutton[$this->keymap['prev']] . $this->Config_icons['prev'] . '</a>';
			}
			$pager[] = $next . '/' . $pagecount . ' ';
			if ($pnum < $pagecount - 1) {
				$pager[] = '<a href="' . $base . $this->pagekey . '=' . $next . '" ' . $accesskey . '="' . $this->keymap['next'] . '">' . $this->keybutton[$this->keymap['next']] . $this->Config_icons['next'] . '</a>';
				if ($pnum !== $pagecount - 2) $pager[] = '<a href="' . $base . $this->pagekey . '=' . ($pagecount - 1) . '">' . $this->Config_icons['last'] . '</a>';
			}

			$pager = $this->html_give_session_id($pager);
			if ($this->outputMode = 'xhtml') {
				$pager = '<div style="text-align:center">' . join(' ', $pager) . '</div>';
			} else {
				$pager = '<center>' . join(' ', $pager) . '</center>';
			}

			$mpc->setString($pager);
			$pager = $mpc->autoConvertModKtai();
			
		} else {
			$h_reg = preg_quote($this->hashkey, '/') . '=[^&#]+';
		}
		
		// Optimize query strings
		$_func = create_function(
			'$match',
			'if ($match[3][0] === \'?\') $match[3] = preg_replace(\'/^.*?'.$h_reg.'(#[^#]+)?$/\', \'' . $this->SERVER['REQUEST_URI'] . '$1\', $match[3]);' . 
			'$match[3] = preg_replace(\'/(?:&(?:amp;)?)+/\', \'&amp;\', $match[3]);' .
			'$match[3] = str_replace(\'?&amp;\', \'?\', $match[3]);' .
			'$match[3] = str_replace(\'&amp;#\', \'#\', $match[3]);' .
			'return $match[1] . $match[3] . (isset($match[4])? $match[4] : \'\');'
		);
		
		$header = preg_replace_callback('#(<a[^>]*? href=([\'"])?)([^\s"\'>]+)(\\2)?#isS', $_func, $header);

		$body = preg_replace_callback('#(<a[^>]*? href=([\'"])?)([^\s"\'>]+)(\\2)?#isS', $_func, $body);
			
		$footer = preg_replace_callback('#(<a[^>]*? href=([\'"])?)([^\s"\'>]+)(\\2)?#isS', $_func, $footer);
		
		$this->outputBody = $header . $pager . $body . $pager . $footer;
		
		if ($this->inputHtml) {
			$this->outputHtml = '<html><head>' . $this->outputHead . '</head><body>' . $this->outputBody . '</body></html>';
		}
		return;
	}

	// HTML を携帯端末用にシェイプアップする
	function html_diet_for_hp ($body) {
		// タグを小文字に統一
		$body = preg_replace('#</?[a-zA-Z]+#eS', 'strtolower("$0")', $body);

		// 半角カナに変換
		if (function_exists('mb_convert_kana')) {
			$body = preg_replace_callback('/(^|<textarea.+?\/textarea>|<pre.+?\/pre>|<[^>]*>)(.*?)(?=<textarea.+?\/textarea>|<pre.+?\/pre>|<[^>]*>|$)/sS',
				create_function(
					'$match',
					'return $match[1] . mb_convert_kana(preg_replace(\'/[\s]+/\',\' \',str_replace(array("\r\n","\r","\n"),\'\',$match[2])), \'knr\', \''.$this->inputEncode.'\');'
				), $body);
		}

		// Is <form> action anchor only?
		$body = preg_replace_callback('#(<form[^>]*?\baction=([\'"])?)([^\s"\'>]+)((?:\\2)?)#isS',
			create_function(
				'$match',
				'if ($match[3][0] !== \'#\') return $match[0];
				return $match[1] . preg_replace(\'/#.*$/\', \'\', \'' . $this->SERVER['REQUEST_URI'] . '\') . (($match[3] !== \'#\')? $match[3] : \'\') . $match[4];'
			), $body);

		// Hint character for encoding judgment
		if (! empty($this->Config_encodeHintWord)) {
			$body = preg_replace('/<form[^>]+?>/isS',
				'$0' . 
				'<input name="' . $this->Config_encodeHintName . '" type="hidden" value="' . $this->Config_encodeHintWord . '"/>',
				$body);
		}

		//// Remove etc.
		// <a> with JavaScript
		$body = preg_replace('#<a[^>]+?href=(?:"|\')?javascript:[^>]+?>(.+?)</a>#isS', '$1', $body);

		//// tag attribute
		$body = str_replace('\\"', "\x08", $body);
		// Any
		$reg = '#(<[^>]+?)\s+(?:class|clear|target|nowrap|title|on[^=]+)=(?:\'[^\']*\'|"[^"\x08]*")([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		$reg = '#(<[^>]+?)\s+(?:class|clear|target|nowrap|title|on[^=]+?|cell[^=]+?)=[^ >/]+([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		// img
		$reg = '#(<img[^>]+?)\s+(?:hspace|vspace|border)=(?:\'[^\']*\'|"[^"\x08]*")([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		$reg = '#(<img[^>]+?)\s+(?:hspace|vspace|border)=[^ >/]+([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		// input
		$reg = '#(<input[^>]+?)\s+(?:size|alt|border)=(?:\'[^\']*\'|"[^"\x08]*")([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		$reg = '#(<input[^>]+?)\s+(?:size|alt|border)=[^ >/]+([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		$body = str_replace("\x08", '\\"', $body);
		
		// css property
		$reg = '#(<[^>]+?style=[\'"][^\'"]*?)\s*(?<!-)(?:display:\s*non|width|height|margin|padding|float|position|left|top|right|bottom|clear|overflow)[^;\'"]+(?:[ ;]+)?([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		
		// password to text
		$body = preg_replace('#(<input[^>]*?\s+type=[\'"])password#iS', '$1text', $body);
		
		// id to name
		$body = preg_replace_callback('#<([a-zA-Z]+)([^>]+)>#sS', array(& $this, '_attr_idToname'), $body);
		
		$pat = $rep = array();
		
		$pat[] = '#<!--.+?-->#sS';
		$rep[] = '';
		
		$pat[] = '#<((?:no)?script|style)\b.+?/\\1>#sS';
		$rep[] = '';
		
		$pat[] = '#</?(?:code|label|small|script|link|strong|b)\b[^>]*>#S';
		$rep[] = '';

		$pat[] = '#<del[^>]*>#S';
		$rep[] = '[del]';

		$pat[] = '#(<(?!textarea)[^>]+?) style=(?:\'\'|"")#S';
		$rep[] = '$1';

		$pat[] = '#<h([5-6])(.+?)/h\\1>#sS';
		$rep[] = '<h4$2/h4>';

		// Add icon
		if (! empty($this->Config_icons['hTag'])) {
			$pat[] = '#(<h[1-6][^>]*?>)#S';
			$rep[] = '$1' . $this->Config_icons['hTag'];
		}
		
		if ($this->outputMode === 'xhtml') {
			$pat[] = '#(<(?:[bh]r|img)\b[^>]*?[^/])>#S';
			$rep[] = '$1/>';
		}
		
		$body = preg_replace($pat, $rep, $body);

		if ($this->outputMode === 'xhtml') {
			$pat = array(' />', '</del>', '<br>',  '<hr>', '<center>', '</center>',  '&deg;');
			$rep = array('/>',  '[/del]', '<br/>', '<hr/>', '<div style="text-align:center">', '</div>', '&#176;');
		} else {
			$pat = array(' />', '/>', '</li>', '</del>');
			$rep = array('>'  , '>' , ''     , '[/del]');
		}
		$body = str_replace($pat, $rep, $body);

		// <table>を正規化
		// 入れ子テーブルを展開
		$args = preg_split('#(<table(?:.(?!<table))+?</table>)#sS', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
		if (isset($args[1])) {
			$body = '';
			foreach($args as $val) {
				if (substr($val, 0, 6) === '<table') {
					// remove tag attribute
					$val = str_replace('\\"', "\x08", $val);
					$reg = '#(<table[^>]+?)\s+(?:align|width)=(?:\'[^\']*\'|"[^"\x08]*")([^>]*>)#iS';
					while(preg_match($reg, $val)) {
						$val = preg_replace($reg, '$1$2', $val);
					}
					$val = str_replace("\x08", '\\"', $val);
				} else {
					$val = preg_replace('#(</?)(?:t(?:able|r|h))[^>]*?>#S', '$1div>', $val);
					$val = preg_replace('#</td>#S', ' ', $val);
					$val = preg_replace('#</?(?:col|t(?:d|body|head|foot))[^>]*?>#S', '', $val);
				}
				$body .= $val;
			}
		}

		// Remove empty elements
		$body = preg_replace('#<([bipqsu]|(?!textarea|td)[a-z]{2,})(?: [^>]+)?></\\1>#', '', $body);
		
		$body = $this->html_give_session_id($body);

		// Host name
		$body = preg_replace('#(<[^>]+? (?:href|src)=[\'"]?)'.preg_quote($this->myRoot, '#').'/?#iS', '$1/', $body);
		
		return $body;
	}

	// HTML を指定サイズ内に収まるように分割する
	function html_split($html, $startnum = 0) {

		// ページ分断で閉じられなかったらきちんと閉じて次ページの先頭で再度開くタグ
		$checks = array('address', 'blockquote', 'center', 'div', 'dl', 'fieldset', 'ol', 'p', 'pre', 'table', 'td', 'tr', 'ul');
		
		$out = array();
		$ids = array();
		$stacks = array();
		$opentags = array();
		$i = $startnum;
		$len = 0;

		$arr = $this->_html_split_make_array($html);
		foreach($arr as $key => $val) {
		
			if (! isset($out[$i])) $out[$i] = '';
			$out[$i] .= $val;
			$len += strlen($val);

			// タグの開閉をチェックする
			if ($val[0] === '<') {
				if (preg_match('/^<([a-zA-Z]+)/', $val, $match) && in_array($match[1], $checks)) {
					array_unshift($stacks, $match[1]);
					array_unshift($opentags, $val);
					$len += strlen($match[1]) + 3;
				}
				if (preg_match('/\/([a-zA-Z]+)>$/', $val, $match) && in_array($match[1], $checks)) {
					$stack_key = array_search($match[1], $stacks);
					if ($stack_key !== FALSE) {
						unset($stacks[$stack_key]);
						unset($opentags[$stack_key]);
						$len -= (strlen($match[1]) + 3);
					}
				}
			}

			// id or name のチェック
			if (preg_match_all('/<[^>]+?(?:id|name)=(\'|")(.+?)\\1/iS', $val, $match, PREG_PATTERN_ORDER)) {
				foreach($match[2] as $_id) {
					$ids[$_id] = $i;
				}
			}

			// 次の塊も合わせてバイト数チェック
			$nextlen = (isset($arr[$key + 1]))? strlen($arr[$key + 1]) : 0;
			if (($len + $nextlen) > $this->splitMaxSize) {
				// 次のページへ
				$len = 0;
				$next = $i + 1;
				$out[$next] = '';
				foreach ($stacks as $_key => $_tag) {
					$out[$i] .= '</' . $_tag . '>';
					$out[$next] = $opentags[$_key] . $out[$next];
					$len += strlen($opentags[$_key]);
				}
				$i++;
			}
		}
		
		return array($out, $ids);
	}

	function html_give_session_id ($html) {
		// For regex simplify
		$html = str_replace(array('<a', '<A'), "\x08", $html);
		
		// Check IMG & INPUT src
		$html = preg_replace_callback('#(<(img|input)[^>]*?) src=([\'"])?([^\s"\'>]+)(?:\\3)?([^>]*>)([^\x08]*?</a>)?#isS',
				array(& $this, '_html_check_img_src'), $html);

		// Check A href
		$html = preg_replace_callback('#(\x08[^>]*? href=([\'"])?)([^\s"\'>]+)(\\2)?#isS',
				array(& $this, '_href_give_session_id'), $html);
		
		// Back to "<a" from "\x08"
		$html = str_replace("\x08", '<a', $html);
		
		// Check FORM
		if (defined('SID') && SID) {
			list($sid_key, $sid_val) = explode('=', SID);
			$html = preg_replace_callback('#(<form[^>]*?\baction=([\'"])?)([^\s"\'>]+)((?:\\2)?[^>]*>)#isS',
				create_function(
					'$match',
					'if (strpos($match[3], "'.$this->myRoot.'") !== 0 && preg_match("#^https?://#i", $match[3])) return $match[0];
					return $match[0] . \'<input type="hidden" name="'.$sid_key.'" value="'.$sid_val.'">\';'
				), $html);
		}
		
		return $html;
	}

	function checkIp ($address, $carrier) {
		$ret = FALSE;
		$ip_file = dirname(__FILE__) . '/ipranges/' . strtolower($carrier) . '.ip';
		
		if (file_exists($ip_file)) {
			$iprange = file($ip_file);
			$iprange = array_diff(array_map('trim', $iprange), array(''));
			$address = $this->_dumpAddress($address);
			$ret = $this->_compareIp($address, $iprange);
		}
		return $ret;
	}

	function getHtmlDeclaration () {
		if ($this->outputMode === 'xhtml') {
			switch (strtoupper($this->outputEncode)) {
				case 'SJIS':
				case 'SHIFT-JIS':
				case 'SHIFT_JIS':
					$encode = 'Shift_JIS';
					break;
				default :
					$encode = $this->outputEncode;
			}
			$dec = '<?xml version="1.0" encoding="' . $encode . '"?>'
			     . $this->xmlDocType
			     . '<html xmlns="http://www.w3.org/1999/xhtml" lang="' . $this->langcode . '" xml:lang="' . $this->langcode . '">';
		} else {
			$dec = '<html>';
		}
		return $dec;
	}
	
	function getOutputContentType () {
		$ctype = 'text/html';
		if ($this->outputMode === 'xhtml') {
			if ($this->checkIp($this->SERVER['REMOTE_ADDR'], $this->vars['ua']['carrier']) || strpos($this->SERVER['HTTP_USER_AGENT'], 'DoCoMo/2.0 ISIM') === 0) {
				$ctype = 'application/xhtml+xml';
			}
		}
		return $ctype;
	}
	
	function _extractHeadBody () {

		$this->inputHead = '';
		$this->inputBody = '';

		// preg_match では、サイズが大きいページで正常処理できないことがあるので。
		$arr1 = explode('<head', $this->inputHtml, 2);
		if (isset($arr1[1]) && strpos($arr1[1], '</head>') !== FALSE) {
			$arr2 = explode('</head>', $arr1[1], 2);
			$this->inputHead = substr($arr2[0], strpos($arr2[0], '>') + 1);
		}
		$arr1 = explode('<body', $this->inputHtml, 2);
		if (isset($arr1[1]) && strpos($arr1[1], '</body>') !== FALSE) {
			$arr2 = explode('</body>', $arr1[1], 2);
			$this->inputBody = substr($arr2[0], strpos($arr2[0], '>') + 1);
		}

		if ($this->inputHead) {
			$_head = '';
			if (preg_match('#<meta[^>]+http-equiv=("|\')Refresh\\1[^>]*>#iUS', $this->inputHead, $match)) {
				$_head .= str_replace('/>', '>', $match[0]);
			} else if (preg_match('#<title[^>]*>.*</title>#isUS', $this->inputHead, $match)) {
				$_head .= mb_convert_encoding($match[0], $this->outputEncode, $this->inputEncode);
			}
			$this->outputHead = $_head;
		}
	}

	function _html_split_make_array ($html) {
		$u = '';
		// 文字コード別に1文字の正規表現をセット
		switch (strtoupper($this->outputEncode)) {
			case 'EUC-JP':
			case 'EUC':
			case 'EUCJP':
			case 'EUC_JP':
				$p = '(?:[\xA1-\xFE][\xA1-\xFE]|[\x01-\x7F]|\x8E[\xA0-\xDF])';
				break;
			case 'SHIFT_JIS':
			case 'SHIFT-JIS':
			case 'SJIS':
				$p = '(?:[\x81-\x9F\xE0-\xFC][\x40-\xFC]|[\x01-\x7F]|[\xA0-\xDF])';
				break;
			case 'UTF-8':
			case 'UTF_8':
			case 'UTF8':
				$u = 'u';
			default:
				$p = '.';
		}
		
		// できるだけひとまとめにする塊
		$oneps = array(
			'table',
			'th',
			'tr',
			'td',
			'div',
			'p',
			'h1',
			'h2',
			'h3',
			'h4',
			//'h5',
			//'h6',
			'ul',
			'ol'
		);
		$regs = array();
		foreach($oneps as $onep) {
			$regs[] = '<'.$onep.'(?:.(?!<'.$onep.'))+?</'.$onep.'>';
		}
		
		$first = '';
		$last = '';
		
		if (preg_match('#^(<([a-zA-Z]+)[^>]*>)(.*)(</\\2>)$#sS', $html, $match)) {
			$first = $match[1];
			$html = $match[3];
			$last = $match[4];
		}

		$args = preg_split(
			'#(' .
			'(?><form.+?/form>)|' .
			''.join('|', $regs) . '|' .
			'<a.+?/a>|' .
			'<[^>]+>|' .
			'&(?:[a-zA-Z]{2,8}|\#[0-9]{1,6}|\#x[0-9a-fA-F]{2,4});|' .
			'\s|' .
			$p . '{,80}' .
			')#sS'.$u, $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		$out = array();
		if ($first) $out[] = $first;
		foreach($args as $arg) {
			if (strlen($arg) > $this->splitMaxSize) {
				if (substr($arg, 0, 5) === '<form') {
					$out[] = '<div>[ Can\'t edit with your device. (form is too large.) ]</div>';
					continue;
				} else {
					if ($arg === $html) {
						if ($arg[0] === '<') {
							if (preg_match('/^<([a-z]+)/i', $arg, $match)) {
								$close = '</'.$match[1].'>';
								list($arg1, $arg2) = explode($close, $arg, 2);
								$arg1 .= $close;
								$out = array_merge($out, $this->_html_split_make_array($arg1));
								$out = array_merge($out, $this->_html_split_make_array($arg2));
								continue;
							}
						}
						$out[] = '<div>[ Rendering error. ]</div>';
						continue;
					}
					$out = array_merge($out, $this->_html_split_make_array($arg));
				}
			} else if ($arg !== '') {
				$out[] = $arg;
			}
		}
		if ($last) $out[] = $last;
		
		return $out;
	}
	
	function _uaSetup () {
		$this->keybutton = array(
			'1'	=>	'[1]',
			'2'	=>	'[2]',
			'3'	=>	'[3]',
			'4'	=>	'[4]',
			'5'	=>	'[5]',
			'6'	=>	'[6]',
			'7'	=>	'[7]',
			'8'	=>	'[8]',
			'9'	=>	'[9]',
			'0'	=>	'[0]',
			'#'	=>	'[#]',
			'*'	=>	'[*]'
		);
		$this->vars['ua']['isBot'] = FALSE;
		$this->vars['ua']['isKTai'] = FALSE;
		$this->vars['ua']['carrier'] = 'Unknown';
		$this->vars['ua']['allowPNG'] = FALSE;
		$this->vars['ua']['allowInputImage'] = FALSE;
		
		if (isset($this->SERVER['HTTP_USER_AGENT'])) {
			$this->vars['ua']['isBot'] = preg_match('/Googlebot-Mobile|Y!J-SRD/i', $this->SERVER['HTTP_USER_AGENT']);
			
			if ( preg_match('#(?:^(?:KDDI-[^\s]+ )?|\b)([a-zA-Z.-]+)(?:/([0-9.]+))?#', $this->SERVER['HTTP_USER_AGENT'], $match) ) {
			
				$this->vars['ua']['agent'] = $ua_agent = $this->SERVER['HTTP_USER_AGENT'];
				$this->vars['ua']['name'] = $ua_name = $match[1];
				$this->vars['ua']['ver'] = $ua_vers = isset($match[2])? $match[2] : '';
				$max_size = 100;
				$carrier = '';
				
				// Browser-name only
				switch ($ua_name) {
					case 'DoCoMo':
						$carrier = 'docomo';
						$max_size = 5;
						if (preg_match('#\b[cC]([0-9]+)\b#', $ua_agent, $matches)) {
							$max_size = $matches[1];	// Cache max size
							$max_size = min($max_size, 30);
						}
						break;
				
					// Vodafone (ex. J-PHONE)
					case 'J-PHONE':
						$carrier = 'softbank';
						$max_size = 6;
						if (preg_match('/^([0-9]+)\./', $ua_vers, $matches)) {
							switch($matches[1]){
								case '3': $max_size =   6; break; // C type: lt 6000bytes
								case '4': $max_size =  12; break; // P type: lt  12Kbytes
								case '5': $max_size =  40; break; // W type: lt  40Kbytes
							}
						}
						break;
				
					case 'Vodafone':
					case 'SoftBank':
						$carrier = 'softbank';
						$max_size = 40;
						if (preg_match('/^([0-9]+)\./', $ua_vers, $matches)) {
							switch($matches[1]){
								case '1': $max_size = 40; break;
							}
						}
						break;
				
					// UP.Browser
					case 'UP.Browser':
						$carrier = 'au';
						if (preg_match('#^KDDI#', $ua_agent)) $max_size = 9;
						break;
				}
				
				if ($max_size) {
					$this->maxSize = $max_size * 1024;
				}
				
				// Set Key Button & $this->vars['ua']
				switch ($carrier) {
					case 'docomo':
						$this->keybutton = array(
							'1'	=>	'&#63879;',
							'2'	=>	'&#63880;',
							'3'	=>	'&#63881;',
							'4'	=>	'&#63882;',
							'5'	=>	'&#63883;',
							'6'	=>	'&#63884;',
							'7'	=>	'&#63885;',
							'8'	=>	'&#63886;',
							'9'	=>	'&#63887;',
							'0'	=>	'&#63888;',
							'#'	=>	'&#63877;',
							'*'	=>	'[*]'
						);
						if (isset($this->SERVER['HTTP_X_DCMGUID'])) $this->vars['ua']['uid'] = $this->SERVER['HTTP_X_DCMGUID'];
						$this->vars['ua']['isKTai'] = TRUE;
						$this->vars['ua']['carrier'] = $carrier;
						$this->vars['ua']['allowPNG'] = FALSE;
						$this->vars['ua']['allowInputImage'] = FALSE;
						break;
					
					case 'softbank':
						$this->keybutton = array(
							'1'	=>	chr(27).'$F<'.chr(15),
							'2'	=>	chr(27).'$F='.chr(15),
							'3'	=>	chr(27).'$F>'.chr(15),
							'4'	=>	chr(27).'$F?'.chr(15),
							'5'	=>	chr(27).'$F@'.chr(15),
							'6'	=>	chr(27).'$FA'.chr(15),
							'7'	=>	chr(27).'$FB'.chr(15),
							'8'	=>	chr(27).'$FC'.chr(15),
							'9'	=>	chr(27).'$FD'.chr(15),
							'0'	=>	chr(27).'$FE'.chr(15),
							'#'	=>	chr(27).'$F0'.chr(15),
							'*'	=>	'[*]'
						);
						if (isset($this->SERVER['HTTP_X_JPHONE_UID'])) $this->vars['ua']['uid'] = $this->SERVER['HTTP_X_JPHONE_UID'];
						$this->vars['ua']['isKTai'] = TRUE;
						$this->vars['ua']['carrier'] = $carrier;
						$this->vars['ua']['allowPNG'] = TRUE;
						$this->vars['ua']['allowInputImage'] = TRUE;
						break;
					
					case 'au':
						$this->keybutton = array(
							'1'	=>	'<img localsrc="180">',
							'2'	=>	'<img localsrc="181">',
							'3'	=>	'<img localsrc="182">',
							'4'	=>	'<img localsrc="183">',
							'5'	=>	'<img localsrc="184">',
							'6'	=>	'<img localsrc="185">',
							'7'	=>	'<img localsrc="186">',
							'8'	=>	'<img localsrc="187">',
							'9'	=>	'<img localsrc="188">',
							'0'	=>	'<img localsrc="325">',
							'#'	=>	'<img localsrc="818">',
							'*'	=>	'[*]'
						);
						if (isset($this->SERVER['HTTP_X_UP_SUBNO'])) $this->vars['ua']['uid'] = $this->SERVER['HTTP_X_UP_SUBNO'];
						$this->vars['ua']['isKTai'] = TRUE;
						$this->vars['ua']['carrier'] = $carrier;
						$this->vars['ua']['allowPNG'] = TRUE;
						$this->vars['ua']['allowInputImage'] = FALSE;
						break;
				}
			}
		}

		return;
	}
	
	function _attr_idToname ($match) {
		$tag = strtolower($match[1]);
		$pre = '';
		$add = '';
		if ($this->outputMode === 'xhtml') {
			if (strpos($match[2], ' id=') === FALSE && strpos($match[2], ' name=') !== FALSE) {
				if ($tag === 'a' || ! in_array($tag, array('textarea', 'input', 'select'))) {
					$match[2] = str_replace(' name=', ' id=', $match[2]);
				}
			}
			if ($tag !== 'a') {
				if (! in_array($tag, array('textarea', 'tr', 'thead', 'tfoot', 'tbody'))) {
					if (preg_match('/ id=(\'|")?([^\'"]+)(?:\\1)?/i', $match[2], $_match)) {
						if (in_array($tag, array('table', 'ol', 'ul', 'dl'))) {
							$_pos = 'pre';
						} else {
							$_pos = 'add';
						}
						$$_pos = '<a id="' . $_match[2] . '"></a>';
					}
				}
				$match[2] = rtrim(preg_replace('/ id=[\'"][^\'"]*[\'"]/', '', $match[2]));
			}
			if (! in_array($tag, array('textarea', 'input', 'select'))) {
				$match[2] = rtrim(preg_replace('/ name=[\'"][^\'"]*[\'"]/', '', $match[2]));
			}
		} else {
			if (strpos($match[2], ' name=') === FALSE && strpos($match[2], ' id=') !== FALSE) {
				if ($tag === 'a') {
					$match[2] = str_replace(' id=', ' name=', $match[2]);
				} else if (! in_array($tag, array('textarea', 'tr', 'thead', 'tfoot', 'tbody'))) {
					if (preg_match('/ id=(\'|")?([^\'"]+)(?:\\1)?/i', $match[2], $_match)) {
						if (in_array($tag, array('table', 'ol', 'ul', 'dl'))) {
							$_pos = 'pre';
						} else {
							$_pos = 'add';
						}
						$$_pos = '<a name="' . $_match[2] . '"></a>';
					}
				}
			}
			$match[2] = rtrim(preg_replace('/ id=[\'"][^\'"]*[\'"]/', '', $match[2]));
		}
		return $pre . '<' . $match[1] . $match[2] . '>' . $add;
	}
	
	function _href_give_session_id ($match) {
		
		static $session_name = NULL;
		
		if (is_null($session_name)) {
			$session_name = session_name();
		}
		$url = $match[3];
		$ext_icon = '';
		// Url rewrite
		if (! empty($this->Config_urlRewrites['regex']) && ! empty($this->Config_urlRewrites['tostr'])) {
			$url = preg_replace($this->Config_urlRewrites['regex'], $this->Config_urlRewrites['tostr'], $url);
		}
		
		$parsed_base = parse_url($this->myRoot);
		$parsed_url = parse_url($url);
		
		if (strtolower(substr($url, 0, 6)) === 'mailto') {
			$parsed_url['scheme'] = 'mailto';
			$parsed_url['host'] = $parsed_base['host'];
		}
		if (empty($parsed_url['host']) || ($parsed_url['host'] === $parsed_base['host'] && $parsed_url['scheme'] === $parsed_base['scheme'])) {
			$url = preg_replace('/(?:\?|&(?:amp;)?)' . $session_name . '=[^&#>]+/', '', $url);
			
			list($href, $hash) = array_pad(explode('#', $url, 2), 2, '');
			
			if (!$href) {
				$href = isset($this->SERVER['QUERY_STRING'])? '?' . $this->SERVER['QUERY_STRING'] : '';
				$href = preg_replace('/(?:\?|&(?:amp;)?)' . $session_name . '=[^&]+/', '', $href);
			};

			$add = array();
			if (defined('SID') && SID && empty($_COOKIE[$session_name]) && ! $this->vars['ua']['isBot']) {
				$add[] = SID;
			}
			if ($hash) {
				$href = preg_replace('/(?:\?|&(?:amp;)?)' . preg_quote($this->hashkey, '/') . '=[^&]+/', '', $href);
				$add[] = $this->hashkey . '=' . $hash;
			}
			if ($add) $href .= ((strpos($href, "?") === FALSE)? '?' : '&amp;') . (join('&amp;', $add));
			$url = $href . ($hash? '#' . $hash : '');
		} else if ($parsed_url['host'] !== $parsed_base['host']) {
			$hostsReg = $this->_getHostsRegex($this->Config_directLinkHosts);
			if (!preg_match($hostsReg, $parsed_url['host'])) {
				$url =($this->Config_redirect? $this->Config_redirect : $this->myRoot . '/redirect.php?l=') . rawurlencode(str_replace('&amp;', '&', $url));
			}
			$ext_icon = $this->Config_icons['extLink'];
		}
		return $ext_icon . $match[1] . $url . (isset($match[4])? $match[4] : '');
	}
	
	function _html_check_img_src ($match) {
		$type = strtolower($match[2]);

		if (! $this->vars['ua']['allowInputImage'] && $type === 'input') {
			return str_replace('image', 'submit', $match[1] . $match[5]) . (isset($match[6])? $match[6] : '');
		}

		$url = $match[4];
		$parsed_base = parse_url($this->myRoot);
		$parsed_url = parse_url($url);
		
		$hostsReg = '#(?!)#';
		if ($this->Config_showImgHosts) {
			if ($this->Config_showImgHosts === 'all') {
				$hostsReg = '#(?=)#';
			} else {
				$hostsReg = $this->_getHostsRegex($this->Config_showImgHosts);
			}
		}
		
		if (empty($parsed_url['host'])
		 || ($parsed_url['host'] === $parsed_base['host'] && $parsed_url['scheme'] === $parsed_base['scheme'])
		 || preg_match($hostsReg, $parsed_url['host'])) {
			$png = ($this->vars['ua']['allowPNG'])? '&amp;p' : '';
			if (! $parsed_url['host']) {
				if ($url[0] === '/') {
					$url = $this->myRoot . $url;
				} else {
					$base = preg_replace('#/[^/]*$#', '',$this->SERVER['REQUEST_URI']);
					$pices = explode('/', $base);
					if (strpos($url, '../') === 0) {
						$count = substr_count($url, '../');
						$url = $this->myRoot . join('/', array_slice($pices, $count + 1)) . substr($url, $count * 3 - 1);
					} else if (strpos($url, './') === 0) {
						$url = $this->myRoot . $base . substr($url, 1);
					} else {
						$url = $this->myRoot . $base . '/' . $url;
					}
				}
			}
			
			// Size tag
			$reps = array();
			$width = $height = '';
			if (preg_match('/(width=[\'"]?)(\d+)/i', ($match[1] . $match[5]), $arg)) {
				$w_org = $arg[0];
				$w_tag = $arg[1];
				$width = $arg[2];
			}
			if (preg_match('/(height=[\'"]?)(\d+)/i', ($match[1] . $match[5]), $arg)) {
				$h_org = $arg[0];
				$h_tag = $arg[1];
				$height = $arg[2];
			}
			if ($width && $height) {
				$zoom = min($this->Config_pictSizeMax/$width, $this->Config_pictSizeMax/$height);
				if ($zoom < 1) {
					$reps['from'][] = $w_org;
					$reps['to'][]   = $w_tag . round($width * $zoom);
					$reps['from'][] = $h_org;
					$reps['to'][]   = $h_tag . round($height * $zoom);
				}
			} else if ($width) {
				if ($this->Config_pictSizeMax < $width) {
					$reps['from'][] = $w_org;
					$reps['to'][]   = $w_tag . $this->Config_pictSizeMax;
				}
			} else if ($height) {
				if ($this->Config_pictSizeMax < $height) {
					$reps['from'][] = $h_org;
					$reps['to'][]   = $h_tag . $this->Config_pictSizeMax;
				}
			}
			
			$ret = $match[1] . ' src="' . $this->Config_hypCommonURL . '/gate.php?way=imgconv&amp;m=i4k&amp;s=' . $this->Config_pictSizeMax . $png . '&amp;u=' . rawurlencode(str_replace('&amp;', '&', $url)) . '"' . $match[5];
			if (isset($reps['from'])) {
				$ret = str_replace($reps['from'], $reps['to'], $ret);
			}
			
			return $ret . (isset($match[6])? $match[6] : '');
		} else {
			if ($type === 'input') {
				return str_replace('image', 'submit', $match[1] . $match[5]) . (isset($match[6])? $match[6] : '');
			} else {
				if (! isset($match[6])) {
					return "\x08" . ' href="' . $url . '">[PIC]</a>';
				} else {
					return htmlspecialchars($parsed_url['host']) . $match[6];
				}
			}
		}
	}
	
	function _getHostsRegex ($arr, $dem = '#') {
		if (is_array($arr) && $arr) {
			$hosts = array();
			foreach($arr as $host) {
				$hosts[] = preg_quote($host, $dem);
			}
			$hostsReg = $dem . '(?:' . join('|', $hosts) . ')$' . $dem;
		} else {
			$hostsReg = $dem . '(?!)' . $dem;
		}
		return $hostsReg;
	}
	
/* -------------------------------------------------------------------------
	ClientDetect class
	a part of PC2M Website Transcoder for Mobile Clients
	Copyright (C) 2005-2007 ucb.rcdtokyo and the contributors
	http://www.rcdtokyo.com/pc2m/note/
------------------------------------------------------------------------- */
	/**
	 * Private method used by checkIpRange method
	 *
	 * @param  string  $address
	 * @param  array   $iprange
	 * @return bool
	 * @access private
	 */
	function _compareIp($address, $iprange)
	{
		foreach ($iprange as $value) {
			list($network, $mask) = explode('/', $value);
			$network = $this->_dumpAddress($network);
			$mask = $this->_dumpNetmask($mask);
			if (($address & $mask) == ($network & $mask)) {
				return true;
				break;
			}
		}
		return false;
	}

	/**
	 * Private method used by checkIpRange method
	 *
	 * @access private
	 * @param  string  $mask
	 * @return string
	 */
	function _dumpNetmask($mask)
	{
		$i = 0;
		$x = '';
		while ($i < $mask) {
			$x .= '1';
			$i++;
		}
		while ($i < 32) {
			$x .= '0';
			$i++;
		}
		$array = array();
		$array[] = bindec(substr($x, 0, 8));
		$array[] = bindec(substr($x, 8, 8));
		$array[] = bindec(substr($x, 16, 8));
		$array[] = bindec(substr($x, 24, 8));
		return ($array[0] << 24) | ($array[1] << 16) | ($array[2] << 8) | $array[3];
	}

	/**
	 * Private method used by checkIpRange method
	 *
	 * @param  string  $address
	 * @return string
	 * @access private
	 */
	function _dumpAddress($address)
	{
		$array = explode('.', $address);
		return ($array[0] << 24) | ($array[1] << 16) | ($array[2] << 8) | $array[3];
	}

}

}