<?php
/*
 * Created on 2011/11/09 by nao-pon http://xoops.hypweb.net/
 * $Id: k_tai_conf.php,v 1.4 2011/12/13 08:12:18 nao-pon Exp $
 */

$_k_tai_conf_template_dirs = hypconfGetDirnameAsOptions(XOOPS_TRUST_PATH . '/class/hyp_common/ktairender/templates');

$config['main_switch'] = 'use_k_tai_render';

$config[] = array(
	'name' => 'ua_regex',
	'title' => $constpref.'_UA_REGEX',
	'description' => $constpref.'_UA_REGEX_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'text',
	'size' => 80
	);
$config[] = array(
	'name' => 'themeSet',
	'title' => $constpref.'_THEMESET',
	'description' => $constpref.'_THEMESET_DESC',
	'formtype' => 'theme',
	'valuetype' => 'text',
	'notempty' => true
);
$config[] = array(
	'name' => 'templateSet',
	'title' => $constpref.'_TEMPLATESET',
	'description' => $constpref.'_TEMPLATESET_DESC',
	'formtype' => 'tplset',
	'valuetype' => 'text',
	'notempty' => true
);
$config[] = array(
	'name' => 'template',
	'title' => $constpref.'_TEMPLATE',
	'description' => $constpref.'_TEMPLATE_DESC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => $_k_tai_conf_template_dirs
);
$config[] = array(
	'name' => 'jquery_profiles',
	'title' => $constpref.'_JQM_PROFILES',
	'description' => $constpref.'_JQM_PROFILES_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text'
	);
$config[] = array(
	'name' => 'themeSets',
	'arrkey' => 'jqm',
	'title' => $constpref.'_THEMESETS_JQM',
	'description' => $constpref.'_THEMESETS_JQM_DESC',
	'formtype' => 'theme',
	'valuetype' => 'text',
	'notempty' => false
);
$config[] = array(
	'name' => 'templateSets',
	'arrkey' => 'jqm',
	'title' => $constpref.'_TEMPLATESETS_JQM',
	'description' => $constpref.'_TEMPLATESETS_JQM_DESC',
	'formtype' => 'tplset',
	'valuetype' => 'text',
	'notempty' => true
);
$config[] = array(
	'name' => 'templates',
	'arrkey' => 'jqm',
	'title' => $constpref.'_TEMPLATE_JQM',
	'description' => $constpref.'_TEMPLATE_JQM_DESC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => $_k_tai_conf_template_dirs
);
$config[] = array(
	'name' => 'jquery_theme',
	'title' => $constpref.'_JQM_THEME',
	'description' => $constpref.'_JQM_THEME_DESC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','x','y','z')
	);
$config[] = array(
	'name' => 'jquery_theme_content',
	'title' => $constpref.'_JQM_THEME_CONTENT',
	'description' => $constpref.'_JQM_THEME_CONTENT_DESC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','x','y','z')
	);
$config[] = array(
	'name' => 'jquery_theme_block',
	'title' => $constpref.'_JQM_THEME_BLOCK',
	'description' => $constpref.'_JQM_THEME_BLOCK_DESC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','x','y','z')
	);
$config[] = array(
	'name' => 'jqm_css',
	'title' => $constpref.'_JQM_CSS',
	'description' => $constpref.'_JQM_CSS_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'file:jqm-custom.css',
	'size' => 80
	);
$config[] = array(
	'name' => 'jquery_remove_flash',
	'title' => $constpref.'_JQM_REMOVE_FLASH',
	'description' => $constpref.'_JQM_REMOVE_FLASH_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text'
	);
$config[] = array(
	'name' => 'jquery_resolve_table',
	'title' => $constpref.'_JQM_RESOLVE_TABLE',
	'description' => $constpref.'_JQM_RESOLVE_TABLE_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int'
	);
$config[] = array(
	'name' => 'jquery_image_convert',
	'title' => $constpref.'_JQM_IMAGE_CONVERT',
	'description' => $constpref.'_JQM_IMAGE_CONVERT_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'size' => 4
	);
$config[] = array(
	'name' => 'disabledBlockIds',
	'title' => $constpref.'_DISABLEDBLOCKIDS',
	'description' => $constpref.'_DISABLEDBLOCKIDS_DESC',
	'formtype' => 'check',
	'valuetype' => 'array',
	'options' => 'blocks',
	);
$config[] = array(
	'name' => 'limitedBlockIds',
	'title' => $constpref.'_LIMITEDBLOCKIDS',
	'description' => $constpref.'_LIMITEDBLOCKIDS_DESC',
	'formtype' => 'check',
	'valuetype' => 'array',
	'options' => 'blocks'
	);
$config[] = array(
	'name' => 'showBlockIds',
	'title' => $constpref.'_SHOWBLOCKIDS',
	'description' => $constpref.'_SHOWBLOCKIDS_DESC',
	'formtype' => 'check',
	'valuetype' => 'array',
	'options' => 'blocks'
	);
$config[] = array(
	'name' => 'useJqmBlockCtl',
	'title' => $constpref.'_USEJQMBLOCKCTL',
	'description' => $constpref.'_USEJQMBLOCKCTL_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int'
);
$config[] = array(
	'name' => 'disabledBlockIds_jqm',
	'title' => $constpref.'_DISABLEDBLOCKIDS_JQM',
	'description' => $constpref.'_DISABLEDBLOCKIDS_JQM_DESC',
	'formtype' => 'check',
	'valuetype' => 'array',
	'options' => 'blocks',
	);
$config[] = array(
	'name' => 'limitedBlockIds_jqm',
	'title' => $constpref.'_LIMITEDBLOCKIDS_JQM',
	'description' => $constpref.'_LIMITEDBLOCKIDS_JQM_DESC',
	'formtype' => 'check',
	'valuetype' => 'array',
	'options' => 'blocks'
	);
$config[] = array(
	'name' => 'showBlockIds_jqm',
	'title' => $constpref.'_SHOWBLOCKIDS_JQM',
	'description' => $constpref.'_SHOWBLOCKIDS_JQM_DESC',
	'formtype' => 'check',
	'valuetype' => 'array',
	'options' => 'blocks'
	);
