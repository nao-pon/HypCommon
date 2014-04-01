<?php
error_reporting(0);
while(ob_end_clean()){}
if (is_object($xoopsUser) && $xoopsUser->getVar('uid')) {
	header('Content-Length: 0');
} else {
	$langmanpath = XOOPS_TRUST_PATH.'/libs/altsys/class/D3LanguageManager.class.php' ;
	if( file_exists( $langmanpath ) ) {
		require_once( $langmanpath ) ;
		$langman =& D3LanguageManager::getInstance() ;
		$langman->read( 'modinfo.php' , 'hypconf' , 'hypconf' ) ;
		$err = _MI_HYPCONF_ERR_KEEP_ALIVE;
		if (defined('_CHARSET') && _CHARSET !== 'UTF-8') {
			$err = mb_convert_encoding($err, 'UTF-8', _CHARSET);
		}
	} else {
		$err = 'Login is uncontinuable. Please log in again before transmitting data.';
	}
	header('Content-Length: ' . strlen($err));
	echo $err;
}
exit(0);
