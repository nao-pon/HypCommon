<?php
if( defined( 'FOR_XOOPS_LANG_CHECKER' ) ) $mydirname = 'hypconf' ;
$constpref = '_MI_' . strtoupper( $mydirname ) ;

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( $constpref.'_LOADED' ) ) {

define($constpref.'_LOADED' , 1 ) ;

// The name of this module
define($constpref.'_NAME', 'HypCommon������');

// A brief description of this module
define($constpref.'_DESC', 'HypCommonFunc ��Ϣ������');

define($constpref.'_MSG_SAVED' , '�������¸���ޤ�����');
define($constpref.'_COUSTOM_BLOCK' , '��������֥�å�');

// admin menus
define($constpref.'_ADMENU_CONTENTSADMIN' , '����γ�ǧ');
define($constpref.'_ADMENU_MAIN_SWITCH' , '�ᥤ�� �����å�');
define($constpref.'_ADMENU_K_TAI_CONF' , '��Х����б�������');
define($constpref.'_ADMENU_MYBLOCKSADMIN' , '����������������');
define($constpref.'_ADMENU_XPWIKI_RENDER', 'xpWiki�����顼����');

// main_switch
define($constpref.'_USE_SET_QUERY_WORDS', '������ɤ�����˥��å�');
define($constpref.'_USE_SET_QUERY_WORDS_DESC', '');
define($constpref.'_USE_WORDS_HIGHLIGHT', '������ɤ�ϥ��饤��ɽ��');
define($constpref.'_USE_WORDS_HIGHLIGHT_DESC', '');
define($constpref.'_USE_PROXY_CHECK', '��ƻ��˥ץ��������å��򤹤�');
define($constpref.'_USE_PROXY_CHECK_DESC', '');
define($constpref.'_USE_DEPENDENCE_FILTER', '�����¸ʸ���ե��륿��');
define($constpref.'_USE_DEPENDENCE_FILTER_DESC', '');
define($constpref.'_USE_POST_SPAM_FILTER', 'POST SPAM �ե��륿��');
define($constpref.'_USE_POST_SPAM_FILTER_DESC', '');
define($constpref.'_POST_SPAM_TRAP_SET', '�ϥˡ��ݥå�(̵���ե�����ɤ�Bot�)��ư�ǻųݤ���');
define($constpref.'_POST_SPAM_TRAP_SET_DESC', '');
define($constpref.'_USE_K_TAI_RENDER', '��Х����б���ǽ��ͭ���ˤ���');
define($constpref.'_USE_K_TAI_RENDER_DESC', '');
define($constpref.'_USE_SMART_REDIRECT', '���ޡ��ȥ�����쥯�Ȥ�ͭ���ˤ���');
define($constpref.'_USE_SMART_REDIRECT_DESC', '');

// k_tai_render
define($constpref.'_UA_REGEX', 'User agent');
define($constpref.'_UA_REGEX_DESC', '��Х����б���ǽ�ǽ������� User agent �� PCRE(Perl�ߴ�)����ɽ���ǵ��ҡ�');
define($constpref.'_JQUERY_PROFILES', 'jQuery Mobile');
define($constpref.'_JQUERY_PROFILES_DESC', 'jQuery Mobile ��Ŭ�Ѥ���ץ�ե�����̾�򥫥�޶��ڤ�ǵ��ҡ��ץ�ե�����̾�Ϸ����б������顼���������Ƥ��ơ�docomo, au, softbank, willcom, android, iphone, ipod, ipad, windows mobile �ʤɤ����ѤǤ��ޤ���');
define($constpref.'_JQUERY_THEME', 'jqm�ơ���');
define($constpref.'_JQUERY_THEME_DESC', '�ڡ������Τ� jQuery Mobile �Υơ��ޡ�ɸ��Ǥ� a, b, c, d, e ��ͭ���Ǥ���');
define($constpref.'_JQUERY_THEME_CONTENT', '�ᥤ����');
define($constpref.'_JQUERY_THEME_CONTENT_DESC', '�ᥤ�󥳥�ƥ�Ĥ�Ŭ�Ѥ��� jQuery Mobile �Υơ��ޡ�');
define($constpref.'_JQUERY_THEME_BLOCK', '�֥�å���');
define($constpref.'_JQUERY_THEME_BLOCK_DESC', '�֥�å���Ŭ�Ѥ��� jQuery Mobile �Υơ��ޡ�');
define($constpref.'_DISABLEDBLOCKIDS', '̵���֥�å�');
define($constpref.'_DISABLEDBLOCKIDS_DESC', '��Х��륢�������������򤵤줿�֥�å���̵���ˤ��ޤ���');
define($constpref.'_LIMITEDBLOCKIDS', 'ͭ���֥�å�');
define($constpref.'_LIMITEDBLOCKIDS_DESC', '��Х��륢�������������򤵤줿�֥�å���ͭ���ˤ��ޤ�����ĤǤ����򤹤��������Υ֥�å��Ϥ��٤�̵���ˤʤ�ޤ���������ꤷ�ʤ��ȥե��륿��󥰤Ϥ���ޤ���');
define($constpref.'_SHOWBLOCKIDS', 'Ÿ���֥�å�');
define($constpref.'_SHOWBLOCKIDS_DESC', '��Х��륢���������˾��ɽ������֥�å���<br />jQuery Mobile ���ѻ����ޤꤿ����ɽ����������֤�Ÿ������ޤ���<br />����η���ɽ���Ǥ����򤷤��֥�å���ɽ�����졢������Υ֥�å��Ϥ��Υ֥�å���ɽ�����뤿��Υ�󥯤ˤʤ�ޤ���');

// xpwiki_render
define($constpref.'_XPWIKI_RENDER_NONE', '���Ѥ��ʤ�');
define($constpref.'_XPWIKI_RENDER_DIRNAME', 'xpWiki �����顼');
define($constpref.'_XPWIKI_RENDER_DIRNAME_DESC', '�����ȥ磻�� xpWiki �����顼��ǽ�ǻ��Ѥ��� xpWiki ����ꤷ�Ƥ���������<br />�����ȥ磻�ɤ� xpWiki �����顼��ǽ����Ѥ���ȡ��ۤȤ�ɤΥ⥸�塼��� xpWiki(PukiWiki)�ε�ˡ���Ȥ���褦�ˤʤ�ޤ���');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER', '�����ȥ磻�� Wiki �إ�ѡ�');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_DESC', '�֤Ϥ��פ����򤹤�ȥƥ����ȥ��ꥢ����ǽ��ĥ���� Wiki �إ�ѡ��ڤӥ�å����ǥ����򥵥��ȥ磻�ɤǻ��ѤǤ���褦�ˤʤ�ޤ���');
define($constpref.'_XPWIKI_RENDER_NOTUSE_WIKIHELPER_MODULES', 'Wiki �إ�ѡ�̵��');
define($constpref.'_XPWIKI_RENDER_NOTUSE_WIKIHELPER_MODULES_DESC', '�����ȥ磻�� Wiki �إ�ѡ���̵���ˤ���⥸�塼������򤷤Ʋ�������');
define($constpref.'_REQUERE_XCL', '��������� XOOPS Cube Legacy �����ƥ�ǤΤ����Ѳ�ǽ�Ǥ���');
define($constpref.'_XCL_REQUERE_2_2_1', '���ε�ǽ�ϡ�XOOPS Cube Legacy 2.2.1 �ʹߤ�ͭ���ˤʤ�ޤ������������ȼ��� "class/module.textsanitizer.php" ��񤭴����Ƥ��ε�ǽ��ͭ���ˤ��Ƥ�����ϡ����Υ�å�������̵�뤷�Ʋ�������');
define($constpref.'_TEXTFILTER_ALREADY_EXISTS', 'preload �ǥ��쥯�ȥ�� "SetupHyp_TextFilter.class.php" ������ޤ��������������ޤǤ����Ǥ������ȿ�Ǥ���ޤ���');

}
