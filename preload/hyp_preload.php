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
	
	// 各種設定
	var $use_set_query_words = 1;   // 検索ワードを定数にセット
	var $use_words_highlight = 1;   // 検索ワードをハイライト表示
	
	var $use_proxy_check = 1;       // POST時プロキシチェックする
	var $no_proxy_check = '/^(127\.0\.0\.1|192\.168\.1\.)/'; // 除外IP
	
	var $use_dependence_filter = 1; // 機種依存文字フィルター
	
	var $use_post_spam_filter = 1;  // POST SPAM フィルター
	var $use_mail_notify = 1;       // POST SPAM メール通知
	var $post_spam_a   = 1;         // <a> タグ 1個あたりのポイント
	var $post_spam_bb  = 1;         // BBリンク 1個あたりのポイント
	var $post_spam_url = 1;         // URL      1個あたりのポイント
	var $post_spam_user  = 30;      // POST SPAM 閾値: ログインユーザー
	var $post_spam_guest = 15;      // POST SPAM 閾値: ゲスト
	var $post_spam_rules = array(); // コンストラクタ内で設定

	// 検索ワード定数名
	var $q_word  = 'XOOPS_QUERY_WORD';         // 検索ワード
	var $q_word2 = 'XOOPS_QUERY_WORD2';        // 検索ワード分かち書き
	var $se_name = 'XOOPS_SEARCH_ENGINE_NAME'; // 検索元名
	var $kakasi_cache_dir = '';                // コンストラクタ内で設定
	
	// コンストラクタ
	function HypCommonPreLoad (& $controller) {
		
		/*
			KAKASI のパスは、XOOPS_TRUST_PATH/class/hyp_common/hyp_kakasi.php で
			設定する。規定値: '/usr/bin/kakasi'
		*/
		
		// KAKASI での分かち書き結果のキャッシュ先
		$this->kakasi_cache_dir = XOOPS_ROOT_PATH.'/cache2/kakasi/';
		
		// POST SPAM のポイント加算設定
		$this->post_spam_rules = array(
			// 同じURLが1行に3回 11pt
			"/((?:ht|f)tps?:\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+)[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1/i" => 11,
			
			// 100文字以上の英数文字のみで構成されている 15pt
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
			
			// 機種依存文字フィルター
			if ($this->use_dependence_filter) {
				$_POST = HypCommonFunc::dependence_filter($_POST);
			}
			
			// PostSpam をチェック
			if ($this->use_post_spam_filter) {
				// 加算 pt
				if ($this->post_spam_rules) {
					foreach ($this->post_spam_rules as $rule => $point) {
						if ($rule && $point) {
							HypCommonFunc::PostSpam_filter($rule, $point);
						}
					}
				}
				
				// PukiWikiMod のスパム定義読み込み 30pt
				$datfile = XOOPS_ROOT_PATH.'/modules/pukiwiki/cache/spamdeny.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("/".trim(join("",file($datfile)))."/i", 30);
				}
				
				// Default スパムサイト定義読み込み 30pt
				$datfile = dirname(dirname(__FILE__)) . '/spamsites.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("#((ht|f)tps?://(.+\.)*|@)(".str_replace(array('.',"\r","\n"),array('\.',''),trim(join("|",file($datfile)))).")#i", 30);
				}
				
				// 判定
				global $xoopsUser, $xoopsUserIsAdmin;
				if (!$xoopsUserIsAdmin) {
					// 閾値
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
