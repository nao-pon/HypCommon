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

// �����
$ret = "ERROR: 9";

if (empty($_SERVER['QUERY_STRING'])) exit($ret);

// ͭ���ʥѥ�᡼����
$allows = array("m","p","z","q","o","s");



// CLI�Ǥξ�� $_GET �Ǥϼ����Ǥ��ʤ��Τ��ȼ�����
foreach(explode('&',$_SERVER['QUERY_STRING']) as $prm)
{
	list($key,$val) = array_pad(explode('=',$prm),2,'');
	
	$key = str_replace("\0","",$key);
	
	// ɬ�פʤ��ѥ�᡼���ϼΤƤ�
	if (!in_array($key,$allows)) continue;
	$val = rawurldecode(str_replace("\0","",$val));

	$$key = $val;
}

// �ꥵ����
if ($m == "r")
{
	// ɬ�פʥѥ�᡼���������뤫�ɤ���
	$needs = array("p","z","q","o","s");
	foreach($needs as $key)
	{
		if (empty($$key)) exit($ret);
	}
	
	$q = intval($q);
	$z = intval($z);
	$p = escapeshellcmd($p);
	
	// �ѿ������å�
	
	// �ǥ��쥯�ȥ��̤�ѥ����󸡽�
	if (preg_match("/([\|\s]|\.\.\/)/",$p.$o.$s)) exit($ret);
	
	// ���ޥ�ɤȸ��ե������¸�߳�ǧ
	if (!file_exists($p."convert") || !file_exists($o)) exit($ret);
	
	// ���᡼���ե����뤫��
	$size = @getimagesize($o);
	if (!$size) exit($ret); //�����ե�����ǤϤʤ�
	
	// ��������ϰ�
	if ($z < 1 || $z > 100) exit($ret);
	
	// quality ���ϰ�
	if ($q < 1 || $q > 100) exit($ret);
	
	// �������Υ�����
	$w = $size[0];
	$h = $size[1];
	
	// �¹�
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
	
	// ��λ
	exit($ret);
}

// ��ž
else if ($m == "rj" || $m == "ri")
{
	// ɬ�פʥѥ�᡼���������뤫�ɤ���
	$needs = array("p","z","q","s");
	foreach($needs as $key)
	{
		if (empty($$key)) exit($ret);
	}
	
	$q = intval($q);
	$z = intval($z);
	$p = escapeshellcmd($p);
	
	// �ѿ������å�
	
	// �ǥ��쥯�ȥ��̤�ѥ����󸡽�
	if (preg_match("/([\|\s]|\.\.\/)/",$p.$s)) exit($ret);
	
	// ���᡼���ե����뤫��
	$size = @getimagesize($s);
	if (!$size) exit($ret); //�����ե�����ǤϤʤ�
	
	// ��ž���ϰ�
	if ($z < 90 || $z > 270) exit($ret);
	
	// quality ���ϰ�
	if ($q < 1 || $q > 100) exit($ret);
	
	// �������Υ�����
	$w = $size[0];
	$h = $size[1];
	
	if ($m == "rj")
	{
		// ���ޥ�ɤȸ��ե������¸�߳�ǧ
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
		// ���ޥ�ɤȸ��ե������¸�߳�ǧ
		if (!file_exists($p."convert") || !file_exists($s)) exit($ret);
		
		$out = array();
		// �¹�
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
	
	// ��λ
	exit($ret);
}


exit($ret);

?>