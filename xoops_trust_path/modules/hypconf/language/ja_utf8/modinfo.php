<?php
if( defined( 'FOR_XOOPS_LANG_CHECKER' ) ) $mydirname = 'hypconf' ;
$constpref = '_MI_' . strtoupper( $mydirname ) ;

if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || ! defined( $constpref.'_LOADED' ) ) {

define($constpref.'_LOADED' , 1 ) ;

// The name of this module
define($constpref.'_NAME', 'HypCommonの設定');

// A brief description of this module
define($constpref.'_DESC', 'HypCommonFunc 関連の設定');

define($constpref.'_MSG_SAVED' , '設定を保存しました。');
define($constpref.'_COUSTOM_BLOCK' , 'カスタムブロック');
define($constpref.'_NOT_SPECIFY' , '指定しない');

// admin menus
define($constpref.'_ADMENU_CONTENTSADMIN' , '設定の確認');
define($constpref.'_ADMENU_MAIN_SWITCH' , 'メイン スイッチ');
define($constpref.'_ADMENU_K_TAI_CONF' , 'モバイル対応の設定');
define($constpref.'_ADMENU_MYBLOCKSADMIN' , 'アクセス権限設定');
define($constpref.'_ADMENU_XPWIKI_RENDER', 'xpWikiレンダラー設定');
define($constpref.'_ADMENU_SPAM_BLOCK', 'スパム防止設定');
define($constpref.'_ADMENU_MISC', 'その他の設定');

// notice error
define($constpref.'_MAIN_SWITCH_NOT_ENABLE', 'メインスイッチで「<b>$1</b>」が無効になっています。ここでの設定を機能させるためには、メインスイッチで「<b>$1</b>」を有効にしてください。');
define($constpref.'_THERE_ARE_NO_CONFIG' , '現在設定されている項目はありません。すべて規定値が適用されます。');
define($constpref.'_ERR_KEEP_ALIVE' , 'ログインを継続することができません。データを送信する前に再度ログインして下さい。');

// main_switch
define($constpref.'_USE_SET_QUERY_WORDS', '検索ワードを定数にセット');
define($constpref.'_USE_SET_QUERY_WORDS_DESC', '');
define($constpref.'_USE_WORDS_HIGHLIGHT', '検索ワードをハイライト表示');
define($constpref.'_USE_WORDS_HIGHLIGHT_DESC', '「検索ワードを定数にセット」が有効の場合に機能します。<br />ハイライト一覧が &lt;body&gt; タグ直下に挿入されます。任意の場所に挿入したい場合は、テーマ内に &lt;!--HIGHLIGHT_SEARCH_WORD--&gt; を記述するとその部分に挿入されます。');
define($constpref.'_USE_PROXY_CHECK', '投稿時にプロキシチェックをする');
define($constpref.'_USE_PROXY_CHECK_DESC', '');
define($constpref.'_INPUT_FILTER_STRENGTH', 'GET, POST 制御文字フィルター強度');
define($constpref.'_INPUT_FILTER_STRENGTH_DESC', '');
define($constpref.'_USE_DEPENDENCE_FILTER', '機種依存文字フィルター');
define($constpref.'_USE_DEPENDENCE_FILTER_DESC', '');
define($constpref.'_USE_CSRF_PROTECT', 'CSRF プロテクション');
define($constpref.'_USE_CSRF_PROTECT_DESC', 'すべての POST リクエストに対し、セッション毎固定トークン方式の CSRF 防御機能を有効にします。');
define($constpref.'_USE_POST_SPAM_FILTER', 'POST SPAM フィルター');
define($constpref.'_USE_POST_SPAM_FILTER_DESC', '');
define($constpref.'_POST_SPAM_TRAP_SET', 'ハニーポット(無効フィールドのBot罠)を自動で仕掛ける');
define($constpref.'_POST_SPAM_TRAP_SET_DESC', '');
define($constpref.'_USE_K_TAI_RENDER', 'モバイル対応機能を有効にする');
define($constpref.'_USE_K_TAI_RENDER_DESC', '');
define($constpref.'_USE_SMART_REDIRECT', 'スマートリダイレクトを有効にする');
define($constpref.'_USE_SMART_REDIRECT_DESC', 'リダイレクトメッセージ表示のためのページ変遷をなくし、メッセージをポップアップ表示します。');
define($constpref.'_USE_KEEP_ALIVE', 'キープアライブ機能を有効にする');
define($constpref.'_USE_KEEP_ALIVE_DESC', 'JavaScript (jQuery) を利用し、一定間隔でサーバーにアクセスすることで、セッションタイムアウトによるログアウトを防止します。(jQuery 必須)');
// main_switch value
define($constpref.'_INPUT_FILTER_STRENGTH_0', '制御文字の内 NULL 以外は許可');
define($constpref.'_INPUT_FILTER_STRENGTH_1', '制御文字の内 SoftBankの絵文字と\t,\r,\n は許可');
define($constpref.'_INPUT_FILTER_STRENGTH_2', '制御文字の内 \t,\r,\n のみ許可');

// k_tai_render
define($constpref.'_UA_REGEX', 'User agent');
define($constpref.'_UA_REGEX_DESC', 'モバイル対応機能で処理する User agent を PCRE(Perl互換)正規表現で記述。');
define($constpref.'_THEMESET', 'XOOPSテーマ');
define($constpref.'_THEMESET_DESC', 'モバイル対応時に使用するテーマ名(指定しない場合はテーマの切り替えをしません)');
define($constpref.'_TEMPLATESET', 'DBテンプレートセット');
define($constpref.'_TEMPLATESET_DESC', 'モバイル対応時に使用するDBテンプレートセット名(指定しない場合はデフォルトテンプレートセットが使用されます)');
define($constpref.'_TEMPLATE', '携帯対応レンダラーテンプレート');
define($constpref.'_TEMPLATE_DESC', '"'.XOOPS_TRUST_PATH.'/class/hyp_common/ktairender/templates" ディレクトリに配置した携帯対応レンダラー用のテンプレートディレクトリ名');
define($constpref.'_JQM_PROFILES', 'jQuery Mobile');
define($constpref.'_JQM_PROFILES_DESC', 'jQuery Mobile を適用するプロファイル名をカンマ区切りで記述。プロファイル名は携帯対応レンダラーで定義されていて、docomo, au, softbank, willcom, android, iphone, ipod, ipad, windows mobile などが使用できます。');
define($constpref.'_THEMESETS_JQM', 'XOOPSテーマ(jqm)');
define($constpref.'_THEMESETS_JQM_DESC', 'jQuery Mobile 適用時のテーマ名(指定しない場合は、モバイル対応時のテーマ名が使用されます)');
define($constpref.'_TEMPLATESETS_JQM', 'DBテンプレートセット(jqm)');
define($constpref.'_TEMPLATESETS_JQM_DESC', 'jQuery Mobile 適用時のDBテンプレートセット名(指定しない場合は、モバイル対応時のテーマ名が使用されます)');
define($constpref.'_TEMPLATE_JQM', '携帯対応レンダラーテンプレート(jqm)');
define($constpref.'_TEMPLATE_JQM_DESC', 'jQuery Mobile 適用時の "'.XOOPS_TRUST_PATH.'/class/hyp_common/ktairender/templates" ディレクトリに配置した携帯対応レンダラー用のテンプレートディレクトリ名');
define($constpref.'_JQM_THEME', 'jqmテーマ');
define($constpref.'_JQM_THEME_DESC', 'ページ全体の jQuery Mobile のテーマ。標準では a, b, c, d, e が有効です。');
define($constpref.'_JQM_THEME_CONTENT', 'メイン部');
define($constpref.'_JQM_THEME_CONTENT_DESC', 'メインコンテンツに適用する jQuery Mobile のテーマ。');
define($constpref.'_JQM_THEME_BLOCK', 'ブロック部');
define($constpref.'_JQM_THEME_BLOCK_DESC', 'ブロックに適用する jQuery Mobile のテーマ。');
define($constpref.'_JQM_CSS', 'jqm 追加 CSS');
define($constpref.'_JQM_CSS_DESC', 'jQuery Mobile 用の追加の CSS を記述。<br />テーマ用 CSS の作成は <a href="http://jquerymobile.com/themeroller/" target="_blank">ThemeRoller | jQuery Mobile</a> や <a href="http://as001.productscape.com/themeroller.cfm" target="_blank">jQuery Mobile Themeroller</a> などを利用すると簡単です。');
define($constpref.'_JQM_REMOVE_FLASH' , 'Flash除去(jqm)');
define($constpref.'_JQM_REMOVE_FLASH_DESC' , 'jQuery Mobile 適用時に Flash を除去するプロファイル名をカンマ区切りで記述。プロファイル名は携帯対応レンダラーで定義されていて、docomo, au, softbank, willcom, android, iphone, ipod, ipad, windows mobile などが使用できます。');
define($constpref.'_JQM_RESOLVE_TABLE' , '入れ子テーブル展開(jqm)');
define($constpref.'_JQM_RESOLVE_TABLE_DESC' , 'jQuery Mobile 適用時に入れ子になっているテーブルを展開する。');
define($constpref.'_JQM_IMAGE_CONVERT' , '最大画像幅[px](jqm)');
define($constpref.'_JQM_IMAGE_CONVERT_DESC' , 'jQuery Mobile 適用時に画像を指定幅[px]サイズまで縮小する。「0」で無効になります。');
define($constpref.'_DISABLEDBLOCKIDS', '無効ブロック');
define($constpref.'_DISABLEDBLOCKIDS_DESC', 'モバイルアクセス時に選択されたブロックを無効にします。');
define($constpref.'_LIMITEDBLOCKIDS', '有効ブロック');
define($constpref.'_LIMITEDBLOCKIDS_DESC', 'モバイルアクセス時に選択されたブロックを有効にします。一つでも選択すると非選択のブロックはすべて無効になります。何も指定しないとフィルタリングはされません。');
define($constpref.'_SHOWBLOCKIDS', '展開ブロック');
define($constpref.'_SHOWBLOCKIDS_DESC', 'モバイルアクセス時に常に表示するブロック。<br />jQuery Mobile 使用時は折りたたみ表示が初期状態で展開されます。<br />従来の携帯表示では選択したブロックは表示され、非選択のブロックはそのブロックを表示するためのリンクになります。');
define($constpref.'_USEJQMBLOCKCTL', 'jqm専用ブロックコントロールの使用');
define($constpref.'_USEJQMBLOCKCTL_DESC', '以下のブロックコントロールを jQuery Mobile 使用時に適用します。<br />「いいえ」を選択すると上記のモバイルアクセス時のブロックコントロールが適用されます。');
define($constpref.'_DISABLEDBLOCKIDS_JQM', '無効ブロック(jqm)');
define($constpref.'_DISABLEDBLOCKIDS_JQM_DESC', 'jQuery Mobile 使用時に選択されたブロックを無効にします。');
define($constpref.'_LIMITEDBLOCKIDS_JQM', '有効ブロック(jqm)');
define($constpref.'_LIMITEDBLOCKIDS_JQM_DESC', 'jQuery Mobile 使用時に選択されたブロックを有効にします。一つでも選択すると非選択のブロックはすべて無効になります。何も指定しないとフィルタリングはされません。');
define($constpref.'_SHOWBLOCKIDS_JQM', '展開ブロック(jqm)');
define($constpref.'_SHOWBLOCKIDS_JQM_DESC', 'jQuery Mobile 使用時に折りたたみ表示を初期状態で展開するブロック。');

// xpwiki_render
define($constpref.'_XPWIKI_RENDER_NONE', '使用しない');
define($constpref.'_XPWIKI_RENDER_DIRNAME', 'xpWiki レンダラー');
define($constpref.'_XPWIKI_RENDER_DIRNAME_DESC', 'サイトワイド xpWiki レンダラー機能で使用する xpWiki を指定してください。<br />サイトワイドで xpWiki レンダラー機能を使用すると、ほとんどのモジュールで xpWiki(PukiWiki)の記法が使えるようになります。');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER', 'サイトワイド Wiki ヘルパー');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_0', 'いいえ');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_1', 'はい (すべて)');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_2', 'はい (Class 名に "wikihelper" を持つエリアのみ)');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_DESC', '「はい」を選択するとテキストエリアが機能拡張され Wiki ヘルパー及びリッチエディタをサイトワイドで使用できるようになります。');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_ADMIN', 'Wiki ヘルパー(管理画面)');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_ADMIN_DESC', '管理画面でも Wiki ヘルパー及びリッチエディタを利用します。');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_BBCODE', 'Wiki ヘルパー(BBCodeエディタ)');
define($constpref.'_XPWIKI_RENDER_USE_WIKIHELPER_BBCODE_DESC', 'XCL 2.2 以降の xoops_dhtmltarea(Smartyプラグイン) で editor=bbcode としたテキストエリアにも適用します。');
define($constpref.'_XPWIKI_RENDER_NOTUSE_WIKIHELPER_MODULES', 'Wiki ヘルパー無効');
define($constpref.'_XPWIKI_RENDER_NOTUSE_WIKIHELPER_MODULES_DESC', 'サイトワイド Wiki ヘルパーを無効にするモジュールを選択して下さい。');
define($constpref.'_REQUERE_XCL', 'この設定は XOOPS Cube Legacy システムでのみ利用可能です。');
define($constpref.'_XCL_REQUERE_2_2_1', 'この機能は、XOOPS Cube Legacy 2.2.1 以降で有効になります。ただし、独自に "class/module.textsanitizer.php" を書き換えてこの機能を有効にしている場合は、このメッセージは無視して下さい。');
define($constpref.'_TEXTFILTER_ALREADY_EXISTS', 'preload ディレクトリに "SetupHyp_TextFilter.class.php" があります。それを削除するまでここでの設定は反映されません。');

// spam_block
define($constpref.'_USE_MAIL_NOTIFY', 'POST SPAM メール通知 0:なし, 1:SPAM判定のみ, 2:すべて');
define($constpref.'_USE_MAIL_NOTIFY_DESC', '');
define($constpref.'_SEND_MAIL_INTERVAL', 'まとめ送りのインターバル(分) (0 で随時送信)');
define($constpref.'_SEND_MAIL_INTERVAL_DESC', '');
define($constpref.'_POST_SPAM_A', '&lt;a&gt; タグ 1個あたりのポイント');
define($constpref.'_POST_SPAM_A_DESC', '');
define($constpref.'_POST_SPAM_BB', 'BBリンク 1個あたりのポイント');
define($constpref.'_POST_SPAM_BB_DESC', '');
define($constpref.'_POST_SPAM_URL', 'URL 1個あたりのポイント');
define($constpref.'_POST_SPAM_URL_DESC', '');
define($constpref.'_POST_SPAM_UNHOST', '不明 HOST の加算ポイント');
define($constpref.'_POST_SPAM_UNHOST_DESC', '');
define($constpref.'_POST_SPAM_HOST', 'Spam HOST の加算ポイント');
define($constpref.'_POST_SPAM_HOST_DESC', '');
define($constpref.'_POST_SPAM_WORD', 'Spam Word の加算ポイント');
define($constpref.'_POST_SPAM_WORD_DESC', '');
define($constpref.'_POST_SPAM_FILED', 'Spam 罠用無効フィールド入力時の加算ポイント');
define($constpref.'_POST_SPAM_FILED_DESC', '');
define($constpref.'_POST_SPAM_TRAP', 'Spam 罠用無効フィールド名');
define($constpref.'_POST_SPAM_TRAP_DESC', '');
define($constpref.'_POST_SPAM_USER', 'Spam 判定の閾値: ログインユーザー');
define($constpref.'_POST_SPAM_USER_DESC', '');
define($constpref.'_POST_SPAM_GUEST', 'Spam 判定の閾値: ゲスト');
define($constpref.'_POST_SPAM_GUEST_DESC', '');
define($constpref.'_POST_SPAM_BADIP', 'アクセス拒否リストへ登録する閾値');
define($constpref.'_POST_SPAM_BADIP_DESC', '');
define($constpref.'_POST_SPAM_BADIP_TTL', '<b>Protector連携</b>: アクセス拒否の拒否継続時間[秒] (0:無期限, null:Protector不使用)');
define($constpref.'_POST_SPAM_BADIP_TTL_DESC', '');
define($constpref.'_POST_SPAM_BADIP_FOREVER', '<b>Protector連携</b>: 無期限アクセス拒否閾値');
define($constpref.'_POST_SPAM_BADIP_FOREVER_DESC', '');
define($constpref.'_POST_SPAM_BADIP_TTL0', '<b>Protector連携</b>: 無期限アクセス拒否継続時間[秒] (0:本当に無期限)');
define($constpref.'_POST_SPAM_BADIP_TTL0_DESC', '');
define($constpref.'_POST_SPAM_SITE_AUTO_REGIST', 'spamsites.conf.dat 自動登録');
define($constpref.'_POST_SPAM_SITE_AUTO_REGIST_DESC', 'Spam 罠用無効フィールドに入力された URL を spamsites.conf.dat に自動登録する。');
define($constpref.'_POST_SPAM_SAFE_URL', '自動登録しない正規表現パターン(デリミタは含めない)');
define($constpref.'_POST_SPAM_SAFE_URL_DESC', 'デリミタは "#" が使用されます。ここで指定したパターンと当サイトのホスト名にマッチする場合は登録されません。');
define($constpref.'_POST_SPAM_SITES', 'spamsites.conf.dat　の編集');
define($constpref.'_POST_SPAM_SITES_DESC', 'サーバー上の実パス: ' . XOOPS_TRUST_PATH . '/class/hyp_common/config/spamsites.conf.dat<br />ここで、データを更新すると下記の「システム上に設定されている spamsites.dat」と重複するエントリは除外されます。<br />データが更新できない場合は、上記ファイルに書き込み権限を与えてください。');
define($constpref.'_POST_SPAM_SITES_SYSTEM', '<h4>システム上に設定されている spamsites.dat の確認</h4><p>サーバー上の実パス: %s</p>');

// misc
define($constpref.'_MISC_HEAD_LAST_TAG', '&lt;head&gt;内の最後に挿入するタグ');
define($constpref.'_MISC_HEAD_LAST_TAG_DESC', 'ここに記述した内容が &lt;/head&gt; の直前に挿入されます。(jQuery Mobile を利用しない携帯対応時を除く)<br />&lt;meta&gt;, &lt;script&gt;, &lt;link&gt; タグなどが記述できます。<br />&lt;{$xoops_url}&gt; または [XOOPS_URL] は "'.XOOPS_URL.'" に置換されます。');
define($constpref.'_XOOPSTPL_PLUGINS_DIR', 'Smartyプラグインディレクトリ(優先順)');
define($constpref.'_XOOPSTPL_PLUGINS_DIR_DESC', 'Smartyプラグインが保存されているディレクトリを指定します。上から優先順に行単位で記述してください。(同名のファイルが存在した場合上のディレクトリのファイルが使用されます)<br />何も記入せずに保存するとXOOPSの初期値に戻ります。<br />独自プラグインを管理したい場合は、一番上に '.XOOPS_TRUST_PATH.'/lib/my_smartyplugins などとして、そのディレクトリに独自プラグインを置くと最優先で使用されます。<br />※ 初期状態で表示されているディレクトリについて専門知識がない場合は、優先順位も含めて変更されないことをお勧めします。');

}
