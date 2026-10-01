<?php
class ControllerExtensionMpPhotoGalleryAlbumVideo extends \mpphotogallery\Controllercatalog {
	use \mpphotogallery\trait_mpphotogallery_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);

		$this->igniteTraitMpPhotoGalleryCatalog($registry);
	}

	public function index() {
		if (!$this->config->get('module_mpphotogallery_status')) {
			return;
		}

		$this->load->language($this->extension_path . 'mpphotogallery/album_video');

		$this->load->model($this->extension_path . 'mpphotogallery/video');

		$this->load->model($this->extension_path . 'mpphotogallery/album');

		$this->load->model('tool/image');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/style.css');
		$this->document->addStyle('catalog/view/javascript/mpphotogallery/assets/smgallery/css/lightgallery.css');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/lightgallery-all.js');
		$this->document->addScript('catalog/view/javascript/mpphotogallery/assets/smgallery/js/jquery.mousewheel.min.js');

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (isset($this->request->get['page']) && (int)$this->request->get['page']) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if ((int)$this->config->get('module_mpphotogallery_albumvideo_limit')) {
			$limit = (int)$this->config->get('module_mpphotogallery_albumvideo_limit');
		} else {
			$limit = 20;
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['display_description'] = $this->config->get('module_mpphotogallery_albumvideo_description');
		$data['cursive_font'] = $this->config->get('module_mpphotogallery_albumvideo_cursive_font');

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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'mpphotogallery/album_video', $url)
		);

		$this->load->model('tool/image');

		$data['galleries'] = [];

		$filter_data = array(
			'start' => ($page - 1) * $limit,
			'limit' => $limit,
		);

		$data['galleries'] = [];

		$gallery_total = $this->model_extension_mpphotogallery_video->getTotalMpGalleries($filter_data);
		$gallery_info = $this->model_extension_mpphotogallery_video->getMpGallery($filter_data);
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

		foreach ($gallery_info as $album) {
			// 07-05-2022: updation task start
			if (!(int)$album['video_width']) {
				$album['video_width'] = (int)$this->config->get('module_mpphotogallery_albumvideo_width');
			}
			if (!(int)$album['video_width']) {
				$album['video_width'] = 213;
			}
			if (!(int)$album['video_height']) {
				$album['video_height'] = (int)$this->config->get('module_mpphotogallery_albumvideo_height');
			}
			if (!(int)$album['video_height']) {
				$album['video_height'] = 310;
			}
			// 07-05-2022: updation task end

			$gallery_videos = $this->model_extension_mpphotogallery_video->getAlbumVideoDescription($album['mpgallery_id']);
			$videos = [];
			foreach ($gallery_videos as $key => $gallery_video) {
				if ($key == 0) {
					if ($gallery_video['video_thumb'] && file_exists(DIR_IMAGE . $gallery_video['video_thumb'])) {
						$thumb = $this->model_tool_image->resize($gallery_video['video_thumb'], $popup_width, $popup_height);
					} else {
						$thumb = $this->model_tool_image->resize('no_image.png', $popup_width, $popup_height);
					}
				} else {
					if ($gallery_video['video_thumb'] && file_exists(DIR_IMAGE . $gallery_video['video_thumb'])) {
						$thumb = $this->model_tool_image->resize($gallery_video['video_thumb'], $album['video_width'], $album['video_height']);
					} else {
						$thumb = $this->model_tool_image->resize('no_image.png', $album['video_width'], $album['video_height']);
					}
				}

				$videos[] = array(
					'name'		=> $gallery_video['name'],
					'link'		=> $gallery_video['link'],
					'thumb'     => $thumb,
					'width'     => $album['video_width'],
					'height'    => $album['video_height'],
				);
			}

			$data['galleries'][] = array(
				'mpgallery_id'   		=> $album['mpgallery_id'],
				'title'        		=> $album['title'],
				'top_description'   => html_entity_decode($album['top_description'], ENT_QUOTES, 'UTF-8'),
				'videos'       		=> $videos,
			);
		}

		$data['mpphotogallery_color'] = $this->config->get('module_mpphotogallery_color');

		$url = '';

		$pagination = new Pagination();
		$pagination->total = $gallery_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link($this->extension_path . 'mpphotogallery/album_video', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($gallery_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($gallery_total - $limit)) ? $gallery_total : ((($page - 1) * $limit) + $limit), $gallery_total, ceil($gallery_total / $limit));

		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view($this->extension_path . 'mpphotogallery/album_video', $data));
	}
}