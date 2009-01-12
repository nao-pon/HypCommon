<?php
// $Id: header.php,v 1.1 2009/01/12 23:53:20 nao-pon Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
include_once XOOPS_ROOT_PATH.'/class/xoopsblock.php';
if ($xoopsConfig['theme_set'] != 'default' && file_exists(XOOPS_THEME_PATH.'/'.$xoopsConfig['theme_set'].'/theme.php')) {
    //
    // Fall back on simple protector of common.php by checking the constant that
    // is defined in common.php
    //
    if (!defined("XOOPS_CACHE_PATH")) {
        die();
    }

    // the old way..
    $xoopsOption['theme_use_smarty'] = 0;
    if (file_exists(XOOPS_THEME_PATH.'/'.$xoopsConfig['theme_set'].'/language/lang-'.$xoopsConfig['language'].'.php')) {
        include XOOPS_THEME_PATH.'/'.$xoopsConfig['theme_set'].'/language/lang-'.$xoopsConfig['language'].'.php';
    } elseif (file_exists(XOOPS_THEME_PATH.'/'.$xoopsConfig['theme_set'].'/language/lang-english.php')) {
        include XOOPS_THEME_PATH.'/'.$xoopsConfig['theme_set'].'/language/lang-english.php';
    }
    $config_handler =& xoops_gethandler('config');
    $xoopsConfigMetaFooter =& $config_handler->getConfigsByCat(XOOPS_CONF_METAFOOTER);
    xoops_header(false);
    include XOOPS_THEME_PATH.'/'.$xoopsConfig['theme_set'].'/theme.php';
    $xoopsOption['show_rblock'] = (!empty($xoopsOption['show_rblock'])) ? $xoopsOption['show_rblock'] : 0;
    // include Smarty template engine and initialize it
    require_once XOOPS_ROOT_PATH.'/class/template.php';
    $xoopsTpl = new XoopsTpl();
    if (checkDebugGroup() && in_array(3, $xoopsConfig['debug_mode'])) {
        $xoopsTpl->xoops_setDebugging(true);
    }
    if ($xoopsUser != '') {
        $pm_handler =& xoops_gethandler('privmessage');
        $pm_criterias = new CriteriaCompo(new Criteria('read_msg', 0));
        $pm_criterias->add(new Criteria('to_userid', $xoopsUser->getVar('uid')));
        $pm_criterias->add(new Criteria('send', 0));
        $pm_num = $pm_handler->getCount($pm_criterias);

        $xoopsTpl->assign(array('xoops_isuser' => true, 'xoops_userid' => $xoopsUser->getVar('uid'), 'xoops_uname' => $xoopsUser->getVar('uname'), 'xoops_isadmin' => $xoopsUserIsAdmin, 'xoops_pm_num' => $pm_num));
    }
    $xoopsTpl->assign('xoops_requesturi', htmlspecialchars($GLOBALS['xoopsRequestUri'], ENT_QUOTES));
    include XOOPS_ROOT_PATH.'/include/old_functions.php';

    if ($xoopsOption['show_cblock'] || (!empty($xoopsModule) && preg_match("/index\.php$/i", xoops_getenv('PHP_SELF')) && $xoopsConfig['startpage'] == $xoopsModule->getVar('dirname'))) {
        $xoopsOption['show_rblock'] = $xoopsOption['show_cblock'] = 1;
    }
    themeheader($xoopsOption['show_rblock']);
    if ($xoopsOption['show_cblock']) make_cblock();  //create center block
} else {
    $xoopsOption['theme_use_smarty'] = 1;
    // include Smarty template engine and initialize it
    require_once XOOPS_ROOT_PATH.'/class/template.php';
    $xoopsTpl = new XoopsTpl();
    $xoopsTpl->xoops_setCaching(2);
    if (checkDebugGroup() && in_array(3, $xoopsConfig['debug_mode'])) {
        $xoopsTpl->xoops_setDebugging(true);
    }
    $xoopsTpl->assign(array('xoops_theme' => $xoopsConfig['theme_set'], 'xoops_imageurl' => XOOPS_THEME_URL.'/'.$xoopsConfig['theme_set'].'/', 'xoops_themecss'=> xoops_getcss($xoopsConfig['theme_set']), 'xoops_requesturi' => htmlspecialchars($GLOBALS['xoopsRequestUri'], ENT_QUOTES), 'xoops_sitename' => htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES), 'xoops_slogan' => htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES), 'emaillogin' => $xoopsConfig['emaillogin'], 'autologin' => $xoopsConfig['autologin']));
    // Meta tags
    $config_handler =& xoops_gethandler('config');
    $criteria = new CriteriaCompo(new Criteria('conf_modid', 0));
    $criteria->add(new Criteria('conf_catid', XOOPS_CONF_METAFOOTER));
    $config =& $config_handler->getConfigs($criteria, true);
    foreach (array_keys($config) as $i) {
        // prefix each tag with 'xoops_'
        $xoopsTpl->assign('xoops_'.$config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
    }
    //unset($config);
    // show banner?
    if ($xoopsConfig['banners'] == 1) {
        $xoopsTpl->assign('xoops_banner', xoops_getbanner());
    } else {
        $xoopsTpl->assign('xoops_banner', '&nbsp;');
    }
    // Weird, but need extra <script> tags for 2.0.x themes
    $xoopsTpl->assign('xoops_js', '//--></script><script type="text/javascript" src="'.XOOPS_URL.'/include/xoops.js"></script><script type="text/javascript"><!--');
    // get all blocks and assign to smarty
    $xoopsblock = new XoopsBlock();
    $block_arr = array();
    if (is_object($xoopsUser)) {
        $pm_handler =& xoops_gethandler('privmessage');
        $pm_criterias = new CriteriaCompo(new Criteria('read_msg', 0));
        $pm_criterias->add(new Criteria('to_userid', $xoopsUser->getVar('uid')));
        $pm_criterias->add(new Criteria('send', 0));
        $pm_num = $pm_handler->getCount($pm_criterias);

        $xoopsTpl->assign(array('xoops_isuser' => true, 'xoops_userid' => $xoopsUser->getVar('uid'), 'xoops_uname' => $xoopsUser->getVar('uname'), 'xoops_isadmin' => $xoopsUserIsAdmin, 'xoops_pm_num' => $pm_num));
        if (!empty($xoopsModule)) {
            // set page title
            $xoopsTpl->assign(array('xoops_pagetitle' => $xoopsModule->getVar('name'), 'xoops_modulename' => $xoopsModule->getVar('name'), 'xoops_dirname' => $xoopsModule->getVar('dirname')));
            if (preg_match("/index\.php$/i", xoops_getenv('PHP_SELF')) && $xoopsConfig['startpage'] == $xoopsModule->getVar('dirname')) {
                $block_arr =& $xoopsblock->getAllByGroupModule($xoopsUser->getGroups(), $xoopsModule->getVar('mid'), true, XOOPS_BLOCK_VISIBLE);
            } else {
                $block_arr =& $xoopsblock->getAllByGroupModule($xoopsUser->getGroups(), $xoopsModule->getVar('mid'), false, XOOPS_BLOCK_VISIBLE);
            }
        } else {
            $xoopsTpl->assign('xoops_pagetitle', htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES));
            if (!empty($xoopsOption['show_cblock'])) {
                $block_arr =& $xoopsblock->getAllByGroupModule($xoopsUser->getGroups(), 0, true, XOOPS_BLOCK_VISIBLE);
            } else {
                $block_arr =& $xoopsblock->getAllByGroupModule($xoopsUser->getGroups(), 0, false, XOOPS_BLOCK_VISIBLE);
            }
        }
    } else {
        $xoopsTpl->assign(array('xoops_isuser' => false, 'xoops_isadmin' => false));
        if (!empty($xoopsModule)) {
            // set page title
            $xoopsTpl->assign(array('xoops_pagetitle' => $xoopsModule->getVar('name'), 'xoops_modulename' => $xoopsModule->getVar('name'), 'xoops_dirname' => $xoopsModule->getVar('dirname')));
            if (preg_match("/index\.php$/i", xoops_getenv('PHP_SELF')) && $xoopsConfig['startpage'] == $xoopsModule->getVar('dirname')) {
                $block_arr =& $xoopsblock->getAllByGroupModule(XOOPS_GROUP_ANONYMOUS, $xoopsModule->getVar('mid'), true, XOOPS_BLOCK_VISIBLE);
            } else {
                $block_arr =& $xoopsblock->getAllByGroupModule(XOOPS_GROUP_ANONYMOUS, $xoopsModule->getVar('mid'), false, XOOPS_BLOCK_VISIBLE);
            }
        } else {
            $xoopsTpl->assign('xoops_pagetitle', htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES));
            if (!empty($xoopsOption['show_cblock'])) {
                $block_arr =& $xoopsblock->getAllByGroupModule(XOOPS_GROUP_ANONYMOUS, 0, true, XOOPS_BLOCK_VISIBLE);
            } else {
                $block_arr =& $xoopsblock->getAllByGroupModule(XOOPS_GROUP_ANONYMOUS, 0, false, XOOPS_BLOCK_VISIBLE);
            }
        }
    }
    foreach (array_keys($block_arr) as $i) {
        $bcachetime = $block_arr[$i]->getVar('bcachetime');
        if (empty($bcachetime)) {
            $xoopsTpl->xoops_setCaching(0);
        } else {
            $xoopsTpl->xoops_setCaching(2);
            $xoopsTpl->xoops_setCacheTime($bcachetime);
        }
        $btpl = $block_arr[$i]->getVar('template');
        if ($btpl != '') {
            if (empty($bcachetime) || !$xoopsTpl->is_cached('db:'.$btpl, 'blk_'.$block_arr[$i]->getVar('bid'))) {
                $xoopsLogger->addBlock($block_arr[$i]->getVar('name'));
                $bresult =& $block_arr[$i]->buildBlock();
                if (!$bresult) {
                    continue;
                }
                $xoopsTpl->assign_by_ref('block', $bresult);
                $bcontent = $xoopsTpl->fetch('db:'.$btpl, 'blk_'.$block_arr[$i]->getVar('bid'));
                $xoopsTpl->clear_assign('block');
            } else {
                $xoopsLogger->addBlock($block_arr[$i]->getVar('name'), true, $bcachetime);
                $bcontent = $xoopsTpl->fetch('db:'.$btpl, 'blk_'.$block_arr[$i]->getVar('bid'));
            }
        } else {
            $bid = $block_arr[$i]->getVar('bid');
            if (empty($bcachetime) || !$xoopsTpl->is_cached('db:system_dummy.html', 'blk_'.$bid)) {
                $xoopsLogger->addBlock($block_arr[$i]->getVar('name'));
                $bresult =& $block_arr[$i]->buildBlock();
                if (!$bresult) {
                    continue;
                }
                $xoopsTpl->assign_by_ref('dummy_content', $bresult['content']);
                $bcontent = $xoopsTpl->fetch('db:system_dummy.html', 'blk_'.$bid);
                $xoopsTpl->clear_assign('block');
            } else {
                $xoopsLogger->addBlock($block_arr[$i]->getVar('name'), true, $bcachetime);
                $bcontent = $xoopsTpl->fetch('db:system_dummy.html', 'blk_'.$bid);
            }
        }
        
        if($xoopsConfigSearch['highlighting']) $bcontent = xoops_word_highlight($bcontent, XOOPS_QUERY_WORD, false);
        
        switch ($block_arr[$i]->getVar('side')) {
            case XOOPS_SIDEBLOCK_LEFT:
                if (!isset($show_lblock)) {
                    $xoopsTpl->assign('xoops_showlblock', 1);
                    $show_lblock = 1;
                }
                $xoopsTpl->append('xoops_lblocks', array('title' => $block_arr[$i]->getVar('title'), 'content' => $bcontent, 'weight' => $block_arr[$i]->getVar('weight')));
                break;
            case XOOPS_CENTERBLOCK_LEFT:
                if (!isset($show_cblock)) {
                    $xoopsTpl->assign('xoops_showcblock', 1);
                    $show_cblock = 1;
                }
                $xoopsTpl->append('xoops_clblocks', array('title' => $block_arr[$i]->getVar('title'), 'content' => $bcontent, 'weight' => $block_arr[$i]->getVar('weight')));
                break;
            case XOOPS_CENTERBLOCK_RIGHT:
                if (!isset($show_cblock)) {
                    $xoopsTpl->assign('xoops_showcblock', 1);
                    $show_cblock = 1;
                }
                $xoopsTpl->append('xoops_crblocks', array('title' => $block_arr[$i]->getVar('title'), 'content' => $bcontent, 'weight' => $block_arr[$i]->getVar('weight')));
                break;
            case XOOPS_CENTERBLOCK_CENTER:
                if (!isset($show_cblock)) {
                    $xoopsTpl->assign('xoops_showcblock', 1);
                    $show_cblock = 1;
                }
                $xoopsTpl->append('xoops_ccblocks', array('title' => $block_arr[$i]->getVar('title'), 'content' => $bcontent, 'weight' => $block_arr[$i]->getVar('weight')));
                break;
            case XOOPS_SIDEBLOCK_RIGHT:
                if (!isset($show_rblock)) {
                    $xoopsTpl->assign('xoops_showrblock', 1);
                    $show_rblock = 1;
                }
                $xoopsTpl->append('xoops_rblocks', array('title' => $block_arr[$i]->getVar('title'), 'content' => $bcontent, 'weight' => $block_arr[$i]->getVar('weight')));
                break;
        }
        unset($bcontent);
    }
    //unset($block_arr);
    if (!isset($show_lblock)) {
        $xoopsTpl->assign('xoops_showlblock', 0);
    }
    if (!isset($show_rblock)) {
        $xoopsTpl->assign('xoops_showrblock', 0);
    }
    if (!isset($show_cblock)) {
        $xoopsTpl->assign('xoops_showcblock', 0);
    }
    if (xoops_getenv('REQUEST_METHOD') != 'POST' && !empty($xoopsModule) && !empty($xoopsConfig['module_cache'][$xoopsModule->getVar('mid')])) {
        $xoopsTpl->xoops_setCaching(2);
        $xoopsTpl->xoops_setCacheTime($xoopsConfig['module_cache'][$xoopsModule->getVar('mid')]);
        if (!isset($xoopsOption['template_main'])) {
            $xoopsCachedTemplate = 'db:system_dummy.html';
        } else {
            $xoopsCachedTemplate = 'db:'.$xoopsOption['template_main'];
        }
        // generate safe cache Id
        $xoopsCachedTemplateId = 'mod_'.$xoopsModule->getVar('dirname').'|'.md5(str_replace(XOOPS_URL, '', $GLOBALS['xoopsRequestUri']));
        if ($xoopsTpl->is_cached($xoopsCachedTemplate, $xoopsCachedTemplateId)) {
            $xoopsLogger->addExtra($xoopsCachedTemplate, $xoopsConfig['module_cache'][$xoopsModule->getVar('mid')]);
            $xoopsTpl->assign('xoops_contents', $xoopsTpl->fetch($xoopsCachedTemplate, $xoopsCachedTemplateId));
            $xoopsTpl->xoops_setCaching(0);
            if (!headers_sent()) {
                header ('Content-Type:text/html; charset='._CHARSET);
            }
            $xoopsTpl->display($xoopsConfig['theme_set'].'/theme.html');

            if(checkDebugGroup()){
                if (in_array(2, $xoopsConfig['debug_mode'])) {
                    echo $xoopsLogger->dumpAll();
                }
                if (in_array(4, $xoopsConfig['debug_mode'])) {
                    echo dumpGPCFS();
                }
            }
            exit();
        }
    } else {
        $xoopsTpl->xoops_setCaching(0);
    }
    if (!isset($xoopsOption['template_main'])) {
        // new themes using Smarty does not have old functions that are required in old modules, so include them now
        include XOOPS_ROOT_PATH.'/include/old_theme_functions.php';
        // need this also
        $xoopsTheme['thename'] = $xoopsConfig['theme_set'];
        ob_start();
    }
}
?>
