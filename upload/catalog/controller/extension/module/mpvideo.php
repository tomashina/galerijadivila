<?php
class ControllerExtensionModuleMpvideo extends \mpphotogallery\Controllercatalog {

	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	public function index($setting) {
		if (!$this->config->get('module_mpphotogallery_status')) {
			return '';
		}

		$this->load->language($this->extension_path . 'module/mpvideo');

		static $module = 0;

		$this->load->model($this->extension_path . 'mpphotogallery/video');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.js');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');
		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/smgallery/css/lightgallery.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/lightgallery-all.js');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/jquery.mousewheel.min.js');

		$data['heading_title'] = isset($setting['video_description'][$this->config->get('config_language_id')]['title']) ? $setting['video_description'][$this->config->get('config_language_id')]['title'] : $this->language->get('heading_title');

		$data['limit'] = !empty($setting['limit']) ? $setting['limit'] : 5;
		$data['carousel'] = !empty($setting['carousel']) ? $setting['carousel'] : '';
		$data['extitle'] = !empty($setting['extitle']) ? $setting['extitle'] : '';

		$data['videos'] = [];

		if ($data['carousel']) {
			$videos = $this->model_extension_mpphotogallery_video->getVideosByMpGallery($setting['mpgallery_id']);
		} else {
			$videos = $this->model_extension_mpphotogallery_video->getVideosByMpGallery($setting['mpgallery_id'], $setting['limit']);
		}

		foreach ($videos as $video_info) {
			if ($video_info['video_thumb'] && file_exists(DIR_IMAGE . $video_info['video_thumb'])) {
				$thumb = $this->model_tool_image->resize($video_info['video_thumb'], $setting['width'], $setting['height']);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', $setting['width'], $setting['height']);
			}

			$data['videos'][] = array(
				'mpgallery_id'  	=> $video_info['mpgallery_id'],
				'name'  		=> $video_info['name'],
				'link'  		=> $video_info['link'],
				'thumb'       	=> $thumb,
			);
		}

		$data['module'] = $module++;

		$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');

		$data['text_view'] = $this->language->get('text_view');
		$data['view'] = $this->url->link($this->extension_path . 'mpphotogallery/album_video', '', true);

		if ($data['videos']) {
			return $this->load->view($this->extension_path . 'module/mpvideo', $data);
		}

		return '';
	}
}