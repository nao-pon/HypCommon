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
	
	var $configEncoding;       // Configエンコーディング

	var $encodehint_word;      // POSTエンコーディング判定用文字
	var $encodehint_name;      // POSTエンコーディング判定用 Filed name
	
	var $use_set_query_words;  // 検索ワードを定数にセット
	var $use_words_highlight;  // 検索ワードをハイライト表示
	var $msg_words_highlight;  // ハイライトキーワードメッセージ
	
	var $use_proxy_check;      // POST時プロキシチェックする
	var $no_proxy_check;       // 除外IP
	var $msg_proxy_check; 
	
	var $use_dependence_filter;// 機種依存文字フィルター
	
	var $use_post_spam_filter; // POST SPAM フィルター
	var $use_mail_notify;      // POST SPAM メール通知
	var $post_spam_a;          // <a> タグ 1個あたりのポイント
	var $post_spam_bb;         // BBリンク 1個あたりのポイント
	var $post_spam_url;        // URL      1個あたりのポイント
	var $post_spam_host;       // Spam HOST の加算ポイント
	var $post_spam_word;       // Spam Word の加算ポイント
	var $post_spam_filed;      // Spam 無効フィールドの加算ポイント
	var $post_spam_trap;       // Spam 罠用無効フィールド名
	var $post_spam_trap_set;   // 無効フィールドの罠を自動で仕掛ける
		
	var $post_spam_user;       // POST SPAM 閾値: ログインユーザー
	var $post_spam_guest;      // POST SPAM 閾値: ゲスト
	var $post_spam_rules;      // コンストラクタ内で設定

	// 検索ワード定数名
	var $q_word;               // 検索ワード
	var $q_word2;              // 検索ワード分かち書き
	var $se_name;              // 検索元名
	var $kakasi_cache_dir;   
	
	// コンストラクタ
	function HypCommonPreLoadBase (& $controller) {
		parent::XCube_ActionFilter($controller);
	}
	
	function preFilter() {
		// <from> フィルター
		if (! empty($this->encodehint_word) || ! empty($this->post_spam_trap_set)) {
			ob_start(array(&$this, 'formFilter'));
		}
	}
	
	function postFilter() {
		// XOOPS の表示文字エンコーディング
		$this->encode = strtoupper(_CHARSET);
		
		// 設定ファイルのエンコーディングを検査
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
			// POST 文字列の文字エンコードを判定
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
			
			// 文字エンコーディング外の文字を数値エンティティに変換
			//$_POST = HypCommonFunc::encode_numericentity($_POST, _CHARSET, HYP_POST_ENCODING);

			// 機種依存文字フィルター
			if ($this->encode === 'EUC-JP' && $this->use_dependence_filter) {
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
				
				// 無効なフィールド定義
				if (! empty($this->post_spam_trap)) {
					$this->ignore_fileds[$this->post_spam_trap] = array('');
				}
				if (is_array($this->ignore_fileds) && $this->ignore_fileds) {
					HypCommonFunc::PostSpam_filter('array_rule', array('ignore_fileds' => array($this->ignore_fileds, $this->post_spam_filed)));
				}
				
				// PukiWikiMod のスパム定義読み込み 31pt
				$datfile = XOOPS_ROOT_PATH.'/modules/pukiwiki/cache/spamdeny.dat';
				if (file_exists($datfile)) {
					HypCommonFunc::PostSpam_filter("/".trim(join("",file($datfile)))."/i", 31);
				}
				
				// Default スパムサイト定義読み込み
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

				// Default スパムワード定義読み込み
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
				
				// 判定
				global $xoopsUser, $xoopsUserIsAdmin;
				if (!$xoopsUserIsAdmin) {
					// 閾値
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
		
		// スパムロボット用の罠を仕掛ける
		if (! empty($this->post_spam_trap_set)) {
			$insert .= "\n<input name=\"{$this->post_spam_trap}\" type=\"text\" size=\"1\" style=\"display:none;speak:none;\" />";
		}
		// エンコーディング判定用ヒント文字
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
		// Key:excerpt があればトラックかも->文字コード変換
		if (isset($post['excerpt']) && function_exists('mb_convert_variables')) {
			if (isset($post['charset']) && $post['charset'] != '') {
				// TrackBack Ping で指定されていることがある
				// うまくいかない場合は自動検出に切り替え
				if (mb_convert_variables($this->encode,
				    $post['charset'], $post) !== $post['charset']) {
					mb_convert_variables($this->encode, 'auto', $post);
				}
			} else if (! empty($post)) {
				// 全部まとめて、自動検出／変換
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
		
		// 各種設定
		$this->configEncoding = 'EUC-JP'; // このファイルの文字コード
		
		$this->encodehint_word = 'ぷ';    // POSTエンコーディング判定用文字
		$this->encodehint_name = 'HypEncHint'; // POSTエンコーディング判定用 Filed name
		
		$this->use_set_query_words = 1;   // 検索ワードを定数にセット
		$this->use_words_highlight = 1;   // 検索ワードをハイライト表示
		$this->msg_words_highlight = 'これらのキーワードがハイライトされています'; 
		
		$this->use_proxy_check = 1;       // POST時プロキシチェックする
		$this->no_proxy_check  = '/^(127\.0\.0\.1|192\.168\.1\.)/'; // 除外IP
		$this->msg_proxy_check = 'Can not post from public proxy.';
		
		$this->use_dependence_filter = 1; // 機種依存文字フィルター
		
		// POST SPAM
		$this->use_post_spam_filter = 1;  // POST SPAM フィルター
		$this->use_mail_notify = 1;       // POST SPAM メール通知 0:なし, 1:SPAM判定のみ, 2:すべて
		$this->post_spam_a   = 1;         // <a> タグ 1個あたりのポイント
		$this->post_spam_bb  = 1;         // BBリンク 1個あたりのポイント
		$this->post_spam_url = 1;         // URL      1個あたりのポイント
		$this->post_spam_host  = 31;      // Spam HOST の加算ポイント
		$this->post_spam_word  = 10;      // Spam Word の加算ポイント
		$this->post_spam_filed = 51;      // Spam 無効フィールドの加算ポイント
		$this->post_spam_trap  = '___url';// Spam 罠用無効フィールド名
		$this->post_spam_trap_set = 1;    // 無効フィールドの罠を自動で仕掛ける
		
		$this->post_spam_user  = 30;      // POST SPAM 閾値: ログインユーザー
		$this->post_spam_guest = 15;      // POST SPAM 閾値: ゲスト
		$this->post_spam_badip = 50;      // アクセス拒否リストへ登録する閾値
	
		// 検索ワード定数名
		$this->q_word  = 'XOOPS_QUERY_WORD';         // 検索ワード
		$this->q_word2 = 'XOOPS_QUERY_WORD2';        // 検索ワード分かち書き
		$this->se_name = 'XOOPS_SEARCH_ENGINE_NAME'; // 検索元名
	
		// KAKASI での分かち書き結果のキャッシュ先
		$this->kakasi_cache_dir = XOOPS_ROOT_PATH.'/cache2/kakasi/';
		
		// POST SPAM のポイント加算設定
		$this->post_spam_rules = array(
			// 同じURLが1行に3回 11pt
			"/((?:ht|f)tps?:\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+)[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1[^!~*'();\/?:\@&=+\$,%#\w.-]+?\\1/i" => 11,
			
			// 65文字以上の英数文字のみで構成されている 15pt
			// '/^[\x00-\x7f\s]{65,}$/' => 15,
			
			// 無効な文字コードがある 31pt
			'/[\x00-\x08\x11-\x12\x14-\x1f\x7f\xff]+/' => 31
		);
		
		// 無効なフィールド定義
		$this->ignore_fileds = array(
			// 'url' => array('newbb/post.php', 'comment_post.php'),
		);
		
		parent::HypCommonPreLoadBase($controller);
		
	}
}
}
?>
