<?php
class ControllerExtensionModuleAnchorPrice extends Controller {
	public function captureProduct($route, $args, $output = null) {
		if (!$this->config->get('module_anchor_price_status')) {
			return;
		}

		$product_id = 0;
		$source = 'import_edit_event';

		if (strpos($route, '/addProduct') !== false) {
			$product_id = (int)$output;
			$source = 'import_add_event';
		} elseif (isset($args[0])) {
			$product_id = (int)$args[0];
		}

		if ($product_id > 0) {
			$this->load->model('extension/module/anchor_price');
			$this->model_extension_module_anchor_price->capturePublishedProduct($product_id, $source);
		}
	}

	public function beforeView(&$route, &$data, &$output = '') {
		if (!$this->config->get('module_anchor_price_status') || !is_array($data) || !$this->isProductPriceView($route)) {
			return;
		}

		$product_ids = array();
		$this->collectProductIds($data, $product_ids);

		if (!$product_ids) {
			return;
		}

		$this->load->model('extension/module/anchor_price');
		$records = $this->model_extension_module_anchor_price->getByProductIds(array_keys($product_ids));

		if (!$records) {
			return;
		}

		$display = array();

		foreach ($records as $product_id => $record) {
			$display[$product_id] = $this->model_extension_module_anchor_price->getDisplayData($record);
		}

		$this->appendDisplayData($data, $display);
	}

	public function cron() {
		$this->response->addHeader('Content-Type: application/json; charset=utf-8');

		if (!$this->config->get('module_anchor_price_status')) {
			$this->response->addHeader('HTTP/1.1 404 Not Found');
			$this->response->setOutput(json_encode(array('success' => false, 'error' => 'Modul nije aktivan.')));
			return;
		}

		$provided_key = '';

		if (!empty($this->request->server['HTTP_X_ANCHOR_PRICE_KEY'])) {
			$provided_key = (string)$this->request->server['HTTP_X_ANCHOR_PRICE_KEY'];
		} elseif (!empty($this->request->get['key'])) {
			// cPanel Cron Jobs can call a protected URL even when custom HTTP
			// headers are not available in its graphical interface.
			$provided_key = (string)$this->request->get['key'];
		}

		$stored_key = (string)$this->config->get('module_anchor_price_cron_key');
		$valid_key = $stored_key !== '' && (function_exists('hash_equals') ? hash_equals($stored_key, $provided_key) : $stored_key === $provided_key);

		if (!$valid_key) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			$this->response->setOutput(json_encode(array('success' => false, 'error' => 'Neispravan ključ.')));
			return;
		}

		$this->load->model('extension/module/anchor_price');
		$result = $this->model_extension_module_anchor_price->generatePublication(false);

		if (empty($result['success'])) {
			$this->response->addHeader('HTTP/1.1 500 Internal Server Error');
		}

		$this->response->setOutput(json_encode($result));
	}

	private function isProductPriceView($route) {
		$view = (string)$route;
		$marker = strpos($view, '/template/');

		if ($marker !== false) {
			$view = substr($view, $marker + 10);
		}

		$allowed = array(
			'product/product',
			'product/quickview',
			'product/category',
			'product/search',
			'product/special',
			'product/manufacturer_info',
			'product/compare',
			'extension/module/featured',
			'extension/module/latest',
			'extension/module/special',
			'extension/module/bestseller',
			'extension/module/basel_products',
			'extension/module/basel_megamenu',
			'account/wishlist',
			'common/header',
			'common/cart',
			'checkout/cart',
			'checkout/confirm',
			'quickcheckout/cart',
			'quickcheckout/confirm',
			'extension/quickcheckout/cart',
			'extension/quickcheckout/confirm',
			'blog/blog'
		);

		return in_array($view, $allowed, true);
	}

	private function collectProductIds($value, &$product_ids) {
		if (!is_array($value)) {
			return;
		}

		$product_id = $this->productIdFromViewData($value);

		if ($product_id > 0) {
			$product_ids[$product_id] = true;
		}

		foreach ($value as $child) {
			if (is_array($child)) {
				$this->collectProductIds($child, $product_ids);
			}
		}
	}

	private function appendDisplayData(&$value, $display) {
		if (!is_array($value)) {
			return;
		}

		$product_id = $this->productIdFromViewData($value);

		if ($product_id > 0 && isset($display[$product_id])) {
			foreach ($display[$product_id] as $key => $field_value) {
				$value[$key] = $field_value;
			}
		}

		foreach ($value as &$child) {
			if (is_array($child)) {
				$this->appendDisplayData($child, $display);
			}
		}
		unset($child);
	}

	private function productIdFromViewData(array $value) {
		if (isset($value['product_id']) && (int)$value['product_id'] > 0) {
			return (int)$value['product_id'];
		}

		// Basel mega-menu products use `id`, while unrelated menu nodes can also
		// have an id. Requiring the product fields avoids treating those as items.
		if (isset($value['id'], $value['name'], $value['price'], $value['link']) && (int)$value['id'] > 0) {
			return (int)$value['id'];
		}

		return 0;
	}
}
