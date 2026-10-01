<?php
class ModelExtensionModuleAnchorPrice extends Model {
	const ARCHIVE_DAYS = 30;
	const PUBLICATION_LOCATION_CODE = 'WEB';

	private $table_exists;
	private $audit_table_exists;
	private $publication_language_id;
	private $public_tax;
	private $default_customer_group_id;

	public function tableExists() {
		if ($this->table_exists !== null) {
			return $this->table_exists;
		}

		$query = $this->db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->db->escape(DB_DATABASE) . "' AND TABLE_NAME = '" . $this->db->escape(DB_PREFIX . "anchor_price") . "' LIMIT 1");
		$this->table_exists = (bool)$query->num_rows;

		return $this->table_exists;
	}

	public function getByProductIds($product_ids, $store_id = null) {
		$records = array();

		if (!$this->config->get('module_anchor_price_status') || !$this->tableExists() || !is_array($product_ids)) {
			return $records;
		}

		$ids = array();

		foreach ($product_ids as $product_id) {
			$product_id = (int)$product_id;

			if ($product_id > 0) {
				$ids[$product_id] = $product_id;
			}
		}

		if (!$ids) {
			return $records;
		}

		if ($store_id === null) {
			$store_id = (int)$this->config->get('config_store_id');
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price` WHERE store_id = '" . (int)$store_id . "' AND verification_status = 'confirmed' AND product_id IN (" . implode(',', $ids) . ")");

		foreach ($query->rows as $row) {
			$records[(int)$row['product_id']] = $row;
		}

		return $records;
	}

	public function getDisplayData($record) {
		if (!$record || empty($record['reference_date'])) {
			return array();
		}

		$currency_code = !empty($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
		$price = $this->currency->format((float)$record['gross_price'], $currency_code);
		$timestamp = strtotime($record['reference_date']);
		$language_code = (string)$this->config->get('config_language');

		if (strpos($language_code, 'hr') === 0 || strpos($language_code, 'croatia') === 0) {
			$date = date('j. n. Y.', $timestamp);
			$text = 'Cijena na ' . $date . ': ' . $price;
		} else {
			$date = date('j M Y', $timestamp);
			$text = 'Price on ' . $date . ': ' . $price;
		}

		return array(
			'anchor_price'       => $price,
			'anchor_price_value' => $price,
			'anchor_price_date'  => $date,
			'anchor_price_text'  => $text,
			'anchor_price_rule'  => $record['rule_code'],
			'anchor_price_status'=> $record['verification_status']
		);
	}

	public function syncMissingProducts($source = 'cron_sync') {
		if (!$this->tableExists() || !$this->auditTableExists()) {
			return 0;
		}

		$currency_code = $this->config->get('config_currency');
		$store_id = (int)$this->config->get('config_store_id');
		$count = 0;
		$now = new DateTime('now', new DateTimeZone('Europe/Zagreb'));
		$observed_date = $now->format('Y-m-d');

		$query = $this->db->query("SELECT p.product_id, p.price, p.tax_class_id FROM `" . DB_PREFIX . "product` p INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p2s.product_id = p.product_id AND p2s.store_id = '" . $store_id . "') LEFT JOIN `" . DB_PREFIX . "anchor_price` ap ON (ap.product_id = p.product_id AND ap.store_id = p2s.store_id) WHERE ap.anchor_price_id IS NULL AND p.status = '1' AND p.date_available <= NOW()");

		$tax = $this->getPublicTax();

		foreach ($query->rows as $product) {
			// A missing active product has no trustworthy historical first-listing date.
			// Keep the observed value pending until an administrator verifies it.
			$anchor_date = $observed_date;
			$rule_code = 'first_listing';
			$gross_price = $tax->calculate((float)$product['price'], (int)$product['tax_class_id'], true);
			$tax_context = $tax->getRates((float)$product['price'], (int)$product['tax_class_id']);
			$tax_context_json = json_encode($tax_context);

			if ($tax_context_json === false) {
				$tax_context_json = '{}';
			}

			$this->db->query('START TRANSACTION');
			try {
				$this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "anchor_price` SET product_id = '" . (int)$product['product_id'] . "', store_id = '" . $store_id . "', price = '" . (float)$product['price'] . "', gross_price = '" . (float)$gross_price . "', currency_code = '" . $this->db->escape($currency_code) . "', tax_class_id = '" . (int)$product['tax_class_id'] . "', tax_context = '" . $this->db->escape($tax_context_json) . "', reference_date = '" . $this->db->escape($anchor_date) . "', rule_code = '" . $this->db->escape($rule_code) . "', source = '" . $this->db->escape($source) . "', verification_status = 'pending', created_by = '0', date_added = NOW(), date_modified = NOW()");

				if ($this->db->countAffected()) {
					$anchor_price_id = (int)$this->db->getLastId();
					$this->addSnapshotAudit($anchor_price_id, $product, $store_id, $currency_code, $gross_price, $tax_context_json, $anchor_date, $rule_code, $source, 'pending');
					$count++;
				}
				$this->db->query('COMMIT');
			} catch (Exception $exception) {
				$this->db->query('ROLLBACK');
				throw $exception;
			}
		}

		return $count;
	}

	public function capturePublishedProduct($product_id, $source = 'import_edit_event') {
		if (!$this->tableExists() || !$this->auditTableExists()) {
			return false;
		}

		$product_id = (int)$product_id;
		$store_id = (int)$this->config->get('config_store_id');
		$source = $source === 'import_add_event' ? 'import_add_event' : 'import_edit_event';
		$now = new DateTime('now', new DateTimeZone('Europe/Zagreb'));
		$query = $this->db->query("SELECT p.product_id, p.price, p.tax_class_id FROM `" . DB_PREFIX . "product` p INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p2s.product_id = p.product_id AND p2s.store_id = '" . $store_id . "') LEFT JOIN `" . DB_PREFIX . "anchor_price` ap ON (ap.product_id = p.product_id AND ap.store_id = p2s.store_id) WHERE p.product_id = '" . $product_id . "' AND p.status = '1' AND p.date_available <= '" . $this->db->escape($now->format('Y-m-d')) . "' AND ap.anchor_price_id IS NULL LIMIT 1");

		if (!$query->num_rows) {
			return false;
		}

		$product = $query->row;
		$currency_code = (string)$this->config->get('config_currency');
		$tax = $this->getPublicTax();
		$gross_price = $tax->calculate((float)$product['price'], (int)$product['tax_class_id'], true);
		$tax_context = json_encode($tax->getRates((float)$product['price'], (int)$product['tax_class_id']));

		if ($tax_context === false) {
			$tax_context = '{}';
		}

		$verification_status = $source === 'import_add_event' ? 'confirmed' : 'pending';
		$reference_date = $now->format('Y-m-d');
		$this->db->query('START TRANSACTION');
		try {
			$this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "anchor_price` SET product_id = '" . $product_id . "', store_id = '" . $store_id . "', price = '" . (float)$product['price'] . "', gross_price = '" . (float)$gross_price . "', currency_code = '" . $this->db->escape($currency_code) . "', tax_class_id = '" . (int)$product['tax_class_id'] . "', tax_context = '" . $this->db->escape($tax_context) . "', reference_date = '" . $this->db->escape($reference_date) . "', rule_code = 'first_listing', source = '" . $this->db->escape($source) . "', verification_status = '" . $verification_status . "', created_by = '0', date_added = NOW(), date_modified = NOW()");

			if (!$this->db->countAffected()) {
				$this->db->query('COMMIT');
				return false;
			}

			$this->addSnapshotAudit((int)$this->db->getLastId(), $product, $store_id, $currency_code, $gross_price, $tax_context, $reference_date, 'first_listing', $source, $verification_status);
			$this->db->query('COMMIT');
		} catch (Exception $exception) {
			$this->db->query('ROLLBACK');
			throw $exception;
		}

		return true;
	}

	private function addSnapshotAudit($anchor_price_id, array $product, $store_id, $currency_code, $gross_price, $tax_context, $reference_date, $rule_code, $source, $verification_status) {
		if (!$this->auditTableExists()) {
			return;
		}

		$new_data = json_encode(array(
			'price' => number_format((float)$product['price'], 4, '.', ''),
			'gross_price' => number_format((float)$gross_price, 4, '.', ''),
			'currency_code' => $currency_code,
			'tax_class_id' => (int)$product['tax_class_id'],
			'tax_context' => $tax_context,
			'reference_date' => $reference_date,
			'rule_code' => $rule_code,
			'source' => $source,
			'verification_status' => $verification_status
		));

		if ($new_data === false) {
			$new_data = '{}';
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "anchor_price_audit` SET anchor_price_id = '" . (int)$anchor_price_id . "', product_id = '" . (int)$product['product_id'] . "', store_id = '" . (int)$store_id . "', user_id = '0', action = 'create', old_data = '{}', new_data = '" . $this->db->escape($new_data) . "', reason = 'Automatic snapshot: " . $this->db->escape($source) . "', date_added = NOW()");
	}

	private function auditTableExists() {
		if ($this->audit_table_exists === null) {
			$query = $this->db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->db->escape(DB_DATABASE) . "' AND TABLE_NAME = '" . $this->db->escape(DB_PREFIX . "anchor_price_audit") . "' LIMIT 1");
			$this->audit_table_exists = (bool)$query->num_rows;
		}

		return $this->audit_table_exists;
	}

	public function generatePublication($force = false) {
		if (!$this->tableExists() || !$this->publicationTableExists()) {
			return array('success' => false, 'error' => 'Modul sidrenih cijena nije instaliran.');
		}

		$store_id = (int)$this->config->get('config_store_id');
		$location_code = self::PUBLICATION_LOCATION_CODE;
		$lock_name = 'anchor_price_publication_' . $store_id;
		$lock = $this->db->query("SELECT GET_LOCK('" . $this->db->escape($lock_name) . "', 10) AS acquired");

		if (empty($lock->row['acquired'])) {
			return array('success' => false, 'error' => 'Druga objava cjenika je već u tijeku.');
		}

		try {
			$this->discardUnpublishedPublications($store_id);
			$this->syncMissingProducts('price_list_sync');
			$products = $this->getPublicationProducts($store_id);
			$this->assertPublicationProducts($products);
			if (!$products) {
				throw new Exception('Cjenik nema nijedan potvrđen aktivan proizvod.');
			}
			$now = new DateTime('now', new DateTimeZone('Europe/Zagreb'));

			if (!$force) {
				$existing = $this->getTodayPublication($store_id, $location_code, $now);
				if ($existing && $this->publishedBatchIsValid($store_id, $existing['batch_key'])) {
					$this->expireOldPublications($now);
					$this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock_name) . "')");
					return array('success' => true, 'locations' => array($location_code => array('success' => true, 'existing' => true, 'publication' => $existing)));
				}
			}

			$batch_key = $this->createBatchKey();
			$result = $this->generateLocationPublication($location_code, $products, $now, $batch_key);

			if (empty($result['success'])) {
				$this->expireOldPublications($now);
				$this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock_name) . "')");
				return array('success' => false, 'locations' => array($location_code => $result));
			}

			$result = $this->publishPublication($result, $now, $batch_key);
			$this->expireOldPublications($now);
			$this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock_name) . "')");

			return array('success' => true, 'locations' => array($location_code => $result));
		} catch (Exception $exception) {
			if (isset($result) && !empty($result['success']) && empty($result['existing']) && !empty($result['publication'])) {
				$this->invalidatePublication($result['publication'], 'Dnevni cjenik nije dovršen.');
			}
			$this->db->query("SELECT RELEASE_LOCK('" . $this->db->escape($lock_name) . "')");
			return array('success' => false, 'error' => $exception->getMessage());
		}
	}

	private function getTodayPublication($store_id, $location_code, DateTime $now) {
		$start = clone $now;
		$start->setTime(0, 0, 0);
		$end = clone $start;
		$end->modify('+1 day');
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . (int)$store_id . "' AND location_code = '" . $this->db->escape($location_code) . "' AND status = 'published' AND published_at >= '" . $this->db->escape($start->format('Y-m-d H:i:s')) . "' AND published_at < '" . $this->db->escape($end->format('Y-m-d H:i:s')) . "' ORDER BY publication_id DESC LIMIT 1");

		if (!$query->num_rows) {
			return array();
		}

		$publication = $query->row;
		if (!$this->publicationFilesAreValid($publication)) {
			$this->invalidatePublication($publication, 'Postojeći dnevni CSV/XML nedostaje ili mu se SHA-256 kontrolni zbroj ne podudara.');
			return array();
		}

		return $publication;
	}

	private function publishedBatchIsValid($store_id, $batch_key) {
		$batch_key = strtolower(trim((string)$batch_key));
		if (!preg_match('/^[a-f0-9]{32}$/', $batch_key)) {
			return false;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . (int)$store_id . "' AND batch_key = '" . $this->db->escape($batch_key) . "' AND status = 'published'");

		return $query->num_rows === 1 && $this->validatedPublication($query->row, array(self::PUBLICATION_LOCATION_CODE));
	}

	private function invalidatePublication(array $publication, $reason) {
		foreach (array('csv', 'xml') as $format) {
			$path = $this->publicationPath($publication, $format);
			if ($path && is_file($path)) {
				@unlink($path);
			}
		}
		$this->db->query("UPDATE `" . DB_PREFIX . "anchor_price_publication` SET status = 'failed', error_message = '" . $this->db->escape(substr($reason, 0, 2000)) . "' WHERE publication_id = '" . (int)$publication['publication_id'] . "'");
	}

	private function generateLocationPublication($location_code, array $products, DateTime $now, $batch_key) {
		$location_code = strtoupper(preg_replace('/[^A-Z0-9_-]/i', '', (string)$location_code));

		if ($location_code !== self::PUBLICATION_LOCATION_CODE) {
			return array('success' => false, 'error' => 'Nepoznata oznaka prodajnog mjesta.');
		}

		$store_id = (int)$this->config->get('config_store_id');
		$sequence_query = $this->db->query("SELECT COALESCE(MAX(sequence_no), 0) + 1 AS next_sequence FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . $store_id . "' AND location_code = '" . $this->db->escape($location_code) . "'");
		$sequence_no = (int)$sequence_query->row['next_sequence'];
		$location = $this->publicationLocation($location_code);
		$base_filename = 'cjenik_' . $location['address'] . '_' . str_pad($sequence_no, 6, '0', STR_PAD_LEFT) . '_' . $now->format('Ymd_His');
		$filename = $base_filename . '.csv';
		$xml_filename = $base_filename . '.xml';
		$relative_path = 'anchor_price/' . $filename;
		$xml_relative_path = 'anchor_price/' . $xml_filename;

		$this->db->query("INSERT INTO `" . DB_PREFIX . "anchor_price_publication` SET store_id = '" . $store_id . "', batch_key = '" . $this->db->escape($batch_key) . "', location_code = '" . $this->db->escape($location_code) . "', sequence_no = '" . $sequence_no . "', filename = '" . $this->db->escape($filename) . "', relative_path = '" . $this->db->escape($relative_path) . "', xml_filename = '" . $this->db->escape($xml_filename) . "', xml_relative_path = '" . $this->db->escape($xml_relative_path) . "', status = 'generating', product_count = '0', checksum_sha256 = '', xml_checksum_sha256 = '', error_message = '', published_at = NULL, date_added = '" . $this->db->escape($now->format('Y-m-d H:i:s')) . "'");
		$publication_id = $this->db->getLastId();

		$directory = rtrim(DIR_DOWNLOAD, '/\\') . DIRECTORY_SEPARATOR . 'anchor_price';

		if (!is_dir($directory) && !@mkdir($directory, 0750, true)) {
			return $this->failPublication($publication_id, 'Nije moguće pripremiti mapu javnih cjenika.');
		}

		$final_path = $directory . DIRECTORY_SEPARATOR . $filename;
		$temp_path = $final_path . '.tmp.' . str_replace('.', '', uniqid('', true));
		$xml_final_path = $directory . DIRECTORY_SEPARATOR . $xml_filename;
		$xml_temp_path = $xml_final_path . '.tmp.' . str_replace('.', '', uniqid('', true));
		$handle = @fopen($temp_path, 'wb');
		$xml_products = array();

		if (!$handle) {
			return $this->failPublication($publication_id, 'Nije moguće otvoriti privremenu CSV datoteku.');
		}

		if (fwrite($handle, "\xEF\xBB\xBF") === false) {
			fclose($handle);
			@unlink($temp_path);
			return $this->failPublication($publication_id, 'Pogreška pri zapisu oznake kodiranja CSV datoteke.');
		}
		$headers = array('Prodajni kanal', 'ID proizvoda', 'Naziv proizvoda', 'Šifra/model', 'SKU', 'Marka/proizvođač', 'Jedinica mjere', 'Cijena po jedinici (EUR)', 'Redovna maloprodajna cijena (EUR)', 'Aktualna maloprodajna cijena (EUR)', 'Poseban oblik prodaje', 'Naziv posebnog oblika prodaje', 'Aktualna akcijska cijena (EUR)', 'Sidrena cijena (EUR)', 'Datum sidrene cijene', 'Barkod', 'Dostupnost', 'Količina', 'Status zalihe', 'Valuta');
		if (!$this->writeCsvRow($handle, $headers)) {
			fclose($handle);
			@unlink($temp_path);
			return $this->failPublication($publication_id, 'Pogreška pri zapisu zaglavlja CSV datoteke.');
		}

		$product_count = 0;
		$currency_code = $this->config->get('config_currency');
		$default_unit = trim((string)$this->config->get('module_anchor_price_default_unit'));

		if ($default_unit === '') {
			$default_unit = 'kom';
		}

		$tax = $this->getPublicTax();

		foreach ($products as $product) {
			$regular_gross = $tax->calculate((float)$product['price'], (int)$product['tax_class_id'], true);
			$has_discount = ($product['discount'] !== null && $product['discount'] !== '' && (float)$product['discount'] > 0);
			$base_gross = $has_discount ? $tax->calculate((float)$product['discount'], (int)$product['tax_class_id'], true) : $regular_gross;
			$has_special = ($product['special'] !== null && $product['special'] !== '');
			$special_gross = $has_special ? $tax->calculate((float)$product['special'], (int)$product['tax_class_id'], true) : '';
			$has_sale_price = $has_special || $has_discount;
			$selling_gross = $has_special ? $special_gross : $base_gross;
			$barcode = $this->validPublicationBarcode($product);
			$is_available = (int)$product['quantity'] > 0;
			$stock_status = $is_available ? 'Dostupno' : $this->csvText($product['stock_status']);

			$row = array(
				'Web trgovina',
				$product['product_id'],
				$this->csvText($product['name']),
				$this->csvText($product['model']),
				$this->csvText($product['sku']),
				$this->csvText($product['manufacturer']),
				$default_unit,
				$this->decimal($selling_gross),
				$this->decimal($regular_gross),
				$this->decimal($selling_gross),
				$has_sale_price ? 'DA' : 'NE',
				$has_special ? 'Akcija' : ($has_discount ? 'Popust' : ''),
				$has_sale_price ? $this->decimal($selling_gross) : '',
				$this->decimal($product['anchor_gross_price']),
				$product['reference_date'],
				$barcode,
				$is_available ? 'Dostupno' : 'Nije dostupno',
				(int)$product['quantity'],
				$stock_status,
				$product['currency_code'] ? $product['currency_code'] : $currency_code
			);

			if (!$this->writeCsvRow($handle, $row)) {
				fclose($handle);
				@unlink($temp_path);
				return $this->failPublication($publication_id, 'Pogreška pri zapisu CSV retka za proizvod ' . (int)$product['product_id'] . '.');
			}

			$xml_products[] = array(
				'product_id' => (int)$product['product_id'],
				'name' => $this->csvText($product['name']),
				'model' => $this->csvText($product['model']),
				'sku' => $this->csvText($product['sku']),
				'manufacturer' => $this->csvText($product['manufacturer']),
				'unit' => $default_unit,
				'regular_price' => number_format((float)$regular_gross, 2, '.', ''),
				'current_price' => number_format((float)$selling_gross, 2, '.', ''),
				'special_price' => $has_sale_price ? number_format((float)$selling_gross, 2, '.', '') : '',
				'anchor_price' => number_format((float)$product['anchor_gross_price'], 2, '.', ''),
				'anchor_date' => $product['reference_date'],
				'barcode' => $barcode,
				'available' => $is_available ? 'true' : 'false',
				'quantity' => (int)$product['quantity'],
				'stock_status' => $stock_status,
				'currency' => $product['currency_code'] ? $product['currency_code'] : $currency_code
			);

			$product_count++;
		}

		if (!fflush($handle)) {
			fclose($handle);
			@unlink($temp_path);
			return $this->failPublication($publication_id, 'Pogreška pri završnom zapisu CSV datoteke.');
		}
		if (function_exists('fsync')) {
			@fsync($handle);
		}
		fclose($handle);

		$xml_handle = @fopen($xml_temp_path, 'wb');
		if (!$xml_handle) {
			@unlink($temp_path);
			return $this->failPublication($publication_id, 'Nije moguće otvoriti privremenu XML datoteku.');
		}
		$publication_brand = trim((string)$this->config->get('config_name')) ?: 'Galerija Divila';
		$xml_ok = fwrite($xml_handle, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<priceList brand=\"" . $this->xmlEscape($publication_brand) . "\" location=\"WEB\" generatedAt=\"" . $this->xmlEscape($now->format(DateTime::ATOM)) . "\" currency=\"EUR\">\n") !== false;
		foreach ($xml_products as $xml_product) {
			if (!$xml_ok || fwrite($xml_handle, $this->xmlProduct($xml_product)) === false) {
				$xml_ok = false;
				break;
			}
		}
		$xml_ok = $xml_ok && fwrite($xml_handle, "</priceList>\n") !== false && fflush($xml_handle);
		if (function_exists('fsync')) {
			@fsync($xml_handle);
		}
		fclose($xml_handle);
		if (!$xml_ok) {
			@unlink($temp_path);
			@unlink($xml_temp_path);
			return $this->failPublication($publication_id, 'Pogreška pri zapisu XML cjenika.');
		}

		if (!$product_count || !@rename($temp_path, $final_path)) {
			@unlink($temp_path);
			@unlink($xml_temp_path);
			return $this->failPublication($publication_id, !$product_count ? 'Cjenik nema nijedan proizvod.' : 'Atomska objava CSV datoteke nije uspjela.');
		}
		if (!@rename($xml_temp_path, $xml_final_path)) {
			@unlink($xml_temp_path);
			@unlink($final_path);
			return $this->failPublication($publication_id, 'Atomska objava XML datoteke nije uspjela.');
		}

		$checksum = hash_file('sha256', $final_path);
		$xml_checksum = hash_file('sha256', $xml_final_path);
		if ($checksum === false || $xml_checksum === false) {
			@unlink($final_path);
			@unlink($xml_final_path);
			return $this->failPublication($publication_id, 'Nije moguće izračunati SHA-256 kontrolni zbroj cjenika.');
		}
		@chmod($final_path, 0640);
		@chmod($xml_final_path, 0640);
		$this->db->query("UPDATE `" . DB_PREFIX . "anchor_price_publication` SET status = 'staged', product_count = '" . (int)$product_count . "', checksum_sha256 = '" . $this->db->escape($checksum) . "', xml_checksum_sha256 = '" . $this->db->escape($xml_checksum) . "', error_message = '', published_at = NULL WHERE publication_id = '" . (int)$publication_id . "' AND batch_key = '" . $this->db->escape($batch_key) . "'");
		$publication = $this->getPublication($publication_id, false);

		return array('success' => true, 'existing' => false, 'publication' => $publication);
	}

	private function publishPublication(array $result, DateTime $now, $batch_key) {
		if (empty($result['publication']['publication_id'])) {
			throw new Exception('Dnevni cjenik nije spreman za objavu.');
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND batch_key = '" . $this->db->escape($batch_key) . "' AND location_code = '" . self::PUBLICATION_LOCATION_CODE . "' AND status = 'staged'");
		if ($query->num_rows !== 1) {
			throw new Exception('Dnevni cjenik nije potpun.');
		}

		$publication = $query->row;
			if (!$this->publicationFilesAreValid($publication)) {
			throw new Exception('Kontrolni zbroj pripremljenog cjenika nije valjan.');
		}
		$publication_id = (int)$publication['publication_id'];

		$this->db->query('START TRANSACTION');
		try {
			$this->db->query("UPDATE `" . DB_PREFIX . "anchor_price_publication` SET status = 'published', published_at = '" . $this->db->escape($now->format('Y-m-d H:i:s')) . "' WHERE publication_id = '" . $publication_id . "' AND batch_key = '" . $this->db->escape($batch_key) . "' AND status = 'staged'");
			if ($this->db->countAffected() !== 1) {
				throw new Exception('Atomska objava dnevnog cjenika nije uspjela.');
			}
			$this->db->query('COMMIT');
		} catch (Exception $exception) {
			$this->db->query('ROLLBACK');
			throw $exception;
		}

		$publication = $this->getPublication($publication_id, false);
		return array('success' => true, 'existing' => false, 'publication' => $publication);
	}

	private function discardUnpublishedPublications($store_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . (int)$store_id . "' AND status IN ('generating', 'staged')");
		foreach ($query->rows as $publication) {
			$this->invalidatePublication($publication, 'Nedovršena priprema cjenika uklonjena je prije novog pokušaja.');
		}
	}

	private function createBatchKey() {
		return md5(uniqid((string)mt_rand(), true));
	}

	private function getPublicationProducts($store_id) {
		$language_id = $this->getPublicationLanguageId();
		$customer_group_id = $this->getDefaultCustomerGroupId();
		$sql = "SELECT p.product_id, p.model, p.sku, p.ean, p.price, p.quantity, p.tax_class_id, p.manufacturer_id, pd.name, COALESCE(m.name, '') AS manufacturer, COALESCE(ss.name, '') AS stock_status, ap.anchor_price_id, ap.verification_status, ap.gross_price AS anchor_gross_price, ap.reference_date, ap.currency_code, (SELECT pdsc.price FROM `" . DB_PREFIX . "product_discount` pdsc WHERE pdsc.product_id = p.product_id AND pdsc.customer_group_id = '" . $customer_group_id . "' AND pdsc.quantity = '1' AND ((pdsc.date_start = '0000-00-00' OR pdsc.date_start < NOW()) AND (pdsc.date_end = '0000-00-00' OR pdsc.date_end > NOW())) ORDER BY pdsc.priority ASC, pdsc.price ASC LIMIT 1) AS discount, (SELECT ps.price FROM `" . DB_PREFIX . "product_special` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . $customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM `" . DB_PREFIX . "product` p INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p2s.product_id = p.product_id AND p2s.store_id = '" . (int)$store_id . "') LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = p.product_id AND pd.language_id = '" . $language_id . "') LEFT JOIN `" . DB_PREFIX . "anchor_price` ap ON (ap.product_id = p.product_id AND ap.store_id = p2s.store_id) LEFT JOIN `" . DB_PREFIX . "manufacturer` m ON (m.manufacturer_id = p.manufacturer_id) LEFT JOIN `" . DB_PREFIX . "stock_status` ss ON (ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . $language_id . "') WHERE p.status = '1' AND p.price > '0' AND p.date_available <= NOW() ORDER BY p.product_id ASC";

		$rows = $this->db->query($sql)->rows;
		$fallback_manufacturer = '';

		foreach ($rows as &$row) {
			if (trim((string)$row['manufacturer']) === '') {
				$row['manufacturer'] = $fallback_manufacturer;
			}
		}
		unset($row);

		return $rows;
	}

	private function assertPublicationProducts(array $products) {
		$total = 0;
		foreach ($products as $product) {
			if (empty($product['anchor_price_id'])
				|| $product['verification_status'] !== 'confirmed'
				|| trim((string)$product['name']) === '') {
				$total++;
			}
		}
		if ($total > 0) {
			throw new Exception($total . ' proizvoda s istaknutom cijenom nema potvrđenu sidrenu cijenu ili naziv. Objava je zaustavljena.');
		}
	}

	public function getPublications() {
		if (!$this->publicationTableExists()) {
			return array();
		}

		$store_id = (int)$this->config->get('config_store_id');
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . $store_id . "' AND location_code = '" . self::PUBLICATION_LOCATION_CODE . "' AND status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL " . (int)self::ARCHIVE_DAYS . " DAY) ORDER BY published_at DESC, publication_id DESC");
		$publications = array();
		foreach ($query->rows as $row) {
			if ($this->validPublicationMetadata($row, array(self::PUBLICATION_LOCATION_CODE)) && $this->publicationFilesExist($row)) {
				$publications[] = $row;
			}
		}
		return $publications;
	}

	public function getPublication($publication_id, $public_only = true) {
		if (!$this->publicationTableExists()) {
			return false;
		}

		$sql = "SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE publication_id = '" . (int)$publication_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ($public_only) {
			$sql .= " AND status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL " . (int)self::ARCHIVE_DAYS . " DAY)";
		}

		$query = $this->db->query($sql . " LIMIT 1");
		if (!$query->num_rows || !$public_only) {
			return $query->num_rows ? $query->row : false;
		}

		$publication = $query->row;
		return $this->validPublicationMetadata($publication, array(self::PUBLICATION_LOCATION_CODE)) && $this->publicationFilesExist($publication) ? $publication : false;
	}

	public function getLatestPublication($format = 'csv') {
		$format = strtolower((string)$format) === 'xml' ? 'xml' : 'csv';

		foreach ($this->getPublications() as $publication) {
			if ($this->publicationFileIsValid($publication, false, $format)) {
				return $publication;
			}
		}

		return false;
	}

	private function validatedPublication(array $row, array $allowed_location_codes) {
		return $this->validPublicationMetadata($row, $allowed_location_codes)
			&& $this->publicationFilesAreValid($row);
	}

	private function validPublicationMetadata(array $row, array $allowed_location_codes) {
		$location_code = isset($row['location_code']) ? $row['location_code'] : '';
		$batch_key = isset($row['batch_key']) ? strtolower(trim((string)$row['batch_key'])) : '';

		return in_array($location_code, $allowed_location_codes, true)
			&& preg_match('/^[a-f0-9]{32}$/', $batch_key)
			&& !empty($row['published_at'])
			&& (int)$row['product_count'] > 0;
	}

	private function publicationFilesExist(array $row) {
		foreach (array('csv', 'xml') as $format) {
			$path = $this->publicationPath($row, $format);

			if (!$path || !is_file($path) || !is_readable($path)) {
				return false;
			}
		}

		return true;
	}

	public function publicationPath($publication, $format = 'csv') {
		$format = strtolower((string)$format) === 'xml' ? 'xml' : 'csv';
		$field = $format === 'xml' ? 'xml_relative_path' : 'relative_path';
		if (!$publication || empty($publication[$field])) {
			return false;
		}

		$relative = str_replace('\\', '/', (string)$publication[$field]);

		if (strpos($relative, 'anchor_price/') !== 0 || strpos($relative, '..') !== false || substr($relative, -4) !== '.' . $format) {
			return false;
		}

		$base = rtrim(DIR_DOWNLOAD, '/\\') . DIRECTORY_SEPARATOR;
		$path = $base . str_replace('/', DIRECTORY_SEPARATOR, $relative);

		return $path;
	}

	public function publicationFileIsValid($publication, $path = false, $format = 'csv') {
		$format = strtolower((string)$format) === 'xml' ? 'xml' : 'csv';
		$checksum_field = $format === 'xml' ? 'xml_checksum_sha256' : 'checksum_sha256';
		if (!$publication || !isset($publication[$checksum_field])) {
			return false;
		}

		if ($path === false) {
			$path = $this->publicationPath($publication, $format);
		}

		$expected_checksum = strtolower(trim((string)$publication[$checksum_field]));
		if (!$path || !is_file($path) || !is_readable($path) || !preg_match('/^[a-f0-9]{64}$/', $expected_checksum)) {
			return false;
		}

		$actual_checksum = hash_file('sha256', $path);
		if ($actual_checksum === false) {
			return false;
		}

		return function_exists('hash_equals') ? hash_equals($expected_checksum, strtolower($actual_checksum)) : $expected_checksum === strtolower($actual_checksum);
	}

	public function publicationFilesAreValid($publication) {
		return $this->publicationFileIsValid($publication, false, 'csv')
			&& $this->publicationFileIsValid($publication, false, 'xml');
	}

	private function publicationTableExists() {
		$query = $this->db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->db->escape(DB_DATABASE) . "' AND TABLE_NAME = '" . $this->db->escape(DB_PREFIX . "anchor_price_publication") . "' LIMIT 1");

		return (bool)$query->num_rows;
	}

	private function getPublicationLanguageId() {
		if ($this->publication_language_id !== null) {
			return $this->publication_language_id;
		}

		$language_code = (string)$this->getStoreSettingValue('config_language');
		if ($language_code === '') {
			throw new Exception('Default store language is not configured.');
		}
		$query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($language_code) . "' LIMIT 1");
		if (!$query->num_rows) {
			throw new Exception('Default store language is not available.');
		}
		$this->publication_language_id = (int)$query->row['language_id'];

		return $this->publication_language_id;
	}

	private function getDefaultCustomerGroupId() {
		if ($this->default_customer_group_id !== null) {
			return $this->default_customer_group_id;
		}

		$this->default_customer_group_id = (int)$this->getStoreSettingValue('config_customer_group_id');
		if ($this->default_customer_group_id < 1) {
			throw new Exception('Default customer group is not configured.');
		}

		return $this->default_customer_group_id;
	}

	private function getStoreSettingValue($key) {
		$store_id = (int)$this->config->get('config_store_id');
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . $store_id . "' AND `key` = '" . $this->db->escape($key) . "' ORDER BY setting_id DESC LIMIT 1");

		if (!$query->num_rows && $store_id !== 0) {
			$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `key` = '" . $this->db->escape($key) . "' ORDER BY setting_id DESC LIMIT 1");
		}

		return $query->num_rows ? $query->row['value'] : '';
	}

	private function getPublicTax() {
		if ($this->public_tax !== null) {
			return $this->public_tax;
		}

		$session_customer_group_id = $this->config->get('config_customer_group_id');
		$this->config->set('config_customer_group_id', $this->getDefaultCustomerGroupId());

		try {
			$tax = new Cart\Tax($this->registry);
			$country_id = (int)$this->config->get('config_country_id');
			$zone_id = (int)$this->config->get('config_zone_id');

			if ($this->config->get('config_tax_default') === 'shipping') {
				$tax->setShippingAddress($country_id, $zone_id);
			} elseif ($this->config->get('config_tax_default') === 'payment') {
				$tax->setPaymentAddress($country_id, $zone_id);
			}

			$tax->setStoreAddress($country_id, $zone_id);
			$this->public_tax = $tax;
		} finally {
			$this->config->set('config_customer_group_id', $session_customer_group_id);
		}

		return $this->public_tax;
	}

	private function failPublication($publication_id, $message) {
		$this->db->query("UPDATE `" . DB_PREFIX . "anchor_price_publication` SET status = 'failed', error_message = '" . $this->db->escape($message) . "' WHERE publication_id = '" . (int)$publication_id . "'");

		return array('success' => false, 'error' => $message, 'publication_id' => (int)$publication_id);
	}

	private function expireOldPublications(DateTime $now) {
		$cutoff = clone $now;
		$cutoff->modify('-' . (int)self::ARCHIVE_DAYS . ' days');
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "anchor_price_publication` WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND status = 'published' AND published_at < '" . $this->db->escape($cutoff->format('Y-m-d H:i:s')) . "'");

		foreach ($query->rows as $row) {
			foreach (array('csv', 'xml') as $format) {
				$path = $this->publicationPath($row, $format);

				if ($path && is_file($path)) {
					@unlink($path);
				}
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "anchor_price_publication` SET status = 'expired' WHERE publication_id = '" . (int)$row['publication_id'] . "'");
		}
	}

	private function slugify($value) {
		$value = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');

		if (function_exists('iconv')) {
			$converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

			if ($converted !== false) {
				$value = $converted;
			}
		}

		$value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value));

		return trim($value, '-');
	}

	private function publicationLocation($location_code) {
		$type = 'cjenik';
		$base_url = defined('HTTPS_SERVER') ? HTTPS_SERVER : (defined('HTTP_SERVER') ? HTTP_SERVER : '');
		$address = parse_url($base_url, PHP_URL_HOST);

		$type = $this->slugify($type);
		$address = $this->slugify($address);

		return array(
			'type' => $type !== '' ? substr($type, 0, 40) : 'prodajni-objekt',
			'address' => $address !== '' ? substr($address, 0, 120) : 'nepoznata-adresa'
		);
	}

	private function publicationAddressLine($address) {
		$address = preg_replace('~<br\s*/?>~i', "\n", (string)$address);
		$address = html_entity_decode(strip_tags(str_replace(array("\r\n", "\r"), "\n", $address)), ENT_QUOTES, 'UTF-8');
		$fallback = '';

		foreach (explode("\n", $address) as $line) {
			$line = trim($line);

			if ($line === '' || preg_match('/\b(oib|iban|mbs|mati[cč]ni|ra[cč]un|banka|swift|vat)\b/iu', $line)) {
				continue;
			}

			if ($fallback === '') {
				$fallback = $line;
			}

			if (preg_match('/\p{L}/u', $line) && preg_match('/\d/', $line)) {
				return $line;
			}
		}

		return $fallback;
	}

	private function decimal($value) {
		return number_format((float)$value, 2, ',', '');
	}

	private function csvText($value) {
		return trim(html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8'));
	}

	private function xmlEscape($value) {
		return htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1, 'UTF-8');
	}

	private function xmlProduct(array $product) {
		$xml = "  <product>\n";
		foreach (array(
			'productId' => 'product_id',
			'name' => 'name',
			'model' => 'model',
			'sku' => 'sku',
			'manufacturer' => 'manufacturer',
			'unit' => 'unit',
			'regularPrice' => 'regular_price',
			'currentPrice' => 'current_price',
			'specialPrice' => 'special_price',
			'anchorPrice' => 'anchor_price',
			'anchorDate' => 'anchor_date',
			'barcode' => 'barcode',
			'available' => 'available',
			'quantity' => 'quantity',
			'stockStatus' => 'stock_status',
			'currency' => 'currency'
		) as $tag => $key) {
			$xml .= '    <' . $tag . '>' . $this->xmlEscape(isset($product[$key]) ? $product[$key] : '') . '</' . $tag . ">\n";
		}
		return $xml . "  </product>\n";
	}

	private function writeCsvRow($handle, array $row) {
		foreach ($row as &$value) {
			if (is_string($value) && preg_match('/^[\s]*[=+\-@]/u', $value)) {
				$value = "'" . $value;
			}
		}
		unset($value);

		if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 50504) {
			return fputcsv($handle, $row, ';', '"', '\\') !== false;
		}

		return fputcsv($handle, $row, ';', '"') !== false;
	}

	private function validPublicationBarcode(array $product) {
		foreach (array('ean') as $field) {
			$value = isset($product[$field]) ? trim((string)$product[$field]) : '';
			if ($this->isValidGtin($value)) {
				return $value;
			}
		}

		return '';
	}

	private function isValidGtin($value) {
		$length = strlen($value);
		if (!in_array($length, array(8, 12, 13, 14), true) || !preg_match('/^[0-9]+$/', $value)) {
			return false;
		}

		$sum = 0;
		$weight = 3;
		for ($index = $length - 2; $index >= 0; $index--) {
			$sum += ((int)$value[$index]) * $weight;
			$weight = ($weight === 3) ? 1 : 3;
		}

		return ((10 - ($sum % 10)) % 10) === (int)$value[$length - 1];
	}
}
