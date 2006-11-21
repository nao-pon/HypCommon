#!/usr/bin/env php
<?
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
$allows = array("m","p","z","q","o","s");



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
if ($m == "r")
{
	// 必要なパラメーターがあるかどうか
	$needs = array("p","z","q","o","s");
	foreach($needs as $key)
	{
		if (empty($$key)) exit($ret);
	}
	
	$q = intval($q);
	$z = intval($z);
	$p = escapeshellcmd($p);
	
	// 変数チェック
	
	// ディレクトリ遡りパターン検出
	if (preg_match("/([\|\s]|\.\.\/)/",$p.$o.$s)) exit($ret);
	
	// コマンドと元ファイルの存在確認
	if (!file_exists($p."convert") || !file_exists($o)) exit($ret);
	
	// イメージファイルか？
	$size = @getimagesize($o);
	if (!$size) exit($ret); //画像ファイルではない
	
	// ズームの範囲
	if ($z < 1 || $z > 100) exit($ret);
	
	// quality の範囲
	if ($q < 1 || $q > 100) exit($ret);
	
	// 元画像のサイズ
	$w = $size[0];
	$h = $size[1];
	
	// 実行
	$out = array();
	exec( "{$p}convert -size {$w}x{$h} -geometry {$z}%  -quality {$q} +profile \"*\" {$o} {$s}" , $out) ;
	
	if ($out)
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

// 回転
else if ($m == "rj" || $m == "ri")
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
	if ($z < 90 || $z > 270) exit($ret);
	
	// quality の範囲
	if ($q < 1 || $q > 100) exit($ret);
	
	// 元画像のサイズ
	$w = $size[0];
	$h = $size[1];
	
	if ($m == "rj")
	{
		// コマンドと元ファイルの存在確認
		if (!file_exists($p."jpegtran") || !file_exists($s)) exit($ret);
		
		$tmpfname = @tempnam(dirname($s), "tmp_");
		exec( "{$p}jpegtran -rotate {$z} -copy all {$s} > {$tmpfname}" );
		if ( ! @filesize($tmpfname) || ! @unlink($s) )
		{
			$ret = "ERROR: 1";
		}
		else
		{
			$ret = "ERROR: 0";
			copy($tmpfname, $s);
			chmod($s, 0606);
		}
		unlink($tmpfname);
	}
	else
	{
		// コマンドと元ファイルの存在確認
		if (!file_exists($p."convert") || !file_exists($s)) exit($ret);
		
		$out = array();
		// 実行
		exec( "{$p}convert -size {$w}x{$h} -rotate +{$z} -quality {$q} {$s} {$s}", $out) ;
		
		if ($out)
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

?>