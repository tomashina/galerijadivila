<?php
//  Product Option Image Ultimate
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

namespace liveopencart\ext;

class poiu extends \liveopencart\lib\v0023\extension {
	
	protected $extension_code 	= 'poiu3';
	
	protected function installed() {
		return $this->liveopencart_ext_poip->installed();
	}
	
	public function installedRO() {
		return $this->liveopencart_ext_poip->installedRO();
	}
	
	protected function getROImagesAssocByROIds($product_id) {
		$query = $this->db->query("
			SELECT *
			FROM ".DB_PREFIX."poip_option_image
			WHERE product_id = ".(int)$product_id."
				AND relatedoptions_id != 0
			ORDER BY relatedoptions_id ASC, sort_order ASC
			");
		
		$used_images = array();
		$poip_images_by_ro_ids = array();
		foreach ( $query->rows as $poip_image ) {
			$ro_id = $poip_image['relatedoptions_id'];
			if ( !isset($poip_images_by_ro_ids[$ro_id]) ) {
				$poip_images_by_ro_ids[$ro_id] = array();
				$used_images[$ro_id] = array();
			}
			if ( !in_array($poip_image['image'], $used_images[$ro_id]) ) { // do not display the same image twice
				$poip_images_by_ro_ids[$ro_id][] = $this->liveopencart_ext_poip->getModel()->getImagePropertiesForProductForm($poip_image);
				$used_images[$ro_id][] = $poip_image['image'];
			}
		}
		return $poip_images_by_ro_ids;
	}
	
	public function loadImagesToROData($product_id, $ro_data) {
		
		if ( $this->installed() && $this->liveopencart_ext_poip->getSetting('images_for_ro') ) {
	
			$poip_images_by_ro_ids = $this->getROImagesAssocByROIds($product_id);
			
			foreach ( $ro_data as &$ro_dt ) {
				foreach ( $ro_dt['ro'] as &$ro_comb ) {
					$ro_id = $ro_comb['relatedoptions_id'];
					if ( isset($poip_images_by_ro_ids[$ro_id]) ) {
						$ro_comb['images'] = $poip_images_by_ro_ids[$ro_id];
					}
				}
				unset($ro_comb);
			}
			unset($ro_dt);
			
			return $ro_data;
		}	
	}
	
	public function removeROImagesByProductId($product_id) {
		$this->removeROImagesByFilter( " product_id = ".(int)$product_id );
	}
	
	public function removeROImagesByFilter($sql_where="") {
		
		if ( $this->installed() ) {
		
			if ( !$sql_where ) { // remove all
				$query = $this->db->query("DELETE FROM ".DB_PREFIX."poip_option_image WHERE relatedoptions_id != 0 ");
			} else {
				$ro_comb_sql = "SELECT relatedoptions_id FROM ".DB_PREFIX."relatedoptions WHERE ".$sql_where;
				$query = $this->db->query("DELETE FROM ".DB_PREFIX."poip_option_image WHERE relatedoptions_id != 0 AND relatedoptions_id IN(".$ro_comb_sql.") " );
			}
		}
	}
	
	public function saveImagesForROCombsWithOptionIds($product_id, $ro_combs, $ro_ids, $product_option_id, $product_option_value_id) {
		
		if ( $this->installed() && $this->liveopencart_ext_poip->getSetting('images_for_ro') ) {
			foreach ( $ro_ids as $ro_id ) {
				$ro_comb = $ro_combs[$ro_id];
				if ( !empty($ro_comb['images']) ) {
					foreach ( $ro_comb['images'] as $image_data ) {
						if ( $image_data['image'] ) {
							$sort_order = isset($image_data['srt']) ? $image_data['srt'] : 0;
							$this->liveopencart_ext_poip->getModel()->insertProductOptionImage($product_id, $product_option_id, $product_option_value_id, $image_data['image'], $sort_order, $ro_id);

						}
					}
				}
			}
		}
	}
	
	public function saveImagesForROCombs($product_id, $ro_combs, $option_data) {
		if ( $this->installed() && $this->liveopencart_ext_poip->getSetting('images_for_ro') ) {
			foreach ( $option_data as $option_id => $option_values ) {
				
				$product_option_id = $this->getProductOptionIdByOptionId($product_id, $option_id);
				if ( $product_option_id ) {
					
					foreach ( $option_values as $option_value_id => $option_value_data ) {
						
						if ( $option_value_data['ro_ids'] ) {
							$product_option_value_id = $this->getProductOptionValueIdByOptionValueId($product_option_id, $option_value_id);
							$this->saveImagesForROCombsWithOptionIds($product_id, $ro_combs, $option_value_data['ro_ids'], $product_option_id, $product_option_value_id);
						}
					}
				}
			}
		}
	}
	
	// result:
	// 1 = image added
	// 0 = ro comb not found
	// -1 = image already exists
	public function saveImageForROComb($product_id, $ro_id, $image, $sort_order) {
		
		$result = 0;
		
		if ( $this->installedRO() ) {
			
			$options = \liveopencart\ext\ro::getInstance($this->registry)->getROCombBasicOptionValues($ro_id);
			
			if ( $options ) {
				$result = 1;
				$image_exist_cnt = 0;
				foreach ( $options as $option_id => $option_value_id ) {
					
					$pov = $this->getProductOptionValueByOptionIdAndOptionValueId($product_id, $option_id, $option_value_id);
					if ( $pov ) {
						
						if ( $this->liveopencart_ext_poip->getModel()->existProductOptionImage($product_id, $pov['product_option_id'], $pov['product_option_value_id'], $image, $sort_order, $ro_id) ) {
							$image_exist_cnt++;
						} else {
							$this->liveopencart_ext_poip->getModel()->insertProductOptionImage($product_id, $pov['product_option_id'], $pov['product_option_value_id'], $image, $sort_order, $ro_id);
						}
					}
				}
				if ( $image_exist_cnt == count($options) ) {
					$result = -1;
				}
			}
		}
		return $result;
	}
	
	protected function getProductOptionValueByOptionIdAndOptionValueId($product_id, $option_id, $option_value_id) {
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option_value WHERE product_id = ".(int)$product_id." AND option_id = ".(int)$option_id." AND option_value_id = ".(int)$option_value_id." ");
		if ( $query->num_rows ) {
			return $query->row;
		}
	}
	
	protected function getProductOptionIdByOptionId($product_id, $option_id) {
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option WHERE product_id = ".(int)$product_id." AND option_id = ".(int)$option_id." ");
		if ( $query->num_rows ) {
			return $query->row['product_option_id'];
		}
	}
	protected function getProductOptionValueIdByOptionValueId($product_option_id, $option_value_id) {
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option_value WHERE product_option_id = ".(int)$product_option_id." AND option_value_id = ".(int)$option_value_id." ");
		if ( $query->num_rows ) {
			return $query->row['product_option_value_id'];
		}
	}
	
}