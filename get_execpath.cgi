#!/usr/bin/env php
<?php
$dat = $path = "";

$dat .= "<?php\n";

$out = array();
exec( "whereis -b convert" , $out) ;
if ($out)
{
	$path = array_pad(explode(" ",$out[0]),2,"");
	$path = (preg_match("#^(/.+/)convert$#",$path[1],$match))? $match[1] : "";
	$dat .= "define('HYP_IMAGEMAGICK_PATH', '{$path}');\n";
}

$out = array();
exec( "whereis -b jpegtran" , $out) ;
if ($out)
{
	$path = array_pad(explode(" ",$out[0]),2,"");
	$path = (preg_match("#^(/.+/)jpegtran$#",$path[1],$match))? $match[1] : "";
	$dat .= "define('HYP_JPEGTRAN_PATH', '{$path}');\n";
}
$dat .= "?>\n";

$filename = "execpath.inc.php";

if ($fp = @fopen($filename,"wb"))
{
	fputs($fp, $dat);
	fclose($fp);
	chmod("get_execpath.cgi", 0600);
	chmod("image_magick.cgi", 0705);
	if (php_sapi_name() == "cli")
	{
		echo "Content-Type: text/plain\n\n";
	}
	else
	{
		header("Content-Type: text/plain");
	}
	echo "Made a file '{$filename}'. It's OK.";
}
else
{
	if (php_sapi_name() == "cli")
	{
		echo "Content-Disposition: attachment; filename=\"{$filename}\"\n";
		echo "Content-Length: ".strlen($dat)."\n";
		echo "Content-Type: text/plain\n\n";
	}
	else
	{
		@ini_set('default_charset','');
		@mb_http_output('pass');
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Length: ".strlen($dat));
		header("Content-Type: text/plain");
	}
	echo $dat;
}
exit();
?>