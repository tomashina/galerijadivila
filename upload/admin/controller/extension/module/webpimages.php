<?php
/******************************************************
 * @package Webp Images for OC2x, OC3x
 * @version 2.2
 * @author https://aits.xyz
 * @copyright Copyright (C)2020 aits.xyz All rights reserved.
 * @email:info@aits.xyz 
 * $date: 15 April 2020
*******************************************************/

class ControllerExtensionModuleWebpImages extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/webpimages');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		//$this->document->addScript(' ');
		$this->document->addStyle('view/stylesheet/webpimages.css');
		if (isset($this->session->data['webp'])) {
			unset($this->session->data['webp']);
		}
		$ver ='';
		$PREFIX = '';
		$store_id = 0;
		if(substr(VERSION,0,1)=='3' ) {
			$PREFIX = 'analytics_';
			$ver = '3';
			$sub_ver = substr(VERSION,0,3);
		} else {
			$ver = '2';
			$PREFIX = '';
			$sub_ver = substr(VERSION,0,3);
		}
		if (!isset($this->request->get['store_id'])) {
			$store_id = 0;
		} else {
			$store_id = $this->request->get['store_id'];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_webpimages', $this->request->post, $store_id);

			$this->session->data['success'] = $this->language->get('text_success');

			if ($ver == '3') {
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			} else {
				$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if ($ver != '3') {
			$data['token'] = $this->session->data['token'];
			$data['heading_title'] = $this->language->get('heading_title');
			$data['text_version'] = $this->language->get('text_version');
			$data['text_success'] = $this->language->get('text_success');
			$data['text_edit'] = $this->language->get('text_edit');
			$data['entry_quality'] = $this->language->get('entry_quality');
			$data['entry_status'] = $this->language->get('entry_status');
			$data['entry_cookie'] = $this->language->get('entry_cookie');
			$data['help_gd'] = $this->language->get('help_gd');
			$data['help_quality'] = $this->language->get('help_quality');
			$data['help_cookie'] = $this->language->get('help_cookie');
			$data['button_save'] = $this->language->get('button_save');
			$data['button_cancel'] = $this->language->get('button_cancel');
			
			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/webpimages', 'token=' . $this->session->data['token']. '&store_id=' . $store_id, true)
			);

			$data['action'] = $this->url->link('extension/module/webpimages', 'token=' . $this->session->data['token'] . '&store_id=' . $store_id, true);
			$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

		} else {

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
				'href' => $this->url->link('extension/module/webpimages', 'user_token=' . $this->session->data['user_token']. '&store_id=' . $store_id, true)
			);

			$data['action'] = $this->url->link('extension/module/webpimages', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true);

			$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
				
			$data['user_token'] = $this->session->data['user_token'];
		}

		if (isset($this->request->post['module_webpimages_status'])) {
			$data['module_webpimages_status'] = $this->request->post['module_webpimages_status'];
		} else {
			$data['module_webpimages_status'] = $this->config->get('module_webpimages_status');
		}
		
		if (isset($this->request->post['module_webpimages_cookie'])) {
			$data['module_webpimages_cookie'] = $this->request->post['module_webpimages_cookie'];
		} else {
			$data['module_webpimages_cookie'] = $this->config->get('module_webpimages_cookie');
		}

		if (isset($this->request->post['module_webpimages_quality'])) {
			$data['module_webpimages_quality'] = $this->request->post['module_webpimages_quality'];
		} elseif($this->config->get('module_webpimages_quality')) {
			$data['module_webpimages_quality'] = $this->config->get('module_webpimages_quality');
		} else {
			$data['module_webpimages_quality'] = 80;
			$data['module_webpimages_cookie'] = true;
		}

		$webp_installed = gd_info();
		$data['webp'] = $webp_installed['WebP Support'];
		
		if ($data['webp']) {
			$data['text_alert'] = $this->language->get('text_gd');
		} else {
			$data['text_alert'] = $this->language->get('text_error_gd');
		}

		$data['gdinfo'] = $webp_installed;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (isset($ver) && $ver == '3') {
			$this->response->setOutput($this->load->view('extension/module/webpimages', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/module/webpimages_TPL', $data));	
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/webpimages')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}