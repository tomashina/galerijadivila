<?php
// class ModelExtensioncompgafad extends Model {

// class ModelExtensionModulecompgafad extends Model {
	
//namespace Opencart\Admin\Model\Extension\compgafad\Module;
//class compgafad extends \Opencart\System\Engine\Model {

//$this->load->model($this->modpath);
//$json['script'] = $this->{$this->modvar}->getaddtocart($product_id, $quantity, $option);

class ModelExtensionModulecompgafad extends Model {
	private $modpath = 'module/compgafad'; 
	private $modvar = 'model_module_compgafad';
	private $modname = 'compgafad';
	private $status = false;
	private $setting = array();
	
	public function __construct($registry) {
		parent::__construct($registry);
		ini_set("serialize_precision", -1);
		
		if(substr(VERSION,0,3)=='2.3') {
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
		}
		if(substr(VERSION,0,3)=='3.0') {			
			$this->modpath = 'extension/module/compgafad';
			$this->modvar = 'model_extension_module_compgafad';
			$this->modname = 'module_compgafad';
		} 
		if(substr(VERSION,0,3)=='4.0') {
			$this->modpath = 'extension/compgafad/module/compgafad';
			$this->modvar = 'model_extension_compgafad_module_compgafad';
			$this->modname = 'module_compgafad';
		}
		
		$this->setting = $this->getSetting();
		$this->status = ($this->config->get($this->modname.'_status') && $this->setting['status']) ? true : false;	
 	}	
	public function pageview() {
		if($this->status) {
			$g_code = '';
			if($this->setting['gmid']) {
				$g_code .= '<!-- Global site tag (gtag.js) - Google Analytics -->
				<script async src="https://www.googletagmanager.com/gtag/js?id='.($this->setting['gmid']).'"></script>
				<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag(\'js\', new Date());';
				if($this->setting['gmid']) { 
					$g_code .= 'gtag(\'config\', \''.$this->setting['gmid'].'\');';
				}
				if($this->setting['prch_adwid']) {
					$g_code .= 'gtag(\'config\', \''.$this->setting['prch_adwid'].'\', {\'allow_enhanced_conversions\':true});';
				}
				$g_code .= '</script>';
			}
			
			$jsnm = 'compgafad.js';
			if(substr(VERSION,0,5) == '4.0.2') { $jsnm = 'compgafad_402.js'; }
			
			$src = 'catalog/view/javascript/'.$jsnm.'?vr='.rand();
			if(substr(VERSION,0,3) == '4.0') {
				$src = 'extension/compgafad/catalog/view/javascript/'.$jsnm.'?vr='.rand();
			}
			
			$g_code .= '<script src="'.$src.'" type="text/javascript"></script>';
			
			return $g_code;
		}			
	}
	public function login() {
		if($this->status) {
			if($this->setting['gmid']) { 
				return "<script type='text/javascript'> gtag('event', 'login', {'method': 'Account Login'}); </script>";
			}
		}
	}
	public function logoutbefore() {
		$this->session->data['compgafad_logout_flag'] = 1;
	}
	public function logout() {
		if($this->status && isset($this->session->data['compgafad_logout_flag'])) {
			unset($this->session->data['compgafad_logout_flag']);
			if($this->setting['gmid']) { 
				return "<script type='text/javascript'> gtag('event', 'logout', {'method': 'Logout'}); </script>";
			}
		}
	}
	public function signupbefore() {
		$this->session->data['compgafad_signup_flag'] = 1;
	}
	public function signup() {
		$code = '';
		if($this->status && isset($this->session->data['compgafad_signup_flag'])) {
			unset($this->session->data['compgafad_signup_flag']);
			if($this->setting['gmid']) {
				$code .= "<script type='text/javascript'> gtag('event', 'sign_up', {'method': 'Signup'}); </script>";
			}
			$code .= $this->get_adw(1.0, 'sign_up');
		}
		return $code;
	}
	public function contact() {
		if($this->status) {
			if($this->setting['gmid']) { 
				return "<script type='text/javascript'> gtag('event', 'contact', {'event_category': 'contact', 'event_label': 'contact'}); </script>";
			}
		}
	}
	public function addtocart() {
		$json['script'] = false;
		if ($this->status && isset($this->request->post['product_id']) && isset($this->request->post['quantity'])) {
			$pid = (int)$this->request->post['product_id'];
			$quantity = (int)$this->request->post['quantity'];
			
			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}
				
			$this->load->model('catalog/product');
			
			$pinfo = $this->model_catalog_product->getProduct($pid);
			
			if ($pinfo) {
				$json = array();
				
				if ((int)$quantity >= $pinfo['minimum']) {
					$quantity = (int)$this->request->post['quantity'];
				} else {
					$quantity = $pinfo['minimum'] ? $pinfo['minimum'] : 1;
				}
				
				if(substr(VERSION,0,3)=='4.0') {
					$product_options = $this->model_catalog_product->getOptions($pid);
				} else {
					$product_options = $this->model_catalog_product->getProductOptions($pid);
				}
	
				foreach ($product_options as $product_option) {
					if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
						$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
					}
				}

				if (!$json) {
					// do add to cart
					$option_price = 0;
	
					foreach ($option as $product_option_id => $value) {
						$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$pid . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");
	
						if ($option_query->num_rows) {
							if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
	
								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}	
								}
							} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
								foreach ($value as $product_option_value_id) {
									$option_value_query = $this->db->query("SELECT pov.option_value_id, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix, ovd.name FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
	
									if ($option_value_query->num_rows) {
										if ($option_value_query->row['price_prefix'] == '+') {
											$option_price += $option_value_query->row['price'];
										} elseif ($option_value_query->row['price_prefix'] == '-') {
											$option_price -= $option_value_query->row['price'];
										}
									}
								}
							}
						}
					}
					
					$pinfo['price'] = $pinfo['special'] ? $pinfo['special'] : $pinfo['price'];
					
					$pinfo['quantity'] = $quantity;
					
					$g_pdata = array($pinfo);
					$g_value = $this->tax->calculate($pinfo['price'] + $option_price, $pinfo['tax_class_id'], $this->config->get('config_tax')) * $quantity;
					
					$gadw = $this->get_adw($g_value, 'add_to_cart');
					
					$json['script'] = $this->get_gevent('add_to_cart', 'ecommerce', 'add_to_cart', $g_pdata, $g_value) . $gadw;
				}
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function addtowishlist() {
		$json['script'] = false;
		if($this->status && isset($this->request->post['product_id']) && isset($this->request->post['quantity'])) {	
			$pid = (int)$this->request->post['product_id'];
			$quantity = (int)$this->request->post['quantity'];	
			
			$this->load->model('catalog/product');
			
			$pinfo = $this->model_catalog_product->getProduct($pid);
			
			if ($pinfo) {
				if ((int)$quantity >= $pinfo['minimum']) {
					$quantity = (int)$this->request->post['quantity'];
				} else {
					$quantity = $pinfo['minimum'] ? $pinfo['minimum'] : 1;
				}
				
				$pinfo['price'] = $pinfo['special'] ? $pinfo['special'] : $pinfo['price'];
					
				$pinfo['quantity'] = $quantity;
				
				$g_pdata = array($pinfo);
				$g_value = $this->tax->calculate($pinfo['price'], $pinfo['tax_class_id'], $this->config->get('config_tax')) * $quantity;
				
				$json['script'] = $this->get_gevent('add_to_wishlist', 'ecommerce', htmlspecialchars_decode(strip_tags($pinfo['name'])), $g_pdata, $g_value);
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function viewcont() {
		if($this->status && isset($this->request->get['product_id'])) { 
   			$this->load->model('catalog/product');
			
			$pinfo = $this->model_catalog_product->getProduct($this->request->get['product_id']);
			
			if($pinfo) {
				$pinfo['price'] = $pinfo['special'] ? $pinfo['special'] : $pinfo['price'];
				$pinfo['quantity'] = $pinfo['minimum'] ? $pinfo['minimum'] : 1;
				
				$g_pdata = array($pinfo);
				$g_value = $this->tax->calculate($pinfo['price'], $pinfo['tax_class_id'], $this->config->get('config_tax'));
				
				return $this->get_gevent('view_item', 'ecommerce', htmlspecialchars_decode(strip_tags($pinfo['name'])), $g_pdata, $g_value);
			}
		}
	}
	public function viewcategory() {
		if($this->status && !empty($this->request->get['path'])) {			
			$this->load->model('catalog/product');
			
			$path = '';
 			$parts = explode('_', (string)$this->request->get['path']);
 			$category_id = (int)end($parts);
			$catname = $this->getcatnamefromID($category_id);
			
			$pinfo = array();
			$ptotal = array();
			$result = $this->getcategory($category_id);

			if($result) {
				foreach($result as $rs) {
					$pid = $rs['product_id'];
					$pdata = $this->model_catalog_product->getProduct($pid);
					$pdata['price'] = $pdata['special'] ? $pdata['special'] : $pdata['price'];
 					$pdata['quantity'] = $pdata['minimum'] ? $pdata['minimum'] : 1;
					
					$pinfo[$pid] = $pdata;
					$pinfo[$pid]['price'] = $pdata['price'];
 					$ptotal[] = $this->tax->calculate($pdata['price'], $pdata['tax_class_id'], $this->config->get('config_tax'));
				}
				
				$g_pdata = $pinfo;
				$g_value = array_sum($ptotal);
				
				return $this->get_gevent('view_category', 'ecommerce', htmlspecialchars_decode(strip_tags($catname)), $g_pdata, $g_value);
			}
		}
	}
	public function search() {
		if($this->status && !empty($this->request->get['search'])) {
			$srchstr = $this->request->get['search'];
			$this->load->model('catalog/product');
			
			$pinfo = array();
			$pinfo = array();
			$result = $this->getsearchrs($this->request->get['search']);
			
			if($result) {
				foreach($result as $rs) {
					$pid = $rs['product_id'];
					$pdata = $this->model_catalog_product->getProduct($pid);
					$pdata['price'] = $pdata['special'] ? $pdata['special'] : $pdata['price'];
 					$pdata['quantity'] = $pdata['minimum'] ? $pdata['minimum'] : 1;
					
					$pinfo[$pid] = $pdata;
					$pinfo[$pid]['price'] = $pdata['price'];
 					$ptotal[] = $this->tax->calculate($pdata['price'], $pdata['tax_class_id'], $this->config->get('config_tax'));
				}
				
				$g_pdata = $pinfo;
				$g_value = array_sum($ptotal);
				
				return $this->get_gevent('search', 'ecommerce', htmlspecialchars_decode(strip_tags($srchstr)), $g_pdata, $g_value, $srchstr);
			}
		}
	}
	public function remove_from_cart() {
		if (isset($this->request->post['key']) || isset($this->request->get['remove'])) {
			foreach($this->cart->getProducts() as $cartprod) {
				if((isset($cartprod['key']) && $cartprod['key'] == $this->request->get['remove']) || 
					(isset($cartprod['key']) && $cartprod['key'] == $this->request->post['key']) || 
					(isset($cartprod['cart_id']) && $cartprod['cart_id'] == $this->request->post['key'])) 
				{
					$g_pdata = array($cartprod);
					$g_value = $cartprod['total'];
					
					$this->session->data['event_removecart_code'] = $this->get_gevent('remove_from_cart', 'ecommerce', 'remove_from_cart', $g_pdata, $g_value);
				}
			}
		}
	}
	public function viewcart() {
		if($this->status && $this->cart->hasProducts()) {
			$g_pdata = $this->cart->getProducts();
			$g_value = $this->cart->getTotal();
			
			$g_code = $this->get_gevent('view_cart', 'ecommerce', 'view_cart', $g_pdata, $g_value);
			
			if(isset($this->session->data['event_removecart_code'])) {
				$g_code .= $this->session->data['event_removecart_code'];
				unset($this->session->data['event_removecart_code']);
			}
			
			return $g_code;
		}
	}
	public function beginchk() {
		if($this->status && $this->cart->hasProducts()) {
			$g_pdata = $this->cart->getProducts();
			$g_value = $this->cart->getTotal();
			
			return $this->get_gevent('begin_checkout', 'ecommerce', 'begin_checkout', $g_pdata, $g_value) . $this->get_adw($g_value, 'begin_checkout');
		}
	}
	public function purchasebefore() {
		if(isset($this->session->data['order_id'])) { 
			$this->session->data['compgafad_order_id'] = $this->session->data['order_id'];
		} else if(isset($this->session->data['xsuccess_order_id'])) { 
			$this->session->data['compgafad_order_id'] = $this->session->data['xsuccess_order_id'];
		} else {
			$this->session->data['compgafad_order_id'] = $this->getorderid();
		}
	}
	public function purchase() {
		$this->purchasebefore();
		if($this->status && !empty($this->session->data['compgafad_order_id'])) {
			$this->set_ord_flg($this->session->data['compgafad_order_id']);
			
			$order_id = $this->session->data['compgafad_order_id'];
			unset($this->session->data['compgafad_order_id']);			
			
			$this->load->model('checkout/order');
 			$orderdata = $this->model_checkout_order->getOrder($order_id);
 			$orderdata['order_products'] = $this->getorderproduct($order_id); 
			$orderdata['order_tax'] = $this->getordertax($order_id);
			$orderdata['order_shipping'] = $this->getordershipping($order_id);
			
			$g_pdata = $orderdata['order_products'];
			$g_value = $orderdata['total'];
			$srchstr = '';
			
			$gcode_adw = $this->get_adwconv($orderdata);
			
			return $this->get_gevent('purchase', 'ecommerce', 'purchase', $g_pdata, $g_value, $srchstr, $orderdata) . $gcode_adw;
		}
	}
	
	// Helpers
	public function getSetting() {		
		$storeid = $this->config->get('config_store_id');
		
		$setting = $this->config->get($this->modname.'_setting');		
		
		$setting['status'] = (!isset($setting[$storeid]['status'])) ? false : $setting[$storeid]['status'];
		$setting['themenm'] = (!isset($setting[$storeid]['themenm'])) ? 'def' : $setting[$storeid]['themenm'];
		$setting['gmid'] = (!isset($setting[$storeid]['gmid'])) ? '' : $setting[$storeid]['gmid'];
		$setting['prch_adwid'] = (!isset($setting[$storeid]['prch_adwid'])) ? '' : $setting[$storeid]['prch_adwid'];
		$setting['prch_adwlbl'] = (!isset($setting[$storeid]['prch_adwlbl'])) ? '' : $setting[$storeid]['prch_adwlbl'];
		$setting['bgnchk_adwid'] = (!isset($setting[$storeid]['bgnchk_adwid'])) ? '' : $setting[$storeid]['bgnchk_adwid'];
		$setting['bgnchk_adwlbl'] = (!isset($setting[$storeid]['bgnchk_adwlbl'])) ? '' : $setting[$storeid]['bgnchk_adwlbl'];
		$setting['addtc_adwid'] = (!isset($setting[$storeid]['addtc_adwid'])) ? '' : $setting[$storeid]['addtc_adwid'];
		$setting['addtc_adwlbl'] = (!isset($setting[$storeid]['addtc_adwlbl'])) ? '' : $setting[$storeid]['addtc_adwlbl'];
		$setting['signup_adwid'] = (!isset($setting[$storeid]['signup_adwid'])) ? '' : $setting[$storeid]['signup_adwid'];
		$setting['signup_adwlbl'] = (!isset($setting[$storeid]['signup_adwlbl'])) ? '' : $setting[$storeid]['signup_adwlbl'];
		
		return $setting;		
	}
	public function get_adw($val, $evname) { 
		$adwid = 0;
		$adw_currency = $this->session->data['currency'];
		
		if($this->setting['signup_adwid'] && $evname == 'sign_up') { 
			$adwid = $this->setting['signup_adwid'];
			$adwlbl = $this->setting['signup_adwlbl'];
		} else if($this->setting['addtc_adwid'] && $evname == 'add_to_cart') {
			$adwid = $this->setting['addtc_adwid'];
			$adwlbl = $this->setting['addtc_adwlbl'];
		} else if($this->setting['bgnchk_adwid'] && $evname == 'begin_checkout') {
			$adwid = $this->setting['bgnchk_adwid'];
			$adwlbl = $this->setting['bgnchk_adwlbl'];
		}	
		
		if($adwid) {
			return "<script type=\"text/javascript\"> gtag('event', 'conversion', {'send_to': '$adwid/$adwlbl', 'event_name': '$evname', 'value': '$val', 'currency': '$adw_currency' }); </script>";
		}
			
		return '';
	}
	public function get_adwconv($orderdata) {
		//ADW
		$adw_enh_data = array();
		if(!empty($orderdata['email'])) { $adw_enh_data['email'] = $orderdata['email']; }
		if(!empty($orderdata['telephone'])) { $adw_enh_data['phone_number'] = $orderdata['telephone']; }
		if(!empty($orderdata['firstname'])) { $adw_enh_data['first_name'] = $orderdata['firstname']; }
		if(!empty($orderdata['lastname'])) { $adw_enh_data['last_name'] = $orderdata['lastname']; }
		if(!empty($orderdata['payment_address_1'])) { $adw_enh_data['home_address']['street'] = $orderdata['payment_address_1']; }
		if(!empty($orderdata['payment_city'])) { $adw_enh_data['home_address']['city'] = $orderdata['payment_city']; }
		if(!empty($orderdata['payment_zone'])) { $adw_enh_data['home_address']['region'] = $orderdata['payment_zone']; }
		if(!empty($orderdata['payment_postcode'])) { $adw_enh_data['home_address']['postal_code'] = $orderdata['payment_postcode']; }
		if(!empty($orderdata['payment_country'])) { $adw_enh_data['home_address']['country'] = $orderdata['payment_country']; }
						
		$adwid = $this->setting['prch_adwid'];
		$adwlbl = $this->setting['prch_adwlbl'];
		$adw_currency = $this->session->data['currency'];
		$adw_order_id = $orderdata['order_id'];
		$adw_total = $this->getcurval($orderdata['total']);
			
$code1 = ''; $code2 = '';

if($this->setting['prch_adwid']) {
$code1 = $this->get_gsetdata($orderdata);

if($adw_enh_data) { 
$code1 .= "<script>var enhanced_conversion_data = ".json_encode($adw_enh_data, true).";</script>"; 
}
$code2 = "<script type=\"text/javascript\"> gtag('event', 'conversion', {'send_to': '$adwid/$adwlbl', 'transaction_id': '$adw_order_id', 'value': '$adw_total', 'currency': '$adw_currency' }); </script>";
}

return $code1 . $code2;
	}
	public function get_gsetdata($orderdata) {
		$adw_enh_data = array();
		if(!empty($orderdata['email'])) { $adw_enh_data['sha256_email_address'] = hash('sha256', $orderdata['email']); }
		if(!empty($orderdata['telephone'])) { $adw_enh_data['sha256_phone_number'] = hash('sha256', $orderdata['telephone']); }
		if(!empty($orderdata['firstname'])) { $adw_enh_data['address']['sha256_first_name'] = hash('sha256', $orderdata['firstname']); }
		if(!empty($orderdata['lastname'])) { $adw_enh_data['address']['sha256_last_name'] = hash('sha256', $orderdata['lastname']); }
		if(!empty($orderdata['payment_address_1'])) { $adw_enh_data['address']['street'] = $orderdata['payment_address_1']; }
		if(!empty($orderdata['payment_city'])) { $adw_enh_data['address']['city'] = $orderdata['payment_city']; }
		if(!empty($orderdata['payment_zone'])) { $adw_enh_data['address']['region'] = $orderdata['payment_zone']; }
		if(!empty($orderdata['payment_postcode'])) { $adw_enh_data['address']['postal_code'] = $orderdata['payment_postcode']; }
		if(!empty($orderdata['payment_country'])) { $adw_enh_data['address']['country'] = $orderdata['payment_country']; }
		if($adw_enh_data) { 
			return "<script>gtag('set', 'user_data', ".json_encode($adw_enh_data, true).";</script>"; 
		}
	}
	public function get_gevent($evname, $evcat = '', $evlbl = 0, $g_pdata = array(), $g_value = 0, $srchstr = '', $orderdata = array()) {
 		if($this->status && $this->setting['gmid']) {			
			$cnt = -1; 
			$gitems = array();							
					
 			if($g_pdata) { 
				foreach ($g_pdata as $pinfo) {
					$cnt++;
					if(isset($pinfo['tax_class_id'])) {
						$pinfo['price'] = $this->tax->calculate($pinfo['price'], $pinfo['tax_class_id'], $this->config->get('config_tax'));
					}
					if(isset($pinfo['tax'])) {
						$pinfo['price'] = $pinfo['price'] + $pinfo['tax'];
					}
					$catname = $this->getcatname($pinfo['product_id']);
					$brand_name = $this->getbrandname($pinfo['product_id']);				
					
					$gitems[$cnt] = array(
						'affiliation' => htmlspecialchars_decode(strip_tags($this->getstorename())),
						'id' => $pinfo['model'] ? $pinfo['model'] : $pinfo['product_id'],
						'name' => htmlspecialchars_decode(strip_tags($pinfo['name'])),
						'item_id' => $pinfo['model'] ? $pinfo['model'] : $pinfo['product_id'],
						'item_name' => htmlspecialchars_decode(strip_tags($pinfo['name'])),
						'item_brand' => htmlspecialchars_decode(strip_tags($brand_name)),
						'item_category' => htmlspecialchars_decode(strip_tags($catname)),
						'currency' => $this->session->data['currency'],
						'price' => $this->getcurval($pinfo['price']),
						'quantity' => $pinfo['quantity'],
						'index' => $cnt,
						'list_position' => $cnt,
					);
				}
			}
			
			$gtag = array(
				'affiliation' => htmlspecialchars_decode(strip_tags($this->getstorename())),
				'event_category' => (!empty($catname) && $evcat) ? $evcat : $catname,
				'event_label' => $evlbl,
				'currency' => $this->session->data['currency'],
				'value' => $this->getcurval($g_value),
 			);
			
			if($orderdata) {			
				$gtag['transaction_id'] = $orderdata['order_id'];
				$gtag['tax'] = $orderdata['order_tax'];
				$gtag['shipping'] = $orderdata['order_shipping'];			
			}
			
			if($gitems) { $gtag['items'] = $gitems; }
			
			if(!empty($srchstr)) { $gtag['search_term'] = htmlspecialchars_decode(strip_tags($srchstr)); }
						
			if(isset($this->session->data['coupon']) && $evlbl == 'ecommerce') {
				$gtag['coupon'] = $this->session->data['coupon'];

			}
 			
			return "<script type='text/javascript'> gtag('event', '".$evname."', ".json_encode($gtag,true)."); </script>";			
		}
		return '';
	}
	public function get_page_url() {
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "https://";		 
		$url.= $_SERVER['HTTP_HOST'];
		$url.= $_SERVER['REQUEST_URI'];
		return $url;
	}
	public function set_ord_flg($order_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` set compgafad_ordflag = 1 where order_id = '" . (int)$order_id . "' ");		
	}
	public function getorderid() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE compgafad_ordflag = 0 and date(date_added) = curdate() and order_status_id > 0 AND ip like '" . $this->db->escape($_SERVER['REMOTE_ADDR']) . "' order by date_added desc limit 1");		
		if($query->num_rows) {
			return $query->row['order_id'];
		}
		return 0;
	}
	public function getProduct($pid) {
		if($pid) { 
			$query = $this->db->query("SELECT DISTINCT *, pd.name, pd.meta_description, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$pid . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
			
			if ($query->num_rows) {
				$query->row['price'] = $query->row['discount'] ? $query->row['discount'] : $query->row['price'];
				return $query->row;
			} else {
				return false;
			}
		}
		return false;
	}
	public function getstorename() {
		$stq = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store WHERE store_id = '".(int)$this->config->get('config_store_id')."' ");
		return htmlspecialchars_decode(strip_tags(isset($stq->row['name']) ? $stq->row['name'] : $this->config->get('config_name')));
	}
	public function getcatname($product_id) {
		if($product_id) { 
			$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description cd 
			INNER JOIN " . DB_PREFIX . "product_to_category pc ON pc.category_id = cd.category_id 
			WHERE 1 AND pc.product_id = '".$product_id."' AND cd.language_id = '". (int)$this->config->get('config_language_id') ."' limit 1");
			return htmlspecialchars_decode(strip_tags((!empty($query->row['name'])) ? $query->row['name'] : ''));
		} 
		return '';
	}
	public function getcatnamefromID($category_id) {
		if($category_id) { 
			$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description cd
			WHERE 1 AND category_id = '".$category_id."' AND cd.language_id = '". (int)$this->config->get('config_language_id') ."' limit 1");
			return htmlspecialchars_decode(strip_tags((!empty($query->row['name'])) ? $query->row['name'] : ''));
		} 
		return '';
	}
	public function getbrandname($pid) {
		if($pid) { 
			$query = $this->db->query("SELECT name from " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "product p on m.manufacturer_id = p.manufacturer_id WHERE 1 AND p.product_id = ".$pid);
			return htmlspecialchars_decode(strip_tags((!empty($query->row['name'])) ? $query->row['name'] : ''));
		}
		return '';
	}
	public function getprorel($pid) {
		$q = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr 
		LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) 
		LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
		WHERE pr.product_id = '" . (int)$pid . "' AND p.status = '1' 
		AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		return $q->rows;
	}
	public function getcategory($category_id) {
		$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p 
		LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
		LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
		LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
		WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
		AND p2c.category_id = '" . (int)$category_id . "'
		AND p.status = '1' AND p.date_available <= NOW() 
		AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$sql .= " GROUP BY p.product_id LIMIT 5";
		
		$query = $this->db->query($sql);
			
		return $query->rows;
	}
	public function getsearchrs($srchstr) {
		$filter_data = array('filter_name' => $srchstr, 'start' => 0, 'limit' => 5);
		
		$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p 
		LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
		LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
		WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
		AND p.status = '1' AND p.date_available <= NOW() 
		AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$data['filter_name'] = $srchstr;
		if (!empty($data['filter_name'])) {
			$sql .= " AND ( pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
			$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			$sql .= ")";
		}
		$sql .= " GROUP BY p.product_id LIMIT 5";
		
		$query = $this->db->query($sql);
			
		return $query->rows;
	}
	public function getorderproduct($order_id) {
 		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' ");
 		return $query->rows;
	}
	public function getordertax($order_id) {
 		$q = $this->db->query("SELECT sum(value) as taxval FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'tax'");
		if (isset($q->row['taxval']) && $q->row['taxval']) {
			return $this->getcurval($q->row['taxval']);
		} 
		return 0;
	}
	public function getordershipping($order_id) {
 		$q = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'");
		if (isset($q->row['value']) && $q->row['value']) {
			return $this->getcurval($q->row['value']);
		} 
		return 0;
	}
	public function getcurval($taxprc) {
		return round($this->currency->format($taxprc, $this->session->data['currency'], false, false),2);
	}
	public function GetIP() {
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 0;
		return $ipaddress;
	}
}