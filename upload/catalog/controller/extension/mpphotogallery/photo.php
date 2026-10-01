<?php
class ControllerExtensionMpPhotoGalleryPhoto extends \mpphotogallery\Controllercatalog {
	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	public function index() {
		if (!$this->config->get('module_mpphotogallery_status')) {
			return;
		}

		$this->load->language($this->extension_path . 'mpphotogallery/photo');

		$this->load->model($this->extension_path . 'mpphotogallery/album');

		$this->load->model($this->extension_path . 'mpphotogallery/photo');

		$this->load->model($this->extension_path . 'mpphotogallery/video');

		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');
		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/smgallery/css/lightgallery.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/lightgallery-all.js');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/jquery.mousewheel.min.js');

		$data['cursive_font'] = $this->config->get('module_mpphotogallery_photo_cursive_font');

		$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');

		$data['text_share'] = $this->language->get('text_share');

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_album'),
			'href' => $this->url->link($this->extension_path . 'mpphotogallery/album', ''. $url, true)
		);

		// $data['breadcrumbs'][] = array(
		// 	'text' => $this->language->get('text_album_photo'),
		// 	'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_photo', $url)
		// );
		// $data['breadcrumbs'][] = array(
		// 	'text' => $this->language->get('text_album_video'),
		// 	'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_video', $url)
		// );

		if (isset($this->request->get['mpgallery_id'])) {
			$gallery_id = (int)$this->request->get['mpgallery_id'];
		} else {
			$gallery_id = 0;
		}

		$gallery_info = $this->model_extension_mpphotogallery_album->getMpGalleryinfo($gallery_id);
		if ($gallery_info) {
			// 07-05-2022: updation task start
			if (!(int)$gallery_info['width']) {
				$gallery_info['width'] = (int)$this->config->get('module_mpphotogallery_album_width');
			}
			if (!(int)$gallery_info['width']) {
				$gallery_info['width'] = 213;
			}
			if (!(int)$gallery_info['height']) {
				$gallery_info['height'] = (int)$this->config->get('module_mpphotogallery_album_height');
			}
			if (!(int)$gallery_info['height']) {
				$gallery_info['height'] = 310;
			}

			$popup_width = 500;
			if ((int)$this->config->get('module_mpphotogallery_popup_width')) {
				$popup_width = (int)$this->config->get('module_mpphotogallery_popup_width');
			}
			$popup_height = 729;
			if ((int)$this->config->get('module_mpphotogallery_popup_height')) {
				$popup_height = (int)$this->config->get('module_mpphotogallery_popup_height');
			}
			// 07-05-2022: updation task end
			$url = '';

			if (isset($this->request->get['mpgallery_id'])) {
				$url .= '&mpgallery_id=' . $this->request->get['mpgallery_id'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $gallery_info['title'],
				'href' => $this->url->link($this->extension_path . 'mpphotogallery/photo', '' . $url, true)
			);

			$this->document->setTitle($gallery_info['meta_title']);
			$this->document->addLink($this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $this->request->get['mpgallery_id']), 'canonical');

			$data['heading_title'] = $gallery_info['title'];
			$data['top_description'] = html_entity_decode($gallery_info['top_description'], ENT_QUOTES, 'UTF-8');
			$data['bottom_description'] = html_entity_decode($gallery_info['bottom_description'], ENT_QUOTES, 'UTF-8');

			$data['photos'] = [];
			$results = $this->model_extension_mpphotogallery_photo->getAlbumPhotoDescription($this->request->get['mpgallery_id']);

			foreach ($results as $key => $result) {
				if ($key == 0) {
					if ($result['photo'] && file_exists(DIR_IMAGE . $result['photo'])) {
						$image = $this->model_tool_image->resize($result['photo'], $popup_width, $popup_height);
						$popup = $this->model_tool_image->resize($result['photo'], $popup_width, $popup_height);
					} else {
						$image = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
						$popup = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
					}
				} else {
					if ($result['photo'] && file_exists(DIR_IMAGE . $result['photo'])) {
						$image = $this->model_tool_image->resize($result['photo'], $gallery_info['width'], $gallery_info['height']);
						$popup = $this->model_tool_image->resize($result['photo'], $popup_width, $popup_height);
					} else {
						$image = $this->model_tool_image->resize('no_image.png', $gallery_info['width'], $gallery_info['height']);
						$popup = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
					}
				}

				$data['photos'][] = array(
					'thumb' 	=> $image,
					'popup' 	=> $popup,
					'name' 		=> $result['name'],
					'link' 		=> $result['link']
				);
			}

			$data['social_status'] = $this->config->get('module_mpphotogallery_social_status');


			/*og meta tags starts*/
            // changed to meta title - Preety
			$this->mpphotogallery_meta->setMeta(array(
				'attribute' => 'property',
				'attribute_value' => 'og:title',
				'content' => $gallery_info['meta_title'],
			));
			$this->mpphotogallery_meta->setMeta(array(
				'attribute' => 'property',
				'attribute_value' => 'og:description',
				'content' =>  htmlspecialchars($gallery_info['meta_description'], ENT_QUOTES, 'UTF-8') ,
			));
			$this->mpphotogallery_meta->setMeta(array(
				'attribute' => 'property',
				'attribute_value' => 'og:url',
				'content' => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $this->request->get['mpgallery_id']),
			));

			if ($gallery_info['image'] && file_exists(DIR_IMAGE . $gallery_info['image'])) {
				$this->mpphotogallery_meta->setMeta(array(
					'attribute' 		=> 'property',
					'attribute_value' 	=> 'og:image',
					'content' 			=> $this->model_tool_image->resize($gallery_info['image'], $popup_width, $popup_height),
				));
			}


			$data['current_url'] = $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $this->request->get['mpgallery_id'], true);


			$this->model_extension_mpphotogallery_album->updateViewed($this->request->get['mpgallery_id']);



			// Videos
			$data['heading_video'] = $this->language->get('heading_video');

			// 07-05-2022: updation task start
			if (!(int)$gallery_info['video_width']) {
				$gallery_info['video_width'] = (int)$this->config->get('module_mpphotogallery_albumvideo_width');
			}
			if (!(int)$gallery_info['video_width']) {
				$gallery_info['video_width'] = 213;
			}
			if (!(int)$gallery_info['video_height']) {
				$gallery_info['video_height'] = (int)$this->config->get('module_mpphotogallery_albumvideo_height');
			}
			if (!(int)$gallery_info['video_height']) {
				$album['video_height'] = 310;
			}
			// 07-05-2022: updation task end

			$data['video_width'] = $gallery_info['video_width'];
			$data['video_height'] = $gallery_info['video_height'];

			$data['videos'] = [];

			if ($this->config->get('module_mpphotogallery_show_videos')) {
				$videos = $this->model_extension_mpphotogallery_video->getVideosByMpGallery($this->request->get['mpgallery_id']);
				foreach ($videos as $key => $video_info) {
					if ($key == 0) {
						if ($video_info['video_thumb']) {
							$thumb = $this->model_tool_image->resize($video_info['video_thumb'], $popup_width, $popup_height);
						} else {
							$thumb = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
						}
					} else {
						if ($video_info['video_thumb']) {
							$thumb = $this->model_tool_image->resize($video_info['video_thumb'], $data['video_width'], $data['video_height']);
						} else {
							$thumb = $this->model_tool_image->resize('no_image.png', $data['video_width'], $data['video_height']);
						}
					}

					$data['videos'][] = array(
						'mpgallery_id'  	=> $video_info['mpgallery_id'],
						'name'  		=> $video_info['name'],
						'link'  		=> $video_info['link'],
						'thumb'       	=> $thumb,
					);
				}
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view($this->extension_path . 'mpphotogallery/photo', $data));
		} else {
			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link($this->extension_path . 'mpphotogallery/photo', 'mpgallery_id=' . $gallery_id . $url),
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}