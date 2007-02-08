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

class HypCommonPreLoad extends XCube_ActionFilter {
	
	// �Ƽ�����
	var $use_set_query_words = 1;   // ������ɤ�����˥��å�
	var $use_words_highlight = 1;   // ������ɤ�ϥ��饤��ɽ��
	
	var $use_proxy_check = 1;       // POST���ץ��������å�����
	var $no_proxy_check = '/^(127\.0\.0\.1|192\.168\.1\.)/'; // ����IP
	
	var $use_dependence_filter = 1; // �����¸ʸ���ե��륿��
	
	var $use_post_spam_filter = 1;  // POST SPAM �ե��륿��
	var $use_mail_notify = 1;       // POST SPAM �᡼������
	var $post_spam_a   = 1;         // <a> ���� 1�Ĥ�����Υݥ����
	var $post_spam_bb  = 1;         // BB��� 1�Ĥ�����Υݥ����
	var $post_spam_url = 1;         // URL      1�Ĥ�����Υݥ����
	var $post_spam_user  = 30;      // POST SPAM ����: ������桼����
	var $post_spam_guest = 15;      // POST SPAM ����: ������
	var $post_spam_rules = array(); // ���󥹥ȥ饯���������

	// ����������̾
	var $q_word  = 'XOOPS_QUERY_WORD';         // �������
	var $q_word2 = 'XOOPS_QUERY_WORD2';        // �������ʬ������
	var $se_name = 'XOOPS_SEARCH_ENGINE_NAME'; // ������̾
	var $kakasi_cache_dir = '';                // ���󥹥ȥ饯���������
	
	// ���󥹥ȥ饯��
	function HypCommonPreLoad (& $controller) {
		
		/*
			KAKASI �Υѥ��ϡ�XOOPS_TRUST_PATH/class/hyp_common/hyp_kakasi.php ��
			���ꤹ�롣������: '/usr/bin/kakasi'
		*/
		
		// KAKASI �Ǥ�ʬ�����񤭷�̤Υ���å�����
		$this->kakasi_cache_dir = XOOPS_ROOT_PATH.'/cache2/kakasi/';
		
		// POST SPAM �Υݥ���Ȳû�����
		$this->post_spam_rules = array(
			// Ʊ��URL��1�Ԥ�3�� 11pt
			"/((?:ht|f)tps?:\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+)[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1/i" => 11,
			
			// 100ʸ���ʾ�αѿ�ʸ���Τߤǹ�������Ƥ��� 15pt
			'/^[\x00-\x7f\s]{100,}$/' => 15
		);


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
				
				// PukiWikiMod �Υ��ѥ�����ɤ߹��� 30pt
				$datfile = XOOPS_ROOT_PATH.'/modules/pukiwiki/cache/spamdeny.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("/".trim(join("",file($datfile)))."/i", 30);
				}
				
				// Default ���ѥॵ��������ɤ߹��� 30pt
				$datfile = dirname(dirname(__FILE__)) . '/spamsites.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("#((ht|f)tps?://(.+\.)*|@)(".str_replace(array('.',"\r","\n"),array('\.',''),trim(join("|",file($datfile)))).")#i", 30);
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
?>
