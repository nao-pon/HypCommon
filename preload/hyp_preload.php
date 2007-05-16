<?php

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
	
	var $use_set_query_words;  // ������ɤ�����˥��å�
	var $use_words_highlight;  // ������ɤ�ϥ��饤��ɽ��
	
	var $use_proxy_check;      // POST���ץ��������å�����
	var $no_proxy_check;       // ����IP
	
	var $use_dependence_filter;// �����¸ʸ���ե��륿��
	
	var $use_post_spam_filter; // POST SPAM �ե��륿��
	var $use_mail_notify;      // POST SPAM �᡼������
	var $post_spam_a;          // <a> ���� 1�Ĥ�����Υݥ����
	var $post_spam_bb;         // BB��� 1�Ĥ�����Υݥ����
	var $post_spam_url;        // URL      1�Ĥ�����Υݥ����
	var $post_spam_host;       // Spam HOST �βû��ݥ����
	var $post_spam_word;       // Spam Word �βû��ݥ����
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
		
		// Set Query Words
		if ($this->use_set_query_words) {
			HypCommonFunc::set_query_words($this->q_word, $this->q_word2, $this->se_name, $this->kakasi_cache_dir);
			if ($this->use_words_highlight) {
				ob_start(array(&$this, 'obFilter'));
			}
		}
	}
	
	function postFilter() {
		if (!empty($_POST)) {
			
			// Proxy Check
			if ($this->use_proxy_check) {
				HypCommonFunc::BBQ_Check($this->no_proxy_check);
			}
			
			// �����¸ʸ���ե��륿��
			if ($this->use_dependence_filter) {
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
				if (is_array($this->ignore_fileds)) {
					HypCommonFunc::PostSpam_filter('array_rule', array('ignore_fileds' => array($this->ignore_fileds, $this->post_spam_filed)));
				}
				
				// PukiWikiMod �Υ��ѥ�����ɤ߹��� 30pt
				$datfile = XOOPS_ROOT_PATH.'/modules/pukiwiki/cache/spamdeny.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("/".trim(join("",file($datfile)))."/i", 30);
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
					$level = HypCommonFunc::get_postspam_avr($this->post_spam_a, $this->post_spam_bb, $this->post_spam_url);
					if ($level > $spamlev) {
						if ($this->use_mail_notify) $this->sendMail($level);
						//header("Location: ".XOOPS_URL."/");
						exit();
					}
				}
			}
		}
	}
	
	function obFilter( $s ) {
		return HypGetQueryWord::word_highlight($s, constant($this->q_word2));
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
		
		
		foreach($info as $key => $value)
			$_info .= $key . ': ' . $value . "\n";

		$_info .= str_repeat('-', 30) . "\n";
		$message = $_info . '$_POST :' . "\n" . print_r($_POST, TRUE);
		
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
		$this->use_set_query_words = 1;   // ������ɤ�����˥��å�
		$this->use_words_highlight = 1;   // ������ɤ�ϥ��饤��ɽ��
		
		$this->use_proxy_check = 1;       // POST���ץ��������å�����
		$this->no_proxy_check = '/^(127\.0\.0\.1|192\.168\.1\.)/'; // ����IP
		
		$this->use_dependence_filter = 1; // �����¸ʸ���ե��륿��
		
		$this->use_post_spam_filter = 1;  // POST SPAM �ե��륿��
		$this->use_mail_notify = 1;       // POST SPAM �᡼������
		$this->post_spam_a   = 1;         // <a> ���� 1�Ĥ�����Υݥ����
		$this->post_spam_bb  = 1;         // BB��� 1�Ĥ�����Υݥ����
		$this->post_spam_url = 1;         // URL      1�Ĥ�����Υݥ����
		$this->post_spam_host  = 30;      // Spam HOST �βû��ݥ����
		$this->post_spam_word  = 10;      // Spam Word �βû��ݥ����
		$this->post_spam_filed = 16;      // Spam ̵���ե�����ɤβû��ݥ����
		$this->post_spam_user  = 30;      // POST SPAM ����: ������桼����
		$this->post_spam_guest = 15;      // POST SPAM ����: ������
	
		// ����������̾
		$this->q_word  = 'XOOPS_QUERY_WORD';         // �������
		$this->q_word2 = 'XOOPS_QUERY_WORD2';        // �������ʬ������
		$this->se_name = 'XOOPS_SEARCH_ENGINE_NAME'; // ������̾
	
		// KAKASI �Υѥ��ϡ�XOOPS_TRUST_PATH/class/hyp_common/hyp_kakasi.php ��
		// ���ꤹ�롣������: '/usr/bin/kakasi'
		
		// KAKASI �Ǥ�ʬ�����񤭷�̤Υ���å�����
		$this->kakasi_cache_dir = XOOPS_ROOT_PATH.'/cache2/kakasi/';
		
		// POST SPAM �Υݥ���Ȳû�����
		$this->post_spam_rules = array(
			// Ʊ��URL��1�Ԥ�3�� 11pt
			"/((?:ht|f)tps?:\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+)[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1/i" => 11,
			
			// 65ʸ���ʾ�αѿ�ʸ���Τߤǹ�������Ƥ��� 15pt
			'/^[\x00-\x7f\s]{65,}$/' => 15,
			
			// ̵����ʸ�������ɤ����� 30pt
			'/[\x00-\x08\x11-\x12\x14-\x1f\x7f\xff]+/' => 30
		);
		
		// ̵���ʥե������
		$this->ignore_fileds = array(
			'url' => array('newbb/post.php', 'pukiwiki/index.php', 'comment_post.php'),
		);
		
		parent::HypCommonPreLoadBase($controller);
		
	}
}
}
?>
