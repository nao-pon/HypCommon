<?php
// $Id: hyp_common_func.php,v 1.24 2007/12/05 12:12:45 nao-pon Exp $
// HypCommonFunc Class by nao-pon http://hypweb.net
////////////////////////////////////////////////

if( ! class_exists( 'HypCommonFunc' ) )
{

class HypCommonFunc
{
	function get_version() {
		include (dirname(__FILE__) . '/version.php');
		return $version;
	}
	
	// 1バイト文字をエンティティ化
	function str_to_entity(&$str)
	{
		$e_mail = "";
		$i = 0;
		while(isset($str[$i]))
		{
			$e_mail .= "&#".ord((string)$str[$i]).";";
			$i++;
		}
		$str = $e_mail;
		return $str;
	}
	
	// ",' で括ったフレーズ対応スプリット
	function phrase_split($str)
	{
		$words = array();
		$str = preg_replace("/(\"|'|”|’)(.+?)(?:\\1)/e","str_replace(' ','\x08','$2')",$str);
		$words = preg_split('/\s+/',$str,-1,PREG_SPLIT_NO_EMPTY);
		$words = str_replace("\x08"," ",$words);
		return $words;
	}
	
	// 配列対応 & gpc 対応のstripslashes
	function stripslashes_gpc(&$v)
	{
		if(ini_get("magic_quotes_gpc"))
		{
			if (is_array($v))
			{
				$arr =array();
				foreach($v as $k=>$m)
				{
					$arr[$k] = HypCommonFunc::stripslashes_gpc($m);
				}
				$v = $arr;
			}
			else
			{
				$v = stripslashes($v);
			}
		}
		return $v;
	}
	
	// RSS関連のキャッシュを削除する
	function clear_rss_cache($files=array())
	{
		include_once XOOPS_ROOT_PATH.'/class/template.php';
		
		if (empty($files) || !is_array($files))
		{
			$files = array(
				'db:BopComments_rss.html',
				'db:whatsnew_rss.html',
				'db:whatsnew_atom.html',
				'db:whatsnew_rdf.html',
				'db:whatsnew_pda.html',
				'db:whatsnew_block_bop.html',
				'db:whatsnew_block_mod.html',
				'db:whatsnew_block_date.html',
			);
		}
		
		$tpl = new XoopsTpl();
		$tpl->xoops_setCaching(2);
		foreach($files as $tgt)
		{
			if ($tgt) {$tpl->clear_cache($tgt);}
		}
	}
	
	// RPC Update Ping を打つ
	function update_rpc_ping($default_update="http://bulkfeeds.net/rpc http://ping.myblog.jp http://ping.bloggers.jp/rpc/ http://blog.goo.ne.jp/XMLRPC http://ping.cocolog-nifty.com/xmlrpc http://rpc.technorati.jp/rpc/ping")
	{
		global $xoopsConfig;
		
		//RSSキャッシュファイルを削除
		HypCommonFunc::clear_rss_cache();
		
		$update_ping2 = $default_update;
		$update_ping = preg_split ( "/[\s,]+/" , $update_ping2 );

		$ping_blog_name = $xoopsConfig['sitename'];
		$ping_url		= XOOPS_URL."/";

		$ping_update = <<<EOF
	<?xml version="1.0"?>
	<methodCall>
		<methodName>weblogUpdates.ping</methodName>
		<params>
		<param><value>$ping_blog_name</value></param>
		<param><value>$ping_url</value></param>
		</params>
	</methodCall>
EOF;
		//<?
		$ping_update = mb_convert_encoding
					   ( $ping_update , "UTF-8" , "EUC-JP" );

		$ping_update_leng = strlen($ping_update);

		foreach ( $update_ping as $up )
		{
			if ( $up != "" )
			{
				$uph = ereg_replace ( "http:\/\/", "", $up );
				list ( $host , $uri ) = split ( "/", $uph , 2 );
				list ( $host , $port ) = split ( ":", $host );

				if ( $port == "" )
				{
					$port = 80;
					$add_port = "";
				}
				else
				{
					$add_port = ":$port";
				}
				
				$errNo = 0;
				$errStr = "";
				$files = @fsockopen($host, $port , $errNo , $errStr, 10);

				@fputs($files, "POST /$uri HTTP/1.0\r\n" );
				@fputs($files, "Host: $host$add_port\r\n" );
				@fputs($files, "Content-Length: $ping_update_leng\r\n" );
				@fputs($files, "User-Agent: XOOPS update pinger Ver 1.00\r\n" );
				@fputs($files, "Content-Type: text/xml\r\n" );
				@fputs($files, "\r\n" );
				@fputs($files, "$ping_update" );

				fclose ( $files );

			}
		}
		return ;
	}
	
	function make_context($text,$words=array(),$l=255)
	{
		static $strcut = "";
		if (!$strcut)
			$strcut = create_function ( '$a,$b,$c', (function_exists('mb_strcut'))?
				'return mb_strcut($a,$b,$c);':
				'return strcut($a,$b,$c);');
		
		$text = str_replace(array('&lt;','&gt;','&amp;','&quot;','&#039;'),array('<','>','&','"',"'"),$text);
		
		if (!is_array($words)) $words = array();
		
		$ret = "";
		$q_word = str_replace(" ","|",preg_quote(join(' ',$words),"/"));
		
		$match = array();
		if (preg_match("/$q_word/i",$text,$match)) {
			$ret = ltrim(preg_replace('/\s+/', ' ', $text));
			list($pre, $aft) = array_pad(preg_split("/$q_word/i", $ret, 2), 2, "");
			$m = intval($l/2);
			$ret = (strlen($pre) > $m)? "... " : "";
			$ret .= $strcut($pre, max(strlen($pre)-$m+1,0),$m).$match[0];
			$m = $l-strlen($ret);
			$ret .= $strcut($aft, 0, min(strlen($aft),$m));
			if (strlen($aft) > $m) $ret .= " ...";
		}
		
		if (!$ret) {
			$ret = $strcut($text, 0, $l);
			$ret = preg_replace('/&([^;]+)?$/', '', $ret);
		}
		
		return htmlspecialchars($ret, ENT_NOQUOTES);
	}
	
	function set_need_refresh($mode)
	{
		if ($mode)
		{
			setcookie ("HypNeedRefresh", "1");
		}
		else
		{
			setcookie ("HypNeedRefresh", "", time() - 3600);
		}
	}
	
	// HTML の meta タグから文字エンコーディングを取得する
	function get_encoding_by_meta($html)
	{
		$codesets = array(
			'shift_jis' => 'Shift_JIS',
			'x-sjis' => 'Shift_JIS',
			'euc-jp' => 'EUC-JP',
			'x-euc-jp' => 'EUC-JP',
			'iso-2022-jp' => 'JIS',
			'utf-8' => 'UTF-8',
		);
		$match = array();
		if (preg_match("/<meta[^>]*content=(?:\"|')[^\"'>]*charset=([^\"'>]+)(?:\"|')[^>]*>/is",$html,$match))
		{
			$encode = strtolower($match[1]);
			if (array_key_exists($encode,$codesets))
			{
				return $codesets[$encode];
			}
			else
			{
				return "EUC-JP,UTF-8,Shift_JIS,JIS";
			}
		}
		else
		{
			return "EUC-JP,UTF-8,Shift_JIS,JIS";
		}
	}

	// サムネイル画像を作成。
	// 成功ならサムネイルのファイルのパス、不成功なら元ファイルパスを返す
	function make_thumb($o_file, $s_file, $max_width, $max_height, $zoom_limit="1,95", $refresh=FALSE, $quality=75)
	{
		// すでに作成済み
		if (!$refresh && file_exists($s_file)) return $s_file;
		
		$size = @getimagesize($o_file);
		if (!$size) return $o_file;//画像ファイルではない
		
		// 元画像のサイズ
		$org_w = $size[0];
		$org_h = $size[1];
		
		if ($max_width >= $org_w && $max_height >= $org_h) return $o_file;//指定サイズが元サイズより大きい
		
		// 縮小率の設定
		list($zoom_limit_min,$zoom_limit_max) = explode(",",$zoom_limit);
		$zoom = min(($max_width/$org_w),($max_height/$org_h));
		if (!$zoom || $zoom < $zoom_limit_min/100 || $zoom > $zoom_limit_max/100) return $o_file;//ZOOM値が範囲外
		
		@unlink($s_file);
		
		if (defined('HYP_IMAGEMAGICK_PATH') && HYP_IMAGEMAGICK_PATH)
		{
			// ImageMagick を使用
			return HypCommonFunc::make_thumb_imagemagick($o_file, $s_file, $zoom, $quality, $size[2], $org_w, $org_h);
		}
		else
		{
			if (!HypCommonFunc::check_memory4gd($org_w,$org_h))
			{
				// メモリー制限に引っ掛かりそう。（マージン 1MB）
				return $o_file;
			}
			return HypCommonFunc::make_thumb_gd($o_file, $s_file, $zoom, $quality, $size[2], $org_w, $org_h);
		}
	}
	
	function make_thumb_gd($o_file, $s_file, $zoom, $quality, $type ,$org_w, $org_h)
	{
		//GD のバージョンを取得
		static $gd_ver = null;
		if (is_null($gd_ver))
		{
			$gd_ver = HypCommonFunc::gdVersion();
		}
		
		// gd fuction のチェック
		if ($gd_ver < 1 || !function_exists("imagecreate")) return $o_file;//gdをサポートしていない
		
		// gd のバージョンによる関数名の定義
		$imagecreate = ($gd_ver >= 2)? "imagecreatetruecolor" : "imagecreate";
		$imageresize = ($gd_ver >= 2)? "imagecopyresampled" : "imagecopyresized";
		
		$width = $org_w * $zoom;
		$height = $org_h * $zoom;
		
		// サムネイルのファイルタイプが指定されている？(.jpg)
		$s_ext = "";
		$s_ext = preg_replace("/\.([^\.]+)$/","$1",$s_file);
		
		switch($type)
		{
			case "1": //gif形式
				if (function_exists ("imagecreatefromgif"))
				{
					$src_im = imagecreatefromgif($o_file);
					$colortransparent = imagecolortransparent($src_im);
					if ($s_ext != "jpg" && $colortransparent > -1)
					{
						// 透過色あり
						$dst_im = imagecreate($width,$height);
						imagepalettecopy ($dst_im, $src_im);
						imagefill($dst_im,0,0,$colortransparent);
						imagecolortransparent($dst_im, $colortransparent);
						imagecopyresized($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
					}
					else
					{
						// 透過色なし
						$dst_im = $imagecreate($width,$height);
						$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
						if (function_exists('imagetruecolortopalette')) imagetruecolortopalette ($dst_im, false, imagecolorstotal($src_im));
					}
					touch($s_file);
					if ($s_ext == "jpg")
					{
						imagejpeg($dst_im,$s_file,$quality);
					}
					else
					{
						if (function_exists("imagegif"))
						{
							imagegif($dst_im,$s_file);
						}
						else
						{
							imagepng($dst_im,$s_file);
						}
					}
					$o_file = $s_file;
				}
				break;
			case "2": //jpg形式
				$src_im = imagecreatefromjpeg($o_file);
				$dst_im = $imagecreate($width,$height);
				$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
				touch($s_file);
				imagejpeg($dst_im,$s_file,$quality);
				$o_file = $s_file;
				break;
			case "3": //png形式
				$src_im = imagecreatefrompng($o_file);
				if (imagecolorstotal($src_im))
				{
					// PaletteColor
					$colortransparent = imagecolortransparent($src_im);
					if ($s_ext != "jpg" && $colortransparent > -1)
					{
						// 透過色あり
						$dst_im = imagecreate($width,$height);
						imagepalettecopy ($dst_im, $src_im);
						imagefill($dst_im,0,0,$colortransparent);
						imagecolortransparent($dst_im, $colortransparent);
						imagecopyresized($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
					}
					else
					{
						// 透過色なし
						$dst_im = $imagecreate($width,$height);
						$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
						if (function_exists('imagetruecolortopalette')) imagetruecolortopalette ($dst_im, false, imagecolorstotal($src_im));
					}
				}
				else
				{
					// TrueColor
					$dst_im = $imagecreate($width,$height);
					$imageresize ($dst_im,$src_im,0,0,0,0,$width,$height,$org_w,$org_h);
				}
				touch($s_file);
				if ($s_ext == "jpg")
				{
					imagejpeg($dst_im,$s_file,$quality);
				}
				else
				{
					imagepng($dst_im,$s_file);
				}
				$o_file = $s_file;
				break;
			default:
				break;
		}
		@imagedestroy($dst_im);
		@imagedestroy($src_im);
		//chmod($o_file, 0666);
		return $o_file;
	}
	
	function make_thumb_imagemagick($o_file, $s_file, $zoom, $quality, $type ,$org_w, $org_h)
	{
		$zoom = intval($zoom * 100);
		$quality = intval($quality);
		$org_w = intval($org_w);
		$org_h = intval($org_h);

		$ro_file = realpath($o_file);
		$rs_file = realpath(dirname($s_file))."/".basename($s_file);

		// Make Thumb and check success
		if ( ini_get('safe_mode') != "1" )
		{
			exec( HYP_IMAGEMAGICK_PATH."convert -size {$org_w}x{$org_h} -geometry {$zoom}% -quality {$quality} +profile \"*\" {$ro_file} {$rs_file}" ) ;
			//@chmod($s_file, 0666);
		}
		else
		{
			// safeモードの場合は、CGIを起動して取得してみる
			
			$cmds = "?m=r".
					"&p=".rawurlencode(HYP_IMAGEMAGICK_PATH).
					"&z=".$zoom.
					"&q=".$quality.
					"&o=".rawurlencode($ro_file).
					"&s=".rawurlencode($rs_file);
						
			HypCommonFunc::exec_image_magick_cgi($cmds);
		}
		
		if( ! is_readable( $s_file ) )
		{
			// can't exec convert, big thumbs!
			return $o_file;
		}
		return $s_file;
	}

	// 画像をリサイズする
	function ImageResize($img, $isize='', $quality=75) {
		
		$size = @getimagesize($img);
		if (!$size) return false;//画像ファイルではない
		
		$img = realpath($img);
		
		if (!preg_match('/^([\d]+)?x([\d]+)?|([\d]+)%?$/i', trim($isize), $arg)) return false;
		
		if (!empty($arg[3])) {
			$zoom = round($arg[3] / 100);
		} else {
			$w = (empty($arg[1]))? $size[0] : $arg[1];
			$h = (empty($arg[2]))? $size[1] : $arg[2];
			$zoom_w = $w / $size[0];
			$zoom_h = $h / $size[1];
			$zoom = min($zoom_w, $zoom_h);
		}
		$w = round($size[0] * $zoom);
		$h = round($size[1] * $zoom);
		
		$tmp = $img . '.tmp';
		$done = HypCommonFunc::make_thumb($img, $tmp, $w, $h, '1,99', TRUE, $quality);
		
		if ($done === $img) return false;
		
		unlink($img);
		copy($tmp, $img);
		unlink($tmp);
		
		return true;
	}
	
	// 画像を角丸にする
	function ImageMagickRoundCorner($o_file, $s_file = '', $corner = 10, $edge = 0, $refresh = FALSE) {
		
		if (!defined('HYP_IMAGEMAGICK_PATH') || !HYP_IMAGEMAGICK_PATH) return $o_file;
		
		if ($o_file === $s_file) $s_file = '';
		
		// すでに作成済み
		if (!$refresh && $s_file && file_exists($s_file)) return $s_file;
		
		$is_own = FALSE;
		if (!$s_file) {
			// CGI を直接叩かれて悪戯されないように一時ファイルを利用
			$s_file = $o_file . '.tmp';
			$is_own = TRUE;
		}

		$size = @getimagesize($o_file);
		if (!$size) return $o_file;//画像ファイルではない
		
		$ro_file = realpath($o_file);
		$rs_file = realpath(dirname($s_file))."/".basename($s_file);
		
		if (file_exists($rs_file)) unlink($rs_file);
		
		// Make Thumb and check success
		if ( ini_get('safe_mode') != "1" ) {
			// 元画像のサイズ
			$imw = $size[0];
			$imh = $size[1];
			$im_half = floor((min($imw, $imh)/2));
		
			// check value
			$edge = min($edge, $im_half);
			$corner = min($corner, $im_half);
	
			$tmpfile = $rs_file . '_tmp.png';
	
			$cmd = 'convert -size '.$imw.'x'.$imh.' xc:none -channel RGBA -fill white -draw "roundrectangle '.max(0,($edge-1)).','.max(1,($edge-1)).' '.($imw-$edge).','.($imh-$edge).' '.$corner.','.$corner.'" '.$ro_file.' -compose src_in -composite '.$tmpfile;
			exec( HYP_IMAGEMAGICK_PATH . $cmd ) ;
	
			if ($edge) {
				$cmd = 'convert -size '.$imw.'x'.$imh.' xc:none -fill none -stroke white -strokewidth '.$edge.' -draw "roundrectangle '.($edge-1).','.($edge-1).' '.($imw-$edge).','.($imh-$edge).' '.$corner.','.$corner.'" -shade 135x25 -blur 0x1 -normalize '.$tmpfile.' -compose overlay -composite '.$tmpfile;		
				exec( HYP_IMAGEMAGICK_PATH . $cmd ) ;
			}
			copy ($tmpfile, $rs_file);
			unlink($tmpfile);
		} else {
			// safeモードの場合は、CGIを起動して取得してみる
			
			$cmds = "?m=ro".
					"&p=".rawurlencode(HYP_IMAGEMAGICK_PATH).
					"&z=".$corner.
					"&q=".$edge.
					"&o=".rawurlencode($ro_file).
					"&s=".rawurlencode($rs_file);
			
			HypCommonFunc::exec_image_magick_cgi($cmds);
		}
		
		if( ! is_readable( $rs_file ) ) {
			if (file_exists($rs_file)) unlink($rs_file);
			return $ro_file;
		}
		
		if ($is_own) {
			unlink($ro_file);
			copy($rs_file, $ro_file);
			unlink($rs_file);
		}
		
		return $rs_file;
	}
	
	// GD のバージョンを取得
	// RETURN 0:GDなし, 1:Ver 1, 2:Ver 2
	function gdVersion($user_ver = 0)
	{
		if (! extension_loaded('gd')) { return 0; }
		static $gd_ver = 0;
		// Just accept the specified setting if it's 1.
		if ($user_ver == 1) { $gd_ver = 1; return 1; }
		// Use the static variable if function was called previously.
		if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
		// Use the gd_info() function if possible.
		if (function_exists('gd_info')) {
			$ver_info = gd_info();
			$match = array();
			preg_match('/\d/', $ver_info['GD Version'], $match);
			$gd_ver = $match[0];
			return $match[0];
		}
		// If phpinfo() is disabled use a specified / fail-safe choice...
		if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
			if ($user_ver == 2) {
				$gd_ver = 2;
				return 2;
			} else {
				$gd_ver = 1;
				return 1;
			}
		}
		// ...otherwise use phpinfo().
		ob_start();
		phpinfo(8);
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr($info, 'gd version');
		preg_match('/\d/', $info, $match);
		$gd_ver = $match[0];
		return $match[0];
	}
	
	function check_memory4gd($w,$h)
	{
		// GDで処理可能なメモリーサイズ
		static $memory_limit = NULL;
		if (is_null($memory_limit))
		{
			$memory_limit = HypCommonFunc::return_bytes(ini_get('memory_limit'));
		}
		if ($memory_limit)
		{
			// ビットマップ展開時のメモリー上のサイズ
			$bitmap_size = $w * $h * 3 + 54;
			
			if ($bitmap_size > $memory_limit - memory_get_usage() - (1 * 1024 * 1024))
			{
				// メモリー制限に引っ掛かりそう。（マージン 1MB）
				return false;
			}
		}
		return true;
	}
	
	// イメージを回転
	function rotateImage($src, $count = 1, $quality = 95)
	{
		$src = realpath($src);
		
		if (!file_exists($src)) {
			return false;
		}

		list($w, $h, $type) = @getimagesize($src);
		
		if (!$w || !$h || ((!defined('HYP_IMAGEMAGICK_PATH') || !HYP_IMAGEMAGICK_PATH) && $type != 2)) return false;
		
		$angle = (($count > 0 && $count < 4) ? $count : 0 ) * 90;
		if (!$angle) return false;
		
		if (defined('HYP_JPEGTRAN_PATH') && HYP_JPEGTRAN_PATH && $type == 2)
		{
			// jpegtran を使用
			if (ini_get('safe_mode') != "1")
			{
				$ret = true;
				$tmpfname = @tempnam(dirname($src), "tmp_");
				exec( HYP_JPEGTRAN_PATH."jpegtran -rotate {$angle} -copy all {$src} > {$tmpfname}" );
				if ( ! @filesize($tmpfname) || ! @unlink($src) )
				{
					$ret = false;
				}
				else
				{
					rename($tmpfname, $src);
					//chmod($src, 0666);
				}
				@unlink($tmpfname);
				return $ret;
			}
			else
			{
				$cmds = "?m=rj".
						"&p=".rawurlencode(HYP_JPEGTRAN_PATH).
						"&z=".$angle.
						"&q=".$quality.
						"&s=".rawurlencode($src);
							
				return HypCommonFunc::exec_image_magick_cgi($cmds);
			}
		}
		else if (defined('HYP_IMAGEMAGICK_PATH') && HYP_IMAGEMAGICK_PATH)
		{
			// image magick を使用
			if (ini_get('safe_mode') != "1")
			{
				$ret = true;
				$out = array();
				exec( HYP_IMAGEMAGICK_PATH."convert -size {$w}x{$h} -rotate +{$angle} -quality {$quality} {$src} {$src}", $out ) ;
				if ($out)
				{
					$ret = false;
				}
				else
				{
					//chmod($src, 0666);
				}
				return $ret;
			}
			else
			{
				$cmds = "?m=ri".
						"&p=".rawurlencode(HYP_IMAGEMAGICK_PATH).
						"&z=".$angle.
						"&q=".$quality.
						"&s=".rawurlencode($src);
							
				return HypCommonFunc::exec_image_magick_cgi($cmds);				
			}
		}
		else
		{
			// GD を使用
			
			// メモリーチェック
			if (!HypCommonFunc::check_memory4gd($w,$h)) return false;
			
			$angle = 360 - $angle;
			if (($in = imageCreateFromJpeg($src)) === false) {
				return false;
			}
			if ($w == $h || $angle == 180) {
				$out = imageRotate($in, $angle, 0);
			} elseif ($angle == 90 || $angle == 270) {
				$size = ($w > $h ? $w : $h);
				
				$portrait = ($h > $w)? true : false; 
				
				// Create a square image the size of the largest side of our src image
				if (($tmp = imageCreateTrueColor($size, $size)) == false) {
					//echo "Failed create square trueColor<br>";
					return false;
				}
	
				// Exchange sides
				if (($out = imageCreateTrueColor($h, $w)) == false) {
					//echo "Failed create trueColor<br>";
					return false;
				}
	
				// Now copy our src image to tmp where we will rotate and then copy that to $out
				imageCopy($tmp, $in, 0, 0, 0, 0, $w, $h);
				$tmp2 = imageRotate($tmp, $angle, 0);
	
				// Now copy tmp2 to $out;
				imageCopy($out, $tmp2, 0, 0, (($angle == 270 && !$portrait) ? abs($w - $h) : 0), (($angle == 90 && $portrait) ? abs($w - $h) : 0), $h, $w);
				imageDestroy($tmp);
				imageDestroy($tmp2);
			} elseif ($angle == 360) {
				imageDestroy($in);
				return true;
			}
			unlink($src);
			imageJpeg($out, $src, $quality);
			imageDestroy($in);
			imageDestroy($out);
			//chmod($src, 0666);
			return true;
		}
	}
	
	// image_magick.cgi へアクセス
	function exec_image_magick_cgi($cmds)
	{
		if (defined('HYP_IMAGE_MAGICK_URL'))
		{
			$url = HYP_IMAGE_MAGICK_URL;
		}
		else
		{
			die('ERROR: "image_magick.cgi" path is not set.');
		}
		
		$url .= $cmds;
		
		$d = new Hyp_HTTP_Request();
	
		$d->url = $url;
		$d->connect_try = 2;
		$d->connect_timeout = 5;
		$d->read_timeout = 60;
		
		$d->get();
		
		if ($d->rc != 200) die("'".$url."' is NG. Not found or access denied.");
		
		$ret = trim((string)$d->data);
		$ret = ($ret == "ERROR: 0")? true : false;
		
		return $ret;
	}
	
	// 外部実行コマンドのパスを設定
	function set_exec_path($dir)
	{
		HypCommonFunc::set_jpegtran_path($dir);
		HypCommonFunc::set_imagemagick_path($dir);
		HypCommonFunc::set_hyp_image_magic_url();
	}
	
	// Image Magick のパスを設定(定数化)
	function set_imagemagick_path($dir)
	{
		// すでに設定済み
		if (defined('HYP_IMAGEMAGICK_PATH')) return;
		
		if (substr($dir, -1) != "/") $dir .= "/"; 
		if (is_executable($dir."convert"))
		{
			define ('HYP_IMAGEMAGICK_PATH', $dir);
		}
		return;
	}

	// jpegtran のパスを設定(定数化)
	function set_jpegtran_path($dir)
	{
		// すでに設定済み
		if (defined('HYP_JPEGTRAN_PATH')) return;
		if (substr($dir, -1) != "/") $dir .= "/"; 
		if (is_executable($dir."jpegtran"))
		{
			define ('HYP_JPEGTRAN_PATH', $dir);
		}
		return;
	}
	
	
	function set_hyp_image_magic_url($url='')
	{
		// すでに設定済み
		if (defined('HYP_IMAGE_MAGICK_URL')) return;
		
		if ($url)
		{
			define('HYP_IMAGE_MAGICK_URL', $url);
		}
		else
		{
			// セーフモード時は、image_magick.cgi へのURLを探索してみる
			if ( ini_get('safe_mode') == "1" )
			{
				if (defined('XOOPS_URL'))
				{
					//XOOPS環境下
					$moddir = basename(dirname($_SERVER['REQUEST_URI']));
					if (file_exists(XOOPS_ROOT_PATH."/class/hyp_common/image_magick.cgi"))
					{
						define('HYP_IMAGE_MAGICK_URL', XOOPS_URL."/class/hyp_common/image_magick.cgi");
					}
					else if (file_exists(XOOPS_ROOT_PATH."/modules/{$moddir}/include/hyp_common/image_magick.cgi"))
					{
						define('HYP_IMAGE_MAGICK_URL', XOOPS_URL."/modules/{$moddir}/include/hyp_common/image_magick.cgi");
					}
				}
				else
				{
					$url  = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://'); // scheme
					$url .= $_SERVER['HTTP_HOST'];	// host
					$url .= ($_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']);  // port
		
					// DOCUMENT_ROOT と このファイル位置から URL を計算
					if (!empty($_SERVER['DOCUMENT_ROOT']))
					{
						$path = str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(__FILE__));
						$url .= $path."/image_magick.cgi";
						define('HYP_IMAGE_MAGICK_URL', $url);
					}
				}
			}
		}
		return;
	}
	
	// 2ch BBQ あらしお断りシステム にリスティングされているかチェック
	function IsBBQListed($safe_reg = '/^$/', $msg = true, $ip = NULL, $checker = array('list.dsbl.org', 'niku.2ch.net'))
	{
		if (is_null($ip)) $ip = $_SERVER['REMOTE_ADDR'];
		if(! preg_match($safe_reg, $ip))
		{
			$host = array_reverse(explode('.', $ip));
			foreach($checker as $chk) {
				$addr = sprintf("%d.%d.%d.%d.". $chk,
					$host[0],$host[1],$host[2],$host[3]);
				$addr = gethostbyname($addr);
				if(preg_match("/^127\.0\.0/",$addr)) return $msg;
			}
		}
		return false;
	}
	
	// 2ch BBQ チェック用汎用関数
	function BBQ_Check($safe_reg = "/^(127\.0\.0\.1)/", $msg = true, $ip = NULL, $checker = array('list.dsbl.org', 'niku.2ch.net'))
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$_msg = HypCommonFunc::IsBBQListed($safe_reg, $msg, $ip, $checker);
			if ($_msg !== false)
			{
				exit ($_msg);
			}
		}
		return;
	}
	
	// POST SPAM Check
	function PostSpam_Check($post, $encode = '', $encodehint = '')
	{
		if (function_exists('mb_convert_variables') && $encode) {
			// 文字エンコード変換
			if ($encodehint && isset($post[$encodehint])) {
				$post_enc = mb_detect_encoding($post[$encodehint]);
				if ($encode !== $post_enc) {
					mb_convert_variables($encode, mb_detect_encoding($post[$encodehint]), $post);
				}
			} else {
				// Key:url, excerpt があればトラックバックかも->文字コード変換
				if (isset($post['url']) && isset($post['excerpt']) && function_exists('mb_convert_variables')) {
					if (isset($post['charset']) && $post['charset'] != '') {
						// TrackBack Ping で指定されていることがある
						// うまくいかない場合は自動検出に切り替え
						if (mb_convert_variables($encode,
						    $post['charset'], $post) !== $post['charset']) {
							mb_convert_variables($encode, 'auto', $post);
						}
					} else if (! empty($post)) {
						// 全部まとめて、自動検出／変換
						mb_convert_variables($encode, 'auto', $post);
					}
				}
			}
		}
		
		static $filters = NULL;
		if (is_null($filters)) {$filters = HypCommonFunc::PostSpam_filter();}
		$counts = array();
		$counts[0] = $counts[1] = $counts[2] = $counts[3] = 0;
		foreach($post as $key => $dat)
		{
			$tmp = array();
			$tmp['a'] = $tmp['bb'] = $tmp['url'] = $tmp['filter'] = 0;
			if (is_array($dat))
			{
				list($tmp['a'],$tmp['bb'],$tmp['url'],$tmp['filter']) = HypCommonFunc::PostSpam_Check($dat);
			}
			else
			{
				// NULLバイト削除
				$dat = str_replace("\0", '', $dat);
				
				// <a> タグの個数
				$tmp['a'] = count(preg_split("/<a.+?\/a>/i",$dat)) - 1;
				// [url] タグの個数
				$tmp['bb'] = count(preg_split("/\[url=.+?\/url\]/i",$dat)) - 1;
				// URL の個数
				$tmp['url'] = count(preg_split("/(ht|f)tps?:\/\/[^\s]+/i",$dat)) - 1;
				// フィルター
				if ($filters)
				{
					foreach($filters as $reg => $point)
					{
						if ($reg === 'array_rule') {
							if (isset($point['ignore_fileds'])) {
								foreach($point['ignore_fileds'][0] as $checkkey => $targets) {
									foreach($targets as $target) {
										if (strtolower($checkkey) === strtolower($key) && $post[$key] ){
											if (!$target || preg_match('/'.preg_quote($target,'/').'/i',$_SERVER['PHP_SELF'])) {
												$tmp['filter'] += $point['ignore_fileds'][1];
											}
										}
									}
								}
							}
						} else {
							$tmp['filter'] += (count(preg_split($reg,$dat)) - 1) * $point;
						}
					}
				}
			}
			$counts[0] = max($counts[0], $tmp['a']);
			$counts[1] = max($counts[1], $tmp['bb']);
			$counts[2] = max($counts[2], $tmp['url']);
			$counts[3] = max($counts[3], $tmp['filter']);
		}
		return $counts;
	}
	
	// POST SPAM フィルター
	function PostSpam_filter($reg="", $point=1)
	{
		static $regs = array();
		if (empty($reg)) {return $regs;}
		$regs[$reg] = $point;
	}
	
	// POST SPAM Check 汎用関数
	function get_postspam_avr($alink=1,$bb=1,$url=1,$encode='EUC-JP',$encodehint='')
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			list($a_p,$bb_p,$url_p,$filter_p) = HypCommonFunc::PostSpam_Check($_POST, $encode, $encodehint);
			return $a_p * $alink + $bb_p * $bb + $url_p * $url + $filter_p;
		}
		else
		{
			return 0;
		}
	}
	
	// 機種依存文字フィルター
	function dependence_filter($post)
	{
		if (!isset($post) || !function_exists("mb_ereg_replace")) {return $post;}
		
		//$post_enc = defined('HYP_POST_ENCODING')? HYP_POST_ENCODING : _CHARSET;
		//if ($post_enc !== 'EUC-JP' && $post_enc !== 'UTF-8') {return $post;}
		if (!defined('HYP_POST_ENCODING') || (HYP_POST_ENCODING !== 'EUC-JP' && HYP_POST_ENCODING !== 'UTF-8')) {return $post;}

		static $bef = null;
		static $aft = null;
		
		if (is_null($bef))
		{
			$mac = (empty($_SERVER["HTTP_USER_AGENT"]))? FALSE : strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"mac");
			
			if ($mac && HYP_POST_ENCODING !== 'UTF-8') {return $post;}
			
			$enc = (HYP_POST_ENCODING === 'UTF-8')? '_utf8' : '';
			
			$datfile = ($mac === FALSE)? dirname(__FILE__).'/win_ext'.$enc.'.dat' : dirname(__FILE__).'/mac_ext'.$enc.'.dat';
	
			if (file_exists($datfile))
			{
				$bef = $aft = array();
				foreach(file($datfile) as $line)
				{
					if ($line[0] != "/" && $line[0] != "#")
					{
						list($bef[],$aft[]) = explode("\t",rtrim($line));	
					}
				}
			}
		}
		
		if (is_array($post))
		{
			foreach ($post as $_key=>$_val)
			{
				$post[$_key] = HypCommonFunc::dependence_filter($_val);
			}	
		}
		else
		{
			mb_regex_encoding(HYP_POST_ENCODING);

			// 半角カナを全角に
			//$post = mb_convert_kana($post, "KV", "EUC-JP");
			
			// 変換テーブル
			for ($i=0; $i<sizeof($bef); $i++)
			{
				$post = mb_ereg_replace($bef[$i], $aft[$i], $post);
			}	
		}

		return $post;
	}
	
	// 文字エンコード変換前に範囲外の文字を実体参照値に変換する
	function encode_numericentity(& $arg, $toencode, $fromencode, $keys = array()) {
		if (strtoupper($fromencode) === strtoupper($toencode)) return;
		if (is_array($arg)) {
			foreach (array_keys($arg) as $key) {
				if (!$keys || in_array($key, $keys)) {
					HypCommonFunc::encode_numericentity($arg[$key], $toencode, $fromencode, $keys);
				}
			}
		} else {
			if ($arg === mb_convert_encoding(mb_convert_encoding($arg, $toencode, $fromencode), $fromencode, $toencode)) {
				return;
			}
			$str = '';
			$max = mb_strlen($arg, $fromencode);
			$convmap = array(0x0080, 0x10FFFF, 0, 0xFFFFFF);
			for ($i = 0; $i < $max; $i++) {
				$org = mb_substr($arg, $i, 1, $fromencode);
				if ($org === mb_convert_encoding(mb_convert_encoding($org, $toencode, $fromencode), $fromencode, $toencode)) {
					$str .= $org;
				} else {
					$str .= mb_encode_numericentity($org, $convmap, $fromencode);
				} 
			}
			$arg = $str;
		}
		return;
	}
	
	// リファラーから検索語と検索エンジンを取得し定数に定義する
	function set_query_words($qw="HYP_QUERY_WORD",$qw2="HYP_QUERY_WORD2",$en="HYP_SEARCH_ENGINE_NAME",$tmpdir="",$enc='EUC-JP')
	{
		if (!defined($qw))
		{
			if (file_exists(dirname(__FILE__)."/hyp_get_engine.php"))
			{
				include_once(dirname(__FILE__)."/hyp_get_engine.php");
				HypGetQueryWord::set_constants($qw,$qw2,$en,$tmpdir,$enc);
			}
			else
			{
				define($qw , "");
				define($qw2, "");
				define($en , "");
			}
		}
	}
	
	// php.ini のサイズ記述をバイト値に変換
	function return_bytes($val) {
		$val = trim($val);
		if ($val == '-1') $val = '';
		if ($val) {
			$last = strtolower($val{strlen($val)-1});
			switch($last) {
				// 'G' は、PHP 5.1.0 より有効となる
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;
		   }
		}
		return $val;
	}

	// 配列から正規表現を得る
	function get_reg_pattern(& $words, $minlen = 1)
	{
		$reg_words = array();

		foreach ($words as $word)
		{
			if (strlen($word) >= $minlen)
				$reg_words[] = $word;
		}

		if (count($reg_words) == 0)
		{
			$result = '(?!)';
		}
		else
		{
			$reg_words = array_unique($reg_words);
			sort($reg_words, SORT_STRING);

			$result = HypCommonFunc::get_reg_pattern_sub($reg_words, 0, count($reg_words), 0);
		}
		
		return $result;
	}

	function get_reg_pattern_sub(& $words, $start, $end, $pos)
	{
		static $lev = 0;
		
		if ($end == 0) return '(?!)';
		
		$lev ++;
		
		$result = '';
		$count = $i = $j = 0;
		$x = (mb_strlen($words[$start]) <= $pos);
		if ($x) { ++$start; }
		
		for ($i = $start; $i < $end; $i = $j)
		{
			$char = mb_substr($words[$i], $pos, 1);
			for ($j = $i; $j < $end; $j++)
			{
				if (mb_substr($words[$j], $pos, 1) != $char) { break; }
			}
			if ($i != $start)
			{
				if ($lev === 1)
				{
					$result .= "\x08";
				}
				else
				{
					$result .= '|';
				}
				
			}
			if ($i >= ($j - 1))
			{
				$result .= str_replace(' ', '\\ ', preg_quote(mb_substr($words[$i], $pos), '/'));
			}
			else
			{
				$result .= str_replace(' ', '\\ ', preg_quote($char, '/')) .
					HypCommonFunc::get_reg_pattern_sub($words, $i, $j, $pos + 1);
			}
			
			++$count;
		}
		if ($lev === 1)
		{
			$limit = 1024 * 30; //マージンを持たせて 30kb で分割
			$_result = "";
			$size = 0;
			foreach(explode("\x08",$result) as $key)
			{
				if (strlen($_result.$key) - $size > $limit)
				{
					$_result .= ")\x08(?:".$key;
					$size = strlen($_result);
				}
				else
				{
					$_result .= ($_result ? "|" : "").$key;
				}
			}
			$result = '(?:' . $_result . ')';
		}
		else
		{
			if ($x or $count > 1) { $result = '(?:' . $result . ')'; }
			if ($x) { $result .= '?'; }
		}
		$lev --;
		return $result;
	}

	function register_bad_ips( $ip = null )
	{
		if( empty( $ip ) ) $ip = $_SERVER['REMOTE_ADDR'] ;
		if( empty( $ip ) ) return false ;
	
		$db = Database::getInstance() ;
		$rs = $db->query( "SELECT conf_value FROM ".$db->prefix("config")." WHERE conf_name='bad_ips' AND conf_modid=0 AND conf_catid=1" ) ;
		list( $bad_ips_serialized ) = $db->fetchRow( $rs ) ;
		$bad_ips = unserialize( $bad_ips_serialized ) ;
		$bad_ips[] = $ip ;
	
		$conf_value = addslashes( serialize( array_unique( $bad_ips ) ) ) ;
		$db->queryF( "UPDATE ".$db->prefix("config")." SET conf_value='$conf_value' WHERE conf_name='bad_ips' AND conf_modid=0 AND conf_catid=1" ) ;
	
		return true ;
	}

}

/*
 *   HTTPリクエストを発行し、データを取得する
 * $url     : http://から始まるURL(http://user:pass@host:port/path?query)
 * $method  : GET, POST, HEADのいずれか(デフォルトはGET)
 * $headers : 任意の追加ヘッダ
 * $post    : POSTの時に送信するデータを格納した配列('変数名'=>'値')
 * $redirect_max : HTTP redirectの回数制限
*/

if( ! class_exists( 'Hyp_HTTP_Request' ) )
{
class Hyp_HTTP_Request
{
	var $url='';
	var $method='GET';
	var $headers='';
	var $post=array();
	var $ua='';

	var $uri='';
	
	// リダイレクト回数制限
	var $redirect_max=10;
	// 同期モード or 非同期モード
	var $blocking=TRUE;
	// 接続試行回数
	var $connect_try=1;
	// 接続時タイムアウト
	var $connect_timeout=30;
	// 通信時タイムアウト
	var $read_timeout=10;
	// POST文字エンコード
	var $content_charset='';
	
	var $network_reg = '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
	
	// プロキシ使用？
	var $use_proxy=0;
	
	// proxy ホスト
	var $proxy_host='proxy.xxx.yyy.zzz';
	
	// proxy ポート番号
	var $proxy_port='';
	
	// プロキシサーバを使用しないホストのリスト
	var $no_proxy=array( 
		'127.0.0.1', 
		'localhost', 
		//'192.168.1.0/24', 
		//'no-proxy.com', 
	);
	
	// プロキシ認証
	var $need_proxy_auth=0;
	var $proxy_auth_user='';
	var $proxy_auth_pass='';

	// result
	var $query = '';   // Query String
	var $rc = '';      // Response Code
	var $header = '';  // Header
	var $data = '';    // Data
	
	function Hyp_HTTP_Request()
	{
		$this->ua="PHP/".PHP_VERSION;
	}
		
	function init()
	{
		$this->url='';
		$this->method='GET';
		$this->headers='';
		$this->post=array();
		$this->ua="PHP/".PHP_VERSION;
		
		// result
		$this->query = '';   // Query String
		$this->rc = '';      // Response Code
		$this->header = '';  // Header
		$this->data = '';    // Data
	}
	function get()
	{
		$max_execution_time = ini_get('max_execution_time');
		$max_execution_time = ($max_execution_time)? $max_execution_time : 30;
		
		$rc = array();
		$arr = parse_url($this->url);
		if (!$this->connect_try) $this->connect_try = 1;
		
		$via_proxy = $this->use_proxy ? ! $this->in_the_net($this->no_proxy, $arr['host']) : FALSE;
		
		// query
		$arr['query'] = isset($arr['query']) ? '?'.$arr['query'] : '';
		// port
		$arr['port'] = isset($arr['port']) ? $arr['port'] : 80;
		
		$url_base = $arr['scheme'].'://'.$arr['host'].':'.$arr['port'];
		$url_path = isset($arr['path']) ? $arr['path'] : '/';
		$this->uri = ($via_proxy ? $url_base : '').$url_path.$arr['query'];
		$this->method = strtoupper($this->method);
		$method = ($this->method == 'HEAD')? 'GET' : $this->method;
		$readsize = ($this->method == 'HEAD')? 1024 : 4096;
		
		$query = $method.' '.$this->uri." HTTP/1.0\r\n";
		$query .= "Host: ".$arr['host']."\r\n";
		if (!empty($this->ua)) $query .= "User-Agent: ".$this->ua."\r\n";
		
		// proxyのBasic認証 
		if ($this->need_proxy_auth and isset($this->proxy_auth_user) and isset($this->proxy_auth_pass)) 
		{
			$query .= 'Proxy-Authorization: Basic '.
				base64_encode($this->proxy_auth_user.':'.$this->proxy_auth_pass)."\r\n";
		}

		// Basic 認証用
		if (isset($arr['user']) and isset($arr['pass']))
		{
			$query .= 'Authorization: Basic '.
				base64_encode($arr['user'].':'.$arr['pass'])."\r\n";
		}
		
		$query .= $this->headers;
		
		// POST 時は、urlencode したデータとする
		if ($this->method == 'POST')
		{
			if (is_array($this->post))
			{
				$_send = array();
				foreach ($this->post as $name=>$val)
				{
					$_send[] = $name.'='.urlencode($val);
				}
				$data = join('&',$_send);
				
				if (preg_match('/^[a-zA-Z0-9_-]+$/', $this->content_charset)) {
					// Legacy but simple
					$query .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
				} else {
					// With charset (NOTE: Some implementation may hate this)
					$query .= 'Content-Type: application/x-www-form-urlencoded' .
						'; charset=' . strtolower($this->content_charset) . "\r\n";
				}

				$query .= 'Content-Length: '.strlen($data)."\r\n";
				$query .= "\r\n";
				$query .= $data;
			}
			else
			{
				$query .= 'Content-Length: '.strlen($this->post)."\r\n";
				$query .= "\r\n";
				$query .= $this->post;
			}
		}
		else
		{
			$query .= "\r\n";
		}
		
		//set_time_limit($this->connect_timeout * $this->connect_try + 60);
		$fp = $connect_try_count = 0;
		while( !$fp && $connect_try_count < $this->connect_try )
		{
			@set_time_limit($this->connect_timeout + $max_execution_time);
			
			if ($now_execution_time = ini_get('max_execution_time')) {
				$this->connect_timeout = min($this->connect_timeout, $now_execution_time - 10);
			}
			
			$errno = 0;
			$errstr = "";
			$fp = fsockopen(
				$via_proxy ? $this->proxy_host : $arr['host'],
				$via_proxy ? $this->proxy_port : $arr['port'],
				$errno,$errstr,$this->connect_timeout);
			if ($fp) break;
			$connect_try_count++;
			sleep(2); //2秒待つ
		}
		if (!$fp)
		{
			$this->query  = $query;  // Query String
			$this->rc     = $errno;  // エラー番号
			$this->header = '';      // Header
			$this->data   = $errstr; // エラーメッセージ
			return;
		}
		
		fputs($fp, $query);
		
		// 非同期モード
		if (!$this->blocking)
		{
			fclose($fp);
			$this->query  = $query;
			$this->rc     = 200;
			$this->header = '';
			$this->data   = 'Blocking mode is FALSE';
			return;
		}
		
		$response = '';
		while (!feof($fp) && ($this->method != 'HEAD' || strpos($response,"\r\n\r\n") === FALSE))
		{
			if ($this->read_timeout)
			{
				@set_time_limit($this->read_timeout + $max_execution_time);
				socket_set_timeout($fp, $this->read_timeout);
			}
			$_response = fread($fp, $readsize);
			$_status = socket_get_status($fp);
			if ($_status['timed_out'] === false)
			{
				$response .= $_response;
			}
			else
			{
				fclose($fp);
				$this->query  = $query;
				$this->rc     = 408;
				$this->header = '';
				$this->data   = 'Request Time-out';
				return;
			}
		}
		fclose($fp);
		
		$resp = explode("\r\n\r\n",$response,2);
		$rccd = explode(' ',$resp[0],3); // array('HTTP/1.1','200','OK\r\n...')
		$rc = (integer)$rccd[1];
		
		// Redirect
		switch ($rc)
		{
			case 303: // See Other
			case 302: // Moved Temporarily
			case 301: // Moved Permanently
				$matches = array();
				if (preg_match('/^Location: (.+)$/m',$resp[0],$matches)
					and --$this->redirect_max > 0)
				{
					$this->url = trim($matches[1]);
					if (!preg_match('/^https?:\//',$this->url)) // no scheme
					{
						if ($this->url{0} != '/') // Relative path
						{
							// to Absolute path
							$this->url = substr($url_path,0,strrpos($url_path,'/')).'/'.$this->url;
						}
						// add sheme,host
						$this->url = $url_base.$this->url;
					} 
					return $this->get();
				}
		}
		
		$this->query = $query;    // Query String
		$this->rc = $rc;          // Response Code
		$this->header = $resp[0]; // Header
		$this->data = $resp[1];   // Data
		return;
	}

	// プロキシを経由する必要があるかどうか判定
	// Check if the $host is in the specified network(s)
	function in_the_net($networks = array(), $host = '')
	{
		if (empty($networks) || $host == '') return FALSE;
		if (! is_array($networks)) $networks = array($networks);
	
		$matches = array();
	
		if (preg_match($this->network_reg, $host, $matches)) {
			$ip = $matches[1];
		} else {
			$ip = gethostbyname($host); // May heavy
		}
		$l_ip = ip2long($ip);
	
		foreach ($networks as $network) {
			if (preg_match($this->network_reg, $network, $matches) &&
			    is_long($l_ip) && long2ip($l_ip) == $ip) {
				// $host seems valid IPv4 address
				// Sample: '10.0.0.0/8' or '10.0.0.0/255.0.0.0'
				$l_net = ip2long($matches[1]); // '10.0.0.0'
				$mask  = isset($matches[2]) ? $matches[2] : 32; // '8' or '255.0.0.0'
				$mask  = is_numeric($mask) ?
					pow(2, 32) - pow(2, 32 - $mask) : // '8' means '8-bit mask'
					ip2long($mask);                   // '255.0.0.0' (the same)
	
				if (($l_ip & $mask) == $l_net) return TRUE;
			} else {
				// $host seems not IPv4 address. May be a DNS name like 'foobar.example.com'?
				foreach ($networks as $network)
					if (preg_match('/\.?\b' . preg_quote($network, '/') . '$/', $host))
						return TRUE;
			}
		}
	
		return FALSE; // Not found
	}
}
}

// create a instance in global scope
//$GLOBALS['hypCommonFunc'] = new HypCommonFunc() ;

// Make context for search by nao-pon
if (!function_exists('xoops_make_context'))
{
function xoops_make_context($text,$words=array(),$l=255)
{
	return HypCommonFunc::make_context($text,$words,$l);
}
}

if (!function_exists('xoops_update_rpc_ping'))
{
function xoops_update_rpc_ping($default_update="http://bulkfeeds.net/rpc http://ping.myblog.jp http://ping.bloggers.jp/rpc/ http://blog.goo.ne.jp/XMLRPC http://ping.cocolog-nifty.com/xmlrpc http://rpc.technorati.jp/rpc/ping")
{
	return HypCommonFunc::update_rpc_ping($default_update);
}
}

if( !function_exists('memory_get_usage') )
{
function memory_get_usage()
{
	$output = array();
	//If its Windows
	//Tested on Win XP Pro SP2. Should work on Win 2003 Server too
	//Doesn't work for 2000
	//If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memory-get-usage.php#54642
	if ( substr(PHP_OS,0,3) == 'WIN')
	{
		exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
		$mem = (empty($output[5]))? 0 : intval(preg_replace( '/[\D]/', '', $output[5] ));
		$mem = $mem * 1024;
	}
	else
	{
		//We now assume the OS is UNIX
		//Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
		//This should work on most UNIX systems
		$pid = getmypid();
		exec("ps -eo%mem,rss,pid | grep $pid", $output);
		$output = explode("  ", $output[0]);
		$mem = (empty($output[1]))? 0 : intval($output[1]);
		//rss is given in 1024 byte units
		$mem = $mem * 1024;
	}
	return $mem;
}
}

// 初期化作業
// ImageMagick のパス設定ファイルがあれば読み込む
if (file_exists(dirname(__FILE__)."/execpath.inc.php"))
{
	include_once(dirname(__FILE__)."/execpath.inc.php");	
}
// ImageMagick のパスを指定 (多くは /usr/bin/ ?)
HypCommonFunc::set_exec_path("/usr/bin/");

}
?>