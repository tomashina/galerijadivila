<?php
class ControllerExtensionMpPhotoGalleryProduct extends \mpphotogallery\Controllercatalog {
	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	// 'trigger' => 'catalog/controller/product/product/before',
	public function controllerProduct(&$route, &$data) {

		// $route = 'product/product';
		// $this->request->get['route'] = 'product/product'
		if ($this->config->get('module_mpphotogallery_status') && 	$this->config->get('module_mpphotogallery_galleryproduct_status') && !empty($this->request->get['product_id'])) {


			$this->document->addStyle('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.css');
  		    $this->document->addScript('catalog/view/javascript/mpphotogallery/owl-carousel/owl.carousel.min.js');

			$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');
			$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/smgallery/css/lightgallery.css');
			$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/lightgallery-all.min.js');
			$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/jquery.mousewheel.min.js');

		}
	}

	// 'trigger' => 'catalog/view/product/product/before',
	public function product(&$route, &$data, &$code) {

		$a = $this->index();
		if ($a) {
			$data['content_bottom'] = $a . "\n" . $data['content_bottom'];
		}
	}

	public function index() {

		if ($this->config->get('module_mpphotogallery_status') && 	$this->config->get('module_mpphotogallery_galleryproduct_status') && !empty($this->request->get['product_id'])) {

			$this->load->language($this->extension_path . 'mpphotogallery/product');

			$this->load->model($this->extension_path . 'mpphotogallery/album');
			$this->load->model($this->extension_path . 'mpphotogallery/product');
			$this->load->model($this->extension_path . 'mpphotogallery/photo');
			// 07-05-2022: updation task start
			$this->load->model($this->extension_path . 'mpphotogallery/video');
			// 07-05-2022: updation task end
			$this->load->model('tool/image');




			$description = [];
			$galleryproduct_description = $this->config->get('module_mpphotogallery_galleryproduct_description');
			if (isset($galleryproduct_description[(int)$this->config->get('config_language_id')])) {
				$description = $galleryproduct_description[(int)$this->config->get('config_language_id')];
			} else {
				$description['title'] = $this->language->get('heading_title');
			}

      $data['heading_title'] = $description['title'] ;

      $data['text_photos'] = $this->language->get('text_photos');
			$data['text_viewed'] = $this->language->get('text_viewed');


      $data['carousel'] = (int)$this->config->get('module_mpphotogallery_galleryproduct_carousel');
      $data['extitle'] = (int)$this->config->get('module_mpphotogallery_galleryproduct_extitle');


      $data['galleries'] = [];

      $galleryproduct_width = (int)$this->config->get('module_mpphotogallery_galleryproduct_width');
      $galleryproduct_height = (int)$this->config->get('module_mpphotogallery_galleryproduct_height');

      if (!$galleryproduct_width) {
      	$galleryproduct_width = (int)$this->config->get('module_mpphotogallery_album_width');
      }
      if (!$galleryproduct_height) {
      	$galleryproduct_height = (int)$this->config->get('module_mpphotogallery_album_height');
      }

      $data['photos'] = [];
			// 07-05-2022: updation task start
			$data['videos'] = [];

			$galleryproduct_override_video = (int)$this->config->get('module_mpphotogallery_galleryproduct_override_video');
			$galleryproduct_override_image = (int)$this->config->get('module_mpphotogallery_galleryproduct_override_image');
			// 07-05-2022: updation task end
			$galleryproduct_photo_width = (int)$this->config->get('module_mpphotogallery_galleryproduct_photo_width');
      $galleryproduct_photo_height = (int)$this->config->get('module_mpphotogallery_galleryproduct_photo_height');

      if (!$galleryproduct_photo_width) {
      	$galleryproduct_photo_width = 200;
      }
      if (!$galleryproduct_photo_height) {
      	$galleryproduct_photo_height = 200;
      }

			$galleries = $this->model_extension_mpphotogallery_product->getProductMpGalleries($this->request->get['product_id']);

			foreach ($galleries as $gallery) {
				$gallery_info = $this->model_extension_mpphotogallery_album->getMpGalleryinfo($gallery['mpgallery_id']);
				if ($gallery_info) {
					if ($gallery_info['image']) {
						$image = $this->model_tool_image->resize($gallery_info['image'], $galleryproduct_width, $galleryproduct_height);
						$popup = $this->model_tool_image->resize($gallery_info['image'], $this->config->get('module_mpphotogallery_popup_width'), $this->config->get('module_mpphotogallery_popup_height'));
					} else {
						$image = $this->model_tool_image->resize('no_image.png', $galleryproduct_width, $galleryproduct_height);
						$popup = $this->model_tool_image->resize('no_image.png', $this->config->get('module_mpphotogallery_popup_width'), $this->config->get('module_mpphotogallery_popup_height'));
					}

					$data['galleries'][] = [
						'mpgallery_id'  => $gallery_info['mpgallery_id'],
						'viewed'  	  => $gallery_info['viewed'],
						'thumb'       => $image,
						'popup'       => $popup,
						'title'        => $gallery_info['title'],
						'total_photos' => $this->model_extension_mpphotogallery_album->getTotalMpGalleryPhotos($gallery['mpgallery_id']),
						'description' => utf8_substr(strip_tags(html_entity_decode($gallery_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_gallery_description_length')) . '..',
						'href'        => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $gallery_info['mpgallery_id'])
					];
				}

				// 07-05-2022: updation task start

				if ($gallery['image'] || $galleryproduct_override_image) {
					$photos = $this->model_extension_mpphotogallery_photo->getPhotosByMpGallery($gallery['mpgallery_id']);

					foreach ($photos as $photo_info) {
						if ($photo_info['photo']) {
							$image = $this->model_tool_image->resize($photo_info['photo'], $galleryproduct_photo_width, $galleryproduct_photo_height);

							$popup = $this->model_tool_image->resize($photo_info['photo'], $this->config->get('module_mpphotogallery_popup_width'), $this->config->get('module_mpphotogallery_popup_height'));
						} else {
							$image = $this->model_tool_image->resize('no_image.png', $galleryproduct_photo_width, $galleryproduct_photo_height);

							$popup = $this->model_tool_image->resize('no_image.png', $this->config->get('module_mpphotogallery_popup_width'), $this->config->get('module_mpphotogallery_popup_height'));
						}

						$data['photos'][] = [
							'mpgallery_id'  	=> $photo_info['mpgallery_id'],
							'name'  		=> $photo_info['name'],
							'link'  		=> $photo_info['link'],
							'image'       	=> $image,
							'popup' 		=> $popup
						];
					}
				}

				if ($gallery['video'] || $galleryproduct_override_video) {
					$videos = $this->model_extension_mpphotogallery_video->getVideosByMpGallery($gallery['mpgallery_id']);
					foreach ($videos as $video_info) {
						if ($video_info['video_thumb']) {
							$thumb = $this->model_tool_image->resize($video_info['video_thumb'], $galleryproduct_photo_width, $galleryproduct_photo_height);
						} else {
							$thumb = $this->model_tool_image->resize('no_image.png', $galleryproduct_photo_width, $galleryproduct_photo_height);
						}

						$data['videos'][] = [
							'mpgallery_id'  	=> $video_info['mpgallery_id'],
							'name'  		=> $video_info['name'],
							'link'  		=> $video_info['link'],
							'thumb'       	=> $thumb,
						];
					}
				}

			}
			// 07-05-2022: updation task end

			$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');


			// in extension settings module variable is static and use for tell number of objects in use in numeric value.
			$data['module'] = 'product_gallery';

			$data['limit'] = 4;

			if ($data['galleries'] && $this->config->get('module_mpphotogallery_galleryproduct_viewas') == 'GA') {
				return $this->load->view($this->extension_path . 'mpphotogallery/product_album', $data);
			}

			if (($data['photos'] || $data['videos']) && $this->config->get('module_mpphotogallery_galleryproduct_viewas') == 'GAP') {
				return $this->load->view($this->extension_path . 'mpphotogallery/product_album_photo', $data);
			}
		}
	}
}