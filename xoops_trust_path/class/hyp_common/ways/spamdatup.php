<?php
ignore_user_abort(true);

// clear output buffer
while( ob_get_level() ) {
	ob_end_clean() ;
}
header('Content-Type: text/javascript; charset=UTF-8');
header('Content-Length: 0');
header('Connection: close');
flush();

include_once $trustpath . '/class/hyp_common/hyp_common_func.php';
HypCommonFunc::spamdat_auto_update($trustpath);

exit('');