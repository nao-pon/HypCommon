<?php
// $Id: hyp_get_engine.php,v 1.3 2007/05/17 02:48:20 nao-pon Exp $
// HypGetQueryWord Class by nao-pon http://hypweb.net
////////////////////////////////////////////////

if( ! class_exists( 'HypGetQueryWord' ) )
{

include_once(dirname(__FILE__)."/hyp_common_func.php");

class HypGetQueryWord
{
	function set_constants($qw="HYP_QUERY_WORD",$qw2="HYP_QUERY_WORD2",$en="HYP_SEARCH_ENGINE_NAME",$tmpdir="")
	{
		list($getengine_name,$getengine_query,$getengine_query2) = HypGetQueryWord::se_getengine($tmpdir);
		define($qw , $getengine_query);
		define($qw2, $getengine_query2);
		define($en , $getengine_name);
	}

	function se_getengine($tmpdir="")
	{
		$_query = array_merge($_POST,$_GET);
		$_query = HypCommonFunc::stripslashes_gpc($_query);
		
		$query = (isset($_query['query']))? $_query['query'] : "";
		if (!$query) $query = (isset($_query['word']))? $_query['word'] : "";
		if (!$query) $query = (isset($_query['mes']))? $_query['mes'] : "";
		
		$se_name=""; //Default
		
		if (!$query)
		{
			$reffer="";
			if(isset($_SERVER['HTTP_REFERER'])) $reffer=$_SERVER['HTTP_REFERER'];
			
			if ($reffer)
			{
				if(substr($reffer,-1)=="/") $reffer=substr($reffer,0,strlen($reffer)-1);
				
				$se=file(dirname(__FILE__)."/hyp_search_engines.dat");
				$found=0;
				
				foreach($se as $linea)
				{
					$linea=trim($linea);
					if((substr($linea,0,2)!="//") && $linea!="")
					{
						//$reffer=strtolower($reffer);
						$tmp=explode("|",$linea);
						if(HypGetQueryWord::se_search($reffer,$tmp[1]))
						if(strpos($reffer,chop($tmp[2]))!==false)
						{
							$se_name=$tmp[0];
							$found=1;
							break;
						}
					}
				}

				if($found==1)
				{
					$vars=explode("?",$reffer);
					if(count($vars)>1)
					{
						$query=explode(chop($tmp[2]),$vars[1]);
						
						if(count($query)>1)
						{
							$query = explode("&",$query[1]);
							$query = $query[0];
						}	
					}
				}
			}
		}
		//デコード関数 by nao-pon
		$enc = (isset($_GET['encode_hint'])) ? mb_detect_encoding ($_GET['encode_hint']) : "AUTO";
		$query = HypGetQueryWord::se_urldecode_euc($query,$enc);

		//Googleのキャッシュからの場合
		$query = preg_replace("/^cache\:[^ ]+ /",'',$query);
		
		if (function_exists('mb_convert_kana')) $query = mb_convert_kana($query,"KVas",'EUC-JP');
		
		//$query = preg_replace("/( |\+|,|、|・)+/"," ",$query);
		
		$query2 = $query;
		if ($query2)
		{
			// 分かち書き
			include_once(dirname(__FILE__)."/hyp_kakasi.php");
			$kakasi = new Hyp_KAKASHI();
			if ($tmpdir && is_writable($tmpdir))
			{
				$kakasi->tmp_dir = $tmpdir;
			}
			$kakasi->get_wakatigaki($query2);
		}
		
		return array($se_name,$query,$query2);
	}

	function se_search($string,$mask){ 
		static $in=array('.', '^', '$', '{', '}', '(', ')', '[', ']', '+', '*', '?'); 
		static $out=array('\\.', '\\^', '\\$', '\\{', '\\}', '\\(', '\\)', '\\[', '\\]', '\\+', '.*', '.'); 
		$mask='^'.str_replace($in,$out,$mask).'$'; 
		return(ereg($mask,$string)); 
	}  

	// escuni2euc - convert "IE escaped Unicode" to "EUC-JP"
	//
	//			Programmed : Ishigaki Kazuhito ishigaki@factory.gr.jp

	// convert "single IE escaped unicode" to "UTF-8"
	// uni2utf8("%u65E5") returns "\xE5\xB1\x80"
	function uni2utf8($uniescape)
	{
		$c = "";
		
		$n = intval(substr($uniescape, -4), 16);
		if ($n < 0x7F) {// 0000-007F
			$c .= chr($n);
		} elseif ($n < 0x800) {// 0080-0800
			$c .= chr(0xC0 | ($n / 64));
			$c .= chr(0x80 | ($n % 64));
		} else {				// 0800-FFFF
			$c .= chr(0xE0 | (($n / 64) / 64));
			$c .= chr(0x80 | (($n / 64) % 64));
			$c .= chr(0x80 | ($n % 64));
		}
		return $c;
	}

	// Convert "IE escaped Unicode" to "EUC-JP"
	// escuni2euc("%u65E5%u672C%u8A9E123") returns "日本語"
	function se_escuni2euc($escunistr)
	{
		$eucstr = "";
		
		while(eregi("(.*)(%u[0-9A-F][0-9A-F][0-9A-F][0-9A-F])(.*)$", $escunistr, $fragment)) {
			$eucstr = mb_convert_encoding(HypGetQueryWord::uni2utf8($fragment[2]).$fragment[3], 'EUC-JP', 'UTF-8').$eucstr;
			$escunistr = $fragment[1];
		}
		return $fragment[1].$eucstr;
	}

	// 日本語(EUC-JP)対応のurldecode by nao-pon
	function se_urldecode_euc($str,$enc="AUTO"){
		if (eregi("%u[0-9A-F][0-9A-F][0-9A-F][0-9A-F]",$str)){
			$query = HypGetQueryWord::se_escuni2euc(urldecode($str));//for IE unicode+urlencoding
		} else {
			$query = urldecode($str);
			$query = mb_convert_encoding($query,"EUC-JP",$enc);
		}
		return $query;
	}

	function word_highlight($body,$q_word)
	{
		// Short URLs
		//simplify_urls($body);
		
		// 外部リンクの場合 class="ext" を付加
		$body = preg_replace("/(<a[^>]+?href=(?:\"|')?(?!https?:\/\/".$_SERVER["HTTP_HOST"].")http[^>]+)>/iS","$1 class=\"ext\">",$body);
		
		if (!$q_word || !$body) return $body;
		
		if (function_exists("xoops_gethandler"))
		{
			$config_handler =& xoops_gethandler('config');
			$xoopsConfigSearch =& $config_handler->getConfigsByCat(XOOPS_CONF_SEARCH);
		}
		else
			$xoopsConfigSearch['keyword_min'] = 3;
		
		//検索語ハイライト
		$search_word = '';
		//$words = array_flip(preg_split('/\s+/',$q_word,-1,PREG_SPLIT_NO_EMPTY));
		$words = array_flip(HypCommonFunc::phrase_split($q_word));
		$keys = array();
		$cnt = 0;
		foreach ($words as $word=>$id)
		{
			if (strlen($word) < $xoopsConfigSearch['keyword_min']) continue;
			$keys[$word] = strlen($word);
			$cnt++;
			if ($cnt > 10) break;
		}
		//arsort($keys,SORT_NUMERIC);
		$keys = HypGetQueryWord::get_search_words(array_keys($keys));
		$id = 0;
		foreach ($keys as $key=>$pattern)
		{
			$s_key = htmlspecialchars($key);
			$search_word .= " <strong class=\"word$id\">$s_key</strong>";
			$pattern = ($s_key{0} == '&') ?
				"/(<head.*?<\/head>|<textarea.*?<\/textarea>|<script.*?<\/script>|<[^>]*>)|($pattern)/sS" :
				"/(<head.*?<\/head>|<textarea.*?<\/textarea>|<script.*?<\/script>|<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[0-9a-zA-Z]+);)|($pattern)/sS";
			$body = preg_replace_callback($pattern,
				create_function('$arr',
					'return $arr[1] ? $arr[1] : "<strong class=\"word'.$id.'\">{$arr[2]}</strong>";'),$body);
			$id++;
		}
		//return "<div class=\"xoopsQuote\">これらのキーワードがハイライトされています：$search_word</div>\n$body";
		return str_replace('<!--HIGHLIGHT_SEARCH_WORD-->',"<div class=\"xoopsQuote\">これらのキーワードがハイライトされています：$search_word</div>",$body);
	}
	// 検索語を展開する
	function get_search_words($words,$special=FALSE)
	{
		global $xoopsConfig;
		
		$retval = array();
		// Perlメモ - 正しくパターンマッチさせる
		// http://www.din.or.jp/~ohzaki/perl.htm#JP_Match
		$eucpre = $eucpost = '';
		if ($xoopsConfig['language'] == "japanese")
		{
			$eucpre = '(?<!\x8F)';
			// # JIS X 0208 が 0文字以上続いて # ASCII, SS2, SS3 または終端
			$eucpost = '(?=(?:[\xA1-\xFE][\xA1-\xFE])*(?:[\x00-\x7F\x8E\x8F]|\z))';
		}
		// $special : htmlspecialchars()を通すか
		$quote_func = create_function('$str',$special ?
			'return preg_quote($str,"/");' :
			'return preg_quote(htmlspecialchars($str),"/");'
		);
		// LANG=='ja'で、mb_convert_kanaが使える場合はmb_convert_kanaを使用
		$convert_kana = create_function('$str,$option',
			($xoopsConfig['language'] == "japanese" and function_exists('mb_convert_kana')) ?
				'return mb_convert_kana($str,$option);' : 'return $str;'
		);
		
		foreach ($words as $word)
		{
			// 英数字は半角,カタカナは全角,ひらがなはカタカナに
			$word_zk = $convert_kana($word,'aKCV');
			$chars = array();
			for ($pos = 0; $pos < mb_strlen($word_zk);$pos++)
			{
				$char = mb_substr($word_zk,$pos,1);
				$arr = array($quote_func($char));
				if (strlen($char) == 1) // 英数字
				{
					$_char = strtoupper($char); // 大文字
					$arr[] = $quote_func($_char);
					$arr[] = $quote_func($convert_kana($_char,"A")); // 全角
					$_char = strtolower($char); // 小文字
					$arr[] = $quote_func($_char);
					$arr[] = $quote_func($convert_kana($_char,"A")); // 全角
				}
				else // マルチバイト文字
				{
					$arr[] = $quote_func($convert_kana($char,"c")); // ひらがな
					$arr[] = $quote_func($convert_kana($char,"k")); // 半角カタカナ
				}
				$chars[] = '(?:'.join('|',array_unique($arr)).')';
			}
			$retval[$word] = $eucpre.join('',$chars).$eucpost;
		}
		return $retval;
	}
}
}

if (!function_exists('xoops_word_highlight'))
{
function xoops_word_highlight($body,$q_word)
{
	return HypGetQueryWord::word_highlight($body,$q_word);
}
}
?>