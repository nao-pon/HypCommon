<?php
/*
 * Created on 2008/06/17 by nao-pon http://hypweb.net/
 * License: GPL v2 or (at your option) any later version
 * $Id: hyp_ktai_render.php,v 1.2 2008/06/17 05:19:13 nao-pon Exp $
 */

if (! class_exists('HypKTaiRender')) {

//// mbstring ////
if (! extension_loaded('mbstring') && ! class_exists('HypMBString')) {
	require (dirname(dirname(__FILE__)) . '/mbemulator/mb-emulator.php');
}

class HypKTaiRender
{
	var $contents = array();
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
	var $keymap = array();
	var $keybutton = array();
	var $showImgHosts = array('amazon.com', 'yimg.jp', 'yimg.com');
	var $redirect = '';
	
	function HypKTaiRender () {
		$this->keymap['prev'] = '4';
		$this->keymap['next'] = '6';
		
		$this->contents['header'] = '';
		$this->contents['body'] = '';
		$this->contents['footer'] = '';
		
		$this->myRoot = 'http' . (!empty($_SERVER['HTTPS'])? 's' : '' ) . '://'
		         . $_SERVER['SERVER_NAME'] . (($_SERVER['SERVER_PORT'] == 80)? '' : ':'.$_SERVER['SERVER_PORT']);
		$this->myRoot = rtrim($this->myRoot, '/');
		
		$this->inputEncode = mb_internal_encoding();
		
		$this->_uaSetup();
	}
	
	function set_myRoot ($url) {
		$parsed_url = parse_url($url);
			$this->myRoot = $parsed_url['scheme'].'://'.$parsed_url['host'].(isset($parsed_url['port'])? ':' . $parsed_url['port'] : '');
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
		$pager = '';
		
		//if ($this->inputHtml) exit($body);
		
		//$this->maxSize = $this->get_maxsize_for_hp();
		
		$pnum = empty($_GET[$this->pagekey])? 0 : intval($_GET[$this->pagekey]);
		
		$extra_len = strlen($header . $footer);
		if ($this->maxSize && (strlen($body) + $extra_len) > $this->maxSize) {
			
			$margin = 200;
			$this->splitMaxSize = $this->maxSize - $extra_len - $margin;
			list($pages, $ids) = $this->html_split($body);

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
			
			if (isset($_GET[$this->hashkey]) && isset($ids[$_GET[$this->hashkey]])) {
				$pnum = $ids[$_GET[$this->hashkey]];
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
			$querys = isset($_SERVER['QUERY_STRING'])? $_SERVER['QUERY_STRING'] : '';
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
				$pager[] = '<a href="' . $base . $this->pagekey . '=0' . '">|&lt;</a>';
				$pager[] = '<a href="' . $base . $this->pagekey . '=' . $prev . '" ' . $accesskey . '="' . $this->keymap['prev'] . '">' . $this->keybutton[$this->keymap['prev']] . '&lt;</a>';
			}
			$pager[] = $next . '/' . $pagecount . ' ';
			if ($pnum < $pagecount - 1) {
				$pager[] = '<a href="' . $base . $this->pagekey . '=' . $next . '" ' . $accesskey . '="' . $this->keymap['next'] . '">&gt;' . $this->keybutton[$this->keymap['next']] . '</a>';
				$pager[] = '<a href="' . $base . $this->pagekey . '=' . ($pagecount - 1) . '">&gt;|</a>';
			}

			$pager = $this->html_give_session_id($pager);
			$pager = '<center>' . join(' ', $pager) . '</center>';
			
		} else {
			$h_reg = preg_quote($this->hashkey, '/') . '=[^&#]+';
		}
		
		// Optimize query strings
		$_func = create_function(
			'$match',
			'if ($match[3][0] === \'?\') $match[3] = preg_replace(\'/^.*?'.$h_reg.'(#[^#]+)?$/\', \'' . $_SERVER['REQUEST_URI'] . '$1\', $match[3]);' . 
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
		// 半角カナに変換
		if (function_exists('mb_convert_kana')) {
			$body = preg_replace_callback('/(^|<textarea.+?\/textarea>|<pre.+?\/pre>|<[^>]*>)(.*?)(?=<textarea.+?\/textarea>|<pre.+?\/pre>|<[^>]*>|$)/sS',
				create_function(
					'$match',
					'return $match[1] . mb_convert_kana(preg_replace(\'/[\s]+/\',\' \',str_replace(array("\r\n","\r","\n"),\'\',$match[2])), \'knr\', \''.$this->inputEncode.'\');'
				), $body);
		}

		// Remove etc.
		// tag attribute
		$reg = '#(<(?!textarea)[^>]+?)\s+(?:class|clear|target|nowrap|title|alt|on[^=]+)=[\'"][^\'"]*[\'"]([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		// css property
		$reg = '#(<(?!textarea)[^>]+?style=[\'"][^\'"]*?)\s*(?<!-)(?:width|height|margin|padding|float|position|left|top|right|bottom|clear|overflow)[^;\'"]+(?:[ ;]+)?([^>]*>)#iS';
		while(preg_match($reg, $body)) {
			$body = preg_replace($reg, '$1$2', $body);
		}
		
		// password to text
		$body = preg_replace('#(<input[^>]*?\s+type=[\'"])password#iS', '$1text', $body);
		
		// id to name
		$body = preg_replace_callback('#<([a-zA-Z]+)([^>]+)>#isS', array(& $this, '_attr_idToname'), $body);
		
		$pat = $rep = array();
		
		$pat[] = '#<!--.+?-->#sS';
		$rep[] = '';
		
		$pat[] = '#<((?:no)?script|style).+?/\\1>#isS';
		$rep[] = '';
		
		$pat[] = '#</?(?:code|label|small|script|link)[^>]*>#iS';
		$rep[] = '';

		$pat[] = '#<del[^>]*>#iS';
		$rep[] = '[del]';

		$pat[] = '#(<(?!textarea)[^>]+?) style=(?:\'\'|"")#iS';
		$rep[] = '$1';

		$body = preg_replace($pat, $rep, $body);

		$pat = array(' />', '</li>', '</del>');
		$rep = array('>'  , ''     , '[/del]');
		$body = str_replace($pat, $rep, $body);

		// Host name
		$body = preg_replace('#(<[^>]+? (?:href|src)=[\'"]?)'.preg_quote($this->myRoot, '#').'#iS', '$1', $body);
				
		$body = $this->html_give_session_id($body);
		
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
					return $match[0] . \'<input type="hidden" name="'.$sid_key.'" value="'.$sid_val.'" />\';'
				), $html);
		}
		
		return $html;
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
			'h4',
			'h5',
			'h6',
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
		$this->max_size = NULL;
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
		
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('#(?:^|\b)([a-zA-Z.-]+)(?:/([0-9.]+))?#', $_SERVER['HTTP_USER_AGENT'], $match)) {
			$ua_agent = $_SERVER['HTTP_USER_AGENT'];
			$ua_name = $match[1];
			$ua_vers = isset($match[2])? $match[2] : '';
			$max_size = 0;
			
			// Browser-name only
			switch ($ua_name) {
				// NetFront / Compact NetFront
				case 'NetFront':
				case 'CNF':
				case 'DoCoMo':
				case 'Opera': // Performing CNF compatible
					if (preg_match('#\b[cC]([0-9]+)\b#', $ua_agent, $matches)) {
						$max_size = $matches[1];	// Cache max size
					}
					break;
			
				// Vodafone (ex. J-PHONE)
				case 'J-PHONE':
					$matches = array("");
					preg_match('/^([0-9]+)\./', $ua_vers, $matches);
					switch($matches[1]){
					case '3': $max_size =   6; break; // C type: lt 6000bytes
					case '4': $max_size =  12; break; // P type: lt  12Kbytes
					case '5': $max_size =  40; break; // W type: lt  48Kbytes
					}
					break;
			
				case 'Vodafone':
				case 'SoftBank':
					$matches = array("");
					preg_match('/^([0-9]+)\./', $ua_vers, $matches);
					switch($matches[1]){
						case '1': $max_size = 40; break;
					}
					break;
			
				// UP.Browser
				case 'UP.Browser':
					// UP.Browser for KDDI cell phones
					// http://www.au.kddi.com/ezfactory/tec/spec/xhtml.html ('About 9KB max')
					// http://www.au.kddi.com/ezfactory/tec/spec/4_4.html (User-agent strings)
					if (preg_match('#^KDDI#', $ua_agent)) $max_size =  9;
					break;
			}
			
			// Browser-name + version
			switch ($ua_name.'/'.$ua_vers) {
				// Restriction For imode:
				//  http://www.nttdocomo.co.jp/mc-user/i/tag/s2.html
				case 'DoCoMo/2.0':	$max_size = min($max_size, 30); break;
			}

			if ($max_size) {
				$this->maxSize = $max_size * 1024;
			}
			
			// Set Key Button
			switch ($ua_name) {
		
				// Graphic icons for imode HTML 4.0, with Shift-JIS text output
				// http://www.nttdocomo.co.jp/mc-user/i/tag/emoji/e1.html
				// http://www.nttdocomo.co.jp/mc-user/i/tag/emoji/list.html
				case 'DoCoMo':
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
					break;
		
				// Graphic icons for Vodafone (ex. J-PHONE) cell phones
				// http://www.dp.j-phone.com/dp/tool_dl/web/picword_top.php
				case 'J-PHONE':
				case 'Vodafone':
				case 'SoftBank':
		
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
					break;
		
				case 'UP.Browser':
		
				// UP.Browser for KDDI cell phones' built-in icons
				// http://www.au.kddi.com/ezfactory/tec/spec/3.html
					if (preg_match('#^KDDI#', $root->ua_agent)) {
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
					}
					break;
			}
		}

		return;
	}
	
	function _attr_idToname ($match) {
		$tag = strtolower($match[1]);
		$add = '';
		if (strpos($match[2], ' name=') === FALSE && strpos($match[2], ' id=') !== FALSE) {
			if ($tag === 'a') {
				$match[2] = str_replace(' id=', ' name=', $match[2]);
			} else if ($tag !== 'textarea') {
				if (preg_match('/ id=(\'|")?([^\'"]+)(?:\\1)?/i', $match[2], $_match)) {
					$add = '<a name="' . $_match[2] . '"></a>';
				}
			}
		}
		$match[2] = rtrim(preg_replace('/ id=[\'"][^\'"]*[\'"]/', '', $match[2]));
		return '<' . $match[1] . $match[2] . '>' . $add;
	}
	
	function _href_give_session_id ($match) {
		
		$url = $match[3];
		
		$parsed_base = parse_url($this->myRoot);
		$parsed_url = parse_url($url);
		
		if (strtolower(substr($url, 0, 6)) === 'mailto') {
			$parsed_url['scheme'] = 'mailto';
			$parsed_url['host'] = $parsed_base['host'];
		}
		if (empty($parsed_url['host']) || ($parsed_url['host'] === $parsed_base['host'] && $parsed_url['scheme'] === $parsed_base['scheme'])) {
			$url = preg_replace('/(?:\?|&(?:amp;)?)' . preg_quote(session_name(), '/') . '=[^&#>]+/', '', $url);
			$url = preg_replace('/(?:\?|&(?:amp;)?)' . preg_quote($this->hashkey, '/') . '=[^&#>]+/', '', $url);
			
			list($href, $hash) = array_pad(explode('#', $url, 2), 2, '');
			
			if (!$href) {
				$href = isset($_SERVER['QUERY_STRING'])? '?' . $_SERVER['QUERY_STRING'] : '';
				$href = preg_replace('/(?:\?|&(?:amp;)?)' . preg_quote(session_name(), '/') . '=[^&]+/', '', $href);
				$href = preg_replace('/(?:\?|&(?:amp;)?)' . preg_quote($this->hashkey, '/') . '=[^&]+/', '', $href);
			};

			$add = array();
			if (defined('SID') && SID) {
				$add[] = SID;
			}
			if ($hash) {
				$add[] = $this->hashkey . '=' . $hash;
			}
			if ($add) $href .= ((strpos($href, "?") === FALSE)? '?' : '&amp;') . (join('&amp;', $add));
			$url = $href . ($hash? '#' . $hash : '');
		} else if ($parsed_url['host'] !== $parsed_base['host']) {
			if ($this->redirect) {
				$url = $this->redirect;
			} else {
				$url = $this->myRoot . '/redirect.php';
			}
			$url .= '?l=' . rawurlencode($url);
		}
		
		return $match[1] . $url . (isset($match[4])? $match[4] : '');
	}
	
	function _html_check_img_src ($match) {
		$type = strtolower($match[2]);
		$url = $match[4];
		$parsed_base = parse_url($this->myRoot);
		$parsed_url = parse_url($url);
		
		$hostsReg = '#(?!)#';
		if ($this->showImgHosts) {
			if ($this->showImgHosts === 'all') {
				$hostsReg = '#(?=)#';
			} else {
				$hosts = array();
				foreach($this->showImgHosts as $_host) {
					$hosts[] = preg_quote($_host, '#');
				}
				$hostsReg = '#(?:' . join('|', $hosts) . ')$#';
			}
		}
		
		if (empty($parsed_url['host'])
		 || ($parsed_url['host'] === $parsed_base['host'] && $parsed_url['scheme'] === $parsed_base['scheme'])
		 || preg_match($hostsReg, $parsed_url['host'])) {
			return $match[0];
		} else {
			if ($type === 'input') {
				return str_replace('image', 'submit', $match[1] . $match[5]) . (isset($match[6])? $match[6] : '');
			} else {
				if (! isset($match[6])) {
					return "\x08" . ' href="' . $url . '">[ PIC ]</a>';
				} else {
					return htmlspecialchars($parsed_url['host']) . $match[6];
				}
			}
		}
	}

}

}