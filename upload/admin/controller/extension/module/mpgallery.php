<?php
class ControllerExtensionModuleMpGallery extends Controller {
	use mpphotogallery\trait_mpphotogallery;
	private $error = [];

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpPhotoGallery($registry);
	}

	public function index() {
		$this->load->language($this->extension_path . 'module/mpgallery');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model($this->model_file['extension/module']['path']);
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->{$this->model_file['extension/module']['obj']}->addModule('mpgallery', $this->request->post);
			} else {
				$this->{$this->model_file['extension/module']['obj']}->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->extension_page_path, $this->token.'=' . $this->session->data[$this->token] . '&type=module', true));
		}


		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link($this->extension_path . 'module/mpphotogallery_setting', $this->token.'=' . $this->session->data[$this->token], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($this->extension_page_path, $this->token.'=' . $this->session->data[$this->token] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->extension_path . 'module/mpgallery', $this->token.'=' . $this->session->data[$this->token], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->extension_path . 'module/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpPhotoMpGallery($data);
		}
		// 17-march-2023: improvements end

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = [];
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link($this->extension_path . 'module/mpgallery', $this->token.'=' . $this->session->data[$this->token], true);
		} else {
			$data['action'] = $this->url->link($this->extension_path . 'module/mpgallery', $this->token.'=' . $this->session->data[$this->token] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link($this->extension_page_path, $this->token.'=' . $this->session->data[$this->token] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->{$this->model_file['extension/module']['obj']}->getModule($this->request->get['module_id']);
		}

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;
		$data['languages'] = $this->getLanguages();

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (isset($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		$this->load->model($this->extension_path . 'mpphotogallery/mpgallery');

		$data['galleries'] = [];

		if (!empty($this->request->post['gallery'])) {
			$galleries = $this->request->post['gallery'];
		} elseif (isset($module_info['gallery'])) {
			$galleries = $module_info['gallery'];
		} else {
			$galleries = [];
		}

		foreach ($galleries as $mpgallery_id) {
			$gallery_info = $this->{'model_' . $this->extension_model . 'mpphotogallery_mpgallery'}->getMpGallery($mpgallery_id);

			if ($gallery_info) {
				$data['galleries'][] = array(
					'mpgallery_id' => $gallery_info['mpgallery_id'],
					'title'       => $gallery_info['title']
				);
			}
		}

		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (isset($module_info['limit'])) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 5;
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (isset($module_info['width'])) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 200;
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (isset($module_info['height'])) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 200;
		}

		if (isset($this->request->post['carousel'])) {
			$data['carousel'] = $this->request->post['carousel'];
		} elseif (isset($module_info['carousel'])) {
			$data['carousel'] = $module_info['carousel'];
		} else {
			$data['carousel'] = '';
		}

		if (isset($this->request->post['extitle'])) {
			$data['extitle'] = $this->request->post['extitle'];
		} elseif (isset($module_info['extitle'])) {
			$data['extitle'] = $module_info['extitle'];
		} else {
			$data['extitle'] = '1';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($this->request->post['gall_description'])) {
			$data['gall_description'] = $this->request->post['gall_description'];
		} elseif (isset($module_info['gall_description'])) {
			$data['gall_description'] = $module_info['gall_description'];
		} else {
			$data['gall_description'] = [];
		}

		$data['mtabs'] = $this->load->controller($this->extension_path . 'mpphotogallery/mtabs');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'module/mpgallery', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'module/mpgallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['gall_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}

		return !$this->error;
	}
}
