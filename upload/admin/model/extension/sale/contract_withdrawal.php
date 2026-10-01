<?php
class ModelExtensionSaleContractWithdrawal extends Model {
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

	public function getWithdrawals($data = array()) {
		$this->ensureSchema();

		$sql = "SELECT * FROM `" . DB_PREFIX . "contract_withdrawal` cw";
		$implode = array();

		if (!empty($data['filter_contract_withdrawal_id'])) {
			$implode[] = "cw.contract_withdrawal_id = '" . (int)$data['filter_contract_withdrawal_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "cw.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(cw.firstname, ' ', cw.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "cw.email LIKE '%" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$implode[] = "cw.status = '" . $this->db->escape($data['filter_status']) . "'";
		}

		if (!empty($data['filter_date_submitted'])) {
			$implode[] = "DATE(cw.date_submitted) = DATE('" . $this->db->escape($data['filter_date_submitted']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'cw.contract_withdrawal_id',
			'cw.order_id',
			'customer',
			'cw.email',
			'cw.status',
			'cw.date_submitted',
			'cw.date_modified'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'customer') {
				$sql .= " ORDER BY CONCAT(cw.firstname, ' ', cw.lastname)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY cw.contract_withdrawal_id";
		}

		if (isset($data['order']) && $data['order'] == 'ASC') {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalWithdrawals($data = array()) {
		$this->ensureSchema();

		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "contract_withdrawal` cw";
		$implode = array();

		if (!empty($data['filter_contract_withdrawal_id'])) {
			$implode[] = "cw.contract_withdrawal_id = '" . (int)$data['filter_contract_withdrawal_id'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "cw.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(cw.firstname, ' ', cw.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "cw.email LIKE '%" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$implode[] = "cw.status = '" . $this->db->escape($data['filter_status']) . "'";
		}

		if (!empty($data['filter_date_submitted'])) {
			$implode[] = "DATE(cw.date_submitted) = DATE('" . $this->db->escape($data['filter_date_submitted']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function addHistory($contract_withdrawal_id, $status, $comment, $notify, $user_id) {
		$this->ensureSchema();

		$this->db->query("UPDATE `" . DB_PREFIX . "contract_withdrawal` SET status = '" . $this->db->escape($status) . "', date_modified = NOW() WHERE contract_withdrawal_id = '" . (int)$contract_withdrawal_id . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "contract_withdrawal_history` SET contract_withdrawal_id = '" . (int)$contract_withdrawal_id . "', status = '" . $this->db->escape($status) . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', user_id = '" . (int)$user_id . "', date_added = NOW()");
	}

	public function getHistories($contract_withdrawal_id) {
		$this->ensureSchema();

		$query = $this->db->query("SELECT cwh.*, u.username FROM `" . DB_PREFIX . "contract_withdrawal_history` cwh LEFT JOIN `" . DB_PREFIX . "user` u ON (cwh.user_id = u.user_id) WHERE cwh.contract_withdrawal_id = '" . (int)$contract_withdrawal_id . "' ORDER BY cwh.date_added DESC");

		return $query->rows;
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
