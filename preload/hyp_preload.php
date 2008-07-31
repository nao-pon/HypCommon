<?php
define('X2_ADD_SMARTYPLUGINS_DIR', XOOPS_TRUST_PATH . '/libs/smartyplugins/x2');

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
		// Use K_TAI Render
		if (! empty($this->use_k_tai_render)) {
			if (isset($_SERVER['HTTP_USER_AGENT']) &&
				preg_match($this->k_tai_conf['ua_regex'], $_SERVER['HTTP_USER_AGENT'])) {

				define('HYP_K_TAI_RENDER', TRUE);
				
				ini_set('session.use_trans_sid', 0);
				
				$skey = ini_get('session.name');
				if(isset($_POST[$skey])) $sid=$_POST[$skey];
				else if(isset($_GET[$skey])) $sid=$_GET[$skey];
				else $sid=null;
				if( preg_match('/^[0-9a-z]{32}$/', $sid) ){
					session_id($sid);
				}
				
				// Set HypKTaiRender
				HypCommonFunc::loadClass('HypKTaiRender');
				$this->HypKTaiRender = new HypKTaiRender();
				$this->HypKTaiRender->set_myRoot(XOOPS_URL);
				$this->HypKTaiRender->Config_emojiDir = XOOPS_URL . '/images/emoji';
			} else {
				define('HYP_K_TAI_RENDER', FALSE);
			}
		}
	}

	function preBlockFilter()
	{
		// Use K_TAI Render (XCL only)
		if (defined('XOOPS_CUBE_LEGACY') && defined('HYP_K_TAI_RENDER') && HYP_K_TAI_RENDER) {
			// Set session key
			$skey = ($GLOBALS['xoopsConfig']['use_mysession'] && $GLOBALS['xoopsConfig']['session_name'] !== '')? $GLOBALS['xoopsConfig']['session_name'] : session_name();
			if(isset($_POST[$skey])) $sid=$_POST[$skey];
			else if(isset($_GET[$skey])) $sid=$_GET[$skey];
			else $sid=null;
			if( preg_match('/^[0-9a-z]{32}$/', $sid) ){
				session_id($sid);
			}

			// Set theme set
			if (isset($this->k_tai_conf['themeSet']) && file_exists(XOOPS_THEME_PATH . '/' . $this->k_tai_conf['themeSet'] . '/theme.html')) {
				$GLOBALS['xoopsConfig']['theme_set'] = $this->k_tai_conf['themeSet'];
				$this->mRoot->mContext->setThemeName($this->k_tai_conf['themeSet']);
				$this->mRoot->mDelegateManager->add( 'XoopsTpl.New' , array(& $this , '_xoopsConfig_theme_set' ) , XCUBE_DELEGATE_PRIORITY_FIRST) ;
			}

	        // For cubeUtils (disable auto login)
	        $config_handler =& xoops_gethandler('config');
	        $moduleConfigCubeUtils =& $config_handler->getConfigsByDirname('cubeUtils');
			if ($moduleConfigCubeUtils) {
	        	$moduleConfigCubeUtils['cubeUtils_use_autologin'] = FALSE;
			}
		}
	}

	function _xoopsConfig_theme_set () {
		$GLOBALS['xoopsConfig']['theme_set'] = $this->k_tai_conf['themeSet'];
	}
	
	function postFilter() {
		// XOOPS の表示文字エンコーディング
		$this->encode = strtoupper(_CHARSET);
		
		// 設定ファイルのエンコーディングを検査
		if ($this->encode !== strtoupper($this->configEncoding)) {
			$this->encodehint_word = '';
		}
		
		if (! empty($_GET)) {
			// 文字コードを正規化
			$enchint = (isset($_GET[$this->encodehint_name]))? $_GET[$this->encodehint_name] : ((isset($_GET['encode_hint']))? $_GET['encode_hint'] : '');
			if ($enchint && function_exists('mb_detect_encoding')) {
				$encode = strtoupper(mb_detect_encoding($enchint));
				if ($encode !== $this->encode) {
					mb_convert_variables($this->encode, $encode, $_GET);
					if (isset($_GET['charset'])) $_GET['charset'] = $this->encode;
				}
			}
		}
		
		if (! empty($_POST)) {
			// Input フィルター (remove "\0", "&#8203;")
			$_POST = HypCommonFunc::input_filter($_POST);
			
			// POST 文字列の文字エンコードを判定
			$enchint = (isset($_POST[$this->encodehint_name]))? $_POST[$this->encodehint_name] : ((isset($_POST['encode_hint']))? $_POST['encode_hint'] : '');
			if ($enchint && function_exists('mb_detect_encoding')) {
				define ('HYP_POST_ENCODING', strtoupper(mb_detect_encoding($enchint)));
			} else if (isset($_POST['charset'])) {
				define ('HYP_POST_ENCODING', strtoupper($_POST['charset']));
			}
			
			// 携帯レンダーの場合絵文字変換
			if (defined('HYP_K_TAI_RENDER') && HYP_K_TAI_RENDER) {
				$_POST = $this->_modKtaiEmojiEncode($_POST);
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
			
			// 文字コードを正規化
			if (defined('HYP_POST_ENCODING') && $this->encode !== HYP_POST_ENCODING) {
				mb_convert_variables($this->encode, HYP_POST_ENCODING, $_POST);
				if (isset($_POST['charset'])) $_POST['charset'] = $this->encode;
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
		
		// Use K_TAI Render
		if (defined('HYP_K_TAI_RENDER') && HYP_K_TAI_RENDER) {
			// Check login
			$this->_checkEasyLogin();
			// Set theme set
			if (isset($this->k_tai_conf['themeSet']) && file_exists(XOOPS_THEME_PATH . '/' . $this->k_tai_conf['themeSet'] . '/theme.html')) {
				$GLOBALS['xoopsConfig']['theme_set'] = $this->k_tai_conf['themeSet'];
				if (defined('XOOPS_CUBE_LEGACY')) {
					// Over write user setting
					$this->mRoot->mContext->setThemeName($this->k_tai_conf['themeSet']);
				}
			}
			// keitai Filter
			ob_start(array(&$this, 'keitaiFilter'));
		} else if (! empty($this->use_k_tai_render)) {
			ob_start(array(&$this, 'emojiFilter'));
		}
		
		// <from> Filter
		if (! empty($this->encodehint_word) || ! empty($this->post_spam_trap_set)) {
			ob_start(array(&$this, 'formFilter'));
		}

		// Set Query Words
		if ($this->use_set_query_words) {
			HypCommonFunc::set_query_words($this->q_word, $this->q_word2, $this->se_name, $this->kakasi_cache_dir, $this->encode);
			if ($this->use_words_highlight) {
				ob_start(array(&$this, 'obFilter'));
			}
		}
	}
	
	function _checkEasyLogin () {
		if (empty($_SESSION['xoopsUserId'])) {
			$this->HypKTaiRender->vars['ua']['isGuest'] = TRUE;
		} else {
			if (empty($this->k_tai_conf['noCheckIpRange']) && ! $this->HypKTaiRender->checkIp ($_SERVER['REMOTE_ADDR'], $this->HypKTaiRender->vars['ua']['carrier'])) {
				$_SESSION = array();
				redirect_header(XOOPS_URL, 0, 'Your IP "' . $_SERVER['REMOTE_ADDR'] . '" doesn\'t match to IP range of "'.$this->HypKTaiRender->vars['ua']['carrier'].'".');
				exit();
				//exit('Your IP "' . $_SERVER['REMOTE_ADDR'] . '" doesn\'t match to IP range of "'.$this->HypKTaiRender->vars['ua']['carrier'].'".');
			}			
		}

		if (! empty($this->k_tai_conf['easyLogin']) && isset($_GET['_EASYLOGIN'])) {
			$uaUid = md5($this->HypKTaiRender->vars['ua']['uid'] . XOOPS_DB_PASS);

			if (empty($this->HypKTaiRender->vars['ua']['uid'])) {
					exit('Could not got your device ID.');
			}
			
			// Read data file
			$myroot = str_replace('/', '_', preg_replace('#https?://#i', '', XOOPS_URL));
			$datfile = XOOPS_TRUST_PATH . '/cache/' . $myroot . '_easylogin.dat';
			if (file_exists($datfile)) {
				$uids = unserialize(HypCommonFunc::flock_get_contents($datfile));
			} else {
				$uids = array();
			}
			
			if (! empty($_SESSION['xoopsUserId'])) {
				// Check & save uids data
				if (! isset($uids[$uaUid]) || $uids[$uaUid] !== $_SESSION['xoopsUserId']) {
					foreach(array_keys($uids, $_SESSION['xoopsUserId']) as $_key) {
						unset($uids[$_key]);
					}
					$uids[$uaUid] = $_SESSION['xoopsUserId'];
					if ($fp = fopen($datfile, 'wb')) {
						flock($fp, LOCK_EX);
						fwrite($fp, serialize($uids));
						fclose($fp);
					}
				}
			} else {
				// Do easy login
				if (empty($this->k_tai_conf['noCheckIpRange']) && ! $this->HypKTaiRender->checkIp ($_SERVER['REMOTE_ADDR'], $this->HypKTaiRender->vars['ua']['carrier'])) {
					exit('Your IP "' . $_SERVER['REMOTE_ADDR'] . '" doesn\'t match to IP range of "'.$this->HypKTaiRender->vars['ua']['carrier'].'".');
				}
			
				$uri = $_SERVER['REQUEST_URI'];
				// Default is login form
				$url = XOOPS_URL . '/user.php?xoops_redirect=' . rawurlencode($uri);
				if (! empty($uids[$uaUid])) {
			        // Login success
			        $member_handler =& xoops_gethandler('member');
			        $user =& $member_handler->getUser($uids[$uaUid]);
					if (false !== $user && $user->getVar('level') > 0) {
						// Update last login
						$user->setVar('last_login', time());
						$member_handler->insertUser($user, TRUE);
						
						// Set session vars
						$_SESSION['xoopsUserId'] = $uids[$uaUid];
						$_SESSION['xoopsUserGroups'] = $user->getGroups();
						$user_theme = $user->getVar('theme');
						if (in_array($user_theme, $GLOBALS['xoopsConfig']['theme_set_allowed'])) {
							$_SESSION['xoopsUserTheme'] = $user_theme;
						}
						
						$url = $this->HypKTaiRender->myRoot . $this->HypKTaiRender->removeQueryFromUrl($uri, array('guid', '_EASYLOGIN'));
			        }
				}
				// Redirect
				header('Location: ' . $url);
				exit();
			}
		}
	}
	
	function _modKtaiEmojiEncode ($vars) {
		if (is_array($vars)) {
			foreach($vars as $key=>$var) {
				$vars[$key] = $this->_modKtaiEmojiEncode($var);
			}
			return $vars;
		}
		static $mpc;
		static $to;
		
		$to = $mpc = NULL;
		
		if (! class_exists('MobilePictogramConverter')) {
			HypCommonFunc::loadClass('MobilePictogramConverter');
		}
		
		if (is_null($mpc)) {
			$carrier = '';
			$mpc = '';
			
			$from_encode = '';
			switch (HYP_POST_ENCODING) {
				case 'UTF-8':
				case 'UTF_8':
				case 'UTF8':
					$from_encode = MPC_FROM_CHARSET_UTF8;
					break;
				case 'SJIS':
				case 'SHIFT-JIS':
				case 'SHIFT_JIS':
					$from_encode = MPC_FROM_CHARSET_SJIS;
					break;
			}
			
			if ($from_encode) {
				switch ($this->HypKTaiRender->vars['ua']['carrier']) {
					case 'docomo':
						$to = MPC_TO_FOMA;
						$carrier = MPC_FROM_FOMA;
						break;
					case 'softbank':
						$to = MPC_TO_SOFTBANK;
						$carrier = MPC_FROM_SOFTBANK;
						break;
					case 'au':
						$to = MPC_TO_EZWEB;
						$carrier = MPC_FROM_EZWEB;
						break;
				}
				if ($carrier) {
					$mpc =& MobilePictogramConverter::factory('', $carrier, $from_encode, MPC_FROM_OPTION_RAW);
				}
			}
		}
		
		if (! $mpc) return $vars;
		
		$mpc->setString($vars);
		return $mpc->Convert($to, MPC_TO_OPTION_MODKTAI);
	}
	
	function obFilter( $s ) {
		
		if ($s === '' || strpos($s, '<html') === FALSE) return $s;
		
		return HypGetQueryWord::word_highlight($s, constant($this->q_word2), _CHARSET, $this->msg_words_highlight);
	}

	function formFilter( $s ) {
		
		if ($s === '' || strpos($s, '<html') === FALSE) return $s;
		
		$insert = '';
		$this->encode = _CHARSET;
		
		// スパムロボット用の罠を仕掛ける
		if (! empty($this->post_spam_trap_set) && (! defined('HYP_K_TAI_RENDER') || ! HYP_K_TAI_RENDER)) {
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
		if ($insert) $insert = "\n".$insert."\n";
		return preg_replace('/<form[^>]+?>/isS' ,
			"$0".$insert, $s);
	}
	
	function keitaiFilter ( $s ) {

		if ($s === '') return;

		$head = $header = $body = $footer = '';
		$header_template = $body_template = $footer_template = '';
		
		$rebuilds = $this->k_tai_conf['rebuilds'];
		
		// テンプレート読み込み
		if ($rebuilds && $this->k_tai_conf['template']) {
			$templates_dir = dirname(dirname( __FILE__ )) . '/ktairender/templates/' . $this->k_tai_conf['template']  . '/';
			foreach(array('header', 'body', 'footer') as $_name) {
				if (file_exists( $templates_dir . $_name . '.html' )) {
					$var_name = $_name . '_template';
					$$var_name = file_get_contents( $templates_dir . $_name . '.html' );
				}
			}
		}
		
		// Is RSS?
		if (preg_match('/<(?:feed.+?<entry|(?:(?:rss|rdf).+?<channel))/isS', $s)) {
			HypCommonFunc::loadClass('HypRss2Html');
			$r = new HypRss2Html($s);
			$s = $r->getHtml();
			$s = mb_convert_encoding($s, _CHARSET, $r->encoding);
		}
		
		// preg_match では、サイズが大きいページで正常処理できないことがあるので。
		$arr1 = explode('<head', $s, 2);
		if (isset($arr1[1]) && strpos($arr1[1], '</head>') !== FALSE) {
			$arr2 = explode('</head>', $arr1[1], 2);
			$head = substr($arr2[0], strpos($arr2[0], '>') + 1);
		}
		$arr1 = explode('<body', $s, 2);
		if (isset($arr1[1]) && strpos($arr1[1], '</body>') !== FALSE) {
			$arr2 = explode('</body>', $arr1[1], 2);
			$body = substr($arr2[0], strpos($arr2[0], '>') + 1);
		}

		$r =& $this->HypKTaiRender;

		if ($body) {
			// 無視する部分(<!--HypKTaiIgnore-->...<!--/HypKTaiIgnore-->)を削除
			while(strpos($body, '<!--HypKTaiIgnore-->') !== FALSE) {
				$arr1 = explode('<!--HypKTaiIgnore-->', $body, 2);
				$arr2 = array_pad(explode('<!--/HypKTaiIgnore-->', $arr1[1], 2), 2, '');
				$body = $arr1[0] . $arr2[1];
			}
			if ($rebuilds) {
				$parts = array();
				$found = FALSE;
				foreach($rebuilds as $id => $var) {
					$qid = preg_quote($id, '#');
					$parts[$id] = '';
					// preg_match では、サイズが大きいページで正常処理できないことがあるので。
					$arr1 = explode('<!--' . $id . '-->', $body, 2);
					if (isset($arr1[1]) && strpos($arr1[1], '<!--/' . $id . '-->') !== FALSE) {
						$arr2 = explode('<!--/' . $id . '-->', $arr1[1], 2);
						$target = trim(preg_replace('/<!--.+?-->/sS', '', $arr2[0]));
						if ($target) {
							$parts[$id] = $var['above'] . $target . $var['below'];
							$found = TRUE;
						}
					}
				}
				
				if ($found) {
					// Easy login
					if (! empty($this->k_tai_conf['easyLogin'])) {
						if (! empty($r->vars['ua']['isGuest'])) {
							$add = '_EASYLOGIN';
							if ($r->vars['ua']['name'] === 'DoCoMo') {
								$add .= '&guid=ON';
							}
							$url = $r->myRoot . $r->removeSID($_SERVER['REQUEST_URI']);
							$url .= ((strpos($url, '?') === FALSE)? '?' : '&') . $add;
							$url = str_replace('&', '&amp;', $url);
							$easylogin = '<a href="' . $url . '">' . $this->k_tai_conf['msg']['easylogin'] . '</a>';
						} else {
							if (is_object($GLOBALS['xoopsUser'])) {
								$uname = htmlspecialchars($GLOBALS['xoopsUser']->getVar('uname'));
								$uid = $GLOBALS['xoopsUser']->getVar('uid');
								$uname = '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $uid . '">' . $uname . '</a>';
							}
							$easylogin = $uname . ' <a href="' . XOOPS_URL . '/user.php?op=logout">' . $this->k_tai_conf['msg']['logout'] . '</a>';
						}
						$parts['easylogin'] = $rebuilds['easylogin']['above'] . $easylogin . $rebuilds['easylogin']['below'];
					}

					foreach(array_keys($rebuilds) as $id) {
						$header_template = str_replace('<' . $id . '>', $parts[$id], $header_template);
						$body_template = str_replace('<' . $id . '>', $parts[$id], $body_template);
						$footer_template = str_replace('<' . $id . '>', $parts[$id], $footer_template);
					}
					
					if ($header_template) $header = $header_template;
					if ($body_template) $body = $body_template;
					if ($footer_template) $footer = $footer_template;
				}
			}
		} else {
			return $s;
		}

		$xhtml = TRUE;

		if ($head) {
			// Check RSS
			$rss = array();
			if (preg_match_all('#<link([^>]+?)>#iS', $head, $match)) {
				foreach($match[1] as $attrs) {
					if (preg_match('#type=("|\')application/(?:atom|rss)\+xml\\1#iS', $attrs)) {
						if (preg_match('#href=("|\')([^ <>"\']+)\\1#is', $attrs, $match2)) {
							$title = 'RSS';
							$url = $match2[2];
							if (preg_match('#title=("|\')([^<>"\']+)\\1#isS', $attrs, $match3)) {
								$title = $match3[2];
							}
							$rss[] = '<a href="'.$url.'">'.$title.'</a>';
						}
					}
				}
			}
			if ($rss) {
				$body = '<div style="font-size:0.9em">[ ' . join(' ', $rss) . ' ]</div>' . $body;
			}
			
			$_head = '<head>';
			if (preg_match('#<meta[^>]+http-equiv=("|\')Refresh\\1[^>]*>#iUS', $head, $match)) {
				$_head .= str_replace('/>', '>', $match[0]);
			} else if (preg_match('#<title[^>]*>.*</title>#isUS', $head, $match)) {
				$_head .= mb_convert_encoding($match[0], 'SJIS-win', $this->encode);
			}
			//if ($xhtml) $_head .= '<meta http-equiv="Content-Type" content="text/xhtml+xml; charset=Shift_JIS"/>';
			$_head .= '</head>';
			$head = $_head;
		}
		
		// Remove  xoopsCode buttons & Smilies buttons.
		if (strpos($body, '<div id="message_bbcode_buttons_pre"') !== FALSE) {
			$body = preg_replace('#<div id="message_bbcode_buttons_pre".+?/div>#sS', '', $body);
			$body = preg_replace('#<div id="message_bbcode_buttons_post".+?/div>#sS', '', $body);
			$body = preg_replace('#<input type="checkbox" id="message_bbcode_onoff".+?<br />#sS', '', $body);
			$body = preg_replace('#<input type="checkbox" id="d3f_post_advanced_options_onoff".+?>#sS', '', $body);
		}
		if (strpos($body, '<a name=\'moresmiley\'>') !== FALSE) {
			$body = preg_replace('#<a name=\'moresmiley\'>.+?<textarea#sS', '<textarea', $body);
			$body = preg_replace('#(?:<img |<a href="\#" )onclick=\'xoopsCodeSmilie\(.+?</a>\]#sS', '', $body);
		}
		
		if ($r->vars['ua']['name'] === 'DoCoMo') {
			$body = preg_replace('/<form[^>]+?user\.php[^>]+?>/isS', '$0<input type="hidden" name="guid" value="ON">', $body);
		}
		
		$r->Config_redirect = $this->k_tai_conf['redirect'];
		$r->Config_showImgHosts = $this->k_tai_conf['showImgHosts'];
		$r->Config_directLinkHosts = $this->k_tai_conf['directLinkHosts'];
		
		$r->Config_imageConvert = TRUE;
		$r->Config_rootPath = XOOPS_ROOT_PATH;
		$r->Config_rootUrl = XOOPS_URL;
		
		$r->contents['header'] = $header;
		$r->contents['body'] = $body;
		$r->contents['footer'] = $footer;
		
		if ($xhtml) {
			$r->outputMode = 'xhtml';
		}
		
		$r->doOptimize();
		
		if ($xhtml) {
			$s = '<?xml version="1.0" encoding="Shift_JIS"?><html>';
		} else {
			$s = '<html>';
		}
		$s .= $head . '<body>' . $r->outputBody . '</body></html>';
		
		$ctype = $r->getOutputContentType();

		$r = NULL;
		unset($r);
		
		header('Content-Type: ' . $ctype . '; charset=Shift_JIS');
		header('Content-Length: ' . strlen($s));
		header('Cache-Control: no-cache');
		
		return $s;
	}
	
	function emojiFilter ($str) {
		
		if ($str === '' || strpos($str, '<html') === FALSE) return $str;
		
		if (preg_match('/\(\((?:e|i|s):[0-9a-f]{4}\)\)/S', $str)) {
			if (! class_exists('MobilePictogramConverter')) {
				HypCommonFunc::loadClass('MobilePictogramConverter');
			}
			$mpc =& MobilePictogramConverter::factory_common();
			$mpc->setImagePath(XOOPS_URL . '/images/emoji');
			$mpc->setString($str, FALSE);
			$str = $mpc->autoConvertModKtai();
		}
		
		return $str;
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
		
		// 各機能のメインスイッチ (On = 1, Off = 0)
		$this->use_set_query_words   = 0; // 検索ワードを定数にセット
		$this->use_words_highlight   = 0; // 検索ワードをハイライト表示
		$this->use_proxy_check       = 0; // POST時プロキシチェックする
		$this->use_dependence_filter = 0; // 機種依存文字フィルター
		$this->use_post_spam_filter  = 0; // POST SPAM フィルター
		$this->post_spam_trap_set    = 0; // 無効フィールドのBot罠を自動で仕掛ける
		$this->use_k_tai_render      = 0; // 携帯対応レンダーを有効にする
				
		// 各種設定
		$this->configEncoding = 'EUC-JP'; // このファイルの文字コード
		
		$this->encodehint_word = 'ぷ';    // POSTエンコーディング判定用文字
		$this->encodehint_name = 'HypEncHint'; // POSTエンコーディング判定用 Filed name
		
		$this->msg_words_highlight = 'これらのキーワードがハイライトされています'; 
		
		$this->no_proxy_check  = '/^(127\.0\.0\.1|192\.168\.1\.)/'; // 除外IP
		$this->msg_proxy_check = 'Can not post from public proxy.';
		
		// POST SPAM
		$this->use_mail_notify = 1;       // POST SPAM メール通知 0:なし, 1:SPAM判定のみ, 2:すべて
		$this->post_spam_a   = 1;         // <a> タグ 1個あたりのポイント
		$this->post_spam_bb  = 1;         // BBリンク 1個あたりのポイント
		$this->post_spam_url = 1;         // URL      1個あたりのポイント
		$this->post_spam_host  = 31;      // Spam HOST の加算ポイント
		$this->post_spam_word  = 10;      // Spam Word の加算ポイント
		$this->post_spam_filed = 51;      // Spam 無効フィールドの加算ポイント
		$this->post_spam_trap  = '___url';// Spam 罠用無効フィールド名
		
		$this->post_spam_user  = 50;      // POST SPAM 閾値: ログインユーザー
		$this->post_spam_guest = 15;      // POST SPAM 閾値: ゲスト
		$this->post_spam_badip = 100;     // アクセス拒否リストへ登録する閾値
	
		// POST SPAM のポイント加算設定
		$this->post_spam_rules = array(
			// 同じURLが1行に3回 11pt
			"/((?:ht|f)tps?:\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+).+?\\1.+?\\1/i" => 11,
			
			// 65文字以上の英数文字のみで構成されている 15pt
			// '/^[\x00-\x7f\s]{65,}$/' => 15,
			
			// 無効な文字コードがある 31pt
			'/[\x00-\x08\x11-\x12\x14-\x1f\x7f\xff]+/' => 31
		);
		
		// 無効なフィールド定義
		$this->ignore_fileds = array(
			// 'url' => array('newbb/post.php', 'comment_post.php'),
		);
		
		// 検索ワード定数名
		$this->q_word  = 'XOOPS_QUERY_WORD';         // 検索ワード
		$this->q_word2 = 'XOOPS_QUERY_WORD2';        // 検索ワード分かち書き
		$this->se_name = 'XOOPS_SEARCH_ENGINE_NAME'; // 検索元名
	
		// KAKASI での分かち書き結果のキャッシュ先
		$this->kakasi_cache_dir = XOOPS_ROOT_PATH.'/cache2/kakasi/';
		
		/////////////////////////
		// 携帯対応レンダー設定
		
		// 携帯端末判定用 UA 正規表現
		$this->k_tai_conf['ua_regex'] = '#(?:SoftBank|Vodafone|J-PHONE|DoCoMo|UP\.Browser)#';
		
		// HTML再構築用タグ設定
		$this->k_tai_conf['rebuilds'] = array(
			'headerlogo'    => array( 'above' => '<center>',
			                          'below' => '</center>'),
			'headerbar'     => array( 'above' => '<hr>',
			                          'below' => ''),
			'breadcrumbs'   => array( 'above' => '',
			                          'below' => ''),
			'leftcolumn'    => array( 'above' => '<hr>',
			                          'below' => ''),
			'centerCcolumn' => array( 'above' => '<hr>',
			                          'below' => ''),
			'centerLcolumn' => array( 'above' => '',
			                          'below' => ''),
			'centerRcolumn' => array( 'above' => '',
			                          'below' => ''),
			'content'       => array( 'above' => '<hr>',
			                          'below' => ''),
			'rightcolumn'   => array( 'above' => '<hr>',
			                          'below' => ''),
			'footerbar'     => array( 'above' => '',
			                          'below' => ''),
			'easylogin'     => array( 'above' => '<div style="text-align:center;font-size:0.9em">[ ',
			                          'below' => ' ]</div>'),
		);
		
		// 携帯用テーマセット
		$this->k_tai_conf['themeSet'] = 'ktai_default';

		// 使用テンプレート
		$this->k_tai_conf['template'] = 'default';
		
		// インラインイメージを表示するホスト名(後方一致)
		$this->k_tai_conf['showImgHosts'] = array('amazon.com', 'yimg.jp', 'yimg.com', 'ad.jp.ap.valuecommerce.com', 'ad.jp.ap.valuecommerce.com', 'ba.afl.rakuten.co.jp', 'assoc-amazon.jp', 'ad.linksynergy.com');
		
		// リダイレクトスクリプトを経由しないホスト名(後方一致)
		$this->k_tai_conf['directLinkHosts'] = array('amazon.co.jp', 'ck.jp.ap.valuecommerce.com');

		// 外部リンク用リダイレクトスクリプト
		$this->k_tai_conf['redirect'] = XOOPS_URL . '/class/hyp_common/redirect.php?l=';
		
		// Easy login を有効にする
		$this->k_tai_conf['easyLogin'] = 1;
		// Easy login で IP アドレス帯域をチェックしない
		$this->k_tai_conf['noCheckIpRange'] = 0;
		// リンクメッセージ
		$this->k_tai_conf['msg']['easylogin'] = '簡単ログイン';
		$this->k_tai_conf['msg']['logout'] = 'ログアウト';
		
		// 携帯対応レンダー設定 以上
		/////////////////////////////

		
		///////////////////////////////
		// 以下は変更してはいけません。
		parent::HypCommonPreLoadBase($controller);

	}
}
}
?>