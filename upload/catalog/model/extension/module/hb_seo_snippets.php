<?php
class ModelExtensionModuleHbSeoSnippets extends Model {	
	public function get_stock_status_id($product_id) {
		$query = $this->db->query("SELECT stock_status_id FROM ".DB_PREFIX."product WHERE product_id = '".(int)$product_id."'");
		if ($query->row) {
			return $query->row['stock_status_id'];
		}else {
			return '0';
		}
	}
	
	public function product_sd($product_info, $data) {		
		$ldjson = '';

		if ($this->config->get('hb_snippets_prod_enable') || $this->config->get('hb_snippets_og_enable') || $this->config->get('hb_snippets_tc_enable')) {
			
			if (isset($this->session->data['currency'])) {
				$currencycode 			= $this->session->data['currency'];
			}else{
				$currencycode 			= $this->config->get('config_currency');
			}
			
			if ($this->config->get('hb_snippets_description') == 'description') {
				$description = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", htmlentities(strip_tags($data['description']))); 
			}else{
				$description = $product_info['meta_description'];
			}
			
			$description = preg_replace('/\s{2,}/', ' ', trim($description));
			
			$product_id 	= $product_info['product_id'];
			$name  			= $product_info['name'];
			//$brand 			= $product_info['manufacturer'];
			$model 			= $product_info['model'];
			$url			= $this->url->link('product/product','product_id='.$product_id);
			$review_count 	= $product_info['reviews'];

			if ((float)$product_info['special']) {
				$price = (float)$product_info['special'];
			}else{
				$price = (float)$product_info['price'];
			}
			
			$actual_price = (float)$product_info['price'];
			
			$formatted_price =  $this->currency->format($price, $currencycode);
			
			$currency_value = $this->currency->getValue($currencycode);
			$price 			= $price * $currency_value;
			$actual_price 	= $actual_price * $currency_value;
			
			if ($this->config->get('hb_snippets_incl_tax')) {
				$price 			= $this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax'));
				$actual_price 	= $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
			}			
			
			$price = number_format($price, 2, '.', '');
			$actual_price = number_format($actual_price, 2, '.', '');
			
			if ($this->config->get('hb_snippets_prod_enable')) {	
				if ($product_info['quantity'] > 0){
					$availability = 'https://schema.org/InStock';
				}else{
					$stock_status_id = $this->get_stock_status_id($product_id);
					if ($this->config->get('hb_snippets_stock')) {
						$availability = $this->config->get('hb_snippets_stock');
						$availability = 'https://schema.org/'.$availability[$stock_status_id];
					}else{
						$availability = 'https://schema.org/OutOfStock';
					}
				}

				$sku = ($product_info['sku']) ? $product_info['sku'] : $product_id;
				$mpn = ($product_info['mpn']) ? $product_info['mpn'] : $product_id;

				$product_images = array();
				if ($product_info['image']) {
					$product_images[] = $data['popup'];
				}

				if (!empty($data['images'])) {
					foreach ($data['images'] as $image) {
						$product_images[] = $image['popup'];
					}
				}

				$brand_name = ($product_info['manufacturer'])? $product_info['manufacturer'] : $this->config->get('hb_snippets_brand');
				$brand = array('@type' => 'Brand', 'name' => $brand_name );

				$price_date = $this->config->get('hb_snippets_pricevaliddate');

				if ($this->config->get('hb_snippets_pricevalid')) {
					$pricedate_query = $this->db->query("SELECT date_end FROM `".DB_PREFIX."product_special` WHERE product_id = '".(int)$product_id."' AND customer_group_id = '".(int)$this->config->get('config_customer_group_id')."' AND date_end > now() ORDER BY priority ASC LIMIT 1");
					
					if ($pricedate_query->row) {
						$price_date = date('Y-m-d',strtotime($pricedate_query->row['date_end']));
					}
				}

				$review_data = array();
				$review_query = $this->db->query("SELECT * FROM `".DB_PREFIX."review` WHERE product_id = '".(int)$product_id."' AND status = 1");
				if ($review_query->rows) {
					$reviews = $review_query->rows;
					
					foreach ($reviews as $rev) {
						$reviewRating =  array(
							'@type'			=> 'Rating',
							'ratingValue'	=> $rev['rating'],
							'bestRating'	=> '5',
							'worstRating'	=>	'1'
						);

						$author = array(
							'@type'			=> 'Person',
							'name'			=> $rev['author'],
						);

						$review_data[] = array(
							'@type'			=> 	'Review',
							'reviewRating'	=> 	$reviewRating,
							'author'		=> 	$author,
							'reviewBody'	=> 	htmlentities($rev['text']),
							'datePublished'	=>	date('Y-m-d', strtotime($rev['date_added']))
						);
					}
				}

				$aggregateRating = array();
				
				if ($review_count > 0) {
					$aggregateRating = array(
						'@type'			=> 	'AggregateRating',
						'ratingValue'	=>	$data['rating'],
						'reviewCount'	=>	$review_count,
						'bestRating'	=> 	'5',
					);
				}

				$offers = array(
					'@type' 			=> 'Offer',
					'url'				=> $url,
					'availability' 		=> $availability,
					'price'				=> $price,
					'priceCurrency'		=> $currencycode,
					'priceValidUntil'	=> $price_date,
				);

				$product_snippet = array(
					'@context' 			=> 	'https://schema.org/',
					'@type'				=> 	'Product',
					'sku'				=> 	$sku,
					'mpn'				=> 	$mpn,
					'image'				=> 	$product_images,
					'name'				=> 	$data['heading_title'],
					'description'		=> 	$description,
					'productID'			=> 	$product_id,
					'brand'				=>	$brand,
					'review'			=> 	$review_data,
					'aggregateRating'	=> 	$aggregateRating,
					'offers'			=> 	$offers,
				);
				
				$ldjson .= '<script type="application/ld+json">';
				$ldjson .= json_encode($product_snippet);
				$ldjson .= "</script>";
			}

			//OPEN GRAPH
			if ($this->config->get('hb_snippets_og_enable')){
				$hb_snippets_ogp = $this->config->get('hb_snippets_ogp');
				if (strlen($hb_snippets_ogp) > 4){				
					$hb_snippets_ogp = str_replace('{name}',$name,$hb_snippets_ogp);
					$hb_snippets_ogp = str_replace('{model}',$model,$hb_snippets_ogp);
					$hb_snippets_ogp = str_replace('{brand}',$brand_name,$hb_snippets_ogp);
					$hb_snippets_ogp = str_replace('{price}',$formatted_price,$hb_snippets_ogp);
				}else{
					$hb_snippets_ogp = $name;
				}
				
				if (strlen($this->config->get('hb_snippets_og_id')) > 5 ){
					$this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
				}
				$this->document->setOpengraph('og:title', $hb_snippets_ogp);
				$this->document->setOpengraph('og:type', 'product');
				$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
				
				$this->load->model('tool/image');
				if ($product_info['image']) {
					$snippet_thumb = $this->model_tool_image->resize($product_info['image'], $this->config->get('hb_snippets_og_piw'), $this->config->get('hb_snippets_og_pih'));
					$this->document->setOpengraph('og:image', $snippet_thumb);
					$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_piw'));
					$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_pih'));
				} 
				
				$this->document->setOpengraph('og:url', $this->url->link('product/product', 'product_id=' . $product_id));
				$this->document->setOpengraph('og:description', $description);
				
				if (!empty($data['images'])) {
					foreach ($data['images'] as $additional_image){
						$this->document->setOpengraph('og:image', $additional_image['popup']);	
						$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_piw'));
						$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_pih'));
					}
				}
				
				if ((float)$product_info['special']) {
					$this->document->setOpengraph('product:sale_price:amount', $price);
					$this->document->setOpengraph('product:sale_price:currency', $currencycode);
					$this->document->setOpengraph('product:original_price:amount', $actual_price);
					$this->document->setOpengraph('product:original_price:currency', $currencycode);
				} else {
					$this->document->setOpengraph('product:original_price:amount', $price);
					$this->document->setOpengraph('product:original_price:currency', $currencycode);
				}

				if ($product_info['quantity'] > 0){
					$this->document->setOpengraph('og:availability', 'instock');
				} else {
					$this->document->setOpengraph('og:availability', 'oos');
				}
				
				if (!empty($data['products'])) {
					foreach ($data['products'] as $product){
						$this->document->setOpengraph('og:see_also', $product['href']);
					}
				}
			}
			//TWITTER CARDS
			if ($this->config->get('hb_snippets_tc_enable')){
				$hb_snippets_tcp = $this->config->get('hb_snippets_tcp');
				if (strlen($hb_snippets_tcp) > 4){				
					$hb_snippets_tcp = str_replace('{name}',$name,$hb_snippets_tcp);
					$hb_snippets_tcp = str_replace('{model}',$model,$hb_snippets_tcp);
					$hb_snippets_tcp = str_replace('{brand}',$brand_name,$hb_snippets_tcp);
					$hb_snippets_tcp = str_replace('{price}',$formatted_price,$hb_snippets_tcp);
				}else{
					$hb_snippets_tcp = $name;
				}
				
				$this->document->setTwittercard('twitter:card', 'summary_large_image');
				$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
				$this->document->setTwittercard('twitter:title', $hb_snippets_tcp);
				$this->document->setTwittercard('twitter:description', $description);
				if ($product_info['image']) {
					$this->document->setTwittercard('twitter:image', $data['popup']);
				}
			}
		}
		
		$this->document->setStructureddata($ldjson);
	}
	
	public function category_social($category_info){
		$this->load->model('tool/image');
		if ($this->config->get('hb_snippets_og_enable')){
			$hb_snippets_ogc = $this->config->get('hb_snippets_ogc');
			if (strlen($hb_snippets_ogc) > 4){
				$ogc_name = $category_info['name'];
				$hb_snippets_ogc = str_replace('{name}',$ogc_name,$hb_snippets_ogc);
			}else{
				$hb_snippets_ogc = $category_info['name'];
			}
			
			if (strlen($this->config->get('hb_snippets_og_id')) > 5 ){
			    $this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
			}
			$this->document->setOpengraph('og:title', $hb_snippets_ogc);
            $this->document->setOpengraph('og:type', 'product.group');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			$this->document->setOpengraph('og:url', $this->url->link('product/category', 'path=' . $category_info['category_id']));
			if ($category_info['image']) {
				$image = $this->model_tool_image->resize($category_info['image'], $this->config->get('hb_snippets_og_ciw'), $this->config->get('hb_snippets_og_cih'));
				$this->document->setOpengraph('og:image', $image);
				$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_ciw'));
				$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_cih'));
			}
			$this->document->setOpengraph('og:description', $category_info['meta_description']);
		}
		
		//TWITTER CARDS
		if ($this->config->get('hb_snippets_tc_enable')){
			$hb_snippets_tcc = $this->config->get('hb_snippets_tcc');
			if (strlen($hb_snippets_tcc) > 4){
				$tcc_name = $category_info['name'];
				$hb_snippets_tcc = str_replace('{name}',$tcc_name,$hb_snippets_tcc);
			}else{
				$hb_snippets_tcc = $category_info['name'];
			}
			
			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $hb_snippets_tcc);
			$this->document->setTwittercard('twitter:description', $category_info['meta_description']);
			if ($category_info['image']) {
				$image = $this->model_tool_image->resize($category_info['image'], $this->config->get('hb_snippets_og_ciw'), $this->config->get('hb_snippets_og_cih'));
			    $this->document->setTwittercard('twitter:image', $image);
			}
		}
	}
	
	public function information_social($information_info){
		if ($this->config->get('hb_snippets_og_enable')){
			if (strlen($this->config->get('hb_snippets_og_id')) > 5 ){
				$this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
			}
			$this->document->setOpengraph('og:title', $information_info['title']);
			$this->document->setOpengraph('og:type', 'website');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			if ($this->config->get('hb_snippets_og_img')) {
				$this->document->setOpengraph('og:image', $this->config->get('config_url') . 'image/' . $this->config->get('hb_snippets_og_img'));
				$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_diw'));
				$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_dih'));
			}
			$this->document->setOpengraph('og:url', $this->url->link('information/information', 'information_id=' .  $information_info['information_id']));
			$this->document->setOpengraph('og:description', $information_info['meta_description']);
		}
		
		//TWITTER CARDS
		if ($this->config->get('hb_snippets_tc_enable')){
			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $information_info['title']);
			$this->document->setTwittercard('twitter:description', $information_info['meta_description']);
			if ($this->config->get('hb_snippets_og_img')) {
				$this->document->setTwittercard('twitter:image', $this->config->get('config_url') . 'image/' . $this->config->get('hb_snippets_og_img'));
			}
			
		}
	}
	
	public function home_social(){
		$this->load->model('tool/image');
		if ($this->config->get('hb_snippets_og_enable')){
			if (strlen($this->config->get('hb_snippets_og_id')) > 5 ){
			        $this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
			    }
				$this->document->setOpengraph('og:title', $this->config->get('config_meta_title'));
				$this->document->setOpengraph('og:type', 'website');
				$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
				if ($this->config->get('hb_snippets_og_img')) {
					$this->document->setOpengraph('og:image', $this->config->get('config_url') . 'image/' . $this->config->get('hb_snippets_og_img'));
					$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_diw'));
					$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_dih'));
				}
				$this->document->setOpengraph('og:url', $this->config->get('config_url'));
				$this->document->setOpengraph('og:description', $this->config->get('config_meta_description'));
		}
		
		//TWITTER CARDS
		if ($this->config->get('hb_snippets_tc_enable')){
			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $this->config->get('config_meta_title'));
			$this->document->setTwittercard('twitter:description', $this->config->get('config_meta_description'));
			if ($this->config->get('hb_snippets_og_img')) {
				$this->document->setTwittercard('twitter:image', $this->config->get('config_url') . 'image/' . $this->config->get('hb_snippets_og_img'));
			}
		}
	}
	
	public function breadcrumbs_sd($breadcrumbs) {		
		if ($this->config->get('hb_snippets_bc_enable')) {
			$ldjson = '';
			$ldjson .= '<script type="application/ld+json">';
			$ldjson .= '{';
			$ldjson .= '"@context": "https://schema.org/",';
			$ldjson .= '"@type": "BreadcrumbList",';
			
			$bclist = array();
			if (!empty($breadcrumbs)) {
				$i = 1;
				foreach ($breadcrumbs as $bc) {
					if ($i == 1){
						$text = 'Home';
					}else{
						$text = $bc['text'];
					}
					$bclist[] = '{"@type": "ListItem", "position": '.$i.', "name": "'.$text.'", "item": "'.$bc['href'].'" }';
					$i++;
				}
			}
			
			if (!empty($bclist)) {
				$bclist = implode(',',$bclist);
				$ldjson .= '"itemListElement": [';
				$ldjson .= $bclist;
				$ldjson .= ' ]';
			}
			
			$ldjson .= '}';
			$ldjson .= "</script>";
		} else {
			$ldjson = '';
		}
		
		$this->document->setStructureddata($ldjson);
	}
	
	public function local_business() {		
		if ($this->config->get('hb_snippets_local_enable')) {
			$ldjson = html_entity_decode($this->config->get('hb_snippets_local_snippet'), ENT_QUOTES, 'UTF-8');
		} else {
			$ldjson = '';
		}
		
		$this->document->setStructureddata($ldjson);
	}
	
	public function knowledge_graph() {		
		if ($this->config->get('hb_snippets_kg_enable')) {
			$ldjson = html_entity_decode($this->config->get('hb_snippets_local_snippet'), ENT_QUOTES, 'UTF-8');
			$store_id = (int)$this->config->get('config_store_id');
			
			if ($this->config->get('config_url')){
				$store_url = $this->config->get('config_url');
			}else{
				$store_url = HTTPS_SERVER;
			}
			
			$ldjson = '';
			$ldjson .= '<script type="application/ld+json">';
			$ldjson .= '{';
			$ldjson .= '"@context": "https://schema.org/",';
			$ldjson .= '"@type": "Organization",';
			$ldjson .= '"name": "'.$this->config->get('config_name').'",';
			$ldjson .= '"url": "'.$store_url.'"';
			
			if ($this->config->get('hb_snippets_logo')) {
				if ($this->config->get('hb_snippets_logo') <> '') {
					$store_logo = $store_url . 'image/' . $this->config->get('hb_snippets_logo');
					$ldjson .= ',"logo": "'.$store_logo.'"';
				}
			}
			
			if ($this->config->get('hb_snippets_contact')) {
				$contacts = $this->config->get('hb_snippets_contact');
				foreach ($contacts as $contact) {
					$c[] = '{"@type": "ContactPoint","telephone": "'.$contact['n'].'","contactType": "'.$contact['t'].'"}';
				}
				$contact_info = implode(',', $c);
				$ldjson .= ',"contactPoint": [ '.$contact_info.' ]';
			}
			
			if ($this->config->get('hb_snippets_socials')) {
				$socials = $this->config->get('hb_snippets_socials');
				foreach ($socials as $social) {
					$s[] = '"'.$social.'"';
				}
				$social_links = implode(',', $s);
				$ldjson .= ',"sameAs": [ '.$social_links.' ]';
			}
			
			$ldjson .= '}';
			$ldjson .= "</script>";
			
			
		} else {
			$ldjson = '';
		}
		
		$this->document->setStructureddata($ldjson);
	}
	
	public function site_search() {		
		if ($this->config->get('hb_snippets_search_enable')) {
			$store_id = (int)$this->config->get('config_store_id');
			
			if ($this->config->get('config_url')){
				$store_url = $this->config->get('config_url');
			}else{
				$store_url = HTTPS_SERVER;
			}
			
			$ldjson = '';
			$ldjson .= '<script type="application/ld+json">';
			$ldjson .= '{';
			$ldjson .= '"@context": "https://schema.org/",';
			$ldjson .= '"@type": "WebSite",';
			$ldjson .= '"url": "'.$store_url.'",';
		
			$search_link = $this->url->link('product/search', 'search=');
			$ldjson .= '"potentialAction": { "@type": "SearchAction", "target": "'.$search_link.'{search_term_string}", "query-input": "required name=search_term_string"}';
			
			$ldjson .= '}';
			$ldjson .= "</script>";
			
		} else {
			$ldjson = '';
		}
		
		$this->document->setStructureddata($ldjson);
	}
	
	public function itemlist($products) {		
		if ($this->config->get('hb_snippets_list_enable') && $products) {
			$store_id = (int)$this->config->get('config_store_id');
			
			if ($this->config->get('config_url')){
				$store_url = $this->config->get('config_url');
			}else{
				$store_url = HTTPS_SERVER;
			}
			
			$ldjson = '';
			$ldjson .= '<script type="application/ld+json">';
			$ldjson .= '{';
			$ldjson .= '"@context": "https://schema.org/",';
			$ldjson .= '"@type": "ItemList",';
			$ldjson .= '"itemListElement":';
			$i = 1;
			
			$list = array();
			
			foreach ($products as $product) {
				$list[] = '{"@type":"ListItem", "position":'.$i.', "url":"'.$product['href'].'","name": "'.$product['name'].'", "image": "'.$product['thumb'].'"}';
				$i++;
			}
			
			$itemlist = implode(',', $list);
			$ldjson .= '[ '.$itemlist.' ]';
			
			$ldjson .= '}';
			$ldjson .= "</script>";
			
		} else {
			$ldjson = '';
		}
		
		$this->document->setStructureddata($ldjson);
	}
	
}