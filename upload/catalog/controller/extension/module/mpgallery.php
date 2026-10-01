<?php
class ControllerExtensionModuleMpGallery extends \mpphotogallery\Controllercatalog {

	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	public function index($setting) {
		if (!$this->config->get('module_mpphotogallery_status')) {
			return '';
		}

		$this->load->language($this->extension_path . 'module/mpgallery');
		static $module = 0;

		$data['text_photos'] = $this->language->get('text_photos');
		$data['text_viewed'] = $this->language->get('text_viewed');

		$this->load->model($this->extension_path . 'mpphotogallery/album');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.min.js');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');
		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/simplelightbox.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/simple-lightbox.js');

		$data['heading_title'] = isset($setting['gall_description'][$this->config->get('config_language_id')]['title']) ? $setting['gall_description'][$this->config->get('config_language_id')]['title'] : $this->language->get('heading_title');

		$data['galleries'] = [];

		$data['limit'] = !empty($setting['limit']) ? $setting['limit'] : 5;
		$data['carousel'] = !empty($setting['carousel']) ? $setting['carousel'] : '';
		$data['extitle'] = !empty($setting['extitle']) ? $setting['extitle'] : '';


		$galleries = $setting['gallery'];
		if ((int)$setting['limit']) {
			$galleries = array_slice($setting['gallery'], 0, (int)$setting['limit']);
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

		foreach ($galleries as $mpgallery_id) {
			$gallery_info = $this->model_extension_mpphotogallery_album->getMpGalleryinfo($mpgallery_id);
			if ($gallery_info) {
				if ($gallery_info['image'] && file_exists(DIR_IMAGE . $gallery_info['image'])) {
					$image = $this->model_tool_image->resize($gallery_info['image'], $setting['width'], $setting['height']);
					$popup = $this->model_tool_image->resize($gallery_info['image'], $popup_width, $popup_height);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', $setting['width'], $setting['height']);
					$popup = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
				}

				$data['galleries'][] = array(
					'mpgallery_id'  => $gallery_info['mpgallery_id'],
					'viewed'  	  => $gallery_info['viewed'],
					'thumb'       => $image,
					'popup'       => $popup,
					'title'        => $gallery_info['title'],
					'total_photos' => $this->model_extension_mpphotogallery_album->getTotalMpGalleryPhotos($mpgallery_id),
					'description' => utf8_substr(strip_tags(html_entity_decode($gallery_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_gallery_description_length')) . '..',
					'href'        => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $gallery_info['mpgallery_id'])
				);
			}
		}

		$data['module'] = $module++;

		$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');

		$data['text_view'] = $this->language->get('text_view');
		$data['view'] = $this->url->link($this->extension_path . 'mpphotogallery/album', '', true);

		if ($data['galleries']) {
			return $this->load->view($this->extension_path . 'module/mpgallery', $data);
		}
		return '';
	}
}