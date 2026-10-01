<?php
class ControllerExtensionModuleShortdescription extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/shortdescription');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_shortdescription', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/shortdescription', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/shortdescription', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_shortdescription_status'])) {
			$data['module_shortdescription_status'] = $this->request->post['module_shortdescription_status'];
		} else {
			$data['module_shortdescription_status'] = $this->config->get('module_shortdescription_status');
		}



if (isset($this->request->post['module_shortdescription_bestsellers'])) {
			$data['module_shortdescription_bestsellers'] = $this->request->post['module_shortdescription_bestsellers'];
		} else {
			$data['module_shortdescription_bestsellers'] = $this->config->get('module_shortdescription_bestsellers');
		}

if (isset($this->request->post['module_shortdescription_featured'])) {
			$data['module_shortdescription_featured'] = $this->request->post['module_shortdescription_featured'];
		} else {
			$data['module_shortdescription_featured'] = $this->config->get('module_shortdescription_featured');
		}
if (isset($this->request->post['module_shortdescription_latest'])) {
			$data['module_shortdescription_latest'] = $this->request->post['module_shortdescription_latest'];
		} else {
			$data['module_shortdescription_latest'] = $this->config->get('module_shortdescription_latest');
		}
if (isset($this->request->post['module_shortdescription_specials'])) {
			$data['module_shortdescription_specials'] = $this->request->post['module_shortdescription_specials'];
		} else {
			$data['module_shortdescription_specials'] = $this->config->get('module_shortdescription_specials');
		}
if (isset($this->request->post['module_shortdescription_category'])) {
			$data['module_shortdescription_category'] = $this->request->post['module_shortdescription_category'];
		} else {
			$data['module_shortdescription_category'] = $this->config->get('module_shortdescription_category');
		}
if (isset($this->request->post['module_shortdescription_related'])) {
			$data['module_shortdescription_related'] = $this->request->post['module_shortdescription_related'];
		} else {
			$data['module_shortdescription_related'] = $this->config->get('module_shortdescription_related');
		}
if (isset($this->request->post['module_shortdescription_product_detail'])) {
			$data['module_shortdescription_product_detail'] = $this->request->post['module_shortdescription_product_detail'];
		} else {
			$data['module_shortdescription_product_detail'] = $this->config->get('module_shortdescription_product_detail');
		}
if (isset($this->request->post['module_shortdescription_search'])) {
			$data['module_shortdescription_search'] = $this->request->post['module_shortdescription_search'];
		} else {
			$data['module_shortdescription_search'] = $this->config->get('module_shortdescription_search');
		}
if (isset($this->request->post['module_shortdescription_manufacturer'])) {
			$data['module_shortdescription_manufacturer'] = $this->request->post['module_shortdescription_manufacturer'];
		} else {
			$data['module_shortdescription_manufacturer'] = $this->config->get('module_shortdescription_manufacturer');
		}
if (isset($this->request->post['module_shortdescription_compare'])) {
			$data['module_shortdescription_compare'] = $this->request->post['module_shortdescription_compare'];
		} else {
			$data['module_shortdescription_compare'] = $this->config->get('module_shortdescription_compare');
		}
if (isset($this->request->post['module_shortdescription_wishlist'])) {
			$data['module_shortdescription_wishlist'] = $this->request->post['module_shortdescription_wishlist'];
		} else {
			$data['module_shortdescription_wishlist'] = $this->config->get('module_shortdescription_wishlist');
		}


if (isset($this->request->post['module_shortdescription_limit'])) {
			$data['module_shortdescription_limit'] = $this->request->post['module_shortdescription_limit'];
		} else {
			$data['module_shortdescription_limit'] = $this->config->get('module_shortdescription_limit');
		}





		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/shortdescription', $data));
	}

	public function install()
	{
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product_description ADD short_description TEXT AFTER description");
	}
	
	public function uninstall()
	{
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product_description DROP short_description");
	}

	protected function validate() {
		
		if (!$this->user->hasPermission('modify', 'extension/module/shortdescription')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
		
	}
}
