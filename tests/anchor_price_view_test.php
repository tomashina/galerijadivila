<?php
require_once __DIR__ . '/../upload/system/engine/registry.php';
require_once __DIR__ . '/../upload/system/engine/controller.php';
require_once __DIR__ . '/../upload/system/library/config.php';
require_once __DIR__ . '/../upload/catalog/controller/extension/module/anchor_price.php';

function assertView($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

class AnchorViewLoader {
	public $calls = 0;
	public function model($route) {
		assertView($route === 'extension/module/anchor_price', 'Unexpected model');
		$this->calls++;
	}
}

class AnchorViewModel {
	public $ids = array();
	public function getByProductIds($ids) {
		$this->ids = $ids;
		return array(7 => array('reference_date' => '2026-10-01'));
	}
	public function getDisplayData($record) {
		return array('anchor_price_text' => 'Cijena na 1. 10. 2026.: 8,00€');
	}
}

$registry = new Registry();
$config = new Config();
$config->set('module_anchor_price_status', 1);
$loader = new AnchorViewLoader();
$model = new AnchorViewModel();
$registry->set('config', $config);
$registry->set('load', $loader);
$registry->set('model_extension_module_anchor_price', $model);
$controller = new ControllerExtensionModuleAnchorPrice($registry);

foreach (array('product/product', 'product/quickview') as $route) {
	$data = array('product_id' => 7, 'price' => '8,00€', 'special' => false);
	$controller->beforeView($route, $data);
	assertView(isset($data['anchor_price_text']), 'Detail anchor missing: ' . $route);
	assertView($data['price'] === '8,00€' && $data['special'] === false, 'Current price changed');
}

foreach (array('product/category', 'basel/template/product/search', 'extension/module/basel_products') as $route) {
	$data = array('products' => array(array('product_id' => 7, 'price' => '8,00€')));
	$controller->beforeView($route, $data);
	assertView(isset($data['products'][0]['anchor_price_text']), 'Card anchor missing: ' . $route);
}

$route = 'common/header';
$data = array('menus' => array(array('id' => 200, 'name' => 'Menu'), array('id' => 7, 'name' => 'Product', 'price' => '8,00€', 'link' => '/product')));
$controller->beforeView($route, $data);
assertView($model->ids === array(7), 'Unrelated menu node selected');
assertView(isset($data['menus'][1]['anchor_price_text']), 'Mega-menu anchor missing');

$calls = $loader->calls;
$route = 'account/order';
$data = array('product_id' => 7, 'price' => '8,00€');
$controller->beforeView($route, $data);
assertView($loader->calls === $calls && !isset($data['anchor_price_text']), 'Unrelated view queried');

$config->set('module_anchor_price_status', 0);
$route = 'product/product';
$controller->beforeView($route, $data);
assertView($loader->calls === $calls && !isset($data['anchor_price_text']), 'Disabled module queried');

echo "anchor_price_view_test: OK\n";
