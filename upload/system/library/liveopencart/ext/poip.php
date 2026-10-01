<?php
//  Product Option Image PRO
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

namespace liveopencart\ext;

class poip extends \liveopencart\lib\v0023\extension {

	protected $extension_code 	= 'poip3';
	protected $version = '3.1.7';
	protected $theme_details = '';
	protected $resource_route_catalog = 'view/theme/extension_liveopencart/product_option_image_pro/';
	protected $resource_subroute_catalog_theme = 'theme/';
	
	protected $extension_model;
	protected $extension_language;
	protected $extension_paths;
	
	public function versionUltimate() {
		return ($this->getExtensionCode() == 'poiu3');
	}
	
	// separate init, but not in __construct to have no trouble with endless loop on possible calls for initLibrary from models loaded in the library
	protected function init() {
		if ( class_exists('\liveopencart\ext\poiu') ) {
			$this->extension_code = \liveopencart\ext\poiu::getInstance($this->registry)->getExtensionCode();
		}
		
		$this->extension_paths = array();
		if ( $this->inAdminSection() ) { // admin section
			$this->extension_paths['model_path'] 		= 'extension/module/product_option_image_pro';
			$this->extension_paths['language_path'] 	= 'extension/module/product_option_image_pro';
		} else { // customer section
			$this->extension_paths['model_path'] 		= 'extension/liveopencart/product_option_image_pro';
			$this->extension_paths['language_path'] 	= 'extension/liveopencart/product_option_image_pro';
		}
		$this->extension_paths['model_id'] = 'model_'.str_replace('/', '_', $this->extension_paths['model_path']);
		
		// library also loads the model (accessible standard way from registry or by $this->liveopencart_ext_poip->getModel() )
		$this->loadModel();
		$this->loadLanguageOwn();
		$this->loadLanguageNewOnly();
		
		$this->registry->set('liveopencart_poip', $this->liveopencart_ext_poip);
	}
	
	protected function inAdminSection() {
		return defined('DIR_CATALOG') && defined('HTTP_CATALOG');
	}
	
	protected function loadModel() {
		$this->load->model( $this->extension_paths['model_path'] );
		$this->extension_model = $this->registry->get( $this->extension_paths['model_id'] );
	}
	
	protected function loadLanguageOwn() {
		if ( !$this->extension_language ) {
			$this->language->load( $this->extension_paths['language_path'], $this->extension_code.'_language' ); // Put the language into a sub key
			$this->extension_language = $this->language->get( $this->extension_code.'_language' );
		}
	}
	
	public function loadLanguage() {
		$this->language->load( $this->extension_paths['language_path'] );
	}
	
	public function loadLanguageNewOnly() {
		$existing_lang_vars = $this->language->all();
		$extension_lang_vars = $this->getLanguageOwn()->all();
		$new_lang_keys = array_diff( array_keys($extension_lang_vars), array_keys($existing_lang_vars) );
		foreach ( $new_lang_keys as $new_lang_key ) {
			$this->language->set($new_lang_key, $extension_lang_vars[$new_lang_key]);
		}
	}
	
	public function getLanguageOwn() {
		return $this->extension_language;
	}
	
	public function getExtensionCode() {
		return $this->extension_code;
	}
	
	public function getModel() {
		return $this->extension_model;
	}
	
	public function installed() {
		return $this->getExtensionInstalledStatus('product_option_image_pro', 'poip_installed');
	}
	public function installedRO() {
		return $this->getExtensionInstalledStatus('related_options', 'ro_installed');
	}
	
	// to share protected methods
	public function hasCachedValue($cache_name, $cache_key=0) {
		return $this->hasCache($cache_name, $cache_key);
	}
	
	// to share protected methods
	public function getCachedValue($cache_name, $cache_key=0) {
		return $this->getCache($cache_name, $cache_key);
	}
	
	// to share protected methods
	public function setCachedValue() {
		
		$args = func_get_args();
		if ( count($args) == 2 ) {
			$cache_name = $args[0];
			$cache_key = 0;
			$cache_value = $args[1];
		} elseif ( count($args) == 3) {
			$cache_name = $args[0];
			$cache_key = $args[1];
			$cache_value = $args[2];
		}
		$this->setCache($cache_name, $cache_key, $cache_value);
	}
	
	// some thing should be caches in the customer section only
	protected function hasCachedValueCatalog($cache_name, $cache_key=0) {
		return !$this->inAdminSection() && $this->hasCachedValue($cache_name, $cache_key);
	}
	
	protected function getCachedValueCatalog($cache_name, $cache_key=0) {
		if ( !$this->inAdminSection() ) {
			return $this->getCachedValue($cache_name, $cache_key);
		}
	}
	protected function setCachedValueCatalog() {
		if ( !$this->inAdminSection() ) {
			return call_user_func_array( array($this, 'setCachedValue') , func_get_args());
		}
	}
	
	public function getModuleSettingsIds($for_option_or_product_page=true) {
		$settings = array();
		
		if (!$for_option_or_product_page) {
			$settings[] = "options_images_edit";
			if ( $this->versionUltimate() ) {
				$settings[] = "images_for_ro";
			}
			$settings[] = "img_hover";
			$settings[] = "img_click";
			$settings[] = "img_main_to_additional";
			//$settings[] = "img_gal";
		}
		
		$settings[] = "img_change";
		
		$settings[] = "img_use";
		$settings[] = "img_limit";
		if (!$for_option_or_product_page) {
			$settings[] = "img_filter_by_comb";
			$settings[] = "img_load_outofstock";
		}
		
		$settings[] = "img_option";
		
		$settings[] = "img_first";
		$settings[] = "dependent_thumbnails";
		$settings[] = "img_radio_checkbox";
		$settings[] = "img_cart";
		
		$settings[] = "img_category";
		if (!$for_option_or_product_page) {
			$settings[] = "img_category_click";
		}
    
    
		return $settings;
	}
	
	// for comp with old adaptations
	public function getResourceLinkPathWithVersion($path, $prefix='') {
		return $this->liveopencart_ext_poip->getResourceLinkWithVersion($path, $prefix);
	}

	
	public function getOptionSettings($product_option_id) {
    
		if (!$this->liveopencart_ext_poip->installed()) return array();
		
		$query = $this->db->query("
			SELECT PMOS.*, PS.product_option_id
			FROM ".DB_PREFIX."poip_main_option_settings PMOS, ".DB_PREFIX."product_option PS
			WHERE PS.product_option_id = ".(int)$product_option_id."
			  AND PS.option_id = PMOS.option_id
		");
		
		if ($query->num_rows) {
			return $query->row;
		}
		
		return array();
    
	}
	
	public function getModuleSettings() {
		return $this->config->get('poip_module');
	}
	
	public function getSetting($setting_name) {
		$settings = $this->getModuleSettings();
		if ( isset($settings[$setting_name]) ) {
			return $settings[$setting_name];
		}
	}
	
	// exact (determined) options settings
	public function getProductOptionSettings($product_option_id) {
    
		$option_settings = array();
		if (!$this->installed()) return $option_settings;
		
		$poip_settings = $this->config->get('poip_module');
		$poip_option_settings = $this->getOptionSettings($product_option_id);
		
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."poip_option_settings WHERE product_option_id = ".(int)$product_option_id." ");
		
		$settings_names = $this->getModuleSettingsIds();
		
		foreach ($settings_names as $setting_name) {
			if ($query->row && isset($query->row[$setting_name]) && $query->row[$setting_name] != 0) {
				$option_settings[$setting_name] = $query->row[$setting_name]-1;
				
			} elseif (isset($poip_option_settings[$setting_name]) && $poip_option_settings[$setting_name] != 0) {
				$option_settings[$setting_name] = $poip_option_settings[$setting_name]-1;
				
			} elseif (isset($poip_settings[$setting_name])) {
				$option_settings[$setting_name] = $poip_settings[$setting_name];
				
			} else {  
				$option_settings[$setting_name] = false;
			}
		}
		
	
		return $option_settings;
    
	}
	
	// all product settings together (for all options)
	public function getProductSettings($product_id) {
		
		if ( $this->hasCachedValueCatalog(__FUNCTION__, $product_id) ) {
			return $this->getCachedValueCatalog(__FUNCTION__, $product_id);
		}
		
		$query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = ".(int)$product_id."  ");
		$poip_settings = array();
		foreach ($query->rows as $row) {
			$poip_settings[$row['product_option_id']] = $this->getProductOptionSettings($row['product_option_id']);
		}
		
		$this->setCachedValueCatalog(__FUNCTION__, $product_id, $poip_settings);
		
		return $poip_settings;
	}
	
	public function saveImagesForROCombs($product_id, $ro_combs, $option_data) {
		if ( $this->versionUltimate() ) {
			$this->liveopencart_ext_poiu->saveImagesForROCombs($product_id, $ro_combs, $option_data);
		} else {
			$this->db->query("DELETE FROM ".DB_PREFIX."poip_option_image WHERE product_id = ".(int)$product_id." AND relatedoptions_id != 0 ");
		}
		
	}
	
	protected function getProductOptionImages($product_id) { // the function is different in catalog and admin (takes more parameters in catalog, but here we need only one)
		return $this->getModel()->getProductOptionImages($product_id);
	}
	
	public function getProductCartOrderImages($product_id, $option_data) {
		
		$result_images = array();
		
		if ( $this->installed() ) {
			$selected_product_option = array();
			$selected_product_option_value = array();
			$selected_options_grupped = [];
			foreach ($option_data as $option_value_data) {
				$po_id = $option_value_data['product_option_id'];
				$pov_id = $option_value_data['product_option_value_id'];
				if (!in_array($po_id, $selected_product_option)) {
					$selected_product_option[] = $po_id;
				}
				
				$pov_ids = is_array($option_value_data['product_option_value_id']) ? $option_value_data['product_option_value_id'] : [$option_value_data['product_option_value_id']];
				
				foreach ( $pov_ids as $pov_id ) {
					if (!in_array($po_id, $selected_product_option_value)) {
						$selected_product_option_value[] = $pov_id;
					}
					if ( !isset($selected_options_grupped[$po_id]) ) {
						$selected_options_grupped[$po_id] = [];
					}
					$selected_options_grupped[$po_id][] = $pov_id;
				}
			}
			
			$product_images = $this->getProductOptionImages($product_id);
			
			if ( count($product_images) > 0 ) {
				
				$product_settings = $this->getProductSettings($product_id);
				
				$cart_options = array();
				$filter_options = array();
				foreach ($product_images as $product_option_id => $product_option_images ) {
					
					if ( in_array($product_option_id, $selected_product_option) ) { // option value is selected
						
						$images_count = 0;
						
						foreach ($product_option_images as $product_option_value => $product_option_value_images) {
							$images_count = $images_count + count($product_option_value_images);
						}
						
						if ($images_count > 0) {
							if ( !empty($product_settings[$product_option_id]['img_cart']) ) {
								$cart_options[] = $product_option_id;
	
							}
						}
					}
				}
				
				$images = false;
				$all_option_images = array();
				
				if ( $cart_options ) {
					
					// for main image in the shopping cart always try to use filtering (get image relevant to all selected options)
					foreach ($cart_options as $po_id) {
						
						if ( isset($selected_options_grupped[$po_id]) && isset($product_images[$po_id]) ) {
							
							$product_option_images = $product_images[$po_id];
							$selected_pov_ids = $selected_options_grupped[$po_id];
							
							$product_option_image_pov_ids = array_keys($product_option_images);
							foreach ( $selected_pov_ids as $pov_id ) {
								
								if ( isset($product_option_images[$pov_id]) ) {
									$current_images = [];
									foreach ($product_option_images[$pov_id] as $image_info) {
										if (!in_array($image_info['image'], $current_images)) {
											$current_images[] = $image_info['image'];
										}
									}
								
									$all_option_images = array_values( array_unique( array_merge($all_option_images, $current_images) ) );
								
									if (count($current_images) > 0) {
										if ($images === false) {
											$images = $current_images;
										} else {
											$images = array_values(array_intersect($images, $current_images));
										}
									}
								}
							}

						}
					}
					
					if ( !$images ) {
						$images = $all_option_images;
					}
						
					$result_images = $images;
					
				}
				
			}
		}
		return $result_images;
	}
	
	public function getProductCartOrderImage($product_id, $option_data, $image) {
		  
		if (!$this->installed()) {
			return $image;
		}
		
		$images = $this->getProductCartOrderImages($product_id, $option_data);
		if ($images && count($images)>0) {
			$image = $images[0];
		}
		
		return $image;
	  
	}
	
	
}