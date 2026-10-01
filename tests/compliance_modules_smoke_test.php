<?php

function assertContains($needle, $path, $message) {
	$contents = file_get_contents($path);

	if ($contents === false || strpos($contents, $needle) === false) {
		throw new RuntimeException($message . ': ' . $path);
	}
}

function assertNotContains($needle, $path, $message) {
	$contents = file_get_contents($path);

	if ($contents !== false && strpos($contents, $needle) !== false) {
		throw new RuntimeException($message . ': ' . $path);
	}
}

$root = dirname(__DIR__);
$admin_anchor = $root . '/upload/admin/model/extension/module/anchor_price.php';
$catalog_anchor = $root . '/upload/catalog/model/extension/module/anchor_price.php';
$anchor_controller = $root . '/upload/admin/controller/extension/module/anchor_price.php';
$withdrawal_controller = $root . '/upload/catalog/controller/extension/account/contract_withdrawal.php';
$withdrawal_model = $root . '/upload/catalog/model/extension/account/contract_withdrawal.php';
$withdrawal_form = $root . '/upload/catalog/view/theme/basel/template/extension/account/contract_withdrawal_form.twig';

assertContains("catalog/view/*/before", $anchor_controller, 'Anchor storefront event is missing');
assertContains('generateDailyPublications', $admin_anchor, 'Daily publication generator is missing');
assertContains("const ARCHIVE_DAYS = 30", $catalog_anchor, 'Thirty-day public archive is missing');
assertContains("'barcode' => \$barcode", $admin_anchor, 'CSV/XML barcode output is missing');
assertNotContains("p.jan", $admin_anchor, 'JAN must not be used as the Galerija Divila barcode');
assertNotContains("p.jan", $catalog_anchor, 'JAN must not be used as the Galerija Divila barcode');

assertContains('contract_withdrawal_csrf', $withdrawal_controller, 'Withdrawal CSRF protection is missing');
assertContains('isRateLimited($ip, 5, 60)', $withdrawal_controller, 'Withdrawal rate limit is missing');
assertContains('validate($order_info, $order_products)', $withdrawal_controller, 'A matched order must be required before withdrawal submission');
assertContains("if (!\$order_info)", $withdrawal_controller, 'Unmatched withdrawal requests must be rejected');
assertContains('idx_ip_date', $withdrawal_model, 'Withdrawal rate-limit index is missing');
assertContains('name="website"', $withdrawal_form, 'Withdrawal honeypot is missing');
assertContains('text_privacy_note', $withdrawal_form, 'Withdrawal GDPR notice is missing');
assertContains('name="confirm" value="1"', $withdrawal_form, 'Withdrawal confirmation action is missing');

echo "compliance_modules_smoke_test: OK\n";
