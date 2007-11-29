<?php

//// mbstring ////
if (! extension_loaded('mbstring')) {
	include_once dirname(dirname(__FILE__)) . '/mbemulator/mb-emulator.php';
}

include_once dirname(dirname(__FILE__)) . '/hyp_common_func.php';

// For not cube.
if (! class_exists('XCube_ActionFilter')) {
class XCube_ActionFilter
{
	var $mController;
	var $mRoot;
	function XCube_ActionFilter(&$controller) {}
	function preFilter() {}
	function preBlockFilter() {}
	function postFilter() {}
}
}

class HypCommonPreLoadBase extends XCube_ActionFilter {
	
	var $configEncoding;       // Config���󥳡��ǥ���

	var $encodehint_word;      // POST���󥳡��ǥ���Ƚ����ʸ��
	var $encodehint_name;      // POST���󥳡��ǥ���Ƚ���� Filed name
	
	var $use_set_query_words;  // ������ɤ�����˥��å�
	var $use_words_highlight;  // ������ɤ�ϥ��饤��ɽ��
	var $msg_words_highlight;  // �ϥ��饤�ȥ�����ɥ�å�����
	
	var $use_proxy_check;      // POST���ץ��������å�����
	var $no_proxy_check;       // ����IP
	var $msg_proxy_check; 
	
	var $use_dependence_filter;// �����¸ʸ���ե��륿��
	
	var $use_post_spam_filter; // POST SPAM �ե��륿��
	var $use_mail_notify;      // POST SPAM �᡼������
	var $post_spam_a;          // <a> ���� 1�Ĥ�����Υݥ����
	var $post_spam_bb;         // BB��� 1�Ĥ�����Υݥ����
	var $post_spam_url;        // URL      1�Ĥ�����Υݥ����
	var $post_spam_host;       // Spam HOST �βû��ݥ����
	var $post_spam_word;       // Spam Word �βû��ݥ����
	var $post_spam_filed;      // Spam ̵���ե�����ɤβû��ݥ����
	var $post_spam_trap;       // Spam ���̵���ե������̾
	var $post_spam_trap_set;   // ̵���ե�����ɤ�櫤�ư�ǻųݤ���
		
	var $post_spam_user;       // POST SPAM ����: ������桼����
	var $post_spam_guest;      // POST SPAM ����: ������
	var $post_spam_rules;      // ���󥹥ȥ饯���������

	// ����������̾
	var $q_word;               // �������
	var $q_word2;              // �������ʬ������
	var $se_name;              // ������̾
	var $kakasi_cache_dir;   
	
	// ���󥹥ȥ饯��
	function HypCommonPreLoadBase (& $controller) {
		parent::XCube_ActionFilter($controller);
	}
	
	function preFilter() {
		// <from> �ե��륿��
		if (! empty($this->encodehint_word) || ! empty($this->post_spam_trap_set)) {
			ob_start(array(&$this, 'formFilter'));
		}
	}
	
	function postFilter() {
		// XOOPS ��ɽ��ʸ�����󥳡��ǥ���
		$this->encode = strtoupper(_CHARSET);
		
		// ����ե�����Υ��󥳡��ǥ��󥰤򸡺�
		if ($this->encode !== strtoupper($this->configEncoding)) {
			$this->encodehint_word = '';
		}
		
		// Set Query Words
		if ($this->use_set_query_words) {
			HypCommonFunc::set_query_words($this->q_word, $this->q_word2, $this->se_name, $this->kakasi_cache_dir, $this->encode);
			if ($this->use_words_highlight) {
				ob_start(array(&$this, 'obFilter'));
			}
		}

		if (! empty($_POST)) {
			// POST ʸ�����ʸ�����󥳡��ɤ�Ƚ��
			$enchint = (isset($_POST[$this->encodehint_name]))? $_POST[$this->encodehint_name] : ((isset($_POST['encode_hint']))? $_POST['encode_hint'] : '');
			if ($enchint && function_exists('mb_detect_encoding')) {
				define ('HYP_POST_ENCODING', strtoupper(mb_detect_encoding($enchint)));
			} else if (isset($_POST['charset'])) {
				define ('HYP_POST_ENCODING', strtoupper($_POST['charset']));
			}

			// Proxy Check
			if (defined('HYP_POST_ENCODING') && $this->use_proxy_check) {
				HypCommonFunc::BBQ_Check($this->no_proxy_check, $this->msg_proxy_check);
			}
			
			// ʸ�����󥳡��ǥ��󥰳���ʸ������ͥ���ƥ��ƥ����Ѵ�
			//$_POST = HypCommonFunc::encode_numericentity($_POST, _CHARSET, HYP_POST_ENCODING);

			// �����¸ʸ���ե��륿��
			if ($this->encode === 'EUC-JP' && $this->use_dependence_filter) {
				$_POST = HypCommonFunc::dependence_filter($_POST);
			}
			
			// PostSpam ������å�
			if ($this->use_post_spam_filter) {
				// �û� pt
				if ($this->post_spam_rules) {
					foreach ($this->post_spam_rules as $rule => $point) {
						if ($rule && $point) {
							HypCommonFunc::PostSpam_filter($rule, $point);
						}
					}
				}
				
				// ̵���ʥե���������
				if (! empty($this->post_spam_trap)) {
					$this->ignore_fileds[$this->post_spam_trap] = array('');
				}
				if (is_array($this->ignore_fileds) && $this->ignore_fileds) {
					HypCommonFunc::PostSpam_filter('array_rule', array('ignore_fileds' => array($this->ignore_fileds, $this->post_spam_filed)));
				}
				
				// PukiWikiMod �Υ��ѥ�����ɤ߹��� 31pt
				$datfile = XOOPS_ROOT_PATH.'/modules/pukiwiki/cache/spamdeny.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("/".trim(join("",file($datfile)))."/i", 31);
				}
				
				// Default ���ѥॵ��������ɤ߹���
				$datfile = dirname(dirname(__FILE__)) . '/spamsites.dat';
				if (file_exists($datfile)) {
					$cachefile = XOOPS_TRUST_PATH . '/cache/hyp_spamsites.dat';
					if (filemtime($datfile) > @ filemtime($cachefile)) {
						$regs = HypCommonFunc::get_reg_pattern(array_map('trim',file($datfile)));
						if ($fp = @ fopen($cachefile, 'wb')) {
							if (flock($fp, LOCK_EX)) {
								fwrite($fp, $regs);
								flock($fp, LOCK_UN);
							}
							fclose($fp);
						}
					} else {
						$regs = join('', file($cachefile));
					}
					foreach(explode("\x08", $regs) as $reg) {
						HypCommonFunc::PostSpam_filter('/((ht|f)tps?:\/\/(.+\.)*|@)' . $reg . '/i', $this->post_spam_host);
					}
				}

				// Default ���ѥ�������ɤ߹���
				$datfile = dirname(dirname(__FILE__)) . '/spamwords.dat';
				if (file_exists($datfile)) {
					$cachefile = XOOPS_TRUST_PATH . '/cache/hyp_spamwords.dat';
					if (filemtime($datfile) > @ filemtime($cachefile)) {
						$regs = HypCommonFunc::get_reg_pattern(array_map('trim',file($datfile)));
						if ($fp = @ fopen($cachefile, 'wb')) {
							if (flock($fp, LOCK_EX)) {
								fwrite($fp, $regs);
								flock($fp, LOCK_UN);
							}
							fclose($fp);
						}
					} else {
						$regs = join('', file($cachefile));
					}
					foreach(explode("\x08", $regs) as $reg) {
						HypCommonFunc::PostSpam_filter('/' . $reg . '/i', $this->post_spam_word);
					}
				}
				
				// Ƚ��
				global $xoopsUser, $xoopsUserIsAdmin;
				if (!$xoopsUserIsAdmin) {
					// ����
					$spamlev = (is_object($xoopsUser))? $this->post_spam_user : $this->post_spam_guest;
					$level = HypCommonFunc::get_postspam_avr($this->post_spam_a, $this->post_spam_bb, $this->post_spam_url, $this->encode, $this->encodehint_name);
					if ($level > $spamlev) {
						if ($level > $this->post_spam_badip) { HypCommonFunc::register_bad_ips(); }
						if ($this->use_mail_notify) { $this->sendMail($level); }
						exit();
					} else {
						if ($this->use_mail_notify > 1) { $this->sendMail($level); }
					}
				}
			}
		}
	}
	
	function obFilter( $s ) {
		return HypGetQueryWord::word_highlight($s, constant($this->q_word2), _CHARSET, $this->msg_words_highlight);
	}

	function formFilter( $s ) {
		$insert = '';
		$this->encode = _CHARSET;
		
		// ���ѥ��ܥå��Ѥ�櫤�ųݤ���
		if (! empty($this->post_spam_trap_set)) {
			$insert .= "\n<input name=\"{$this->post_spam_trap}\" type=\"text\" size=\"1\" style=\"display:none;speak:none;\" />";
		}
		// ���󥳡��ǥ���Ƚ���ѥҥ��ʸ��
		if (! empty($this->encodehint_word)) {
			if (function_exists('mb_convert_encoding') && $this->configEncoding && $this->encode !== $this->configEncoding) {
				$encodehint_word = mb_convert_encoding($this->encodehint_word, $this->encode, $this->configEncoding);
			} else {
				$encodehint_word = $this->encodehint_word;
			}
			$insert .= "\n<input name=\"{$this->encodehint_name}\" type=\"hidden\" value=\"{$encodehint_word}\" />";
		}
		if ($insert) $insert = "\n".'<div>'.$insert."\n".'</div>';
		return preg_replace('/<form[^>]+?method=("|\')post\\1[^>]*?>/isS' ,
			"$0".$insert, $s);
	}
	
	function sendMail ($spamlev) {
		
		global $xoopsUser;
		
		
		if (is_object($xoopsUser)) {
			$info['UID'] = (int)$xoopsUser->uid();
			$info['UNAME'] = $xoopsUser->uname();
		} else {
			$info['UID'] = 0;
			$info['UNAME'] = 'Guest';
		}
		$info['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		$info['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
		$info['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
		$info['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
		$info['SPAM LEVEL'] = $spamlev;
		
		$_info = '';
		foreach($info as $key => $value)
			$_info .= $key . ': ' . $value . "\n";

		$_info .= str_repeat('-', 30) . "\n";
		
		$post = $_POST;
		// Key:excerpt ������Хȥ�å�����->ʸ���������Ѵ�
		if (isset($post['excerpt']) && function_exists('mb_convert_variables')) {
			if (isset($post['charset']) && $post['charset'] != '') {
				// TrackBack Ping �ǻ��ꤵ��Ƥ��뤳�Ȥ�����
				// ���ޤ������ʤ����ϼ�ư���Ф��ڤ��ؤ�
				if (mb_convert_variables($this->encode,
				    $post['charset'], $post) !== $post['charset']) {
					mb_convert_variables($this->encode, 'auto', $post);
				}
			} else if (! empty($post)) {
				// �����ޤȤ�ơ���ư���С��Ѵ�
				mb_convert_variables($this->encode, 'auto', $post);
			}
		}
		
		$message = $_info . '$_POST :' . "\n" . print_r($post, TRUE);
		
		$config_handler =& xoops_gethandler('config');
		$xoopsConfig =& $config_handler->getConfigsByCat(XOOPS_CONF);
		
		$subject = '[' . $xoopsConfig['sitename'] . '] POST Spam Report';

		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
		$xoopsMailer->setFromName($xoopsConfig['sitename']);
		$xoopsMailer->setSubject($subject);
		$xoopsMailer->setBody($message);
		$xoopsMailer->setToEmails($xoopsConfig['adminmail']);
		$xoopsMailer->send();
		$xoopsMailer->reset();

	}
}

if (file_exists(XOOPS_ROOT_PATH.'/class/hyp_common/hyp_preload.conf.php')) {
	include_once(XOOPS_ROOT_PATH.'/class/hyp_common/hyp_preload.conf.php');
} else if (file_exists(dirname(__FILE__).'/hyp_preload.conf.php')) {
	include_once(dirname(__FILE__).'/hyp_preload.conf.php');
}

if (! class_exists('HypCommonPreLoad')) {
class HypCommonPreLoad extends HypCommonPreLoadBase {
	
	function HypCommonPreLoad (& $controller) {
		
		// �Ƽ�����
		$this->configEncoding = 'EUC-JP'; // ���Υե������ʸ��������
		
		$this->encodehint_word = '��';    // POST���󥳡��ǥ���Ƚ����ʸ��
		$this->encodehint_name = 'HypEncHint'; // POST���󥳡��ǥ���Ƚ���� Filed name
		
		$this->use_set_query_words = 1;   // ������ɤ�����˥��å�
		$this->use_words_highlight = 1;   // ������ɤ�ϥ��饤��ɽ��
		$this->msg_words_highlight = '�����Υ�����ɤ��ϥ��饤�Ȥ���Ƥ��ޤ�'; 
		
		$this->use_proxy_check = 1;       // POST���ץ��������å�����
		$this->no_proxy_check  = '/^(127\.0\.0\.1|192\.168\.1\.)/'; // ����IP
		$this->msg_proxy_check = 'Can not post from public proxy.';
		
		$this->use_dependence_filter = 1; // �����¸ʸ���ե��륿��
		
		// POST SPAM
		$this->use_post_spam_filter = 1;  // POST SPAM �ե��륿��
		$this->use_mail_notify = 1;       // POST SPAM �᡼������ 0:�ʤ�, 1:SPAMȽ��Τ�, 2:���٤�
		$this->post_spam_a   = 1;         // <a> ���� 1�Ĥ�����Υݥ����
		$this->post_spam_bb  = 1;         // BB��� 1�Ĥ�����Υݥ����
		$this->post_spam_url = 1;         // URL      1�Ĥ�����Υݥ����
		$this->post_spam_host  = 31;      // Spam HOST �βû��ݥ����
		$this->post_spam_word  = 10;      // Spam Word �βû��ݥ����
		$this->post_spam_filed = 51;      // Spam ̵���ե�����ɤβû��ݥ����
		$this->post_spam_trap  = '___url';// Spam ���̵���ե������̾
		$this->post_spam_trap_set = 1;    // ̵���ե�����ɤ�櫤�ư�ǻųݤ���
		
		$this->post_spam_user  = 30;      // POST SPAM ����: ������桼����
		$this->post_spam_guest = 15;      // POST SPAM ����: ������
		$this->post_spam_badip = 50;      // �����������ݥꥹ�Ȥ���Ͽ��������
	
		// ����������̾
		$this->q_word  = 'XOOPS_QUERY_WORD';         // �������
		$this->q_word2 = 'XOOPS_QUERY_WORD2';        // �������ʬ������
		$this->se_name = 'XOOPS_SEARCH_ENGINE_NAME'; // ������̾
	
		// KAKASI �Ǥ�ʬ�����񤭷�̤Υ���å�����
		$this->kakasi_cache_dir = XOOPS_ROOT_PATH.'/cache2/kakasi/';
		
		// POST SPAM �Υݥ���Ȳû�����
		$this->post_spam_rules = array(
			// Ʊ��URL��1�Ԥ�3�� 11pt
			"/((?:ht|f)tps?:\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+)[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1/i" => 11,
			
			// 65ʸ���ʾ�αѿ�ʸ���Τߤǹ�������Ƥ��� 15pt
			// '/^[\x00-\x7f\s]{65,}$/' => 15,
			
			// ̵����ʸ�������ɤ����� 31pt
			'/[\x00-\x08\x11-\x12\x14-\x1f\x7f\xff]+/' => 31
		);
		
		// ̵���ʥե���������
		$this->ignore_fileds = array(
			// 'url' => array('newbb/post.php', 'comment_post.php'),
		);
		
		parent::HypCommonPreLoadBase($controller);
		
	}
}
}
?>
