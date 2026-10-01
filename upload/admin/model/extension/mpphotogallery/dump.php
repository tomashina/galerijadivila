<?php
class ModelExtensionMpphotogalleryDump extends Model {

	// TRUNCATE `oc_mpgallery`;
	// TRUNCATE `oc_mpgallery_description`;
	// TRUNCATE `oc_mpgallery_photo`;
	// TRUNCATE `oc_mpgallery_photo_description`;
	// TRUNCATE `oc_mpgallery_product`;
	// TRUNCATE `oc_mpgallery_video`;
	// TRUNCATE `oc_mpgallery_video_description`;


	private $tables = [
		'mpgallery',
		'mpgallery_description',
		'mpgallery_photo',
		'mpgallery_photo_description',
		'mpgallery_video',
		'mpgallery_video_description',
		'mpgallery_product',
	];

	private $dumpable = [
		'mpgallery',
		'mpgallery_description',
		'mpgallery_photo',
		'mpgallery_photo_description',
		'mpgallery_video',
		'mpgallery_video_description',
		'mpgallery_product',
	];

	private $dumplinker = [
		'mpgallery' => ['mpgallery_description', 'mpgallery_photo', 'mpgallery_photo_description', 'mpgallery_video', 'mpgallery_video_description', 'mpgallery_product']
	];

	public function getTables() {
		return $this->tables;
	}

	public function getDumpables() {
		return $this->dumpable;
	}

	public function getLinkers() {
		return $this->dumplinker;
	}

	public function detectIfNoData() {
		$nodata = [];
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `". DB_PREFIX ."mpgallery`");
		if ($query->row['total'] == 0) {
			$nodata[] = 'mpgallery';
		}
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `". DB_PREFIX ."mpgallery_description`");
		if ($query->row['total'] == 0) {
			$nodata[] = 'mpgallery';
		}

		return array_unique($nodata);
	}

	public function getDumps() {

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages([
			'sort' => 'sort_order',
			'order' => 'ASC'
		]);

		$mpgallery = [
		"INSERT INTO `". DB_PREFIX ."mpgallery` (`mpgallery_id`, `image`, `status`, `sort_order`, `width`, `height`, `viewed`, `video_width`, `video_height`, `date_added`, `date_modified`)
		VALUES
		('6', 'catalog/mpphotogallery/album-1.jpg', '1', '2', '400', '250', '1172', '', '', '2017-11-16 00:00:00', '0000-00-00 00:00:00'),
		('7', 'catalog/mpphotogallery/album-2.jpg', '1', '1', '400', '250', '699', '100', '100', '2017-11-16 00:00:00', '2022-05-24 16:30:23'),
		('8', 'catalog/mpphotogallery/album-3.jpg', '1', '2', '400', '250', '681', '', '', '2017-11-16 00:00:00', '0000-00-00 00:00:00'),
		('9', 'catalog/mpphotogallery/album-4.jpg', '1', '3', '400', '250', '587', '0', '0', '2017-11-16 00:00:00', '2022-05-24 16:31:40'),
		('10', 'catalog/mpphotogallery/albim-5.jpg', '1', '4', '400', '250', '418', '0', '0', '2017-11-16 00:00:00', '0000-00-00 00:00:00'),
		('11', 'catalog/mpphotogallery/cartoon.jpg', '1', '6', '400', '250', '251', '246', '138', '2019-07-24 00:00:00', '0000-00-00 00:00:00');"

		];

		foreach ($languages as $language) {
			$mpgallery[] = "INSERT INTO `". DB_PREFIX ."mpgallery_description` (`mpgallery_id`, `language_id`, `title`, `description`, `top_description`, `bottom_description`, `meta_title`, `meta_description`, `meta_keyword`)
			 VALUES
			('6', '" . $language['language_id'] . "', 'Nature', '&lt;p&gt;Lorem ipsum dolor sit amet, consectetur \r\nadipiscing elit. Cras eu pulvinar mi. In hac habitasse platea dictumst. \r\nDonec cursus risus eu pellentesque finibus. Duis rutrum quam vel dolor \r\nvenenatis, in vulputate nisi pretium. Fusce tempor tellus sed elit \r\nfermentum, non euismod ante mollis. Proin cursus nisi non augue gravida \r\nfermentum. Donec venenatis odio orci, eget porta ipsum tristique sit \r\namet. Aliquam vitae mattis ex, in dapibus dolor.&lt;/p&gt;', '&lt;p&gt;Lorem ipsum dolor sit amet, consectetur \r\nadipiscing elit. Cras eu pulvinar mi. In hac habitasse platea dictumst. \r\nDonec cursus risus eu pellentesque finibus. Duis rutrum quam vel dolor \r\nvenenatis, in vulputate nisi pretium. Fusce tempor tellus sed elit \r\nfermentum, non euismod ante mollis. Proin cursus nisi non augue gravida \r\nfermentum. Donec venenatis odio orci, eget porta ipsum tristique sit \r\namet. Aliquam vitae mattis ex, in dapibus dolor.&lt;/p&gt;', '&lt;br&gt;', 'Nature', 'Nature', 'Nature'),
			 ('7', '" . $language['language_id'] . "', 'Animals', '', '', '', 'Animals', '', ''),
			 ('8', '" . $language['language_id'] . "', 'Flowers', '', '', '', 'Flowers', '', ''),
			 ('9', '" . $language['language_id'] . "', 'Cars', '', '', '', 'Cars', '', ''),
			 ('10', '" . $language['language_id'] . "', 'Fruits', '', '', '', 'Fruits', '', ''),
			 ('11', '" . $language['language_id'] . "', 'Videos', '', '&lt;p&gt;Videos&lt;br&gt;&lt;/p&gt;', '', 'Videos', '', '');";
		}

		$mpgallery[] = "INSERT INTO `". DB_PREFIX ."mpgallery_photo` (`mpgallery_photo_id`, `mpgallery_id`, `photo`, `link`, `sort_order`, `status`, `highlight`)
			VALUES
			('3', '6', 'catalog/mpphotogallery/wall_15.jpg', '', '6', '0', '0'),
			('4', '6', 'catalog/mpphotogallery/wall_1.jpg', '', '1', '0', '0'),
			('5', '7', 'catalog/mpphotogallery/animals/animal-1.jpg', 'https://en.wikipedia.org/wiki/Animal', '1', '0', '0'),
			('6', '7', 'catalog/mpphotogallery/animals/animal-10.jpg', '', '2', '0', '0'),
			('7', '8', 'catalog/mpphotogallery/flowers/flower-10.jpg', '', '0', '0', '0'),
			('8', '8', 'catalog/mpphotogallery/flowers/flower-2.jpg', '', '1', '0', '0'),
			('9', '9', 'catalog/mpphotogallery/cars/car-10.jpg', '', '1', '0', '0'),
			('10', '9', 'catalog/mpphotogallery/cars/car-2.jpg', '', '2', '0', '0'),
			('11', '10', 'catalog/mpphotogallery/fruits/fruit-1.jpg', '', '0', '0', '0'),
			('12', '10', 'catalog/mpphotogallery/fruits/fruit-10.jpg', '', '0', '0', '0'),
			('13', '6', 'catalog/mpphotogallery/wall_6.jpg', '', '2', '0', '1'),
			('14', '6', 'catalog/mpphotogallery/wall_12.jpg', '', '3', '0', '0'),
			('15', '6', 'catalog/mpphotogallery/wall_13.jpg', '', '4', '0', '0'),
			('16', '6', 'catalog/mpphotogallery/wall_14.jpg', '', '5', '0', '0'),
			('17', '6', 'catalog/mpphotogallery/blue-egg.jpg', '', '0', '0', '0'),
			('18', '6', 'catalog/mpphotogallery/wall_16.jpg', '', '7', '0', '0'),
			('19', '6', 'catalog/mpphotogallery/wall_17.jpg', '', '8', '0', '0'),
			('20', '6', 'catalog/mpphotogallery/wall_18.jpg', '', '9', '0', '0'),
			('21', '6', 'catalog/mpphotogallery/wall_19.jpg', '', '10', '0', '0'),
			('22', '6', 'catalog/mpphotogallery/wall_2.jpg', '', '11', '0', '0'),
			('23', '6', 'catalog/mpphotogallery/wall_3.jpg', '', '12', '0', '0'),
			('24', '6', 'catalog/mpphotogallery/wall_4.jpg', '', '13', '0', '0'),
			('25', '6', 'catalog/mpphotogallery/wall_5.jpg', '', '14', '0', '0'),
			('26', '7', 'catalog/mpphotogallery/animals/animal-3.jpg', '', '3', '0', '1'),
			('27', '7', 'catalog/mpphotogallery/animals/animal-4.jpg', '', '4', '0', '0'),
			('28', '7', 'catalog/mpphotogallery/animals/animal-5.jpg', '', '5', '0', '0'),
			('29', '7', 'catalog/mpphotogallery/animals/animal-7.jpg', '', '6', '0', '0'),
			('30', '7', 'catalog/mpphotogallery/animals/animal-6.jpg', '', '7', '0', '0'),
			('31', '7', 'catalog/mpphotogallery/animals/animal-9.jpg', '', '8', '0', '0'),
			('32', '7', 'catalog/mpphotogallery/animals/animal-8.jpg', '', '9', '0', '0'),
			('33', '7', 'catalog/mpphotogallery/animals/animal-2.jpg', '', '10', '0', '0'),
			('34', '8', 'catalog/mpphotogallery/flowers/flower-3.jpg', '', '2', '0', '0'),
			('35', '8', 'catalog/mpphotogallery/flowers/flower-1.jpg', '', '3', '0', '0'),
			('36', '8', 'catalog/mpphotogallery/flowers/flower-4.jpg', '', '4', '0', '0'),
			('37', '8', 'catalog/mpphotogallery/flowers/flower-5.jpg', '', '5', '0', '0'),
			('38', '8', 'catalog/mpphotogallery/flowers/flower-6.jpg', '', '6', '0', '0'),
			('39', '8', 'catalog/mpphotogallery/flowers/flower-7.jpg', '', '7', '0', '0'),
			('40', '8', 'catalog/mpphotogallery/flowers/flower-8.jpg', '', '8', '0', '0'),
			('41', '8', 'catalog/mpphotogallery/flowers/flower-9.jpg', '', '9', '0', '0'),
			('42', '9', 'catalog/mpphotogallery/cars/car-3.jpg', '', '3', '0', '0'),
			('43', '9', 'catalog/mpphotogallery/cars/car-4.jpg', '', '4', '0', '0'),
			('44', '9', 'catalog/mpphotogallery/cars/car-5.jpg', '', '5', '0', '0'),
			('45', '9', 'catalog/mpphotogallery/cars/car-6.jpg', '', '6', '0', '0'),
			('46', '9', 'catalog/mpphotogallery/cars/car-7.jpg', '', '7', '0', '0'),
			('47', '9', 'catalog/mpphotogallery/cars/car-8.jpg', '', '8', '0', '0'),
			('48', '9', 'catalog/mpphotogallery/cars/car-9.jpg', '', '9', '0', '0'),
			('49', '9', 'catalog/mpphotogallery/cars/car-10.jpg', '', '10', '0', '0'),
			('50', '10', 'catalog/mpphotogallery/fruits/fruit-2.jpg', '', '0', '0', '0'),
			('51', '10', 'catalog/mpphotogallery/fruits/fruit-3.jpg', '', '0', '0', '0'),
			('52', '10', 'catalog/mpphotogallery/fruits/fruit-4.jpg', '', '0', '0', '0'),
			('53', '10', 'catalog/mpphotogallery/fruits/fruit-5.jpg', '', '0', '0', '0'),
			('54', '10', 'catalog/mpphotogallery/fruits/fruit-6.jpg', '', '0', '0', '0'),
			('55', '10', 'catalog/mpphotogallery/fruits/fruit-7.jpg', '', '0', '0', '0'),
			('56', '10', 'catalog/mpphotogallery/fruits/fruit-8.jpg', '', '0', '0', '0'),
			('57', '10', 'catalog/mpphotogallery/fruits/fruit-9.jpg', '', '0', '0', '0');";


		foreach ($languages as $language) {

			$mpgallery[] = "INSERT INTO `". DB_PREFIX ."mpgallery_photo_description` (`mpgallery_photo_id`, `mpgallery_id`, `language_id`, `name`, `description`)
			VALUES
			('7', '8', '1', 'Flower 1', '0'),
			('8', '8', '1', 'Flower 2', '0'),
			('34', '8', '1', 'Flower 3', '0'),
			('35', '8', '1', 'Flower 4', '0'),
			('36', '8', '1', 'Flower 5', '0'),
			('37', '8', '1', 'Flower 6', '0'),
			('38', '8', '1', 'Flower 7', '0'),
			('39', '8', '1', 'Flower 8', '0'),
			('40', '8', '1', 'Flower 9', '0'),
			('41', '8', '1', 'Flower 10', '0'),
			('17', '6', '1', 'Nature', '0'),
			('4', '6', '1', 'Nature', '0'),
			('13', '6', '1', 'Nature', '0'),
			('14', '6', '1', 'Nature', '0'),
			('15', '6', '1', 'Nature', '0'),
			('16', '6', '1', 'Nature', '0'),
			('3', '6', '1', 'Nature', '0'),
			('18', '6', '1', 'Nature', '0'),
			('19', '6', '1', 'Nature', '0'),
			('20', '6', '1', 'Nature', '0'),
			('21', '6', '1', 'Nature', '0'),
			('22', '6', '1', 'Nature', '0'),
			('23', '6', '1', 'Nature', '0'),
			('24', '6', '1', 'Nature', '0'),
			('25', '6', '1', 'Nature', '0'),
			('11', '10', '1', 'Fruit 1', '0'),
			('12', '10', '1', 'Fruit 2', '0'),
			('50', '10', '1', 'Fruit 3', '0'),
			('51', '10', '1', 'Fruit 4', '0'),
			('52', '10', '1', 'Fruit 5', '0'),
			('53', '10', '1', 'Fruit 6', '0'),
			('54', '10', '1', 'Fruit 7', '0'),
			('55', '10', '1', 'Fruit 8', '0'),
			('56', '10', '1', 'Fruit 9', '0'),
			('57', '10', '1', 'Fruit 10', '0'),
			('5', '7', '1', 'Animal 1', ''),
			('6', '7', '1', 'Animal 2', ''),
			('26', '7', '1', 'Animal 3', ''),
			('27', '7', '1', 'Animal 4', ''),
			('28', '7', '1', 'Animal 5', ''),
			('29', '7', '1', 'Animal 6', ''),
			('30', '7', '1', 'Animal 7', ''),
			('31', '7', '1', 'Animal 8', ''),
			('32', '7', '1', 'Animal 9', ''),
			('33', '7', '1', 'Animal 10', ''),
			('9', '9', '1', 'Car 1', ''),
			('10', '9', '1', 'Car 2', ''),
			('42', '9', '1', 'Car 3', ''),
			('43', '9', '1', 'Car 4', ''),
			('44', '9', '1', 'Car 5', ''),
			('45', '9', '1', 'Car 6', ''),
			('46', '9', '1', 'Car 7', ''),
			('47', '9', '1', 'Car 8', ''),
			('48', '9', '1', 'Car 9', ''),
			('49', '9', '1', 'Car 10', '');";
		}


		$mpgallery[] = "INSERT INTO `". DB_PREFIX ."mpgallery_product` (`mpgallery_id`, `product_id`, `video`, `image`)
			 VALUES
			 ('7', '42', '0', '1'),
			 ('7', '30', '0', '1'),
			 ('9', '42', '0', '1'),
			 ('9', '30', '0', '1'),
			 ('9', '47', '0', '1'),
			 ('9', '41', '0', '1');";

		$mpgallery[] = "INSERT INTO `". DB_PREFIX ."mpgallery_video` (`mpgallery_video_id`, `mpgallery_id`, `video_thumb`, `link`, `highlight`, `sort_order`, `status`)
			VALUES
			('35', '11', 'catalog/mpphotogallery/videos/video1.jpg', 'https://www.youtube.com/watch?v=wZq2tyLNPRU', '0', '0', '0'),
			('36', '11', 'catalog/mpphotogallery/videos/video2.jpg', 'https://www.youtube.com/watch?v=9GjrpJoNsVA', '0', '1', '0'),
			('37', '11', 'catalog/mpphotogallery/videos/video3.jpg', 'https://www.youtube.com/watch?v=BwqSraJpqfs', '0', '2', '0'),
			('38', '11', 'catalog/mpphotogallery/videos/video4.jpg', 'https://www.youtube.com/watch?v=Cy_f7dJ_bfU', '0', '3', '0'),
			('39', '11', 'catalog/mpphotogallery/videos/video5.jpg', 'https://www.youtube.com/watch?v=iVAgTiBrrDA', '0', '4', '0'),
			('40', '11', 'catalog/mpphotogallery/videos/video6.jpg', 'https://www.youtube.com/watch?v=S4ERUpmf3MI', '0', '5', '0'),
			('41', '11', 'catalog/mpphotogallery/videos/video7.jpg', 'https://www.youtube.com/watch?v=sY2bdbsy3rg', '0', '6', '0'),
			('42', '11', 'catalog/mpphotogallery/videos/video8.jpg', 'https://www.youtube.com/watch?v=mRx8qG19V6I', '0', '7', '0'),
			('43', '11', 'catalog/mpphotogallery/videos/video9.jpg', 'https://www.youtube.com/watch?v=e-r406NoJo4', '0', '8', '0'),
			('44', '11', 'catalog/mpphotogallery/videos/video10.jpg', 'https://www.youtube.com/watch?v=HNJNqyM2_eY', '0', '9', '0');";

		foreach ($languages as $language) {

			$mpgallery[] = "INSERT INTO `". DB_PREFIX ."mpgallery_video_description` (`mpgallery_video_id`, `mpgallery_id`, `language_id`, `name`, `description`) VALUES ('35', '11', '1', 'Video 1', '0'),
			 ('36', '11', '" . $language['language_id'] . "', 'Video 2', '0'),
			 ('37', '11', '" . $language['language_id'] . "', 'Video 3', '0'),
			 ('38', '11', '" . $language['language_id'] . "', 'Video 4', '0'),
			 ('39', '11', '" . $language['language_id'] . "', 'Video 5', '0'),
			 ('40', '11', '" . $language['language_id'] . "', 'Video 6', '0'),
			 ('41', '11', '" . $language['language_id'] . "', 'Video 7', '0'),
			 ('42', '11', '" . $language['language_id'] . "', 'Video 8', '0'),
			 ('43', '11', '" . $language['language_id'] . "', 'Video 9', '0'),
			 ('44', '11', '" . $language['language_id'] . "', 'Video 10', '0');";
		}

		$dump = [];
		$dump['mpgallery'] = $mpgallery;
		return $dump;

	}

	public function dump($dump) {

		foreach ($this->dumpable as $table) {
			$match = "INSERT INTO `". DB_PREFIX ."{$table}`";

			if (utf8_substr($dump, 0, utf8_strlen($match)) == $match) {
				$this->db->query($dump);
				break;
			}
		}

	}

	public function dropAll() {
		foreach ($this->tables as $table) {
			$this->drop($table);
		}
	}

	public function drop($table) {
		if (in_array($table, $this->tables)) {
			$this->db->query("DROP TABLE `". DB_PREFIX ."{$table}`");
		}
	}

	public function truncateAll() {
		foreach ($this->tables as $table) {
			$this->truncate($table);
		}
	}

	public function truncate($table) {
		if (in_array($table, $this->tables)) {
			$this->db->query("TRUNCATE TABLE `". DB_PREFIX ."{$table}`");
		}
	}

	public function install() {
		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery' ");

		if(!$query->num_rows) {

			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery` (`mpgallery_id` int(11) NOT NULL AUTO_INCREMENT,`image` varchar(255) NOT NULL,`status` tinyint(4) NOT NULL,`sort_order` int(11) NOT NULL,`width` varchar(10) NOT NULL,`height` varchar(10) NOT NULL,`viewed` int(11) NOT NULL,`video_height` VARCHAR(10) NOT NULL,`video_width` VARCHAR(10) NOT NULL,`date_added` datetime NOT NULL,`date_modified` datetime NOT NULL,PRIMARY KEY (`mpgallery_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0");
		}

		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery_description' ");

		if(!$query->num_rows) {

			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery_description` (`mpgallery_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` varchar(255) NOT NULL,`description` text NOT NULL,`top_description` text NOT NULL,`bottom_description` text NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` text NOT NULL,`meta_keyword` text NOT NULL, PRIMARY KEY (`mpgallery_id`,`language_id`), KEY `title` (`title`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		}


		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery_photo' ");

		if(!$query->num_rows) {

			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery_photo` (`mpgallery_photo_id` int(11) NOT NULL AUTO_INCREMENT,`mpgallery_id` int(11) NOT NULL,`photo` varchar(255) NOT NULL,`link` varchar(255) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(4) NOT NULL,`highlight` tinyint(4) NOT NULL,PRIMARY KEY (`mpgallery_photo_id`), KEY `mpgallery_id` (`mpgallery_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0");
		}

		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery_photo_description' ");

		if(!$query->num_rows) {

			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery_photo_description` (`mpgallery_photo_id` int(11) NOT NULL,`mpgallery_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`name` varchar(255) NOT NULL,`description` text NOT NULL, PRIMARY KEY (`mpgallery_photo_id`,`mpgallery_id`,`language_id`), KEY `name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		}

		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery_video' ");

		if(!$query->num_rows) {
			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery_video` (`mpgallery_video_id` int(11) NOT NULL AUTO_INCREMENT,`mpgallery_id` int(11) NOT NULL,`video_thumb` varchar(255) NOT NULL,`link` varchar(255) NOT NULL,`highlight` tinyint(4) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(4) NOT NULL,PRIMARY KEY (`mpgallery_video_id`), KEY `mpgallery_id` (`mpgallery_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0");

		}

		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery_video_description' ");

		if(!$query->num_rows) {

			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery_video_description` (`mpgallery_video_id` int(11) NOT NULL,`mpgallery_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`name` varchar(255) NOT NULL,`description` text NOT NULL, PRIMARY KEY (`mpgallery_video_id`,`mpgallery_id`,`language_id`), KEY `name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		}


		// gallery for product task starts
		// 07-05-2022: updation task start
		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."mpgallery_product' ");

		if(!$query->num_rows) {

			$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpgallery_product` (`mpgallery_id` int(11) NOT NULL, `product_id` int(11) NOT NULL, `video` int(1) NOT NULL, `image` int(1) NOT NULL, PRIMARY KEY (`mpgallery_id`,`product_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		}
		// 07-05-2022: updation task end
		// gallery for product task ends

	}
	// 17-march-2023: improvements start
	public function alter() {

		// gallery for product task starts
		// 07-05-2022: updation task start
		// $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mpgallery_product` WHERE `Field` = 'video'");
		// if (!$query->num_rows) {
		// 	$this->db->query("ALTER TABLE `" . DB_PREFIX . "mpgallery_product` ADD `video` int(1) NOT NULL AFTER `product_id`");
		// }
		// $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mpgallery_product` WHERE `Field` = 'image'");
		// if (!$query->num_rows) {
		// 	$this->db->query("ALTER TABLE `" . DB_PREFIX . "mpgallery_product` ADD `image` int(1) NOT NULL AFTER `product_id`");
		// 	$this->db->query("UPDATE `" . DB_PREFIX . "mpgallery_product` SET `image`='1'");
		// }
		// 07-05-2022: updation task end
		// gallery for product task ends


		// $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "gallery` WHERE `Field` = 'video_height'");
		// if (!$query->num_rows) {
		// 	$this->db->query("ALTER TABLE `" . DB_PREFIX . "gallery` ADD `video_height` VARCHAR(10) NOT NULL AFTER `viewed`");
		// }
		// $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "gallery` WHERE `Field` = 'video_width'");
		// if (!$query->num_rows) {
		// 	$this->db->query("ALTER TABLE `" . DB_PREFIX . "gallery` ADD `video_width` VARCHAR(10) NOT NULL AFTER `viewed`");
		// }
	}
	// 17-march-2023: improvements end
}

