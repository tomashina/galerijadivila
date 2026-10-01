<?php

namespace liveopencart\ext\liveprice;

class product_in_option extends \liveopencart\lib\v0023\sub_library {
  
	use \liveopencart\lib\v0023\traits\cache;
	use \liveopencart\lib\v0023\traits\db;
	
	public function installed() {
		if (!$this->hasCacheSimple('product_in_option_installed')) {
			$query     = $this->db->query("SELECT * FROM  `".DB_PREFIX."modification` WHERE `name` = 'Product in Option' AND `status` = 1 "); // Product Option Price By Customer Group  opencarttools@gmail.com
			$installed = $query->num_rows && $this->existTable('product_product_option_value') && $this->existTable('product_product_option');
			$this->setCacheSimple('product_in_option_installed', $installed);
		}
		return $this->getCacheSimple('product_in_option_installed');
	}
	
	public function extractProductProductOptionsFromOptions(&$options) {
		if ($this->installed()) {
			if (isset($options['product_product_options'])) {
				$product_product_options = $options['product_product_options'];
				unset($options['product_product_options']);
				return $product_product_options;
			}
		}
	}
	
	public function getTotalPriceByProductProductOptions($pp_options) {
		
		$total = 0;
		if ($this->installed()) {
			if (!empty($pp_options) && is_array($pp_options)) {
				foreach ($pp_options as $pp_option_id => $pp_option) {
					if (!empty($pp_option['product_option_value'])) {
						foreach($pp_option['product_option_value'] as $pp_option_value_id => $pp_option_value) {
							if (!empty($pp_option_value['product_id'])) {
								// $pp_option_value_id can be empty
								$total += $this->getProductProductOptionValuePriceByProductProductOptionAndProductProduct($pp_option_id, $pp_option_value['product_id']);
							}
						}
					}
				}
			}
		}
		return $total;
	}
	
	public function getProductAsOptionPriceByCartId($cart_id) {
		
		if ($this->installed()) {
			$product_option_query = $this->db->query("SELECT product_id, product_product_option_value_id FROM ".DB_PREFIX."cart_product_option WHERE cart_id = '".(int)$cart_id."'");
		
			if ($product_option_query->num_rows) {
				//print 'b'.$cart_id;
				$product_option_value_query = $this->db->query("
					SELECT ppov.price, ppov.product_product_id
					FROM " . DB_PREFIX . "product_product_option_value ppov
					LEFT JOIN " . DB_PREFIX . "cart c ON (ppov.product_id = c.product_id)
					WHERE ppov.product_product_option_value_id = '" . (int)$product_option_query->row['product_product_option_value_id'] . "'
					  AND c.product_id = '" . (int)$product_option_query->row['product_id'] . "'
					  AND c.api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "'
					  AND c.customer_id = '" . (int)$this->customer->getId() . "'
					  AND c.session_id = '" . $this->db->escape($this->session->getId()) . "'
				");

				if ( $product_option_value_query->num_rows && !empty($product_option_value_query->row['price'])) {
					return $product_option_value_query->row['price'];
				}
			}
		}
	}
	
	public function getProductAsOptionParentProductIdByCartId($cart_id) {
		if ($this->installed()) {
			$product_option_query = $this->db->query("SELECT product_id, product_product_option_value_id FROM ".DB_PREFIX."cart_product_option WHERE cart_id = '".(int)$cart_id."'");
		
			if ($product_option_query->num_rows) {
				//print 'b'.$cart_id;
				$product_option_value_query = $this->db->query("
					SELECT ppov.price, ppov.product_product_id, ppov.product_id
					FROM " . DB_PREFIX . "product_product_option_value ppov
					LEFT JOIN " . DB_PREFIX . "cart c ON (ppov.product_id = c.product_id)
					WHERE ppov.product_product_option_value_id = '" . (int)$product_option_query->row['product_product_option_value_id'] . "'
					  AND c.product_id = '" . (int)$product_option_query->row['product_id'] . "'
					  AND c.api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "'
					  AND c.customer_id = '" . (int)$this->customer->getId() . "'
					  AND c.session_id = '" . $this->db->escape($this->session->getId()) . "'
				");

				if ( $product_option_value_query->num_rows ) {
					return $product_option_value_query->row['product_id'];
				}
			}
		}
	}
	
	public function getProductProductOptionValuePriceByProductProductOptionAndProductProduct($product_product_option_id, $product_product_id) {
		if ($this->installed()) {
			$query = $this->db->query("
				SELECT *
				FROM `".DB_PREFIX."product_product_option_value`
				WHERE product_product_option_id = ".(int)$product_product_option_id."
				  AND product_product_id = ".(int)$product_product_id."
			");
			if ($query->num_rows) {
				return $query->row['price'];
			}
		}
	}
	
	public function getProductProductOptionValuePrice($product_product_option_value_id) {
		if ($this->installed()) {
			$query = $this->db->query("
				SELECT *
				FROM `".DB_PREFIX."product_product_option_value`
				WHERE product_product_option_value_id = ".(int)$product_product_option_value_id."
			");
			if ($query->num_rows) {
				$query->row['price'];
			}
		}
	}
}
