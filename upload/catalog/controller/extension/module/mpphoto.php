<?php
class ControllerExtensionModuleMpphoto extends \mpphotogallery\Controllercatalog {

	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	public function index($setting) {
		if (!$this->config->get('module_mpphotogallery_status')) {
			return '';
		}

		$this->load->language($this->extension_path . 'module/mpphoto');
		static $module = 0;

		$this->load->model($this->extension_path . 'mpphotogallery/photo');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.min.js');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');
		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/smgallery/css/lightgallery.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/lightgallery-all.min.js');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/jquery.mousewheel.min.js');

		$data['heading_title'] = isset($setting['photo_description'][$this->config->get('config_language_id')]['title']) ? $setting['photo_description'][$this->config->get('config_language_id')]['title'] : $this->language->get('heading_title');

		$data['limit'] = !empty($setting['limit']) ? $setting['limit'] : 5;
		$data['carousel'] = !empty($setting['carousel']) ? $setting['carousel'] : '';
		$data['extitle'] = !empty($setting['extitle']) ? $setting['extitle'] : '';

		$data['photos'] = [];

		if ($data['carousel']) {
			$photos = $this->model_extension_mpphotogallery_photo->getPhotosByMpGallery($setting['mpgallery_id']);
		} else {
			$photos = $this->model_extension_mpphotogallery_photo->getPhotosByMpGallery($setting['mpgallery_id'], $setting['limit']);
		}

		// 07-05-2022: updation task start
		$popup_width = 500;
		if ((int)$this->config->get('module_mpphotogallery_popup_width')) {
			$popup_width = (int)$this->config->get('module_mpphotogallery_popup_width');
		}
		$popup_height = 729;
		if ((int)$this->config->get('module_mpphotogallery_popup_height')) {
			$popup_height = (int)$this->config->get('module_mpphotogallery_popup_height');
		}
		// 07-05-2022: updation task end

		foreach ($photos as $photo_info) {
			if ($photo_info['photo'] && file_exists(DIR_IMAGE . $photo_info['photo'])) {
				$image = $this->model_tool_image->resize($photo_info['photo'], $setting['width'], $setting['height']);

				$popup = $this->model_tool_image->resize($photo_info['photo'], $popup_width, $popup_height);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', $setting['width'], $setting['height']);

				$popup = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
			}

			$data['photos'][] = array(
				'mpgallery_id'  	=> $photo_info['mpgallery_id'],
				'name'  		=> $photo_info['name'],
				'link'  		=> $photo_info['link'],
				'image'       	=> $image,
				'popup' 		=> $popup
			);
		}

		$data['module'] = $module++;

		$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');

		$data['text_view'] = $this->language->get('text_view');
		$data['view'] = $this->url->link($this->extension_path . 'mpphotogallery/album_photo', '', true);

		if ($data['photos']) {
			return $this->load->view($this->extension_path . 'module/mpphoto', $data);
		}

		return '';
	}
}