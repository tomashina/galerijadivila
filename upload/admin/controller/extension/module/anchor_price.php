<?php
class ControllerExtensionModuleAnchorPrice extends Controller {
	private $error = array();

	public function index() {
		$data = $this->load->language('extension/module/anchor_price');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/anchor_price');

		$filter_name = isset($this->request->get['filter_name']) ? trim($this->request->get['filter_name']) : '';
		$filter_model = isset($this->request->get['filter_model']) ? trim($this->request->get['filter_model']) : '';
		$filter_status = isset($this->request->get['filter_status']) ? trim($this->request->get['filter_status']) : '';
		$filter_date_from = isset($this->request->get['filter_date_from']) ? trim($this->request->get['filter_date_from']) : '';
		$filter_date_to = isset($this->request->get['filter_date_to']) ? trim($this->request->get['filter_date_to']) : '';
		$sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'product_name';
		$order = isset($this->request->get['order']) && strtoupper($this->request->get['order']) === 'DESC' ? 'DESC' : 'ASC';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;
		$limit = (int)$this->config->get('config_limit_admin');
		if ($limit < 1) {
			$limit = 20;
		}

		$filter_data = array(
			'filter_name' => $filter_name,
			'filter_model' => $filter_model,
			'filter_status' => $filter_status,
			'filter_date_from' => $filter_date_from,
			'filter_date_to' => $filter_date_to,
			'sort' => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$data['anchors'] = array();
		$data['publications'] = array();
		$data['missing_count'] = 0;
		$total = 0;

		if ($this->model_extension_module_anchor_price->tablesExist()) {
			$total = $this->model_extension_module_anchor_price->getTotalAnchorPrices($filter_data);
			$results = $this->model_extension_module_anchor_price->getAnchorPrices($filter_data);

			foreach ($results as $result) {
				$data['anchors'][] = array(
					'anchor_price_id' => (int)$result['anchor_price_id'],
					'product_id' => (int)$result['product_id'],
					'product_name' => $result['product_name'] !== null && $result['product_name'] !== '' ? $result['product_name'] : '#' . (int)$result['product_id'],
					'model' => $result['model'],
					'sku' => $result['sku'],
					'manufacturer' => $result['manufacturer'],
					'price' => number_format((float)$result['price'], 2, ',', '.'),
					'gross_price' => number_format((float)$result['gross_price'], 2, ',', '.'),
					'currency_code' => $result['currency_code'],
					'reference_date' => $result['reference_date'],
					'verification_status' => $result['verification_status'],
					'status_text' => $this->statusText($result['verification_status']),
					'edit' => $this->url->link('extension/module/anchor_price/edit', 'user_token=' . $this->session->data['user_token'] . '&anchor_price_id=' . (int)$result['anchor_price_id'], true)
				);
			}

			$data['missing_count'] = $this->model_extension_module_anchor_price->getMissingProductCount(true);
			foreach ($this->model_extension_module_anchor_price->getPublications(20) as $publication) {
				$data['publications'][] = array(
					'publication_id' => (int)$publication['publication_id'],
					'location_code' => $publication['location_code'],
					'sequence_no' => (int)$publication['sequence_no'],
						'filename' => $publication['filename'],
						'xml_filename' => isset($publication['xml_filename']) ? $publication['xml_filename'] : '',
					'status' => $publication['status'],
					'product_count' => (int)$publication['product_count'],
					'published_at' => $publication['published_at'],
						'download_csv' => $publication['status'] === 'published' ? $this->url->link('extension/module/anchor_price/download', 'user_token=' . $this->session->data['user_token'] . '&publication_id=' . (int)$publication['publication_id'] . '&format=csv', true) : '',
						'download_xml' => $publication['status'] === 'published' ? $this->url->link('extension/module/anchor_price/download', 'user_token=' . $this->session->data['user_token'] . '&publication_id=' . (int)$publication['publication_id'] . '&format=xml', true) : ''
				);
			}
		} else {
			$this->error['warning'] = $this->language->get('error_not_installed');
		}

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_status'] = $filter_status;
		$data['filter_date_from'] = $filter_date_from;
		$data['filter_date_to'] = $filter_date_to;
		$data['statuses'] = array(
			'confirmed' => $this->language->get('text_status_confirmed'),
			'pending' => $this->language->get('text_status_pending'),
			'disabled' => $this->language->get('text_status_disabled')
		);

		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)),
			array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)),
			array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true))
		);

		$data['filter_action'] = $this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true);
		$data['sync_action'] = $this->url->link('extension/module/anchor_price/sync', 'user_token=' . $this->session->data['user_token'], true);
		$data['publish_action'] = $this->url->link('extension/module/anchor_price/publish', 'user_token=' . $this->session->data['user_token'], true);
		$data['import_action'] = $this->url->link('extension/module/anchor_price/import', 'user_token=' . $this->session->data['user_token'], true);
		$data['settings_action'] = $this->url->link('extension/module/anchor_price/settings', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$data['user_token'] = $this->session->data['user_token'];
		$data['default_unit'] = trim((string)$this->config->get('module_anchor_price_default_unit')) !== '' ? $this->config->get('module_anchor_price_default_unit') : 'kom';
		$data['reference_date_setting'] = $this->model_extension_module_anchor_price->getReferenceDate();
		$data['cron_key'] = (string)$this->config->get('module_anchor_price_cron_key');
		$catalog_url = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;
		$data['cron_url'] = rtrim($catalog_url, '/') . '/index.php?route=extension/module/anchor_price/cron';
		$data['cron_url_with_key'] = $data['cron_url'] . '&key=' . rawurlencode($data['cron_key']);
		$data['public_latest_csv'] = rtrim($catalog_url, '/') . '/index.php?route=information/price_list/download&format=csv';
		$data['public_latest_xml'] = rtrim($catalog_url, '/') . '/index.php?route=information/price_list/download&format=xml';
		$data['publication_warning'] = '';
		if ($this->model_extension_module_anchor_price->tablesExist()) {
			$publication_state = $this->model_extension_module_anchor_price->getDailyPublicationState();
			if ($publication_state['due'] && $publication_state['missing']) {
				$data['publication_warning'] = sprintf($this->language->get('warning_publication_due'), implode(', ', $publication_state['missing']));
			}
		}

		$url = $this->filterUrl();
		$next_order = $order === 'ASC' ? 'DESC' : 'ASC';
		foreach (array('product_name', 'model', 'price', 'gross_price', 'reference_date', 'verification_status') as $field) {
			$data['sort_' . $field] = $this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=' . $field . '&order=' . $next_order, true);
		}

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=' . urlencode($sort) . '&order=' . $order . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), $total ? (($page - 1) * $limit) + 1 : 0, min($page * $limit, $total), $total, $limit ? ceil($total / $limit) : 1);

		$data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
		unset($this->session->data['success']);
		$data['import_result'] = isset($this->session->data['import_result']) ? $this->session->data['import_result'] : array();
		unset($this->session->data['import_result']);
		if (isset($this->session->data['error'])) {
			$this->error['warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/anchor_price', $data));
	}

	public function edit() {
		$data = $this->load->language('extension/module/anchor_price');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/anchor_price');
		$anchor_price_id = isset($this->request->get['anchor_price_id']) ? (int)$this->request->get['anchor_price_id'] : 0;
		$anchor = $this->model_extension_module_anchor_price->getAnchorPrice($anchor_price_id);

		if (!$anchor) {
			$this->session->data['error'] = $this->language->get('error_not_found');
			$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateEdit()) {
			$input = array(
				'price' => $this->normaliseNumber($this->request->post['price']),
				'gross_price' => $this->normaliseNumber($this->request->post['gross_price']),
				'reference_date' => trim($this->request->post['reference_date']),
				'verification_status' => trim($this->request->post['verification_status'])
			);

			try {
				$this->model_extension_module_anchor_price->updateAnchorPrice($anchor_price_id, $input, trim($this->request->post['reason']), $this->userId());
				$this->session->data['success'] = $this->language->get('text_success_edit');
				$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
				return;
			} catch (Exception $exception) {
				$this->error['warning'] = $exception->getMessage();
			}
		}

		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)),
			array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true)),
			array('text' => $this->language->get('text_edit'), 'href' => $this->url->link('extension/module/anchor_price/edit', 'user_token=' . $this->session->data['user_token'] . '&anchor_price_id=' . $anchor_price_id, true))
		);

		$data['action'] = $this->url->link('extension/module/anchor_price/edit', 'user_token=' . $this->session->data['user_token'] . '&anchor_price_id=' . $anchor_price_id, true);
		$data['cancel'] = $this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true);
		$data['anchor'] = $anchor;
		$data['price'] = isset($this->request->post['price']) ? $this->request->post['price'] : $anchor['price'];
		$data['gross_price'] = isset($this->request->post['gross_price']) ? $this->request->post['gross_price'] : $anchor['gross_price'];
		$data['reference_date'] = isset($this->request->post['reference_date']) ? $this->request->post['reference_date'] : $anchor['reference_date'];
		$data['verification_status'] = isset($this->request->post['verification_status']) ? $this->request->post['verification_status'] : $anchor['verification_status'];
		$data['reason'] = isset($this->request->post['reason']) ? $this->request->post['reason'] : '';
		$data['statuses'] = array('confirmed' => $this->language->get('text_status_confirmed'));
		if (!(int)$anchor['product_status']) {
			$data['statuses']['pending'] = $this->language->get('text_status_pending');
			$data['statuses']['disabled'] = $this->language->get('text_status_disabled');
		}
		$data['audits'] = array();

		foreach ($this->model_extension_module_anchor_price->getAuditTrail($anchor_price_id) as $audit) {
			$old = $audit['old_data'] ? json_decode($audit['old_data'], true) : array();
			$new = json_decode($audit['new_data'], true);
			$data['audits'][] = array(
				'action' => $audit['action'],
				'username' => $audit['username'] ? $audit['username'] : $this->language->get('text_system'),
				'reason' => $audit['reason'],
				'old_summary' => $this->snapshotSummary(is_array($old) ? $old : array()),
				'new_summary' => $this->snapshotSummary(is_array($new) ? $new : array()),
				'date_added' => $audit['date_added']
			);
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_price'] = isset($this->error['price']) ? $this->error['price'] : '';
		$data['error_gross_price'] = isset($this->error['gross_price']) ? $this->error['gross_price'] : '';
		$data['error_reference_date'] = isset($this->error['reference_date']) ? $this->error['reference_date'] : '';
		$data['error_status'] = isset($this->error['status']) ? $this->error['status'] : '';
		$data['error_reason'] = isset($this->error['reason']) ? $this->error['reason'] : '';
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/anchor_price_form', $data));
	}

	public function sync() {
		$this->load->language('extension/module/anchor_price');
		if (!$this->config->get('module_anchor_price_status')) {
			$this->session->data['error'] = $this->language->get('error_not_installed');
		} elseif ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->user->hasPermission('modify', 'extension/module/anchor_price')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			try {
				$this->load->model('extension/module/anchor_price');
				$count = $this->model_extension_module_anchor_price->syncMissingProducts($this->userId());
				$this->session->data['success'] = sprintf($this->language->get('text_success_sync'), $count);
			} catch (Exception $exception) {
				$this->session->data['error'] = $exception->getMessage();
			}
		}
		$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function import() {
		$this->load->language('extension/module/anchor_price');
		if (!$this->config->get('module_anchor_price_status')) {
			$this->session->data['error'] = $this->language->get('error_not_installed');
		} elseif ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->user->hasPermission('modify', 'extension/module/anchor_price')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} elseif (empty($this->request->files['anchor_csv']) || (int)$this->request->files['anchor_csv']['error'] !== UPLOAD_ERR_OK) {
			$this->session->data['error'] = $this->language->get('error_import_upload');
		} elseif ((int)$this->request->files['anchor_csv']['size'] < 1 || (int)$this->request->files['anchor_csv']['size'] > 5 * 1024 * 1024 || strtolower(pathinfo($this->request->files['anchor_csv']['name'], PATHINFO_EXTENSION)) !== 'csv') {
			$this->session->data['error'] = $this->language->get('error_import_file');
		} else {
			try {
				$this->load->model('extension/module/anchor_price');
				$dry_run = !empty($this->request->post['dry_run']);
				$result = $this->model_extension_module_anchor_price->importCsv($this->request->files['anchor_csv']['tmp_name'], $dry_run, $this->userId());
				$this->session->data['import_result'] = $result;
				if ($result['errors']) {
					$this->session->data['error'] = sprintf($this->language->get('error_import_rows'), count($result['errors']));
				} elseif ($dry_run) {
					$this->session->data['success'] = sprintf($this->language->get('text_success_import_dry_run'), $result['rows']);
				} else {
					$this->session->data['success'] = sprintf($this->language->get('text_success_import'), $result['created'], $result['updated']);
				}
			} catch (Exception $exception) {
				$this->session->data['error'] = $exception->getMessage();
			}
		}

		$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function publish() {
		$this->load->language('extension/module/anchor_price');
		if (!$this->config->get('module_anchor_price_status')) {
			$this->session->data['error'] = $this->language->get('error_not_installed');
		} elseif ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->user->hasPermission('modify', 'extension/module/anchor_price')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			try {
				$this->load->model('extension/module/anchor_price');
				$publications = $this->model_extension_module_anchor_price->generateDailyPublications(0, $this->userId(), true);
				$files = array();
				foreach ($publications as $publication) {
					$files[] = $publication['filename'] . ' + ' . $publication['xml_filename'];
				}
				$this->session->data['success'] = sprintf($this->language->get('text_success_publish'), implode(', ', $files));
			} catch (Exception $exception) {
				$this->session->data['error'] = $exception->getMessage();
			}
		}
		$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function settings() {
		$this->load->language('extension/module/anchor_price');
		$unit = isset($this->request->post['default_unit']) ? trim($this->request->post['default_unit']) : '';
		$reference_date = isset($this->request->post['reference_date_setting']) ? trim($this->request->post['reference_date_setting']) : '';
		$today = (new DateTime('now', new DateTimeZone('Europe/Zagreb')))->format('Y-m-d');

		if (!$this->config->get('module_anchor_price_status')) {
			$this->session->data['error'] = $this->language->get('error_not_installed');
		} elseif ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->user->hasPermission('modify', 'extension/module/anchor_price')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} elseif (utf8_strlen($unit) < 1 || utf8_strlen($unit) > 16) {
			$this->session->data['error'] = $this->language->get('error_default_unit');
		} elseif (!$this->validDate($reference_date) || $reference_date > $today) {
			$this->session->data['error'] = $this->language->get('error_reference_date_setting');
		} else {
			$this->load->model('setting/setting');
			$settings = $this->model_setting_setting->getSetting('module_anchor_price', 0);
			$settings['module_anchor_price_default_unit'] = $unit;
			$settings['module_anchor_price_reference_date'] = $reference_date;
			$this->model_setting_setting->editSetting('module_anchor_price', $settings, 0);
			$this->session->data['success'] = $this->language->get('text_success_settings');
		}

		$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function download() {
		$this->load->language('extension/module/anchor_price');
		$this->load->model('extension/module/anchor_price');

		if (!$this->user->hasPermission('access', 'extension/module/anchor_price')) {
			$this->session->data['error'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		$publication_id = isset($this->request->get['publication_id']) ? (int)$this->request->get['publication_id'] : 0;
		$format = isset($this->request->get['format']) && strtolower($this->request->get['format']) === 'xml' ? 'xml' : 'csv';
		$publication = $this->model_extension_module_anchor_price->getPublication($publication_id);
		$path = $publication && $publication['status'] === 'published' ? $this->model_extension_module_anchor_price->getPublicationPath($publication, $format) : false;

		if (!$path || !$this->model_extension_module_anchor_price->publicationFileIsValid($publication, $path, $format)) {
			$this->session->data['error'] = $this->language->get('error_file_missing');
			$this->response->redirect($this->url->link('extension/module/anchor_price', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		$this->response->setCompression(0);
		$this->response->addHeader('Content-Type: ' . ($format === 'xml' ? 'application/xml' : 'text/csv') . '; charset=UTF-8');
		$this->response->addHeader('Content-Disposition: attachment; filename="' . basename($format === 'xml' ? $publication['xml_filename'] : $publication['filename']) . '"');
		$this->response->addHeader('Content-Length: ' . filesize($path));
		$this->response->setOutput(file_get_contents($path));
	}

	public function captureProduct($route, $args, $output = null) {
		if (!$this->config->get('module_anchor_price_status')) {
			return;
		}

		$product_id = 0;
		$source = 'product_edit_event';
		if (strpos($route, '/addProduct') !== false) {
			$product_id = (int)$output;
			$source = 'product_add_event';
		} elseif (isset($args[0])) {
			$product_id = (int)$args[0];
		}

		if ($product_id) {
			$this->load->model('extension/module/anchor_price');
			if ($this->model_extension_module_anchor_price->tablesExist()) {
				$this->model_extension_module_anchor_price->capturePublishedProduct($product_id, $source, $this->userId());
			}
		}
	}

	public function captureQuickEdit($route, $args, $output = null) {
		if (!$this->config->get('module_anchor_price_status')) {
			return;
		}

		if (isset($args[0], $args[1]) && $args[1] === 'status') {
			$this->load->model('extension/module/anchor_price');

			if ($this->model_extension_module_anchor_price->tablesExist()) {
				$this->model_extension_module_anchor_price->capturePublishedProduct((int)$args[0], 'quick_edit_event', $this->userId());
			}
		}
	}

	public function install() {
		$this->load->model('extension/module/anchor_price');
		$this->model_extension_module_anchor_price->install();
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('module_anchor_price', 0);
		$settings['module_anchor_price_status'] = 1;
		if (!isset($settings['module_anchor_price_reference_date']) || !$this->validDate($settings['module_anchor_price_reference_date'])) {
			$settings['module_anchor_price_reference_date'] = (new DateTime('now', new DateTimeZone('Europe/Zagreb')))->format('Y-m-d');
		}
		if (!isset($settings['module_anchor_price_default_unit']) || trim($settings['module_anchor_price_default_unit']) === '') {
			$settings['module_anchor_price_default_unit'] = 'kom';
		}
		if (!isset($settings['module_anchor_price_cron_key']) || trim($settings['module_anchor_price_cron_key']) === '') {
			$settings['module_anchor_price_cron_key'] = $this->generateCronKey();
		}
		$this->model_setting_setting->editSetting('module_anchor_price', $settings, 0);

		$this->load->model('user/user_group');
		$user_group_id = (int)$this->user->getGroupId();
		$user_group = $this->model_user_user_group->getUserGroup($user_group_id);
		foreach (array('access', 'modify') as $permission_type) {
			if (!isset($user_group['permission'][$permission_type]) || !in_array('extension/module/anchor_price', $user_group['permission'][$permission_type], true)) {
				$this->model_user_user_group->addPermission($user_group_id, $permission_type, 'extension/module/anchor_price');
			}
		}

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('anchor_price');
		$this->model_setting_event->addEvent('anchor_price', 'admin/model/catalog/product/addProduct/after', 'extension/module/anchor_price/captureProduct');
		$this->model_setting_event->addEvent('anchor_price', 'admin/model/catalog/product/editProduct/after', 'extension/module/anchor_price/captureProduct');
		$this->model_setting_event->addEvent('anchor_price', 'admin/model/extension/module/product_quick_edit/quickEditProduct/after', 'extension/module/anchor_price/captureQuickEdit');
		$this->model_setting_event->addEvent('anchor_price', 'catalog/model/module/oc_model/addProduct/after', 'extension/module/anchor_price/captureProduct');
		$this->model_setting_event->addEvent('anchor_price', 'catalog/model/module/oc_model/editProduct/after', 'extension/module/anchor_price/captureProduct');
		$this->model_setting_event->addEvent('anchor_price', 'catalog/view/*/before', 'extension/module/anchor_price/beforeView');

		$this->model_extension_module_anchor_price->seedExistingProducts($this->userId());
	}

	public function uninstall() {
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('anchor_price');
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('module_anchor_price', 0);
		if ($settings) {
			$settings['module_anchor_price_status'] = 0;
			$this->model_setting_setting->editSetting('module_anchor_price', $settings, 0);
		}
		$this->load->model('extension/module/anchor_price');
		$this->model_extension_module_anchor_price->uninstall();
	}

	private function validateEdit() {
		if (!$this->config->get('module_anchor_price_status')) {
			$this->error['warning'] = $this->language->get('error_not_installed');
		} elseif (!$this->user->hasPermission('modify', 'extension/module/anchor_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach (array('price', 'gross_price') as $field) {
			$value = isset($this->request->post[$field]) ? $this->normaliseNumber($this->request->post[$field]) : '';
			if ($value === '' || !is_numeric($value) || (float)$value < 0) {
				$this->error[$field] = $this->language->get('error_' . $field);
			}
		}

		$date = isset($this->request->post['reference_date']) ? trim($this->request->post['reference_date']) : '';
		if (!$this->validDate($date)) {
			$this->error['reference_date'] = $this->language->get('error_reference_date');
		}

		$status = isset($this->request->post['verification_status']) ? $this->request->post['verification_status'] : '';
		if (!in_array($status, array('confirmed', 'pending', 'disabled'), true)) {
			$this->error['status'] = $this->language->get('error_status');
		}

		$reason = isset($this->request->post['reason']) ? trim($this->request->post['reason']) : '';
		if (utf8_strlen($reason) < 3 || utf8_strlen($reason) > 255) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		return !$this->error;
	}

	private function normaliseNumber($value) {
		$value = trim((string)$value);
		if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
			if (strrpos($value, ',') > strrpos($value, '.')) {
				$value = str_replace('.', '', $value);
				return str_replace(',', '.', $value);
			}
			return str_replace(',', '', $value);
		}
		return str_replace(',', '.', $value);
	}

	private function validDate($value) {
		$date = DateTime::createFromFormat('!Y-m-d', $value);
		return $date && $date->format('Y-m-d') === $value;
	}

	private function statusText($status) {
		$key = 'text_status_' . $status;
		$text = $this->language->get($key);
		return $text === $key ? $status : $text;
	}

	private function snapshotSummary(array $snapshot) {
		if (!$snapshot) {
			return '—';
		}
		$parts = array();
		foreach (array('price', 'gross_price', 'reference_date', 'verification_status') as $field) {
			if (isset($snapshot[$field])) {
				$parts[] = $field . ': ' . $snapshot[$field];
			}
		}
		return implode(' | ', $parts);
	}

	private function filterUrl() {
		$url = '';
		foreach (array('filter_name', 'filter_model', 'filter_status', 'filter_date_from', 'filter_date_to') as $key) {
			if (isset($this->request->get[$key]) && $this->request->get[$key] !== '') {
				$url .= '&' . $key . '=' . urlencode($this->request->get[$key]);
			}
		}
		return $url;
	}

	private function userId() {
		return $this->user && $this->user->isLogged() ? (int)$this->user->getId() : 0;
	}

	private function generateCronKey() {
		if (function_exists('random_bytes')) {
			try {
				return bin2hex(random_bytes(32));
			} catch (Exception $exception) {
				// Use the compatibility fallback below.
			}
		}
		return hash('sha256', uniqid((string)mt_rand(), true));
	}
}
