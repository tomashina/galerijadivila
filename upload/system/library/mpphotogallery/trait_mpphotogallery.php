<?php
namespace mpphotogallery;
// use in admin only
trait trait_mpphotogallery {

	private $token = 'token';
	private $ssl = true;
	private $extension_page_path = 'extension/extension';
	private $extension_path = 'extension/';
	private $extension_model = 'extension_';
	private $extension_prefix = ['module' => '', 'payment' => '', 'shipping' => '', 'total' =>  '', 'captcha' =>  ''];
	private $controller_file = [
		'customer/customer' => 'customer/customer'
	];
	private $model_file = [
		'extension/extension' => [
			'path' => 'extension/extension',
			'obj' => 'model_extension_extension',
		],
		'extension/module' => [
			'path' => 'extension/module',
			'obj' => 'model_extension_module',
		],
		'customer/custom_field' => [
			'path' => 'customer/custom_field',
			'obj' => 'model_customer_custom_field',
		],
		'customer/customer' => [
			'path' => 'customer/customer',
			'obj' => 'model_customer_customer',
		],
		'customer/customer_group' => [
			'path' => 'customer/customer_group',
			'obj' => 'model_customer_customer_group',
		],
		'extension/event' => [
			'path' => 'extension/event',
			'obj' => 'model_extension_event',
		],
	];
	private $affiliate_show = true;
	public function igniteTraitMpPhotoGallery($registry) {

		$this->load->language('mpphotogallery/mpphotogallery');

		if (VERSION < '2.2.0.0') {
			$this->ssl = 'ssl';
		}

		if (VERSION <= '2.2.0.0') {
			$this->extension_path = '';
			$this->extension_model = '';
			$this->extension_page_path = 'extension/%s';
		}
		if (VERSION < '2.0.3.1') {
			$this->controller_file['customer/customer'] = 'sale/customer';

			$this->model_file['customer/customer'] = [
				'path' => 'sale/customer',
				'obj' => 'model_sale_customer',
			];
			$this->model_file['customer/customer_group'] = [
				'path' => 'sale/customer_group',
				'obj' => 'model_sale_customer_group',
			];
			$this->model_file['customer/custom_field'] = [
				'path' => 'sale/custom_field',
				'obj' => 'model_sale_custom_field',
			];
		}
		if (VERSION >= '3.0.0.0') {
			$this->affiliate_show = false;
			$this->token = 'user_token';
			$this->extension_page_path = 'marketplace/extension';

			$this->extension_prefix = [
				'module' => 'module_',
				'payment' => 'payment_',
				'shipping' => 'shipping_',
				'total' => 'total_',
				'captcha' => 'captcha_',
			];
			$this->model_file['extension/extension'] = [
				'path' => 'setting/extension',
				'obj' => 'model_setting_extension',
			];
			$this->model_file['extension/module'] = [
				'path' => 'setting/module',
				'obj' => 'model_setting_module',
			];
			$this->model_file['extension/event'] = [
				'path' => 'setting/event',
				'obj' => 'model_setting_event',
			];
		}

	}

	public function getCustomerGroups() {
		if (VERSION < '2.0.3.1') {
			$this->load->model('sale/customer_group');
			$model_customer_group = 'model_sale_customer_group';
		} else {
			$this->load->model('customer/customer_group');
			$model_customer_group = 'model_customer_customer_group';
		}
		return $this->{$model_customer_group}->getCustomerGroups();
	}

	public function getLanguages() {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		if (VERSION >= '2.2.0.0') {
			foreach ($languages as &$language) {
				$language['lang_flag'] = 'language/'.$language['code'].'/'.$language['code'].'.png';
			}
		} else {
			foreach ($languages as &$language) {
				$language['lang_flag'] = 'view/image/flags/'.$language['image'].'';
			}
		}
		return $languages;
	}

	public function viewLoad($path, &$data, $twig=true) {
		if (VERSION >= '3.0.0.0' && !$twig) {
			$old_template = $this->config->get('template_engine');
			$this->config->set('template_engine', 'template');
		}
		$view = $this->load->view($this->path($path), $data);
		if (VERSION >= '3.0.0.0' && !$twig) {
			$this->config->set('template_engine', $old_template);
		}
		return $view;
	}

	public function path($path) {
		$path_info = pathinfo($path);

		$npath = $path_info['dirname'] . '/'. $path_info['filename'];
		if (VERSION <= '2.3.0.2') {
			$npath.= '.tpl';
		}
		return $npath;
	}

	public function textEditor(&$d) {
		$d['summernote'] = '';
		$data = [];
		return $this->viewLoad('extension/mpphotogallery/texteditor', $data, false);
	}

	public function installDb() {

	}

	public function getMailObject() {
		if (VERSION >= '3.0.0.0') {
			$mail = new \Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
		} else if (VERSION >= '2.0.2.0') {
			$mail = new \Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
		} else {
			$mail = new \Mail($this->config->get('config_mail'));
		}

		return $mail;
	}
	// 17-march-2023: improvements start
	public function labelEnableDisable($status) {
		$text = $this->language->get('text_disabled');
		$label = 'danger';
		if ($status) {
			$text = $this->language->get('text_enabled');
			$label = 'success';
		}
		return '<i class="fa fa-circle text-'. $label .'"></i>';
	}

	// explicit code for 2x, lower than 2.3x versions only.
	// call using ocmod

	public function getAllLanguageMpPhotoGallery(&$data) {
		// method comes through ocmod.
		if (method_exists($this->language, 'getAllLanguageMpPhotoGallery')) {
			$all = $this->language->getAllLanguageMpPhotoGallery();
			foreach ($all as $key => $value) {
				$data[$key] = $value;
			}
		}
		// from oc2.3x we have language all method.
		if (method_exists($this->language, 'all')) {
			$all = $this->language->all();
			foreach ($all as $key => $value) {
				$data[$key] = $value;
			}
		}
	}

	// 17-march-2023: improvements end
}
if (!function_exists('token')) {
	function token($length = 32) {
		// Create random token
		$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$max = strlen($string) - 1;

		$token = '';

		for ($i = 0; $i < $length; $i++) {
			$token .= $string[mt_rand(0, $max)];
		}
		return $token;
	}
}
