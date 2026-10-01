<?php
class ModelExtensionAccountContractWithdrawal extends Model {
	public function getCustomerOrder($order_id) {
		if (!$this->customer->isLogged()) {
			return false;
		}

		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND order_status_id > '0' AND store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (method_exists($this->customer, 'getMaster') && $this->customer->getMaster() == '1' && method_exists($this->customer, 'getGrupapartnera')) {
			$sql .= " AND grupa_partnera = '" . $this->db->escape($this->customer->getGrupapartnera()) . "' AND customer_id != '0'";
		} else {
			$sql .= " AND customer_id = '" . (int)$this->customer->getId() . "' AND customer_id != '0'";
		}

		$query = $this->db->query($sql);

		return $query->num_rows ? $query->row : false;
	}

	public function getOrderByEmail($order_id, $email) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND order_status_id > '0' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->num_rows ? $query->row : false;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT order_product_id, product_id, name, model, quantity FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' ORDER BY order_product_id ASC");

		return $query->rows;
	}

	public function addWithdrawal($data) {
		$this->ensureSchema();

		$date_ordered = !empty($data['date_ordered']) ? "'" . $this->db->escape($data['date_ordered']) . "'" : "NULL";

		$this->db->query("INSERT INTO `" . DB_PREFIX . "contract_withdrawal` SET store_id = '" . (int)$this->config->get('config_store_id') . "', order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$data['customer_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', address = '" . $this->db->escape($data['address']) . "', refund_iban = '" . $this->db->escape($data['refund_iban']) . "', withdrawal_scope = '" . $this->db->escape($data['withdrawal_scope']) . "', products = '" . $this->db->escape(json_encode($data['products'])) . "', statement = '" . $this->db->escape($data['statement']) . "', comment = '" . $this->db->escape($data['comment']) . "', status = 'new', token = '" . $this->db->escape($data['token']) . "', ip = '" . $this->db->escape($data['ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', date_ordered = " . $date_ordered . ", date_submitted = NOW(), date_modified = NOW()");

		$contract_withdrawal_id = $this->db->getLastId();

		$this->addHistory($contract_withdrawal_id, 'new', $data['statement'], 1, 0);

		return $contract_withdrawal_id;
	}

	public function isRateLimited($ip, $limit = 5, $minutes = 60) {
		$this->ensureSchema();

		$ip = trim((string)$ip);
		$limit = max(1, (int)$limit);
		$minutes = max(1, (int)$minutes);

		if ($ip === '' || strlen($ip) > 45 || !filter_var($ip, FILTER_VALIDATE_IP)) {
			return false;
		}

		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "contract_withdrawal` WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND ip = '" . $this->db->escape($ip) . "' AND date_submitted >= DATE_SUB(NOW(), INTERVAL " . $minutes . " MINUTE)");

		return isset($query->row['total']) && (int)$query->row['total'] >= $limit;
	}

	public function getWithdrawal($contract_withdrawal_id) {
		$this->ensureSchema();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "contract_withdrawal` WHERE contract_withdrawal_id = '" . (int)$contract_withdrawal_id . "'");

		if ($query->num_rows) {
			$query->row['products'] = json_decode($query->row['products'], true);

			if (!is_array($query->row['products'])) {
				$query->row['products'] = array();
			}

			return $query->row;
		}

		return false;
	}

	public function getWithdrawalByToken($contract_withdrawal_id, $token) {
		$this->ensureSchema();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "contract_withdrawal` WHERE contract_withdrawal_id = '" . (int)$contract_withdrawal_id . "' AND token = '" . $this->db->escape($token) . "'");

		if ($query->num_rows) {
			$query->row['products'] = json_decode($query->row['products'], true);

			if (!is_array($query->row['products'])) {
				$query->row['products'] = array();
			}

			return $query->row;
		}

		return false;
	}

	public function addHistory($contract_withdrawal_id, $status, $comment, $notify = 0, $user_id = 0) {
		$this->ensureSchema();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "contract_withdrawal_history` SET contract_withdrawal_id = '" . (int)$contract_withdrawal_id . "', status = '" . $this->db->escape($status) . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', user_id = '" . (int)$user_id . "', date_added = NOW()");
	}

	private function ensureSchema() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "contract_withdrawal` (
			`contract_withdrawal_id` INT(11) NOT NULL AUTO_INCREMENT,
			`store_id` INT(11) NOT NULL DEFAULT 0,
			`order_id` INT(11) NOT NULL DEFAULT 0,
			`customer_id` INT(11) NOT NULL DEFAULT 0,
			`firstname` VARCHAR(64) NOT NULL,
			`lastname` VARCHAR(64) NOT NULL,
			`email` VARCHAR(96) NOT NULL,
			`telephone` VARCHAR(32) NOT NULL DEFAULT '',
			`address` TEXT NULL,
			`refund_iban` VARCHAR(34) NOT NULL DEFAULT '',
			`withdrawal_scope` VARCHAR(16) NOT NULL DEFAULT 'full',
			`products` TEXT NULL,
			`statement` TEXT NOT NULL,
			`comment` TEXT NULL,
			`status` VARCHAR(32) NOT NULL DEFAULT 'new',
			`token` VARCHAR(64) NOT NULL,
			`ip` VARCHAR(45) NOT NULL DEFAULT '',
			`user_agent` VARCHAR(255) NOT NULL DEFAULT '',
			`date_ordered` DATE NULL,
			`date_submitted` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`contract_withdrawal_id`),
			KEY `idx_order_id` (`order_id`),
			KEY `idx_customer_id` (`customer_id`),
			KEY `idx_email` (`email`),
			KEY `idx_status` (`status`),
			KEY `idx_ip_date` (`ip`, `date_submitted`),
			KEY `idx_date_submitted` (`date_submitted`),
			UNIQUE KEY `uk_token` (`token`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "contract_withdrawal_history` (
			`contract_withdrawal_history_id` INT(11) NOT NULL AUTO_INCREMENT,
			`contract_withdrawal_id` INT(11) NOT NULL,
			`status` VARCHAR(32) NOT NULL,
			`notify` TINYINT(1) NOT NULL DEFAULT 0,
			`comment` TEXT NULL,
			`user_id` INT(11) NOT NULL DEFAULT 0,
			`date_added` DATETIME NOT NULL,
			PRIMARY KEY (`contract_withdrawal_history_id`),
			KEY `idx_contract_withdrawal_id` (`contract_withdrawal_id`),
			KEY `idx_status` (`status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "contract_withdrawal` LIKE 'refund_iban'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "contract_withdrawal` ADD COLUMN `refund_iban` VARCHAR(34) NOT NULL DEFAULT '' AFTER `address`");
		}

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "contract_withdrawal` LIKE 'ip'");

		if ($query->num_rows && isset($query->row['Type']) && strtolower($query->row['Type']) !== 'varchar(45)') {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "contract_withdrawal` MODIFY COLUMN `ip` VARCHAR(45) NOT NULL DEFAULT ''");
		}

		$query = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "contract_withdrawal` WHERE Key_name = 'idx_ip_date'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "contract_withdrawal` ADD KEY `idx_ip_date` (`ip`, `date_submitted`)");
		}
	}
}
