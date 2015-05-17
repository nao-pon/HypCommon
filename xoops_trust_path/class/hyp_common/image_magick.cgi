<?php
error_reporting(0);

if (php_sapi_name() == "cli")
{
	echo "Content-Type: text/plain\n\n";
}
else
{
	header("Content-Type: text/plain");
}

// 戻り値
$ret = "ERROR: 9";

if (empty($_SERVER['QUERY_STRING'])) exit($ret);

// 有効なパラメーター
$allows = array("m","p","z","r","q","u","o","s");



// CLI版の場合 $_GET では取得できないので独自取得
foreach(explode('&',$_SERVER['QUERY_STRING']) as $prm)
{
	list($key,$val) = array_pad(explode('=',$prm),2,'');

	$key = str_replace("\0","",$key);

	// 必要ないパラメータは捨てる
	if (!in_array($key,$allows)) continue;
	$val = rawurldecode(str_replace("\0","",$val));

	$$key = $val;
}

// リサイズ
if ($m == 'r')
{
	// 必要なパラメーターがあるかどうか
	$needs = array("p","z","q","o","s");
	foreach($needs as $key)
	{
		if (empty($$key)) exit($ret);
	}

	$q = intval($q);
	$z = intval($z);
	$r = intval($r);
	$p = escapeshellcmd($p);

	// 変数チェック

	// ディレクトリ遡りパターン検出
	if (preg_match("/([\|\s]|\.\.\/)/",$p.$o.$s)) exit($ret);

	// 元ファイルの存在確認
	if (!file_exists($o)) exit($ret);

	// イメージファイルか？
	$size = @getimagesize($o);
	if (!$size) exit($ret); //画像ファイルではない

	// ズームの範囲
	if ($z < 1 || $z > 100) exit($ret);

	// quality の範囲
	if ($q < 1 || $q > 100) exit($ret);
	
	// orientation
	if (isset($r) && is_numeric($r) && $r > 1) {
		$autoorient = ($orientation > 1)? ' -auto-orient' : '';
	}

	// unshrap の値
	if (empty($u) || preg_match('/^[0-9.|]+$/', trim($u))) {
		$u = '';
	} else {
		$u = trim($u);
	}
	list($amount, $radius, $threshold) = array_pad(explode('|', $u), 3, '');
	$amount    = ($amount            ? $amount    : 80);
	$radius    = ($radius            ? $radius    : 0.5);
	$threshold = (strlen($threshold) ? $threshold : 3);
	$u = ' -unsharp ' . number_format(($radius * 2) - 1, 2).'x1+'.number_format($amount / 100, 2).'+'.number_format($threshold / 100, 2);

	// 元画像のサイズ
	$w = $size[0];
	$h = $size[1];

	// 実行
	$out = array();
	$res = 1;
	$qo = escapeshellarg($o);
	$qs = escapeshellarg($qs);
	exec( "{$p}convert -thumbnail {$z}%  -quality {$q}{$autoorient}{$u} {$qo} {$qs}" , $out, $res) ;

	if ($res !== 0)
	{
		$ret = "ERROR: 1";
	}
	else
	{
		$ret = "ERROR: 0";
		@chmod($s, 0606);
	}

	// 完了
	exit($ret);
}

// Round Corner
else if ($m == 'ro')
{
	// 必要なパラメーターがあるかどうか
	$needs = array("p","z","q","o","s");
	foreach($needs as $key)
	{
		if (empty($$key)) exit($ret);
	}

	$edge = intval($q);
	$corner = intval($z);
	$p = escapeshellcmd($p);

	// ディレクトリ遡りパターン検出
	if (preg_match("/([\|\s]|\.\.\/)/",$p.$o.$s)) exit($ret);

	// コマンドと元ファイルの存在確認
	if (!file_exists($p."convert") || !file_exists($o)) exit($ret);

	// 出力ファイルが存在する(CGIを直接叩かれてる?)
	if (file_exists($s)) exit($ret);

	// イメージファイルか？
	$size = @getimagesize($o);
	if (!$size) exit($ret); //画像ファイルではない

	// 元画像のサイズ
	$imw = $size[0];
	$imh = $size[1];
	$im_half = floor((min($imw, $imh)/2));

	// check value
	$edge = min($edge, $im_half);
	$corner = min($corner, $im_half);

	$tmpfile = $s . '_tmp.png';

	$out = array();
	$res = 1;
	$cmd = 'convert -size '.$imw.'x'.$imh.' xc:none -channel RGBA -fill white -draw "roundrectangle '.max(0,($edge-1)).','.max(1,($edge-1)).' '.($imw-$edge).','.($imh-$edge).' '.$corner.','.$corner.'" '.escapeshellarg($o).' -compose src_in -composite '.escapeshellarg($tmpfile);
	exec( $p . $cmd, $out, $res ) ;
	if ($res !== 0) $ret = "ERROR: 1";

	if ($res === 0 && $edge) {
		$out = array();
		$res = 1;
		$cmd = 'convert -size '.$imw.'x'.$imh.' xc:none -fill none -stroke white -strokewidth '.$edge.' -draw "roundrectangle '.($edge-1).','.($edge-1).' '.($imw-$edge).','.($imh-$edge).' '.$corner.','.$corner.'" -shade 135x25 -blur 0x1 -normalize '.escapeshellarg($tmpfile).' -compose overlay -composite '.escapeshellarg($tmpfile);
		exec( $p . $cmd, $out, $res ) ;
		if ($res !== 0) $ret = "ERROR: 1";
	}

	if (!$out) {
		copy ($tmpfile, $s);
		unlink($tmpfile);
		@chmod($s, 0606);
		$ret = "ERROR: 0";
	}

	// 完了
	exit($ret);
}

// 回転
else if ($m == 'rj' || $m == 'ri' || $m == 're')
{
	// 必要なパラメーターがあるかどうか
	$needs = array("p","z","q","s");
	foreach($needs as $key)
	{
		if (empty($$key)) exit($ret);
	}

	$q = intval($q);
	$z = intval($z);
	$p = escapeshellcmd($p);
	

	// 変数チェック

	// ディレクトリ遡りパターン検出
	if (preg_match("/([\|\s]|\.\.\/)/",$p.$s)) exit($ret);

	// イメージファイルか？
	$size = @getimagesize($s);
	if (!$size) exit($ret); //画像ファイルではない

	// 回転の範囲
	if ($z < 0 || $z > 270) exit($ret);

	// quality の範囲
	if ($q < 1 || $q > 100) exit($ret);

	if ($m == "re")
	{
		// 元ファイルの存在確認
		if (!file_exists($s)) exit($ret);

		switch ($z) {
			case '0':
				$z = '-a';
			case '90':
				$z = '-9';
				break;
			case '180':
				$z = '-1';
				break;
			case '270':
				$z = '-2';
				break;
			default:
				$z = '';
		}
		if (!$z) exit($ret);

		$out = array();
		$res = 1;
		$qs = escapeshellarg($s);
		exec("exiftran {$z} -i \"{$qs}\"", $out, $res);
		if ( $res !== 0 )
		{
			$ret = "ERROR: 1";
		}
		else
		{
			$ret = "ERROR: 0";
			chmod($s, 0606);
		}
	}
	else if ($m == "rj")
	{
		if ($z < 90) exit($ret);
		
		// 元ファイルの存在確認
		if (!file_exists($s)) exit($ret);

		$out = array();
		$res= 1;
		$qs = escapeshellarg($s);
		exec("{$p}jpegtran -rotate {$z} -copy all -outfile \"{$qs}\" \"{$qs}\", $out, $res);
		if ( $res !== 0 )
		{
			$ret = "ERROR: 1";
		}
		else
		{
			$ret = "ERROR: 0";
			chmod($s, 0606);
		}
	}
	else
	{
		// 元ファイルの存在確認
		if (!file_exists($s)) exit($ret);
		
		if (!$z) {
			$z = '-auto-orient';
		} else {
			$z = '-rotate +'.$z;
		}
		
		$out = array();
		$res= 1;
		$qs = escapeshellarg($s);
		// 実行
		exec( "{$p}convert {$z} -quality {$q} {$qs} {$qs}", $out, $res) ;
		if ( $res !== 0 )
		{
			$ret = "ERROR: 1";
		}
		else
		{
			$ret = "ERROR: 0";
			@chmod($s, 0606);
		}
	}

	// 完了
	exit($ret);
}


exit($ret);
