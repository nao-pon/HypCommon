<?php
include_once dirname(dirname(__FILE__)) . '/hsamazon/hyp_simple_amazon.php';

class HypSimpleRakuten extends HypSimpleAmazon
{
	var $baseUrl = 'http://api.rakuten.co.jp/rws/3.0/rest';
	var $genreUrl = 'http://search.rakuten.co.jp/search/mall/-/$1/';
	var $AffId = '';
	var $DevId = '';
	var $Version = '2010-09-15';

	function HypSimpleRakuten ($AffId = null, $DevId = null) {
		$this->myDirectory = dirname(__FILE__);
		include_once dirname($this->myDirectory) . '/hyp_common_func.php';
		include_once dirname($this->myDirectory) . '/hyp_simplexml.php';

		if (!is_null($AffId)) {
            $this->AffId = $AffId;
        }
        if (!is_null($DevId)) {
            $this->DevId = $DevId;
        }
        
		if (!$this->DevId || !$this->AffId) {
			$configFile = $this->myDirectory . '/ini/rakuten.ini';
			if (is_file($configFile)) {
				$ini = parse_ini_file($configFile);
				if (!$this->DevId && isset($ini['developerId'])) {
					$this->DevId = $ini['developerId'];
				}
				if (!$this->AffId && isset($ini['affiliateId'])) {
					$this->AffId = $ini['affiliateId'];
				}
			}
		}
		
		// Set Default template
		$this->loadTemplate('default');
	}

    function getItemSearchUrl($key, $params = array()) {
		// Init
		$this->init();

        if (!empty($key)) {
			$this->searchKey = mb_convert_encoding($key, 'UTF-8', $this->encoding);
			$params['keyword'] = $this->searchKey;
        }
        $params['operation'] = 'ItemSearch';
        $this->xmlBodyKey = 'itemSearch:ItemSearch';

        return $this->_getUrl($params);
    }

	function getResultType() {
		return 'xml';
	}


    function itemSearch($key = null, $params = array()) {
        if (!empty($key)) {
			$this->searchKey = mb_convert_encoding($key, 'UTF-8', $this->encoding);
			$params['keyword'] = $this->searchKey;
        }
        $params['operation'] = 'ItemSearch';
        $this->xmlBodyKey = 'itemSearch:ItemSearch';

        $this->_sendQuery($params);
    }

	function _getUrl($params) {
        $params['developerId'] = $this->DevId;
        $params['affiliateId'] = $this->AffId;
        $params['version'] = $this->Version;
        $query = '';
        foreach ($params as $key => $value) {
          if (!empty($query)) {
            $query .= "&";
          }
          $query .= sprintf("%s=%s", $key, urlencode($value));
        }
        $url = $this->baseUrl . '?' . $query;
		return $url;
	}

	function _sendQuery ($params) {

        $this->url = $this->_getUrl($params);

        $this->data = null;
		$timer = $this->cacheDir . 'hyp_hsr_' . $this->DevId . '.timer';
		$loop = 0;
		if ($this->OneRequestPerSec) {
			while($loop < $this->retry_count && is_file($timer) && filemtime($timer) >= time()){
				$loop++;
				clearstatcache();
				usleep($this->retry_interval * 1000); // 250ms
			}
		}
		if ($this->OneRequestPerSec && $loop >= $this->retry_count) {
			$this->xml = '';
			$this->error = 'Request Error: Too busy.';
		} else {
			if ($this->OneRequestPerSec) HypCommonFunc::touch($timer);
			$ht = new Hyp_HTTP_Request();
			$ht->init();
			$ht->url = $this->url;
			$ht->get();

			if ($ht->rc === 200) {
				if (! $this->parseXml) {
					$this->xml = '';
				} else {
					$this->data = $ht->data;
					$xm = new HypSimpleXML();
					$this->xml = $xm->XMLstr_in($this->data);
				}
			} else {
				$this->xml = '';
				$this->error = 'HTTP Error: ' . $ht->rc;
			}
		}

	}

	function toCompactArray() {
		$compact = array();

		if (!$this->xml) return;

		$i = 0;
		$sortkeys = array();
		$items_top = $_items = $items = array();

		if (isset($this->xml['Body'][$this->xmlBodyKey])) {
			$items_top = $this->xml['Body'][$this->xmlBodyKey];
			$_items = @ $items_top['Items']['Item'];
		}

		$compact['request'] = NULL;
		$compact['totalresults'] = (isset($items_top['count']))? intval($items_top['count']) : 0;
		$compact['totalpages'] = NULL;

		if ($_items) {
			$this->check_array($_items);
			foreach ($_items as $item) {
				if (!is_array($item) || !isset($item['itemName'])) continue;

				//if ($this->CompactArrayRemoveAdult && isset($item['ItemAttributes']['IsAdultProduct'])) {
				//	continue;
				//}

				$_item = array();

				//$item_m = 'http://m.rakuten.co.jp/'.$item['shopCode'].'/i/'.preg_replace('/^[^:]*:/', '', $item['itemCode']).'/';
				//$item['affiliateUrl'] .= '&amp;m='.urlencode($item_m);

				// For template values
				$_item['_SERVICE'] = $_item['SERVICE'] = 'rakuten';
				$_item['URL'] = $item['affiliateUrl'];
				$_item['ASIN'] = 'Rakuten:'.$item['itemCode'];
				$_item['JAN'] = '';
				$_item['ADDCARTURL'] = '';
				$_item['TITLE'] = $item['itemName'];
				$_item['DISCRIPTION'] = $this->toPlainText(trim(@ $item['catchcopy'] . '' .  @$item['itemCaption']));
				if (preg_match('/4(?:5|9)[0-9]{11}/', $_item['DISCRIPTION'], $match)) {
					$_item['JAN'] = $match[0];
				}
				$shop_m = 'http://m.rakuten.co.jp/'.$item['shopCode'].'/';
				$shopurl = urlencode($item['shopUrl']).'&amp;m='.urlencode($shop_m);
				$shop = $this->makeAffiliateLink($shopurl, $item['shopName'], $item['shopName'], true);

				$_item['BINDING'] = $shop;
				$_item['PRODUCTGROUP'] = $item['shopCode'];
				$_item['MANUFACTURER'] = '';
				//$_item['RELEASEDATE'] = $this->get_releasedate($item);
				//$_item['RELEASEUTIME'] = @ $item['ReleaseUTIME'];
				//if (isset($item['availability'])) $_item['AVAILABILITY'] = ($item['availability']? 'in stock' . (@ $item['postageFlag']? ' carriage not include' : ' carriage include') : 'out of stock');
				if (isset($item['availability'])) $_item['AVAILABILITY'] = ($item['availability']? 'instock' : 'outofstock');
				if (isset($_item['postageFlag']) && !$_item['postageFlag']) {
					$_item['GUIDEURL'] = 'free';
				} else {
					if ($item['shopCode'] === 'book') {
						if ( @ $item['itemPrice'] > 1500) {
							$_item['GUIDEURL'] = 'free';
						} else {
							$_item['GUIDEURL'] = 'http://hb.afl.rakuten.co.jp/hgc/'.$this->AffId.'/?pc='.urlencode('http://books.faq.rakuten.co.jp/app/answers/detail/a_id/2894').'&amp;m='.urlencode('http://m.rakuten.co.jp/book/');
						}
					} else {
						$_item['GUIDEURL'] = 'http://hb.afl.rakuten.co.jp/hgc/'.$this->AffId.'/?pc='.urlencode($item['shopUrl'].'info2.html').'&amp;m='.urlencode($shop_m.'info2.html');
					}
				}
				$_item['SIMG'] = @$item['smallImageUrl'];
				if (@$item['mediumImageUrl']) {
					$_item['MIMG'] = $item['mediumImageUrl'];
					$_item['LIMG'] = $item['mediumImageUrl'];
				} else {
					$_item['LIMG'] = $_item['MIMG'] = $_item['SIMG'];
				}
				$_item['LINKED_SIMG'] = $this->get_image($item, 's');
				$_item['LINKED_MIMG'] = $this->get_image($item, 'm');
				$_item['LINKED_LIMG'] = $this->get_image($item, 'l');
				//$_item['CATEGORY'] = @ $item['GenreInformation']['current']['genreName'];
				$_item['CATEGORY'] = $this->makeAffiliateLink(str_replace('$1', $item['genreId'], $this->genreUrl), '&#12472;&#12515;&#12531;&#12523;', $item['genreId']);
				$_item['PRESENTER'] = 'by: ' . $shop;
				$_item['CREATOR'] = '';
				//$_price = $this->get_listprice($item);
				//$_item['LISTPRICE']= $item['PriceLabel']['FixedPrice'];
				//$_item['LISTPRICE_FORMATTED'] = $_price[1];

				$_item['PRICE'] = intval(@ $item['itemPrice']);
				$_item['PRICE_FORMATTED'] = '&#65509; ' . number_format($_item['PRICE']);
				//$_price = $this->get_usedprice($item);
				//$_item['USEDPRICE']= $_price[0];
				//$_item['USEDPRICE_FORMATTED'] = $_price[1];

				//$_item['RAW'] = $item;

				$compact['Items'][] = $_item;
			}
		}
		mb_convert_variables($this->encoding , 'UTF-8', $compact);
		$this->compactArray = $compact;
	}

	function get_image($item, $size = 's') {
		$img = '';
		if ($size === 's') {
			$img = @ $item['smallImageUrl'];
		} else if ($size === 'm') {
			$img = @ $item['mediumImageUrl'];
		} else {
			$img = @ $item['mediumImageUrl'];
		}
		$from = array(
			'<_TITLE_>',
			'<_URL_>',
			'<_IMGSRC_>',
			'<_IMGSIZE_>',
		);

		if ($img) {
			//$height =$img['Height']['content'];
			//$width = $img['Width']['content'];

			$to['title'] = $item['itemName'];
			$to['url'] = $item['affiliateUrl'];
			$to['imgsrc'] = $img;
			$to['imgsize'] = '';

			$img = str_replace($from, $to, $this->templates[$this->templateSet]['img']);
		}
		return $img;
	}

	function makeSearchLink($key, $alias = '', $needEncode = TRUE, $category='') {
		if (is_array($key)) {
			$_key = array();
			foreach($key as $_k =>$_v) {
				$_key[$_k] = $this->makeSearchLink($_v, $alias, $needEncode, $category='');
			}
			return $_key;
		}
		if (!$alias) $alias = $key;
		$alias = htmlspecialchars($alias);

		if ($needEncode) {
			$e_key = mb_convert_encoding($key, 'eucJP-win', $this->encoding);
		} else {
			$e_key = $key;
		}

		$search_url_raku = 'http://esearch.rakuten.co.jp/rms/sd/esearch/vc?sv=2&sitem=' . urlencode($e_key);
		$mobile_url = 'http://s.j.rakuten.co.jp/r/s/wb?ws=1&w=' . urlencode(mb_convert_encoding($e_key, 'SJIS-win', 'eucJP-win'));
		$url = 'http://hb.afl.rakuten.co.jp/hgc/'.$this->AffId.'/?pc=' . urlencode($search_url_raku);
		$url .= '&amp;m=' . urlencode($mobile_url);

		$s_key = htmlspecialchars($key);
		$attrs = '';
		if ($attr = $this->configs['makeLinkSearch']['Attributes']) {
			if (isset($attr['title'])) {
				$attr['title'] = sprintf($attr['title'], $s_key);
			}
			$attrs = array();
			foreach ($attr as $key => $val) {
				$attrs[] = $key . '="' . $val . '"';
			}
			$attrs = ' ' . join(' ', $attrs);
		}

		return '<a href="' . $url . '"' . $attrs . '>' . $alias . '</a>';
	}

	function makeAffiliateLink($url, $body, $caption = '', $urlisencoded = false) {
		$attrs = '';
		if ($attr = $this->configs['makeLinkSearch']['Attributes']) {
			if ($caption && isset($attr['title'])) {
				$attr['title'] = sprintf($attr['title'], htmlspecialchars($caption));
			}
			$attrs = array();
			foreach ($attr as $key => $val) {
				$attrs[] = $key . '="' . $val . '"';
			}
			$attrs = ' ' . join(' ', $attrs);
		}

		if (!$urlisencoded) $url = urlencode($url);

		return '<a href="http://hb.afl.rakuten.co.jp/hgc/'.$this->AffId.'/?pc='.$url.'"' . $attrs . '>'.$body.$this->beaconImg.'</a>';
	}
}