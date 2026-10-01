<?php
class ControllerExtensionSaleContractWithdrawal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/sale/contract_withdrawal');
		$this->loadCroatianLanguageFallback();
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/sale/contract_withdrawal');

		$this->getList();
	}

	public function info() {
		$this->load->language('extension/sale/contract_withdrawal');
		$this->loadCroatianLanguageFallback();
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/sale/contract_withdrawal');

		$contract_withdrawal_id = isset($this->request->get['contract_withdrawal_id']) ? (int)$this->request->get['contract_withdrawal_id'] : 0;
		$withdrawal_info = $this->model_extension_sale_contract_withdrawal->getWithdrawal($contract_withdrawal_id);

		if (!$withdrawal_info) {
			return new Action('error/not_found');
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$statuses = $this->getStatuses();
			$status = isset($statuses[$this->request->post['status']]) ? $this->request->post['status'] : $withdrawal_info['status'];
			$comment = isset($this->request->post['comment']) ? trim($this->request->post['comment']) : '';
			$notify = !empty($this->request->post['notify']) ? 1 : 0;

			$this->model_extension_sale_contract_withdrawal->addHistory($contract_withdrawal_id, $status, $comment, $notify, $this->user->getId());

			if ($notify) {
				$withdrawal_info['status'] = $status;
				$this->sendStatusMail($withdrawal_info, $statuses[$status], $comment);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/sale/contract_withdrawal/info', 'user_token=' . $this->session->data['user_token'] . '&contract_withdrawal_id=' . $contract_withdrawal_id, true));
		}

		$this->getInfo($withdrawal_info);
	}

	protected function getList() {
		$filters = array(
			'filter_contract_withdrawal_id' => isset($this->request->get['filter_contract_withdrawal_id']) ? $this->request->get['filter_contract_withdrawal_id'] : '',
			'filter_order_id'               => isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '',
			'filter_customer'               => isset($this->request->get['filter_customer']) ? $this->request->get['filter_customer'] : '',
			'filter_email'                  => isset($this->request->get['filter_email']) ? $this->request->get['filter_email'] : '',
			'filter_status'                 => isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '',
			'filter_date_submitted'         => isset($this->request->get['filter_date_submitted']) ? $this->request->get['filter_date_submitted'] : ''
		);

		$sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'cw.contract_withdrawal_id';
		$order = isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC';
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
		$url = $this->buildUrl($filters);

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data = $this->getLanguageData();
		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . $url, true)
			)
		);

		$filter_data = $filters;
		$filter_data['sort'] = $sort;
		$filter_data['order'] = $order;
		$filter_data['start'] = ($page - 1) * $this->config->get('config_limit_admin');
		$filter_data['limit'] = $this->config->get('config_limit_admin');

		$withdrawal_total = $this->model_extension_sale_contract_withdrawal->getTotalWithdrawals($filter_data);
		$results = $this->model_extension_sale_contract_withdrawal->getWithdrawals($filter_data);
		$statuses = $this->getStatuses();

		$data['withdrawals'] = array();

		foreach ($results as $result) {
			$data['withdrawals'][] = array(
				'contract_withdrawal_id' => $result['contract_withdrawal_id'],
				'order_id'               => $result['order_id'],
				'customer'               => $result['firstname'] . ' ' . $result['lastname'],
				'email'                  => $result['email'],
				'status'                 => isset($statuses[$result['status']]) ? $statuses[$result['status']] : $result['status'],
				'date_submitted'         => $this->formatDateTime($result['date_submitted']),
				'date_modified'          => $this->formatDateTime($result['date_modified']),
				'info'                   => $this->url->link('extension/sale/contract_withdrawal/info', 'user_token=' . $this->session->data['user_token'] . '&contract_withdrawal_id=' . $result['contract_withdrawal_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['statuses'] = $statuses;

		foreach ($filters as $key => $value) {
			$data[$key] = $value;
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$url = $this->buildUrl($filters);

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_request_id'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=cw.contract_withdrawal_id' . $url, true);
		$data['sort_order_id'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=cw.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_email'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=cw.email' . $url, true);
		$data['sort_status'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=cw.status' . $url, true);
		$data['sort_date_submitted'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=cw.date_submitted' . $url, true);
		$data['sort_date_modified'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . '&sort=cw.date_modified' . $url, true);

		$url = $this->buildUrl($filters);

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $withdrawal_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($withdrawal_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($withdrawal_total - $this->config->get('config_limit_admin'))) ? $withdrawal_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $withdrawal_total, ceil($withdrawal_total / $this->config->get('config_limit_admin')));
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/sale/contract_withdrawal_list', $data));
	}

	protected function getInfo($withdrawal_info) {
		$data = $this->getLanguageData();
		$statuses = $this->getStatuses();

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'], true)
			)
		);

		$data['action'] = $this->url->link('extension/sale/contract_withdrawal/info', 'user_token=' . $this->session->data['user_token'] . '&contract_withdrawal_id=' . $withdrawal_info['contract_withdrawal_id'], true);
		$data['cancel'] = $this->url->link('extension/sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'], true);
		$data['order_link'] = $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $withdrawal_info['order_id'], true);
		$data['statuses'] = $statuses;
		$data['status'] = $withdrawal_info['status'];
		$data['status_text'] = isset($statuses[$withdrawal_info['status']]) ? $statuses[$withdrawal_info['status']] : $withdrawal_info['status'];
		$data['withdrawal'] = $withdrawal_info;
		$data['withdrawal']['date_submitted'] = $this->formatDateTime($withdrawal_info['date_submitted']);
		$data['withdrawal']['date_modified'] = $this->formatDateTime($withdrawal_info['date_modified']);
		$data['withdrawal']['scope_text'] = $withdrawal_info['withdrawal_scope'] == 'items' ? $this->language->get('text_selected_items') : $this->language->get('text_full_order');

		$data['histories'] = array();
		$histories = $this->model_extension_sale_contract_withdrawal->getHistories($withdrawal_info['contract_withdrawal_id']);

		foreach ($histories as $history) {
			$data['histories'][] = array(
				'date_added' => $this->formatDateTime($history['date_added']),
				'status'     => isset($statuses[$history['status']]) ? $statuses[$history['status']] : $history['status'],
				'comment'    => nl2br(htmlspecialchars($history['comment'], ENT_QUOTES, 'UTF-8')),
				'notify'     => $history['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'user'       => $history['username'] ? $history['username'] : '-'
			);
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/sale/contract_withdrawal_info', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/sale/contract_withdrawal') && !$this->user->hasPermission('modify', 'sale/return')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function getStatuses() {
		return array(
			'new'        => $this->language->get('text_status_new'),
			'processing' => $this->language->get('text_status_processing'),
			'accepted'   => $this->language->get('text_status_accepted'),
			'rejected'   => $this->language->get('text_status_rejected'),
			'refunded'   => $this->language->get('text_status_refunded'),
			'closed'     => $this->language->get('text_status_closed')
		);
	}

	private function loadCroatianLanguageFallback() {
		$language_code = (string)$this->config->get('config_admin_language');

		if (strtolower($language_code) === 'hr-hr' && $language_code !== 'hr-HR') {
			$language = new Language('hr-HR');
			$translations = $language->load('extension/sale/contract_withdrawal');

			foreach ($translations as $key => $value) {
				$this->language->set($key, $value);
			}
		}
	}

	private function getLanguageData() {
		$data = array();
		$keys = array(
			'heading_title',
			'text_list',
			'text_info',
			'text_filter',
			'text_history',
			'text_history_add',
			'text_no_results',
			'text_order',
			'text_customer',
			'text_statement',
			'text_products',
			'text_missing_products',
			'column_request_id',
			'column_order_id',
			'column_customer',
			'column_email',
			'column_status',
			'column_date_submitted',
			'column_date_modified',
			'column_action',
			'column_product',
			'column_model',
			'column_quantity',
			'column_date_added',
			'column_comment',
			'column_notify',
			'column_user',
			'entry_request_id',
			'entry_order_id',
			'entry_customer',
			'entry_email',
			'entry_status',
			'entry_date_submitted',
			'entry_firstname',
			'entry_lastname',
			'entry_telephone',
			'entry_address',
			'entry_refund_iban',
			'entry_scope',
			'entry_comment',
			'entry_notify',
			'entry_ip',
			'entry_user_agent',
			'button_filter',
			'button_view',
			'button_cancel',
			'button_history_add'
		);

		foreach ($keys as $key) {
			$data[$key] = $this->language->get($key);
		}

		return $data;
	}

	private function buildUrl($filters) {
		$url = '';

		foreach ($filters as $key => $value) {
			if ($value !== '') {
				$url .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
			}
		}

		return $url;
	}

	private function sendStatusMail($withdrawal_info, $status_text, $comment) {
		$subject = sprintf($this->language->get('text_mail_subject'), $withdrawal_info['contract_withdrawal_id']);
		$lines = array();

		$lines[] = 'Status vašeg zahtjeva za jednostrani raskid ugovora je ažuriran.';
		$lines[] = '';
		$lines[] = 'Broj zahtjeva: #' . $withdrawal_info['contract_withdrawal_id'];
		$lines[] = 'Broj narudžbe: #' . $withdrawal_info['order_id'];
		$lines[] = 'Status: ' . $status_text;

		if ($comment) {
			$lines[] = '';
			$lines[] = 'Komentar:';
			$lines[] = $comment;
		}

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
		$mail->setTo($withdrawal_info['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setText(implode("\n", $lines));

		try {
			$mail->send();
		} catch (Exception $e) {
			$this->log->write('Contract withdrawal status mail error: ' . $e->getMessage());
		}
	}

	private function formatDateTime($date) {
		return date('Y-m-d H:i:s', strtotime($date));
	}
}
