<?php

define('DIR_LANGUAGE', dirname(__DIR__) . '/upload/catalog/language/');

require_once __DIR__ . '/../upload/system/engine/registry.php';
require_once __DIR__ . '/../upload/system/engine/controller.php';
require_once __DIR__ . '/../upload/system/engine/model.php';
require_once __DIR__ . '/../upload/system/library/config.php';
require_once __DIR__ . '/../upload/system/library/language.php';
require_once __DIR__ . '/../upload/catalog/controller/information/price_list.php';
require_once __DIR__ . '/../upload/catalog/controller/extension/account/contract_withdrawal.php';
require_once __DIR__ . '/../upload/catalog/model/extension/module/anchor_price.php';

function assertLanguageValue($actual, $expected, $message) {
	if ($actual !== $expected) {
		throw new RuntimeException($message . ': expected ' . $expected . ', got ' . $actual);
	}
}

function languageRegistry($active_language, $route) {
	$registry = new Registry();
	$config = new Config();
	$config->set('config_language', 'hr-hr');
	$config->set('config_currency', 'EUR');

	$session = new stdClass();
	$session->data = array('language' => $active_language, 'currency' => 'EUR');

	$language = new Language('en-gb');
	$language->load($route);

	$registry->set('config', $config);
	$registry->set('session', $session);
	$registry->set('language', $language);

	return $registry;
}

function invokeCroatianFallback($controller) {
	$method = new ReflectionMethod($controller, 'loadCroatianLanguageFallback');
	$method->setAccessible(true);
	$method->invoke($controller);
}

$price_hr_registry = languageRegistry('hr-hr', 'information/price_list');
invokeCroatianFallback(new ControllerInformationPriceList($price_hr_registry));
assertLanguageValue($price_hr_registry->get('language')->get('heading_title'), 'Cjenici', 'Croatian price-list translation was not loaded');
assertLanguageValue($price_hr_registry->get('language')->get('column_published'), 'Vrijeme objave', 'Croatian price-list columns were not loaded');

$price_en_registry = languageRegistry('en-gb', 'information/price_list');
invokeCroatianFallback(new ControllerInformationPriceList($price_en_registry));
assertLanguageValue($price_en_registry->get('language')->get('heading_title'), 'Price lists', 'English price-list translation was overridden');

$withdrawal_hr_registry = languageRegistry('hr-hr', 'extension/account/contract_withdrawal');
invokeCroatianFallback(new ControllerExtensionAccountContractWithdrawal($withdrawal_hr_registry));
assertLanguageValue($withdrawal_hr_registry->get('language')->get('heading_title'), 'Obrazac za jednostrani raskid ugovora', 'Croatian withdrawal translation was not loaded');

$withdrawal_en_registry = languageRegistry('en-gb', 'extension/account/contract_withdrawal');
invokeCroatianFallback(new ControllerExtensionAccountContractWithdrawal($withdrawal_en_registry));
assertLanguageValue($withdrawal_en_registry->get('language')->get('heading_title'), 'Contract withdrawal form', 'English withdrawal translation was overridden');

class LanguageFallbackCurrency {
	public function format($amount, $currency_code) {
		return number_format((float)$amount, 2, ',', '.') . ' ' . $currency_code;
	}
}

$anchor_hr_registry = languageRegistry('hr-hr', 'hr-hr');
$anchor_hr_registry->set('currency', new LanguageFallbackCurrency());
$anchor_hr = new ModelExtensionModuleAnchorPrice($anchor_hr_registry);
$anchor_record = array(
	'gross_price' => 100,
	'reference_date' => '2026-10-01',
	'rule_code' => 'baseline_configured',
	'verification_status' => 'confirmed'
);
$anchor_hr_data = $anchor_hr->getDisplayData($anchor_record);
assertLanguageValue(strpos($anchor_hr_data['anchor_price_text'], 'Cijena na ') === 0, true, 'Croatian anchor-price label was not selected');

$anchor_en_registry = languageRegistry('en-gb', 'en-gb');
$anchor_en_registry->set('currency', new LanguageFallbackCurrency());
$anchor_en = new ModelExtensionModuleAnchorPrice($anchor_en_registry);
$anchor_en_data = $anchor_en->getDisplayData($anchor_record);
assertLanguageValue(strpos($anchor_en_data['anchor_price_text'], 'Price on ') === 0, true, 'English anchor-price label was not selected');

echo "croatian_language_fallback_test: OK\n";
