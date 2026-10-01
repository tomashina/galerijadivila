<?php
class ControllerInformationPriceList extends Controller {
	public function index() {
		if (!$this->config->get('module_anchor_price_status')) {
			return $this->notFound();
		}

		$this->load->language('information/price_list');
		$this->loadCroatianLanguageFallback();
		$this->load->model('extension/module/anchor_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_intro'] = $this->language->get('text_intro');
		$data['text_empty'] = $this->language->get('text_empty');
		$data['column_location'] = $this->language->get('column_location');
		$data['column_published'] = $this->language->get('column_published');
		$data['column_products'] = $this->language->get('column_products');
		$data['column_file'] = $this->language->get('column_file');
		$data['button_download'] = $this->language->get('button_download');
		$data['button_download_csv'] = $this->language->get('button_download_csv');
		$data['button_download_xml'] = $this->language->get('button_download_xml');
		$data['latest_csv'] = $this->url->link('information/price_list/download', 'format=csv');
		$data['latest_xml'] = $this->url->link('information/price_list/download', 'format=xml');

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => $data['heading_title'],
			'href' => $this->url->link('information/price_list')
		);

		$data['publications'] = array();
		$publications = $this->model_extension_module_anchor_price->getPublications();

		foreach ($publications as $publication) {
			$data['publications'][] = array(
				'location_name' => $this->language->get('text_location'),
				'published'     => date($this->language->get('datetime_format'), strtotime($publication['published_at'])),
				'product_count' => (int)$publication['product_count'],
				'filename'      => $publication['filename'],
				'xml_filename'  => $publication['xml_filename'],
				'download_csv'  => $this->url->link('information/price_list/download', 'publication_id=' . (int)$publication['publication_id'] . '&format=csv'),
				'download_xml'  => $this->url->link('information/price_list/download', 'publication_id=' . (int)$publication['publication_id'] . '&format=xml')
			);
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/price_list', $data));
	}

	public function download() {
		if (!$this->config->get('module_anchor_price_status')) {
			return $this->notFound();
		}

		$format = isset($this->request->get['format']) && strtolower($this->request->get['format']) === 'xml' ? 'xml' : 'csv';
		$publication_id = isset($this->request->get['publication_id']) ? (int)$this->request->get['publication_id'] : 0;
		$this->load->model('extension/module/anchor_price');
		$publication = $publication_id > 0
			? $this->model_extension_module_anchor_price->getPublication($publication_id, true)
			: $this->model_extension_module_anchor_price->getLatestPublication($format);

		if (!$publication) {
			return $this->notFound();
		}

		$path = $this->model_extension_module_anchor_price->publicationPath($publication, $format);

		if (!$path || !$this->model_extension_module_anchor_price->publicationFileIsValid($publication, $path, $format)) {
			return $this->notFound();
		}

		$filename = basename($format === 'xml' ? $publication['xml_filename'] : $publication['filename']);
		$this->response->setCompression(0);
		$this->response->addHeader('Content-Type: ' . ($format === 'xml' ? 'application/xml' : 'text/csv') . '; charset=utf-8');
		$this->response->addHeader('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
		$this->response->addHeader('Content-Length: ' . filesize($path));
		$this->response->addHeader('X-Content-Type-Options: nosniff');
		$this->response->setOutput(file_get_contents($path));
	}

	private function loadCroatianLanguageFallback() {
		$language_code = !empty($this->session->data['language'])
			? (string)$this->session->data['language']
			: (string)$this->config->get('config_language');
		$language_code = strtolower(str_replace('_', '-', $language_code));

		if ($language_code === 'hr' || $language_code === 'hr-hr') {
			$language = new Language('hr-HR');
			$translations = $language->load('information/price_list');

			foreach ($translations as $key => $value) {
				$this->language->set($key, $value);
			}
		}
	}

	private function notFound() {
		$this->response->addHeader('HTTP/1.1 404 Not Found');
		$this->load->language('error/not_found');
		$this->document->setTitle($this->language->get('heading_title'));
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_error'] = $this->language->get('text_error');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['continue'] = $this->url->link('common/home');
		$data['breadcrumbs'] = array();
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('error/not_found', $data));
	}
}
