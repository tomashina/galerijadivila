<?php
class ControllerExtensionAccountContractWithdrawal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/account/contract_withdrawal');
		$this->loadCroatianLanguageFallback();
		$this->load->model('extension/account/contract_withdrawal');

		if (empty($this->session->data['contract_withdrawal_csrf'])) {
			$this->session->data['contract_withdrawal_csrf'] = $this->generateSecureToken();
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$order_info = $this->getMatchedOrder();
		$order_products = $order_info ? $this->model_extension_account_contract_withdrawal->getOrderProducts($order_info['order_id']) : array();
		$product_lookup = $this->isProductLookupRequest();
		$preview = false;
		$statement = '';
		$selected_products = array();

		if ($product_lookup) {
			$this->validateProductLookupRequest($order_info, $order_products);
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && !$product_lookup && $this->validate($order_info, $order_products)) {
			$selected_products = $this->getSelectedProducts($order_products);
			$statement = $this->buildStatement($selected_products);
			$preview = empty($this->request->post['confirm']);

			if (!$preview) {
				$token = $this->generateSecureToken();
				$withdrawal_data = array(
					'order_id'         => (int)$this->request->post['order_id'],
					'customer_id'      => $order_info ? (int)$order_info['customer_id'] : ($this->customer->isLogged() ? (int)$this->customer->getId() : 0),
					'firstname'        => trim($this->request->post['firstname']),
					'lastname'         => trim($this->request->post['lastname']),
					'email'            => trim($this->request->post['email']),
					'telephone'        => trim($this->request->post['telephone']),
					'address'          => trim($this->request->post['address']),
					'refund_iban'      => $this->request->post['refund_iban'],
					'withdrawal_scope' => $this->request->post['withdrawal_scope'],
					'products'         => $selected_products,
					'statement'        => $statement,
					'comment'          => trim($this->request->post['comment']),
					'token'            => $token,
					'ip'               => isset($this->request->server['REMOTE_ADDR']) ? $this->request->server['REMOTE_ADDR'] : '',
					'user_agent'       => isset($this->request->server['HTTP_USER_AGENT']) ? utf8_substr($this->request->server['HTTP_USER_AGENT'], 0, 255) : '',
					'date_ordered'     => $order_info ? date('Y-m-d', strtotime($order_info['date_added'])) : ''
				);

				$contract_withdrawal_id = $this->model_extension_account_contract_withdrawal->addWithdrawal($withdrawal_data);
				$withdrawal_info = $this->model_extension_account_contract_withdrawal->getWithdrawal($contract_withdrawal_id);

				$this->sendCustomerMail($withdrawal_info);
				$this->sendAdminMail($withdrawal_info);

				$this->session->data['contract_withdrawal_csrf'] = $this->generateSecureToken();
				unset($this->session->data['contract_withdrawal_captcha_csrf']);

				$this->response->redirect($this->url->link('extension/account/contract_withdrawal/success', 'contract_withdrawal_id=' . $contract_withdrawal_id . '&token=' . $token, true));
			}
		}

		$data = $this->getCommonData();
		$data['action'] = $this->url->link('extension/account/contract_withdrawal', '', true);
		$data['products_url'] = $this->url->link('extension/account/contract_withdrawal/products', '', true);
		$data['preview'] = $preview;
		$data['statement'] = $statement;
		$data['selected_products'] = $selected_products;
		$data['order_products'] = $order_products;
		$data['order_matched'] = (bool)$order_info;
		$data['errors'] = $this->error;
		$data['csrf_token'] = $this->session->data['contract_withdrawal_csrf'];

		if (!$preview && $this->isReturnCaptchaEnabled()) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		$data['form'] = $this->getFormData($order_info);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/account/contract_withdrawal_form', $data));
	}

	public function products() {
		$this->load->language('extension/account/contract_withdrawal');
		$this->loadCroatianLanguageFallback();
		$this->load->model('extension/account/contract_withdrawal');

		$json = array(
			'success'  => false,
			'message'  => $this->language->get('error_order_lookup'),
			'products' => array()
		);

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, private');
		$this->response->addHeader('X-Content-Type-Options: nosniff');
		$this->response->addHeader('X-Robots-Tag: noindex, nofollow, noarchive');

		if (!isset($this->request->server['REQUEST_METHOD']) || $this->request->server['REQUEST_METHOD'] !== 'POST') {
			$this->response->setOutput(json_encode($json));
			return;
		}

		$csrf_token = isset($this->request->post['csrf_token']) && is_scalar($this->request->post['csrf_token']) ? (string)$this->request->post['csrf_token'] : '';

		if (!$this->isValidCsrfToken($csrf_token)) {
			$json['message'] = $this->language->get('error_security');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$order_id = isset($this->request->post['order_id']) ? $this->parseOrderId($this->request->post['order_id']) : 0;
		$email = isset($this->request->post['email']) && is_scalar($this->request->post['email']) ? trim((string)$this->request->post['email']) : '';

		if (!$order_id || utf8_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->response->setOutput(json_encode($json));
			return;
		}

		$order_info = $this->findMatchedOrder($order_id, $email);

		if (!$order_info) {
			$this->response->setOutput(json_encode($json));
			return;
		}

		$order_products = $this->model_extension_account_contract_withdrawal->getOrderProducts($order_info['order_id']);

		if (!$order_products) {
			$json['message'] = $this->language->get('error_products_empty');
			$this->response->setOutput(json_encode($json));
			return;
		}

		foreach ($order_products as $product) {
			$json['products'][] = array(
				'order_product_id' => (int)$product['order_product_id'],
				'name'             => (string)$product['name'],
				'model'            => (string)$product['model'],
				'quantity'         => (int)$product['quantity']
			);
		}

		$json['success'] = true;
		$json['message'] = sprintf($this->language->get('text_products_loaded'), count($json['products']));

		$this->response->setOutput(json_encode($json));
	}

	public function success() {
		$this->load->language('extension/account/contract_withdrawal');
		$this->loadCroatianLanguageFallback();
		$this->load->model('extension/account/contract_withdrawal');

		$this->document->setTitle($this->language->get('text_success_title'));

		$contract_withdrawal_id = isset($this->request->get['contract_withdrawal_id']) ? (int)$this->request->get['contract_withdrawal_id'] : 0;
		$token = isset($this->request->get['token']) ? $this->request->get['token'] : '';
		$withdrawal_info = $this->model_extension_account_contract_withdrawal->getWithdrawalByToken($contract_withdrawal_id, $token);

		if (!$withdrawal_info) {
			return new Action('error/not_found');
		}

		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, private');
		$this->response->addHeader('Referrer-Policy: no-referrer');
		$this->response->addHeader('X-Robots-Tag: noindex, nofollow, noarchive');

		$data = $this->getCommonData();
		$data['contract_withdrawal_id'] = $withdrawal_info['contract_withdrawal_id'];
		$data['order_id'] = $withdrawal_info['order_id'];
		$data['email'] = $withdrawal_info['email'];
		$data['status'] = $this->language->get('text_status_new');
		$data['statement'] = $withdrawal_info['statement'];
		$data['date_submitted'] = $this->formatDateTime($withdrawal_info['date_submitted']);
		$data['continue'] = $this->url->link('common/home', '', true);
		$data['account'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/account/contract_withdrawal_success', $data));
	}

	private function getCommonData() {
		$data = array();

		$keys = array(
			'heading_title',
			'text_account',
			'text_intro',
			'text_order',
			'text_customer',
			'text_products',
			'text_full_order',
			'text_selected_items',
			'text_no_products',
			'text_preview',
			'text_preview_help',
			'text_statement',
			'text_success_title',
			'text_success_message',
			'text_reference',
			'text_submitted',
			'text_status',
			'text_back_account',
			'text_continue',
			'text_required_note',
			'text_privacy_note',
			'text_confirm_legal',
			'text_load_products_help',
			'text_products_loaded',
			'text_product_model',
			'text_product_quantity',
			'text_products_no_js',
			'entry_order_id',
			'entry_firstname',
			'entry_lastname',
			'entry_email',
			'entry_telephone',
			'entry_address',
			'entry_refund_iban',
			'entry_scope',
			'entry_comment',
			'button_preview',
			'button_confirm',
			'button_edit',
			'button_load_products',
			'text_loading_products',
			'error_order_lookup'
		);

		foreach ($keys as $key) {
			$data[$key] = $this->language->get($key);
		}

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/account/contract_withdrawal', '', true)
			)
		);

		return $data;
	}

	private function getFormData($order_info) {
		$form = array(
			'order_id'         => '',
			'firstname'        => $this->customer->isLogged() ? $this->customer->getFirstName() : '',
			'lastname'         => $this->customer->isLogged() ? $this->customer->getLastName() : '',
			'email'            => $this->customer->isLogged() ? $this->customer->getEmail() : '',
			'telephone'        => $this->customer->isLogged() ? $this->customer->getTelephone() : '',
			'address'          => '',
			'refund_iban'      => '',
			'withdrawal_scope' => 'full',
			'comment'          => '',
			'order_products'   => array()
		);

		if ($order_info) {
			$form['order_id'] = $order_info['order_id'];
			$form['firstname'] = $order_info['firstname'];
			$form['lastname'] = $order_info['lastname'];
			$form['email'] = $order_info['email'];
			$form['telephone'] = $order_info['telephone'];
			$form['address'] = trim($order_info['payment_address_1'] . "\n" . $order_info['payment_postcode'] . ' ' . $order_info['payment_city']);
		} elseif (isset($this->request->get['order_id'])) {
			$form['order_id'] = (int)$this->request->get['order_id'];
		}

		foreach ($form as $key => $value) {
			if (isset($this->request->post[$key])) {
				$form[$key] = is_array($this->request->post[$key]) ? $this->request->post[$key] : trim($this->request->post[$key]);
			}
		}

		return $form;
	}

	private function getMatchedOrder() {
		if (isset($this->request->post['order_id'])) {
			$order_id = $this->parseOrderId($this->request->post['order_id']);
		} elseif (isset($this->request->get['order_id'])) {
			$order_id = $this->parseOrderId($this->request->get['order_id']);
		} else {
			$order_id = 0;
		}

		if (!$order_id) {
			return false;
		}

		$email = isset($this->request->post['email']) && is_scalar($this->request->post['email']) ? trim((string)$this->request->post['email']) : '';

		return $this->findMatchedOrder($order_id, $email);
	}

	private function findMatchedOrder($order_id, $email) {
		$order_info = $this->model_extension_account_contract_withdrawal->getCustomerOrder((int)$order_id);

		if ($order_info) {
			return $order_info;
		}

		if ($email !== '' && utf8_strlen($email) <= 96 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return $this->model_extension_account_contract_withdrawal->getOrderByEmail((int)$order_id, $email);
		}

		return false;
	}

	private function validate($order_info, $order_products) {
		foreach (array('order_id', 'firstname', 'lastname', 'email', 'telephone', 'address', 'refund_iban', 'withdrawal_scope', 'comment') as $key) {
			if (!isset($this->request->post[$key]) || !is_scalar($this->request->post[$key])) {
				$this->request->post[$key] = '';
			} else {
				$this->request->post[$key] = (string)$this->request->post[$key];
			}
		}

		$this->request->post['refund_iban'] = $this->normalizeIban($this->request->post['refund_iban']);
		$csrf_token = isset($this->request->post['csrf_token']) && is_string($this->request->post['csrf_token']) ? $this->request->post['csrf_token'] : '';
		$session_csrf = isset($this->session->data['contract_withdrawal_csrf']) ? (string)$this->session->data['contract_withdrawal_csrf'] : '';
		$csrf_valid = $this->isValidCsrfToken($csrf_token);

		if (!$csrf_valid) {
			$this->error['warning'] = $this->language->get('error_security');
		}

		if (!empty($this->request->post['website'])) {
			$this->error['warning'] = $this->language->get('error_confirm');
		}

		$ip = isset($this->request->server['REMOTE_ADDR']) && is_scalar($this->request->server['REMOTE_ADDR']) ? trim((string)$this->request->server['REMOTE_ADDR']) : '';

		if ($this->model_extension_account_contract_withdrawal->isRateLimited($ip, 5, 60)) {
			$this->error['warning'] = $this->language->get('error_rate_limit');
		}

		$order_id = trim((string)$this->request->post['order_id']);

		if (!preg_match('/^[1-9][0-9]{0,9}$/', $order_id) || (int)$order_id > 2147483647) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		} else {
			$this->request->post['order_id'] = (string)(int)$order_id;
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 64)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 64)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen(trim($this->request->post['email'])) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if (!$order_info) {
			$this->error['warning'] = $this->language->get('error_order_lookup');
		}

		if (utf8_strlen(trim($this->request->post['telephone'])) > 32) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (utf8_strlen(trim($this->request->post['address'])) > 1000) {
			$this->error['address'] = $this->language->get('error_address');
		}

		if (utf8_strlen(trim($this->request->post['comment'])) > 5000) {
			$this->error['comment'] = $this->language->get('error_comment');
		}

		if ($this->request->post['refund_iban'] && !$this->isValidIban($this->request->post['refund_iban'])) {
			$this->error['refund_iban'] = $this->language->get('error_refund_iban');
		}

		if (!isset($this->request->post['withdrawal_scope']) || !in_array($this->request->post['withdrawal_scope'], array('full', 'items'))) {
			$this->request->post['withdrawal_scope'] = 'full';
		}

		if ($this->request->post['withdrawal_scope'] == 'items') {
			$valid_product_ids = array();

			foreach ($order_products as $product) {
				$valid_product_ids[(string)(int)$product['order_product_id']] = true;
			}

			$requested_product_ids = isset($this->request->post['order_products']) && is_array($this->request->post['order_products']) ? $this->request->post['order_products'] : array();
			$selected_product_ids = array();
			$invalid_selection = false;

			foreach ($requested_product_ids as $order_product_id) {
				if (!is_scalar($order_product_id) || !preg_match('/^[1-9][0-9]{0,10}$/', (string)$order_product_id)) {
					$invalid_selection = true;
					continue;
				}

				$order_product_id = (string)(int)$order_product_id;

				if (!isset($valid_product_ids[$order_product_id])) {
					$invalid_selection = true;
					continue;
				}

				$selected_product_ids[$order_product_id] = $order_product_id;
			}

			$this->request->post['order_products'] = array_values($selected_product_ids);

			if (!$order_products || !$selected_product_ids || $invalid_selection) {
				$this->error['scope'] = $this->language->get('error_scope');
			}
		}

		if ($this->isReturnCaptchaEnabled()) {
			if (!empty($this->request->post['confirm'])) {
				$captcha_csrf = isset($this->session->data['contract_withdrawal_captcha_csrf']) ? (string)$this->session->data['contract_withdrawal_captcha_csrf'] : '';

				if (!$csrf_valid || $captcha_csrf === '' || !hash_equals($session_csrf, $captcha_csrf)) {
					$this->error['captcha'] = $this->language->get('error_security');
				}
			} else {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$this->error['captcha'] = $captcha;
				} elseif ($csrf_valid) {
					$this->session->data['contract_withdrawal_captcha_csrf'] = $session_csrf;
				}
			}
		}

		return !$this->error;
	}

	private function isProductLookupRequest() {
		return isset($this->request->server['REQUEST_METHOD'])
			&& $this->request->server['REQUEST_METHOD'] === 'POST'
			&& isset($this->request->post['load_products'])
			&& is_scalar($this->request->post['load_products'])
			&& (string)$this->request->post['load_products'] === '1';
	}

	private function validateProductLookupRequest($order_info, $order_products) {
		$csrf_token = isset($this->request->post['csrf_token']) && is_scalar($this->request->post['csrf_token']) ? (string)$this->request->post['csrf_token'] : '';
		$order_id = isset($this->request->post['order_id']) ? $this->parseOrderId($this->request->post['order_id']) : 0;
		$email = isset($this->request->post['email']) && is_scalar($this->request->post['email']) ? trim((string)$this->request->post['email']) : '';

		if (!$this->isValidCsrfToken($csrf_token)) {
			$this->error['warning'] = $this->language->get('error_security');
		}

		if (!$order_id) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		} else {
			$this->request->post['order_id'] = (string)$order_id;
		}

		if (utf8_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		} else {
			$this->request->post['email'] = $email;
		}

		if (!$this->error && !$order_info) {
			$this->error['warning'] = $this->language->get('error_order_lookup');
		} elseif (!$this->error && !$order_products) {
			$this->error['warning'] = $this->language->get('error_products_empty');
		}
	}

	private function parseOrderId($order_id) {
		if (!is_scalar($order_id)) {
			return 0;
		}

		$order_id = trim((string)$order_id);

		if (!preg_match('/^[1-9][0-9]{0,9}$/', $order_id) || (int)$order_id > 2147483647) {
			return 0;
		}

		return (int)$order_id;
	}

	private function isValidCsrfToken($csrf_token) {
		$session_csrf = isset($this->session->data['contract_withdrawal_csrf']) ? (string)$this->session->data['contract_withdrawal_csrf'] : '';

		return $csrf_token !== '' && $session_csrf !== '' && hash_equals($session_csrf, (string)$csrf_token);
	}

	private function isReturnCaptchaEnabled() {
		return $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'));
	}

	private function loadCroatianLanguageFallback() {
		$language_code = (string)$this->config->get('config_language');

		if (strtolower($language_code) === 'hr-hr' && $language_code !== 'hr-HR') {
			$language = new Language('hr-HR');
			$translations = $language->load('extension/account/contract_withdrawal');

			foreach ($translations as $key => $value) {
				$this->language->set($key, $value);
			}
		}
	}

	private function generateSecureToken() {
		if (function_exists('random_bytes')) {
			try {
				return bin2hex(random_bytes(32));
			} catch (Exception $e) {
				$this->log->write('Contract withdrawal token error: ' . $e->getMessage());
			}
		}

		return hash('sha256', token(64) . microtime(true));
	}

	private function getSelectedProducts($order_products) {
		if (!$order_products) {
			return array();
		}

		if (!isset($this->request->post['withdrawal_scope']) || $this->request->post['withdrawal_scope'] == 'full') {
			return $order_products;
		}

		$selected = isset($this->request->post['order_products']) ? (array)$this->request->post['order_products'] : array();
		$products = array();

		foreach ($order_products as $product) {
			if (in_array($product['order_product_id'], $selected)) {
				$products[] = $product;
			}
		}

		return $products;
	}

	private function buildStatement($products) {
		$scope_text = $this->request->post['withdrawal_scope'] == 'items' ? $this->language->get('text_selected_items') : $this->language->get('text_full_order');
		$lines = array();

		$lines[] = 'Ja, ' . trim($this->request->post['firstname']) . ' ' . trim($this->request->post['lastname']) . ', ovim izjavljujem da jednostrano raskidam ugovor za narudžbu #' . (int)$this->request->post['order_id'] . '.';
		$lines[] = 'Opseg: ' . $scope_text . '.';

		if ($products) {
			$lines[] = 'Artikli:';

			foreach ($products as $product) {
				$lines[] = '- ' . $product['name'] . ' (' . $product['model'] . '), količina: ' . $product['quantity'];
			}
		}

		if (trim($this->request->post['comment'])) {
			$lines[] = 'Napomena: ' . trim($this->request->post['comment']);
		}

		if ($this->request->post['refund_iban']) {
			$lines[] = 'IBAN za povrat sredstava: ' . $this->request->post['refund_iban'];
		}

		$lines[] = 'E-mail za potvrdu: ' . trim($this->request->post['email']);
		$lines[] = 'Vrijeme podnošenja: ' . $this->formatDateTime(date('Y-m-d H:i:s'));

		return implode("\n", $lines);
	}

	private function sendCustomerMail($withdrawal_info) {
		$subject = sprintf($this->language->get('email_customer_subject'), $withdrawal_info['contract_withdrawal_id']);
		$message = $this->buildMailMessage($withdrawal_info, true);

		$this->sendMail($withdrawal_info['email'], $subject, $message);
	}

	private function sendAdminMail($withdrawal_info) {
		$subject = sprintf($this->language->get('email_admin_subject'), $withdrawal_info['contract_withdrawal_id']);
		$message = $this->buildMailMessage($withdrawal_info, false);
		$emails = array($this->config->get('config_email'));

		if ($this->config->get('config_mail_alert_email')) {
			foreach (explode(',', $this->config->get('config_mail_alert_email')) as $email) {
				$email = trim($email);

				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$emails[] = $email;
				}
			}
		}

		foreach (array_unique($emails) as $email) {
			if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->sendMail($email, $subject, $message);
			}
		}
	}

	private function buildMailMessage($withdrawal_info, $customer_copy) {
		$lines = array();

		if ($customer_copy) {
			$lines[] = 'Potvrđujemo primitak vašeg zahtjeva za jednostrani raskid ugovora.';
		} else {
			$lines[] = 'Zaprimljen je novi zahtjev za jednostrani raskid ugovora.';
		}

		$lines[] = '';
		$lines[] = 'Broj zahtjeva: #' . $withdrawal_info['contract_withdrawal_id'];
		$lines[] = 'Broj narudžbe: #' . $withdrawal_info['order_id'];
		$lines[] = 'Potrošač: ' . $withdrawal_info['firstname'] . ' ' . $withdrawal_info['lastname'];
		$lines[] = 'E-mail: ' . $withdrawal_info['email'];
		if (!empty($withdrawal_info['refund_iban'])) {
			$lines[] = 'IBAN za povrat sredstava: ' . $withdrawal_info['refund_iban'];
		}
		$lines[] = 'Vrijeme zaprimanja: ' . $this->formatDateTime($withdrawal_info['date_submitted']);
		$lines[] = '';
		$lines[] = 'Sadržaj izjave:';
		$lines[] = $withdrawal_info['statement'];

		return implode("\n", $lines);
	}

	private function sendMail($to, $subject, $message) {
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($to);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setText($message);

		try {
			$mail->send();
		} catch (Exception $e) {
			$this->log->write('Contract withdrawal mail error: ' . $e->getMessage());
		}
	}

	private function normalizeIban($iban) {
		return strtoupper(preg_replace('/\s+/', '', trim($iban)));
	}

	private function isValidIban($iban) {
		return (bool)preg_match('/^[A-Z]{2}[0-9A-Z]{13,32}$/', $iban);
	}

	private function formatDateTime($date) {
		return date('Y-m-d H:i:s', strtotime($date)) . ' (' . date_default_timezone_get() . ')';
	}
}
