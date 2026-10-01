<?php
class ControllerExtensionMpPhotoGalleryMtabs extends Controller {
	use mpphotogallery\trait_mpphotogallery;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpPhotoGallery($registry);
	}

	public function index() {

		$this->document->addStyle('view/stylesheet/mpphotogallery/stylesheet.css');

		$data['menus'] = $this->load->controller($this->extension_path . 'module/mpphotogallery_setting/getMpPhotoGalleryMenu');

		function clear(&$m) {
			$s = [
				'<i class="fa fa-circle text-danger"></i>',
				'<i class="fa fa-circle text-success"></i>'
			];
			$r = [
				'',
				''
			];

			foreach ($m['children'] as &$v) {
				$v['name'] = trim(str_replace($s, $r, $v['name']));
				if ($v['children']) {
					clear($v);
				}
			}
			return $m;
		}

		clear($data['menus']);

		return $this->viewLoad($this->extension_path . 'mpphotogallery/mtabs', $data);

	}
}
