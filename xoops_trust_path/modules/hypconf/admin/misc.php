<?php

$config[] = array(
	'name' => 'misc_head_last_tag',
	'title' => $constpref.'_MISC_HEAD_LAST_TAG',
	'description' => $constpref.'_MISC_HEAD_LAST_TAG_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'text',
	'size' => 80
);
if (defined('XOOPS_CUBE_LEGACY')) {
$config[] = array(
	'name' => 'xoopstpl_plugins_dir',
	'title' => $constpref.'_XOOPSTPL_PLUGINS_DIR',
	'description' => $constpref.'_XOOPSTPL_PLUGINS_DIR_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'text',
	'size' => 80
);
}
