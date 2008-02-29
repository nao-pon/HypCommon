<?php
class HypSimpleAmazon
{
	var $myDirectory;
	
	var $Location = 'JP';
	var $AccessKeyId = '0F1572ZQ7P3BTFJ28RR2';
	var $ResponseGroup = 'ItemAttributes,Images,Offers,Variations';
	var $SearchIndex = 'Blended';
	var $Version = '2007-10-29';
	var $AssociateTag = '';
	var $restHost = 'http://webservices.amazon.co.jp';
	var $searchHost = 'http://www.amazon.co.jp';
	var $searchQuery = '/gp/search?ie=UTF8&amp;index=blended&amp;linkCode=ur2&amp;camp=247&amp;creative=1211';
	var $encoding = 'EUC-JP';
	var $SearchIndexes = array();
	var $templateSet = 'default'; 
	var $templates = array();
	var $error = '';
	var $CompactArrayRemoveAdult = FALSE;
	
	var $configs = array(
		'makeLinkSearch' => array(
			'Attributes'  => array(
				'target'   => '_blank',
				'class'    => 'searchAmazon',
				'title'    => 'Lookup: %s',
			),
		),
	);
	
	function HypSimpleAmazon ($AssociateTag = '') {
		
		$this->myDirectory = dirname(__FILE__);

		include_once dirname($this->myDirectory) . '/hyp_common_func.php';
		include_once dirname($this->myDirectory) . '/hyp_simplexml.php';
		
		$this->AssociateTag = $AssociateTag;
		
		$this->loadSearchIndexes();
		
		// Set Default template
		$this->loadTemplate('default');
	}
	
	function _sendQuery ($params) {
		$params['AssociateTag'] = $this->AssociateTag;
		$params['AWSAccessKeyId'] = $this->AccessKeyId;
		$params['Version'] = $this->Version;
		$params['ContentType'] = 'text/xml';
		$params['ResponseGroup'] = $this->ResponseGroup;
		
		if (isset($params['SearchIndex']) && ($params['SearchIndex'] === 'Blended' || $params['SearchIndex'] === 'All')) {
			unset($params['Sort'], $params['MerchantId']);
		}
		
		$querys = array();
		foreach($params as $key=>$val) {
			$querys[] = $key . '=' . rawurlencode($val);
		}
		$url = $this->restHost . '/onca/xml?Service=AWSECommerceService&' . join ('&', $querys);
		
		$ht = new Hyp_HTTP_Request();
		$ht->init();
		$ht->url = $url;
		$ht->get();

		if ($ht->rc == 200) {
			$data = $ht->data;

			$xm = new HypSimpleXML();

			$this->xml = $xm->XMLstr_in($data);
			
			if ($error = @ $this->xml['Items']['Request']['Errors']['Error']) {
				$this->error = $error['Message'];
			}
		} else {
			$this->xml = '';
			$this->error = 'HTTP Error: ' . $ht->rc;
		}
	}
	
	function setLocation($loc) {
		$loc = strtoupper($loc);
		$this->Location = $loc;
		switch($loc) {
			case 'JP':
				$this->restHost = 'http://webservices.amazon.co.jp';
				$this->searchHost = 'http://www.amazon.co.jp';
				break;
			case 'US':
				$this->restHost = 'http://webservices.amazon.com';
				$this->searchHost = 'http://www.amazon.com';
				break;
			case 'UK':
				$this->restHost = 'http://webservices.amazon.co.uk';
				$this->searchHost = 'http://www.amazon.co.uk';
				break;
			case 'DE':
				$this->restHost = 'http://webservices.amazon.de';
				$this->searchHost = 'http://www.amazon.de';
				break;
			case 'FR':
				$this->restHost = 'http://webservices.amazon.fr';
				$this->searchHost = 'http://www.amazon.fr';
				break;
			case 'CA':
				$this->restHost = 'http://webservices.amazon.ca';
				$this->searchHost = 'http://www.amazon.ca';
				break;
			default :
				$this->restHost = 'http://webservices.amazon.com';
				$this->searchHost = 'http://www.amazon.com';
				$this->Location = 'US';
		}
		$this->loadSearchIndexes();
	}
	
	function loadSearchIndexes () {
		$file = $this->myDirectory . '/res/' . $this->Location . '/SerachIndexes';
		$this->SearchIndexes = array();
		foreach(file($file) as $line) {
			if ($line && $line[0] !== '#') {
				$this->SearchIndexes[] = trim($line);
			}
		}
	}
	
	function loadTemplate ($file) {
		if ($template = file_get_contents(dirname(__FILE__) . '/templates/' . $file)) {
			$this->addTemplateSet(basename($file), $template);
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function getTemplateSource ($file) {
		if ($template = file_get_contents(dirname(__FILE__) . '/templates/' . $file)) {
			return $template;
		} else {
			return '';
		}
	}
	
	function getTemplates () {
	    $templates = array();
	    $base = dirname(__FILE__) . '/templates/';
	    if ($dh = opendir($base)) {
			while (($file = readdir($dh)) !== false) {
				if ($file[0] !== '.' && is_file($base . $file)) {
					$templates[] = $file;
				}
			}
			closedir($dh);
		}
		return $templates;
    }
	
	function addTemplateSet ($name, $template) {
		if (!is_array($template)) {
			$_temp = array();
			$_temp['maxResult'] = 0;
			$_temp['base'] = rtrim(preg_replace('#<!--(.+?)(?:\(([\d]+)\))?-->.*?<!--/\\1-->#s', '', $template));
			if (preg_match('#<!--EACH(?:\(([\d]+)\))?-->(.+?)<!--/EACH-->#s', $template, $match)) {
				$_temp['each'] = $match[2];
				if (!empty($match[1])) {
					$_temp['maxResult'] = $match[1];
				}
			}
			if (preg_match('#<!--IMG-->(.+?)<!--/IMG-->#s', $template, $match)) {
				$_temp['img'] = trim($match[1]);
			}
			$template = $_temp;
		}
		if ($name === 'default') {
			$this->templates[$name] = $template;
		} else {
			$this->templates[$name] = array_merge($this->templates['default'], $template);
		}
	}
	
	function setSearchIndex ($str) {
		// Remove '-jp' etc. for AWS 3.0 compat.
		$str = preg_replace('/-[^\-]+$/', '', $str);
		foreach($this->SearchIndexes as $_index) {
			if (strtoupper($str) === strtoupper($_index)) {
				$this->SearchIndex = $_index;
				return;
			}
		}
		$this->SearchIndex = 'Blended';
	}
	
	function init() {
		$this->xml = '';
		$this->compactArray = array();
		$this->html = '';
		$this->searchKey = '';
		$this->error = '';
	}
	
	function itemSearch($key, $options = array()) {
		
		// Init
		$this->init();
		
		$options['Operation'] = 'ItemSearch';
		$options['SearchIndex'] = $this->SearchIndex;
		$options['Keywords'] = mb_convert_encoding($key, 'UTF-8', $this->encoding);
		$this->searchKey = $options['Keywords'];
		
		$this->_sendQuery($options);
	}

	function browseNodeSearch($node, $options = array()) {
		
		// Init
		$this->init();
		
		$options['Operation'] = 'ItemSearch';
		$options['SearchIndex'] = ($this->SearchIndex === 'Blended')? 'Music' : $this->SearchIndex;
		$options['BrowseNode'] = $node;
		
		$this->_sendQuery($options);
	}

	function itemLookup($key, $options = array()) {
		
		// Init
		$this->init();
		
		$options['Operation'] = 'ItemLookup';
		$options['ItemId'] = $key;
		
		$this->_sendQuery($options);
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
			$e_key = mb_convert_encoding($key, 'UTF-8', $this->encoding);
		} else {
			$e_key = $key;
		}
		
		$url = $this->searchHost . $this->searchQuery . ($this->AssociateTag ? '&amp;tag=' . rawurlencode($this->AssociateTag) : '') . '&amp;keywords=' . rawurlencode($e_key);
		//if ($category) $url .= '&amp;url=search-alias%3D'.strtolower($category);
		//if ($category) $url .= '&amp;rs=&amp;rh=i%3Aaps%2Ck%3A'.rawurlencode($e_key).'%2Ci%3A'.strtolower($category);
		
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
	
	function toCompactArray() {
		$compact = array();

		$compact['request'] = @ $this->xml['Items']['Request'];
		$compact['totalresults'] = @ $this->xml['Items']['TotalResults'];
		$compact['totalpages'] = @ $this->xml['Items']['TotalPages'];

		$items = @ $this->xml['Items']['Item'];
		if ($items) {
			$this->check_array($items);
			foreach ($items as $item) {
				
				if ($this->CompactArrayRemoveAdult && isset($item['ItemAttributes']['IsAdultProduct'])) {
					continue;
				}
				
				$_item = array();
				
				// For template values
				$_item['URL'] = $item['DetailPageURL'];
				$_item['ASIN'] = $item['ASIN'];
				$_item['ADDCARTURL'] = $this->getAddCartURL($item['ASIN']);
				$_item['TITLE'] = htmlspecialchars($item['ItemAttributes']['Title']);
				$_item['BINDING'] = @ $item['ItemAttributes']['Binding'];
				$_item['PRODUCTGROUP'] = @ $item['ItemAttributes']['ProductGroup'];
				$_item['MANUFACTURER'] = $this->get_manufacturer($item);
				$_item['RELEASEDATE'] = @ $item['ItemAttributes']['ReleaseDate'];
				
				$_item['AVAILABILITY'] = @ $item['Offers']['Offer']['OfferListing']['Availability'];
				$_item['SIMG'] = @$item['SmallImage']['URL'];
				$_item['MIMG'] = @$item['MediumImage']['URL'];
				$_item['LIMG'] = @$item['LargeImage']['URL'];
				$_item['LINKED_SIMG'] = $this->get_image($item, 's');
				$_item['LINKED_MIMG'] = $this->get_image($item, 'm');
				$_item['LINKED_LIMG'] = $this->get_image($item, 'l');
				$_item['CATEGORY'] = $this->get_category($item);
				$_item['PRESENTER'] = $this->get_presenter($item);
				$_item['CREATOR'] = $this->get_creator($item);
				$_price = $this->get_listprice($item);
				$_item['LISTPRICE']= $_price[0];
				$_item['LISTPRICE_FORMATTED'] = $_price[1];
				$_price = $this->get_price($item);
				$_item['PRICE']= $_price[0];
				$_item['PRICE_FORMATTED'] = $_price[1];
				$_price = $this->get_usedprice($item);
				$_item['USEDPRICE']= $_price[0];
				$_item['USEDPRICE_FORMATTED'] = $_price[1];

				// Array data (For not template)
				foreach($this->get_creators($item) as $key => $val) {
					$key = mb_convert_encoding($key, $this->encoding, 'UTF-8');
					$_item['CREATORS'][$key] = $val; 
				}
				//$_item['RAW'] = $item;
				
				$compact['Items'][] = $_item;
			}
		}
		mb_convert_variables($this->encoding , 'UTF-8', $compact);
		$this->compactArray = $compact;
	}
	
	function getCompactArray($templateSet = '') {
		if ($templateSet && isset($this->templates[$templateSet])) {
			$this->templateSet = $templateSet;
		} else {
			if ($templateSet && $this->loadTemplate($templateSet)) {
				$this->templateSet = $templateSet;
			} else {
				$this->templateSet = 'default';
			}
		}
		
		$this->toCompactArray();
		
		return 	$this->compactArray;
	}
	
	function getResultArray() {
		return $this->xml;
	}
	
	function getAddCartURL ($asin) {
		
		$url = $this->searchHost
		     . '/gp/aws/cart/add.html?AWSAccessKeyId=' . $this->AccessKeyId
		     . '&amp;AssociateTag=' . $this->AssociateTag
		     . '&amp;ASIN.1=' . $asin
		     . '&amp;Quantity.1=1';
		
		return $url;
	}
	
	function getHTML($templateSet = '') {
		if ($templateSet && isset($this->templates[$templateSet])) {
			$this->templateSet = $templateSet;
		} else {
			if ($templateSet && $this->loadTemplate($templateSet)) {
				$this->templateSet = $templateSet;
			} else {
				$this->templateSet = 'default';
			}
		}
		
		$this->toCompactArray();
		$template = $this->templates[$this->templateSet];
		if (!$this->error) {
			$each = '';
			$i = 0;
			$from = array();
			$from_make = FALSE;
			foreach ($this->compactArray['Items'] as $item) {
				if ($template['maxResult'] && ++$i > $template['maxResult']) break;
				
				$to = array();
				//foreach($keys as $key) {
				foreach(array_keys($item) as $key) {
					if (!$from_make) {
						$from[] = '<_' . $key . '_>';
					}
					$to[] = (is_string($item[$key]))? $item[$key] : '';
				}
				if (!$from_make) {
					$from[] = '<_ASSTAG_>';
					$from[] = '<_DEVKEY_>';
				}
				$from_make = TRUE;
				$to[] = $this->AssociateTag;
				$to[] = $this->AccessKeyId;
				
				$_html = str_replace($from, $to, $template['each']);
				
				$_html = preg_replace('#<_count!=(?:[\d]+,)*'.$i.'(?:,[\d]+)*_>.+?<_/count_>#s', '', $_html);
				$_html = preg_replace('#<_count!=(?:[\d]+,)*[\d]+_>(.+?)<_/count_>#s', '$1', $_html);

				$_html = preg_replace('#<_count=(?:[\d]+,)*'.$i.'(?:,[\d]+)*_>(.+?)<_/count_>#s', '$1', $_html);
				$_html = preg_replace('#<_count=(?:[\d]+,)*[\d]+_>.+?<_/count_>#s', '', $_html);
				
				$each .=$_html;
			}
			
			$this->html = str_replace('<_EACH_>', $each, $template['base']);
			
		} else {
			if (! $error = @ $this->compactArray['request']['Errors']['Error']['Message']) {
				$error = $this->error;
			}
			$this->html = str_replace('<_EACH_>', $error, $template['base']);
		}
		
		return $this->html;
	}
	
	function ISBN2ASIN ($isbn) {
		$_isbn = str_replace('-', '', $isbn);
		
		if (strlen($_isbn) !== 13) return $isbn;
		
		$head = intval(substr($_isbn, 0, 3));
			
		if ($head === 978 || $head === 979) {
			$asin = substr($_isbn, 3, 9);
			$sum = 0;
			$n = 10;
			for($i = 0; $i < 9; $i++) {
				$sum += $asin[$i] * $n--;
			}
			$des = 11 - ($sum % 11);
			if ($des === 10) {
				$des = 'X';
			} else if ($des === 11) {
				$des = '0';
			}
			$asin .= $des;
			return $asin;
		} else {
			return $isbn;
		}
	}
	
	function check_array(& $items) {
		if (!is_array($items) || !isset($items[0])) {
			$tmp[0] = $items;
			$items = $tmp;
		}
	}
	
	function get_category($item) {
		$binding = '';
		if (@ $item['ItemAttributes']['Binding']) {
			$binding = $item['ItemAttributes']['Binding'];
		} else if ($item['ItemAttributes']['ProductGroup']) {
			$binding = $item['ItemAttributes']['ProductGroup'];
		}
		
		//if ($this->searchKey) $binding = $this->makeSearchLink($this->searchKey, $binding, FALSE, @ $item['ItemAttributes']['ProductGroup']);

		return $binding;
	}
	
	function get_image($item, $size = 's') {
		$img = '';
		if ($size === 's') {
			$img = @ $item['SmallImage'];
		} else if ($size === 'm') {
			$img = @ $item['MediumImage'];
		} else {
			$img = @ $item['LargeImage'];
		}
		$from = array(
			'<_TITLE_>',
			'<_URL_>',
			'<_IMGSRC_>',
			'<_IMGSIZE_>',
		);

		if ($img) {
			$height =$img['Height']['content'];
			$width = $img['Width']['content'];

			$to['title'] = $item['ItemAttributes']['Title'];
			$to['url'] = $item['DetailPageURL'];
			$to['imgsrc'] = $img['URL'];
			$to['imgsize'] = 'height="' . $height . '" width="' . $width . '"';

			$img = str_replace($from, $to, $this->templates[$this->templateSet]['img']);
		}
		return $img;
	}

	function get_presenter($item) {
		$author = '';
		if (@ $item['ItemAttributes']['Artist']) {
			$author = $item['ItemAttributes']['Artist'];
		} else if (@ $item['ItemAttributes']['Author']) {
			$author = $item['ItemAttributes']['Author'];
		} else if (@ $item['ItemAttributes']['Actor']) {
			$author = $item['ItemAttributes']['Actor'];
		} else if (@ $item['ItemAttributes']['Manufacturer']) {
			$author = $item['ItemAttributes']['Manufacturer'];
		} else if (@ $item['ItemAttributes']['Brand']) {
			$author = $item['ItemAttributes']['Brand'];
		}
		if ($author) {
			$this->check_array($author);
			$author = $this->makeSearchLink($author, '', FALSE);
			$author = 'by: '. join(', ', $author);
		}
		return $author;
	}
	
	function get_creators($item) {
		$creators = array();
		if (@ $item['ItemAttributes']['Creator']) {
			$this->check_array($item['ItemAttributes']['Creator']);
			foreach($item['ItemAttributes']['Creator'] as $dat) {
				$creators[$dat['Role']][] = $this->makeSearchLink($dat['content'], '', FALSE);
			}
		} else if (@ $item['ItemAttributes']['Manufacturer']) {
			$creators['by'][] = $this->makeSearchLink($item['ItemAttributes']['Manufacturer'], '', FALSE);
		}
		return $creators;
	}

	function get_creator($item) {
		$creators = array();
		foreach ($this->get_creators($item) as $key => $arg) {
			$creators[] = $key . ': ' . join(', ', $arg);
		}
		return join('<br />', $creators);
	}
	
	function get_listprice($item) {
		$listprice = array(0 => '', 1 => '');
		if (@ $item['ItemAttributes']['ListPrice']['Amount']) {
			$listprice[0] = $item['ItemAttributes']['ListPrice']['Amount'];
			$listprice[1] = $item['ItemAttributes']['ListPrice']['FormattedPrice'];
		}
		return $listprice;
	}

	function get_price($item) {
		$price = array(0 => '', 1 => '');
		if (@ $item['Offers']['Offer']['OfferListing']['Price']['Amount']) {
			$price[0] = $item['Offers']['Offer']['OfferListing']['Price']['Amount'];
			$price[1] = $item['Offers']['Offer']['OfferListing']['Price']['FormattedPrice'];
		} else if (@ $item['OfferSummary']['LowestNewPrice']['Amount']) {
			$price[0] = $item['OfferSummary']['LowestNewPrice']['Amount'];
			$price[1] = $item['OfferSummary']['LowestNewPrice']['FormattedPrice'];
		} else if (@ $item['VariationSummary']['LowestPrice']['Amount']) {
			$price[0] = $item['VariationSummary']['LowestPrice']['Amount'];
			$price[1] = $item['VariationSummary']['LowestPrice']['FormattedPrice'];
		}
		return $price;
	}

	function get_usedprice($item) {
		$usedprice = array(0 => '', 1 => '');
		if (@ $item['OfferSummary']['LowestUsedPrice']['Amount']) {
			$usedprice[0] = $item['OfferSummary']['LowestUsedPrice']['Amount'];
			$usedprice[1] = $item['OfferSummary']['LowestUsedPrice']['FormattedPrice'];
		}
		return $usedprice;
	}
	
	function get_manufacturer($item) {
		if ($manufacturer = @ $item['ItemAttributes']['Manufacturer']) {
			return $this->makeSearchLink($manufacturer, '', FALSE);
		} else if ($manufacturer = @ $item['ItemAttributes']['Brand']) {
			return $this->makeSearchLink($manufacturer, '', FALSE);
		}
		return '';
	}
}
