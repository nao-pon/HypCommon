<?php
/*
 * Created on 2011/11/17 by nao-pon http://xoops.hypweb.net/
 */

class Hyp_TextFilterAbstract extends Legacy_TextFilter
{
    var $hypInternalTags = array('email', 'siteimg', 'img', 'siteurl', 'url');
    var $hypEscTags      = array('quote', 'color', 'font', 'size', 'b', 'c', 'd', 'i', 'u');
    var $hypBypassTags   = array('fig');

	// PHP 4 style constructor for compat
	public function Hyp_TextFilterAbstract() {
		self::__construct();
	}
	public function __construct() {
        parent::Legacy_TextFilter();
        $this->mMakeXCodeConvertTable->add('Hyp_TextFilter::sMakeXCodeConvertTable', XCUBE_DELEGATE_PRIORITY_3);
        $this->mMakeXCodeConvertTable->add(array(& $this, 'getXcodeBBcode'), XCUBE_DELEGATE_PRIORITY_FINAL);
    }
	
    // Over write
    public function getInstance(&$instance) {
        if (empty($instance)) {
            $instance = new Hyp_TextFilter();
        }
    }

    // Over write
    function toShowTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1, $x2comat = false, $cache = 1) {
        if ($html != 1) {
            $text = $this->renderWikistyle($text, $html, $smiley, $xcode, $image, $br, $cache);
        } else {
            $text = $this->preConvertXCode($text, $xcode);
            $text = $this->makeClickable($text);
            if ($smiley != 0) $text = $this->smiley($text);
        }
        if ($xcode != 0) $text = $this->convertXCode($text, $image);
        if (!$html) {
            $text = $this->renderWikistyleFinsher($text);
        }
        if ($html && $br != 0) $text = $this->nl2Br($text, $html);
        if ($html) $text = $this->postConvertXCode($text, $xcode, $image);
        return $text;
    }

	// Over write
	function toPreviewTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1, $x2comat=false) {
		return $this->toShowTarea($text, $html, $smiley, $xcode, $image, $br, $x2comat, 0);
	}

	// Over write "makeXCodeConvertTable"
	public static function sMakeXCodeConvertTable(& $patterns, & $replacements) {
		if ($key = array_search('/\[quote\]/sU', $patterns)) {
			$replacements[0][$key] = $replacements[1][$key] = '<div class="paragraph">'._QUOTEC.'<div class="xoopsQuote"><blockquote>';
		}
		if ($key = array_search('/\[\/quote\]/sU', $patterns)) {
			$replacements[0][$key] = $replacements[1][$key] = '</blockquote></div></div>';
		}
		$patterns[] = "/\[quote sitecite=([^\"'<>]*)\]/sU";
		$replacements[0][] = $replacements[1][] = '<div class="paragraph">'._QUOTEC.'<div class="xoopsQuote"><blockquote cite="'.XOOPS_URL.'/\\1">';
    }

    // Original function
    function getXcodeBBcode($patterns, $replacements) {
    	$_arr = $this->hypBypassTags;
    	foreach($patterns as $_pat) {
    		if (preg_match('#^/\\\\\[([a-zA-Z0-9_-]+)\b#', $_pat, $_match)) {
   				$_arr[] = $_match[1];
    		}
    	}
    	$this->hypBypassTags = array_unique(array_diff($_arr, $this->hypEscTags, $this->hypInternalTags));
    }

    // Original function
    function renderWiki_getEscTags () {
        rsort($this->hypEscTags);
        return $this->hypEscTags;
    }

    // Original function
    function renderWiki_getBypassTags () {
        rsort($this->hypBypassTags);
        return $this->hypBypassTags;
    }

    // Original function
    function &renderWikistyle($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1, $use_cache = 0)
    {
        static $pat = array();
        static $rep = array();
        static $chkc = array();
        static $patc = array();
        static $repc = array();

        $className = get_class($this);

        $br = ($br)? 1 : 0;
        $use_cache = ($use_cache)? 1 : 0;
        $smiley = ($smiley)? 1 : 0;
        $image = ($image)? 1 : 0;

        // xpWiki
        if (! class_exists('XpWiki')) {
            include XOOPS_TRUST_PATH . '/modules/xpwiki/include.php';
        }

        $render = XpWiki::getSingleton(XPWIKI_RENDERER_DIR);

        // pukiwiki.ini.php setting
        $render->setIniRoot('line_break', $br);
        $render->setIniRoot('render_use_cache', $use_cache);
        $render->setIniRoot('use_extra_facemark', 1);
        $render->setIniRoot('usefacemark', $smiley);
        $render->setIniRoot('render_cache_min', 1440); // 1day
        $render->setIniRoot('link_target', '_blank');
        $render->setIniRoot('nowikiname', 1);
        $render->setIniRoot('show_passage', 0);
        $render->setIniRoot('no_slashes_commentout', 1);

        if ($xcode) {
            if (! isset($pat[$className][$image])) {
                // BB Code code
                $pat[$className][$image][] = '/(?:\r\n|\r|\n)?\[code](?:\r\n|\r|\n)?(.*)(?:\r\n|\r|\n)?\[\/code\](?:\r\n|\r|\n)?/sUS';
                $rep[$className][$image][] = "\n".'#code(){{{'."\n".'$1'."\n".'}}}'."\n";

                // BB Code email
                $pat[$className][$image][] = '/\[email](.+?)\[\/email]/i';
                $rep[$className][$image][] = '$1';

                // BB Code url
                $chkc[$className][$image][] = '[url';
                $patc[$className][$image][] = '/\[url=([\'"]?)((?:ht|f)tp[s]?:\/\/[!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\\1\](.+)\[\/url\]/sU';
                $repc[$className][$image][] = function($m) { return '[['.str_replace(array("\r\n", "\r", "\n"), '&br;', $m[3]).':'.$m[2].']]'; };

                $chkc[$className][$image][] = '[url';
                $patc[$className][$image][] = '/\[url=([\'"]?)([!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\\1\](.+)\[\/url\]/sU';
                $repc[$className][$image][] = function($m) { return '[['.str_replace(array("\r\n", "\r", "\n"), '&br;', $m[3]).':http://'.$m[2].']]'; };

                $chkc[$className][$image][] = '[siteurl';
                $patc[$className][$image][] = '/\[siteurl=([\'"]?)\/?([!~*\'();?:\@&=+\$,%#_0-9a-zA-Z.-][!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\\1\](.+)\[\/siteurl\]/sU';
                $repc[$className][$image][] = function($m) { return '[['.str_replace(array("\r\n", "\r", "\n"), '&br;', $m[3]).':site://'.$m[2].']]'; };

                // BB Code quote
                $pat[$className][$image][] = '/(\[quote[^\]]*])(?:\r\n|\r|\n)(?![<>*|,#: \t+-])/';
                $rep[$className][$image][] = "\n\n$1";
                $pat[$className][$image][] = '/(?:\r\n|\r|\n)*\[\/quote\]/S';
                $rep[$className][$image][] = "\n".'[/quote]'."\n\n";

                if ($image) {
                    // BB Code image with align
                    $pat[$className][$image][] = '/\[img\s+align=([\'"]?)(left|center|right)\1(?:\s+title=([\'"])?((?(3)[^]]*|[^\]\s]*))(?(3)\3))?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\5)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\7)?]([!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\[\/img\]/U';
                    $rep[$className][$image][] = '&ref($9,$2,"t:$4",mw:$6,mh:$8);';

                    // BB Code image normal
                    $pat[$className][$image][] = '/\[img(?:\s+title=([\'"])?((?(1)[^]]*|[^\]\s]*))(?(1)\1))?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\3)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\5)?]([!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\[\/img\]/U';
                    $rep[$className][$image][] = '&ref($7,"t:$2",mw:$4,mh:$6);';
                } else {
                    // BB Code image with align
                    $pat[$className][$image][] = '/\[img\s+align=([\'"]?)(left|center|right)\1(?:\s+title=([\'"])?((?(3)[^]]*|[^\]\s]*))(?(3)\3))?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\5)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\7)?]([!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\[\/img\]/U';
                    $rep[$className][$image][] = '&ref($9,"t:$4",noimg);';

                    // BB Code image normal
                    $pat[$className][$image][] = '/\[img(?:\s+title=([\'"])?((?(1)[^]]*|[^\]\s]*))(?(1)\1))?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\3)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\5)?]([!~*\'();\/?:\@&=+\$,%#_0-9a-zA-Z.-]+)\[\/img\]/U';
                    $rep[$className][$image][] = '&ref($7,"t:$2",noimg);';
                }

				// BB Code siteimage with align
				$pat[$className][$image][] = '/\[siteimg\s+align=([\'"]?)(left|center|right)\1(?:\s+title=([\'"])?((?(3)[^]]*|[^\]\s]*))(?(3)\3))?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\5)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\7)?]\/?([!~*\'();?\@&=+\$,%#_0-9a-zA-Z.-][!~*\'();\/?\@&=+\$,%#_0-9a-zA-Z.-]+?)\[\/siteimg\]/U';
				$rep[$className][$image][] = '&ref(site://$9,$2,"t:$4",mw:$6,mh:$8);';

				// BB Code siteimage normal
				$pat[$className][$image][] = '/\[siteimg(?:\s+title=([\'"])?((?(1)[^]]*|[^\]\s]*))(?(1)\1))?(?:\s+w(?:idth)?=([\'"]?)([\d]+?)\3)?(?:\s+h(?:eight)?=([\'"]?)([\d]+?)\5)?]\/?([!~*\'();?\@&=+\$,%#_0-9a-zA-Z.-][!~*\'();\/?\@&=+\$,%#_0-9a-zA-Z.-]+?)\[\/siteimg\]/U';
				$rep[$className][$image][] = '&ref(site://$7,"t:$2",mw:$4,mh:$6);';

				// BB code list
				$list_tag = '(($m[1]=="1"||$m[1]=="a"||$m[1]=="A"||$m[1]=="r"||$m[1]=="R"||$m[1]=="d")?"+":"-")';
				/// pre convert
				$pat[$className][$image][] = '/\[list/';
				$rep[$className][$image][] = "\x01";
				$pat[$className][$image][] = '/\[\/list\]/';
				$rep[$className][$image][] = "\x02";
				/// outer matting
				$chkc[$className][$image][] = "\x01";
				$patc[$className][$image][] = '/\x01(?:\=([^\]]+))?\](?:\r\n|[\r\n])((?:(?>[^\x01\x02]+)|(?R))*)\x02(?:\r\n|[\r\n]|$)?/';
				$repc[$className][$image][] = function($m) use($list_tag) { return "\n".preg_replace(array('/(?:\x01[^\]]*\]|\x02)(\r\n|[\r\n])/','/\[\*\]/'),array("\n",$list_tag), $m[2])."\n\n"; };
				
				// Some BB Code Tags, Contents allows xpWiki rendering.
                if ($_reg = join('|', $this->renderWiki_getEscTags())) {
                    $chkc[$className][$image][] = '[';
                    $patc[$className][$image][] = '/\[\/?(?:' . $_reg . ')(?:(?: |=)[^\]]+)?\]/';
                    $repc[$className][$image][] = function($m) { return '[ b 6 4 ]' . base64_encode($m[0]) . '[ / b 6 4 ]'; };
                }

                // Other or Unknown BB Code Tags, All part escapes.
                if ($_reg = join('|', $this->renderWiki_getBypassTags())) {
                    $chkc[$className][$image][] = '[';
                    $patc[$className][$image][] = '/\[(' . $_reg . ')(?:\b[^\]]+)?].+\[\/\\1\]/sU';
                    $repc[$className][$image][] = function($m) { return '[ b 6 4 ]' . base64_encode($m[0]) . '[ / b 6 4 ]'; };
                }

            }

            $text = preg_replace($pat[$className][$image], $rep[$className][$image], $text);
            foreach($patc[$className][$image] as $k => $_pat) {
                if (strpos($text, $chkc[$className][$image][$k]) !== false) {
                    $text = preg_replace_callback($_pat, $repc[$className][$image][$k], $text);
                }
            }

        }

        if ($text = $render->transform($text, XPWIKI_RENDERER_DIR)) {
            if (isset($pat[$className]) && strpos($text, '[ b 6 4 ]') !== false) {
                // BB Code decode
                $_word_breaker = $render->root->word_breaker;
                $text = preg_replace_callback(
                        '/\[ b 6 4 ](.+?)\[ \/ b 6 4 ]/S',
                        function($m) use($_word_breaker) { return Hyp_TextFilter::renderWiki_base64decode($m[1], $_word_breaker); },
                        $text);
            }

            // XOOPS Quote style
            $text = str_replace(
                array('<blockquote','</blockquote>'),
                array('<div class="paragraph">'._QUOTEC.'<div class="xoopsQuote"><blockquote','</blockquote></div></div>'),$text
            );
        }

        return $text;
    }

    // Original function
    function renderWiki_ret2br($text)
    {
        $text = str_replace('\\"', '"', $text);
        return str_replace(array("\r\n", "\r", "\n"), '&br;', $text);
    }

    // Original function
    public static function renderWiki_base64decode($text, $word_breaker) {
        //return str_replace(array('<','>','\\"'),array('&lt;','&gt;','"'),base64_decode(strip_tags(str_replace($word_breaker, '', $text))));
        return str_replace(array('<','>'),array('&lt;','&gt;'),base64_decode(strip_tags(str_replace($word_breaker, '', $text))));
    }

    // Original function
    function renderWikistyleFinsher($input) {
        //$input = str_replace(array("\x07", "\x08"), array('<div>', '</div>'), $this->renderWikistyleParagraphRegularize($input));
        $input = $this->renderWikistyleParagraphRegularize($input);
        return $input;
    }

    // Original function
    function renderWikistyleParagraphRegularize($input) {
        // remove <p> include block elements.
        $regex = '#<p>((?:[^<]+|<(?!/?p[^>]*?>)|(?R))+)</p>#';
        if (is_array($input)) {
            if (preg_match('/<(?:div|p|pre|code)/i', $input[1])) {
                //$input = '<div>' . $input[1] . '</div>';
                //$input = "\x07" . $input[1]. "\x08";
                $input = $input[1];
            } else {
                return $input[0];
            }
        }
        return preg_replace_callback($regex, array(& $this, 'renderWikistyleParagraphRegularize'), $input);
    }
}

if (! defined('LEGACY_BASE_VERSION') || version_compare(LEGACY_BASE_VERSION, '2.2.2.2', '>=') || (! defined('_MI_LEGACY_DETAILED_VERSION') || version_compare(_MI_LEGACY_DETAILED_VERSION, 'CorePack 20130503', '<'))) {
	class Hyp_TextFilter extends Hyp_TextFilterAbstract {
		// PHP 4 style constructor for compat
		public function Hyp_TextFilter() {
			self::__construct();
		}
		public function __construct() {
			parent::Hyp_TextFilterAbstract();
		}
		// Over write
		public function makeXCodeConvertTable(& $patterns, & $replacements) {
			self::sMakeXCodeConvertTable($patterns, $replacements);
	    }
	}
} else {
	class Hyp_TextFilter extends Hyp_TextFilterAbstract {
		// PHP 4 style constructor for compat
		public function Hyp_TextFilter() {
			self::__construct();
		}
		public function __construct() {
		parent::Hyp_TextFilterAbstract();
		}
		// Over write
		public static function makeXCodeConvertTable(& $patterns, & $replacements) {
			self::sMakeXCodeConvertTable($patterns, $replacements);
	    }
	}
}