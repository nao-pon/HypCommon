<?php
/*
 * Created on 2011/12/12 by nao-pon http://hypweb.net/
 * $Id: spam_block.php,v 1.1 2011/12/13 08:12:18 nao-pon Exp $
 */

$config['main_switch'] = 'use_post_spam_filter';

$config[] = array(
	'name' => 'use_mail_notify',
	'title' => $constpref.'_USE_MAIL_NOTIFY',
	'description' => $constpref.'_USE_MAIL_NOTIFY_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'send_mail_interval',
	'title' => $constpref.'_SEND_MAIL_INTERVAL',
	'description' => $constpref.'_SEND_MAIL_INTERVAL_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_a',
	'title' => $constpref.'_POST_SPAM_A',
	'description' => $constpref.'_POST_SPAM_A_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_bb',
	'title' => $constpref.'_POST_SPAM_BB',
	'description' => $constpref.'_POST_SPAM_BB_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_url',
	'title' => $constpref.'_POST_SPAM_URL',
	'description' => $constpref.'_POST_SPAM_URL_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_unhost',
	'title' => $constpref.'_POST_SPAM_UNHOST',
	'description' => $constpref.'_POST_SPAM_UNHOST_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_host',
	'title' => $constpref.'_POST_SPAM_HOST',
	'description' => $constpref.'_POST_SPAM_HOST_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_word',
	'title' => $constpref.'_POST_SPAM_WORD',
	'description' => $constpref.'_POST_SPAM_WORD_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_filed',
	'title' => $constpref.'_POST_SPAM_FILED',
	'description' => $constpref.'_POST_SPAM_FILED_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_trap',
	'title' => $constpref.'_POST_SPAM_TRAP',
	'description' => $constpref.'_POST_SPAM_TRAP_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'size' => 8
	);
$config[] = array(
	'name' => 'post_spam_user',
	'title' => $constpref.'_POST_SPAM_USER',
	'description' => $constpref.'_POST_SPAM_USER_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_guest',
	'title' => $constpref.'_POST_SPAM_GUEST',
	'description' => $constpref.'_POST_SPAM_GUEST_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_badip',
	'title' => $constpref.'_POST_SPAM_BADIP',
	'description' => $constpref.'_POST_SPAM_BADIP_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'post_spam_badip_ttl',
	'title' => $constpref.'_POST_SPAM_BADIP_TTL',
	'description' => $constpref.'_POST_SPAM_BADIP_TTL_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 10
	);
$config[] = array(
	'name' => 'post_spam_badip_forever',
	'title' => $constpref.'_POST_SPAM_BADIP_FOREVER',
	'description' => $constpref.'_POST_SPAM_BADIP_FOREVER_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 10
	);
$config[] = array(
	'name' => 'post_spam_badip_ttl0',
	'title' => $constpref.'_POST_SPAM_BADIP_TTL0',
	'description' => $constpref.'_POST_SPAM_BADIP_TTL0_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 10
	);
$config[] = array(
	'name' => 'post_spam_site_auto_regist',
	'title' => $constpref.'_POST_SPAM_SITE_AUTO_REGIST',
	'description' => $constpref.'_POST_SPAM_SITE_AUTO_REGIST_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1,
	);
$config[] = array(
	'name' => 'post_spam_safe_url',
	'title' => $constpref.'_POST_SPAM_SAFE_URL',
	'description' => $constpref.'_POST_SPAM_SAFE_URL_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'size' => 50
	);
$config[] = array(
	'name' => 'post_spam_sites_conf_file',
	'title' => $constpref.'_POST_SPAM_SITES',
	'description' => $constpref.'_POST_SPAM_SITES_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'file:/class/hyp_common/config/spamsites.conf.dat',
	'size' => 80
	);

$_spamsites_dat_file = is_file(XOOPS_TRUST_PATH.'/uploads/hyp_common/spamsites.dat')? XOOPS_TRUST_PATH.'/uploads/hyp_common/spamsites.dat' : XOOPS_TRUST_PATH.'/class/hyp_common/dat/spamsites.dat';
$config['underContents'] = '<hr />'
		. sprintf(hypconf_constant($constpref.'_POST_SPAM_SITES_SYSTEM'), $_spamsites_dat_file)
		. '<p>Updated: '.date('r', filemtime($_spamsites_dat_file)).'</p>'
		. '<form><textarea style="width:98%;height:15em;" readonly="readonly">'
		. htmlspecialchars(file_get_contents($_spamsites_dat_file))
		. '</textarea></form>';