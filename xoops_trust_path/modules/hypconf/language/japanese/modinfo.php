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
define($constpref.'_ADMENU_SPAM_BLOCK', '���ѥ��ɻ�����');

// notice error
define($constpref.'_MAIN_SWITCH_NOT_ENABLE', '�ᥤ�󥹥��å��ǡ�<b>$1</b>�פ�̵���ˤʤäƤ��ޤ��������Ǥ������ǽ�����뤿��ˤϡ��ᥤ�󥹥��å��ǡ�<b>$1</b>�פ�ͭ���ˤ��Ƥ���������');

// main_switch
define($constpref.'_USE_SET_QUERY_WORDS', '������ɤ�����˥��å�');
define($constpref.'_USE_SET_QUERY_WORDS_DESC', '');
define($constpref.'_USE_WORDS_HIGHLIGHT', '������ɤ�ϥ��饤��ɽ��');
define($constpref.'_USE_WORDS_HIGHLIGHT_DESC', '�ָ�����ɤ�����˥��åȡפ�ͭ���ξ��˵�ǽ���ޤ���<br />�ϥ��饤�Ȱ����� &lt;body&gt; ����ľ������������ޤ���Ǥ�դξ����������������ϡ��ơ������ &lt;!--HIGHLIGHT_SEARCH_WORD--&gt; �򵭽Ҥ���Ȥ�����ʬ����������ޤ���');
define($constpref.'_USE_PROXY_CHECK', '��ƻ��˥ץ��������å��򤹤�');
define($constpref.'_USE_PROXY_CHECK_DESC', '');
define($constpref.'_INPUT_FILTER_STRENGTH', 'GET, POST ����ʸ���ե��륿������');
define($constpref.'_INPUT_FILTER_STRENGTH_DESC', '');
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
// main_switch value
define($constpref.'_INPUT_FILTER_STRENGTH_0', '����ʸ������ NULL �ʳ��ϵ���');
define($constpref.'_INPUT_FILTER_STRENGTH_1', '����ʸ������ SoftBank�γ�ʸ����\t,\r,\n �ϵ���');
define($constpref.'_INPUT_FILTER_STRENGTH_2', '����ʸ������ \t,\r,\n �Τߵ���');

// k_tai_render
define($constpref.'_UA_REGEX', 'User agent');
define($constpref.'_UA_REGEX_DESC', '��Х����б���ǽ�ǽ������� User agent �� PCRE(Perl�ߴ�)����ɽ���ǵ��ҡ�');
define($constpref.'_JQM_PROFILES', 'jQuery Mobile');
define($constpref.'_JQM_PROFILES_DESC', 'jQuery Mobile ��Ŭ�Ѥ���ץ�ե�����̾�򥫥�޶��ڤ�ǵ��ҡ��ץ�ե�����̾�Ϸ����б������顼���������Ƥ��ơ�docomo, au, softbank, willcom, android, iphone, ipod, ipad, windows mobile �ʤɤ����ѤǤ��ޤ���');
define($constpref.'_JQM_THEME', 'jqm�ơ���');
define($constpref.'_JQM_THEME_DESC', '�ڡ������Τ� jQuery Mobile �Υơ��ޡ�ɸ��Ǥ� a, b, c, d, e ��ͭ���Ǥ���');
define($constpref.'_JQM_THEME_CONTENT', '�ᥤ����');
define($constpref.'_JQM_THEME_CONTENT_DESC', '�ᥤ�󥳥�ƥ�Ĥ�Ŭ�Ѥ��� jQuery Mobile �Υơ��ޡ�');
define($constpref.'_JQM_THEME_BLOCK', '�֥�å���');
define($constpref.'_JQM_THEME_BLOCK_DESC', '�֥�å���Ŭ�Ѥ��� jQuery Mobile �Υơ��ޡ�');
define($constpref.'_JQM_CSS', 'jqm �ɲ� CSS');
define($constpref.'_JQM_CSS_DESC', 'jQuery Mobile �Ѥ��ɲä� CSS �򵭽ҡ�<br />�ơ����� CSS �κ����� <a href="http://jquerymobile.com/themeroller/" target="_blank">ThemeRoller | jQuery Mobile</a> �� <a href="http://as001.productscape.com/themeroller.cfm" target="_blank">jQuery Mobile Themeroller</a> �ʤɤ����Ѥ���ȴ�ñ�Ǥ���');
define($constpref.'_JQM_REMOVE_FLASH' , 'Flash����(jqm)');
define($constpref.'_JQM_REMOVE_FLASH_DESC' , 'jQuery Mobile Ŭ�ѻ��� Flash ������ץ�ե�����̾�򥫥�޶��ڤ�ǵ��ҡ��ץ�ե�����̾�Ϸ����б������顼���������Ƥ��ơ�docomo, au, softbank, willcom, android, iphone, ipod, ipad, windows mobile �ʤɤ����ѤǤ��ޤ���');
define($constpref.'_JQM_RESOLVE_TABLE' , '����ҥơ��֥�Ÿ��(jqm)');
define($constpref.'_JQM_RESOLVE_TABLE_DESC' , 'jQuery Mobile Ŭ�ѻ�������ҤˤʤäƤ���ơ��֥��Ÿ�����롣');
define($constpref.'_JQM_IMAGE_CONVERT' , '���������[px](jqm)');
define($constpref.'_JQM_IMAGE_CONVERT_DESC' , 'jQuery Mobile Ŭ�ѻ��˲����������[px]�������ޤǽ̾����롣��0�פ�̵���ˤʤ�ޤ���');
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

// spam_block
define($constpref.'_USE_MAIL_NOTIFY', 'POST SPAM �᡼������ 0:�ʤ�, 1:SPAMȽ��Τ�, 2:���٤�');
define($constpref.'_USE_MAIL_NOTIFY_DESC', '');
define($constpref.'_SEND_MAIL_INTERVAL', '�ޤȤ�����Υ��󥿡��Х�(ʬ) (0 �ǿ������)');
define($constpref.'_SEND_MAIL_INTERVAL_DESC', '');
define($constpref.'_POST_SPAM_A', '&lt;a&gt; ���� 1�Ĥ�����Υݥ����');
define($constpref.'_POST_SPAM_A_DESC', '');
define($constpref.'_POST_SPAM_BB', 'BB��� 1�Ĥ�����Υݥ����');
define($constpref.'_POST_SPAM_BB_DESC', '');
define($constpref.'_POST_SPAM_URL', 'URL 1�Ĥ�����Υݥ����');
define($constpref.'_POST_SPAM_URL_DESC', '');
define($constpref.'_POST_SPAM_UNHOST', '���� HOST �βû��ݥ����');
define($constpref.'_POST_SPAM_UNHOST_DESC', '');
define($constpref.'_POST_SPAM_HOST', 'Spam HOST �βû��ݥ����');
define($constpref.'_POST_SPAM_HOST_DESC', '');
define($constpref.'_POST_SPAM_WORD', 'Spam Word �βû��ݥ����');
define($constpref.'_POST_SPAM_WORD_DESC', '');
define($constpref.'_POST_SPAM_FILED', 'Spam ���̵���ե���������ϻ��βû��ݥ����');
define($constpref.'_POST_SPAM_FILED_DESC', '');
define($constpref.'_POST_SPAM_TRAP', 'Spam ���̵���ե������̾');
define($constpref.'_POST_SPAM_TRAP_DESC', '');
define($constpref.'_POST_SPAM_USER', 'Spam Ƚ�������: ������桼����');
define($constpref.'_POST_SPAM_USER_DESC', '');
define($constpref.'_POST_SPAM_GUEST', 'Spam Ƚ�������: ������');
define($constpref.'_POST_SPAM_GUEST_DESC', '');
define($constpref.'_POST_SPAM_BADIP', '�����������ݥꥹ�Ȥ���Ͽ��������');
define($constpref.'_POST_SPAM_BADIP_DESC', '');
define($constpref.'_POST_SPAM_BADIP_TTL', '<b>ProtectorϢ��</b>: �����������ݤε��ݷ�³����[��] (0:̵����, null:Protector�Ի���)');
define($constpref.'_POST_SPAM_BADIP_TTL_DESC', '');
define($constpref.'_POST_SPAM_BADIP_FOREVER', '<b>ProtectorϢ��</b>: ̵���¥���������������');
define($constpref.'_POST_SPAM_BADIP_FOREVER_DESC', '');
define($constpref.'_POST_SPAM_BADIP_TTL0', '<b>ProtectorϢ��</b>: ̵���¥����������ݷ�³����[��] (0:������̵����)');
define($constpref.'_POST_SPAM_BADIP_TTL0_DESC', '');

}
