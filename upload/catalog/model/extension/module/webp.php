<?php
/******************************************************
 * @package Webp Image Converter for OC2x, OC3x
 * @version 2.3
 * @author https://aits.xyz
 * @copyright Copyright (C)2020 aits.xyz All rights reserved.
 * @email:info@aits.xyz 
 * $date: 11 JULY 2020
*******************************************************/
class ModelExtensionModuleWebp extends Model {
	
	public function webp_image($filename) {
		
		$module_status = $this->config->get('module_webpimages_status');
		$webp_quality = ($this->config->get('module_webpimages_quality') ? $this->config->get('module_webpimages_quality') : 75);
		$webp_status = $this->getWebp();
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$image_old  = str_replace('%20', ' ', $filename);
		$clean_filename = $filename;
		$clean_filename = str_replace('%20', '_', $filename);
		$clean_filename = strtolower(str_replace(' ', '_', $clean_filename));
		$image_new  = utf8_substr($clean_filename, 0, utf8_strrpos($clean_filename, '.')) . '.webp';
		
		
		if (!isset($webp_status)) {
			return $image_old;
		}
		
		if (!$webp_status) {
			return $image_old;
		}
		
		if(isset($this->request->get['pdf'])) {
			return $image_old;
		}
		
		list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
		if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG))) { 
			return $image_old;
		}
		
		if (!is_file(DIR_IMAGE . $image_old)) {
			return $image_old;
		}
		
		if (is_file(DIR_IMAGE . $image_new) && (filemtime(DIR_IMAGE . $image_old) < filemtime(DIR_IMAGE . $image_new))) {
			return $image_new;
		}
		
		$path = '';
		$directories = explode('/', dirname($image_new));
			
		foreach ($directories as $directory) {
			
			$path = $path . '/' . $directory;
			
			if (!is_dir(DIR_IMAGE . $path)) {
				@mkdir(DIR_IMAGE . $path, 0777);
			}
		}
			
		if ( $extension == 'jpg' || $extension == 'jpeg') {
			$image_default = imagecreatefromjpeg(DIR_IMAGE . $image_old);
		} elseif ($extension == 'png' || $extension == 'png') {
			$image_default = imagecreatefrompng(DIR_IMAGE . $image_old);
			imagepalettetotruecolor($image_default);
			imagealphablending($image_default, true);
			imagesavealpha($image_default, true);
		}
		
		imagewebp($image_default, DIR_IMAGE . $image_new, $webp_quality);
		
		imagedestroy($image_default);
		
		if (filesize(DIR_IMAGE . $image_new) % 2 == 1) {
			file_put_contents(DIR_IMAGE . $image_new, "$image_old = str_replace(' ', '%20', $image_old);", FILE_APPEND);
		}
		
		if (!isset($image_new) || !is_file(DIR_IMAGE . $image_new)){
			$image_new = $image_old;
		}
		$image_new = str_replace(' ', '%20', $image_new);
		return $image_new;
	}

	public function getWebp() {
		
		$module_status = $this->config->get('module_webpimages_status');
		$webp_gd = gd_info();
		$webp_session = (isset($this->session->data['session_webp']) ? $this->session->data['session_webp'] : '0' );
		$webp_cookie = (isset($_COOKIE['webp_support']) ? $_COOKIE['webp_support'] : '0');
		$this->session->data['session_webp'] = '0';
		$webp_status = false;
		$cookie_name = 'webp_support';
		$cookie_time = time() + 86400*365;
		$cookie_path = '/';
		$cookie_domain = '';
		$cookie_type = 'secure';
		$webp_status = false;
		
		if (!$module_status) {
			return false;
		}

		
		if (isset($webp_gd['WebP Support']) && !$webp_gd['WebP Support'] ) {
			return false;
		}
		
		if ($webp_cookie == '1' && $webp_session == '1') {
			$webp_status = true;
			$this->session->data['session_webp'] = '1';
		}
		
		if (isset($this->request->server['HTTP_ACCEPT']) && strpos($this->request->server['HTTP_ACCEPT'], 'webp')) {
			$webp_status = true;
			$this->session->data['session_webp'] = '1';
		}
		
		if ($webp_status) {
			return $webp_status;
		}
		
		$browser = $this->getBrowser();
		
		if (!isset($browser['webp'])) {
			return false;
		} 
		
		switch ($browser['code']) {

			case "MSIE":
				$webp_status = false;
				break;

			case "Safari":
				$webp_status = false;
				break;

			case "Edge":
				$webp_status = false;
				if (!empty($browser['version']) && (int)$browser['version'] >= 18) {
					// $status = true;
				}
				break;

			case "Firefox":
				$webp_status = true;
				break;

			case "Opera":
				$webp_status = true;
				break;

			case "Chrome":
				$webp_status = true;
				if (!empty($browser['platform']) && $browser['platform'] == 'mac') {
					//$webp_status = false;
				}
				break;

			case "Unknown":
				$webp_status = false;
				break;
		}
		
		if ($webp_status) {
			$this->setWebp($cookie_name, "1", $cookie_time, $cookie_path, $cookie_domain, $cookie_type);
			$this->session->data['session_webp'] ='1';
		} else {
			$this->setWebp($cookie_name, "0", $cookie_time, $cookie_path, $cookie_domain, $cookie_type);
			$this->session->data['session_webp'] ='0';
		}
		
		return $webp_status;
	}

	public function getBrowser() { 
		
		if (isset( $_SERVER['HTTP_USER_AGENT'])) {
			$u_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			return false;
		}
		
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
		$ub ='';

		if (preg_match('/linux/i', $u_agent)) {
		  $platform = 'linux';
		}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		  $platform = 'mac';
		}elseif (preg_match('/windows|win32/i', $u_agent)) {
		  $platform = 'windows';
		}

		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
		  $bname = 'Internet Explorer';
		  $ub = "MSIE";
		}elseif(preg_match('/Firefox/i',$u_agent)){
		  $bname = 'Mozilla Firefox';
		  $ub = "Firefox";
		}elseif(preg_match('/OPR/i',$u_agent)){
		  $bname = 'Opera';
		  $ub = "Opera";
		}elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		  $bname = 'Google Chrome';
		  $ub = "Chrome";
		}elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		  $bname = 'Apple Safari';
		  $ub = "Safari";
		}elseif(preg_match('/Netscape/i',$u_agent)){
		  $bname = 'Netscape';
		  $ub = "Netscape";
		}elseif(preg_match('/Edge/i',$u_agent)){
		  $bname = 'Edge';
		  $ub = "Edge";
		}elseif(preg_match('/Trident/i',$u_agent)){
		  $bname = 'Internet Explorer';
		  $ub = "MSIE";
		}

		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
	
		}
		$i = count($matches['browser']);
		if ($i != 1) {
		  if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
			  $version= (isset($matches['version'][0]) ? $matches['version'][0] : '');
		  }else {
			  $version= (isset($matches['version'][1]) ? $matches['version'][1] : '');
		  }
		}else {
		  $version= $matches['version'][0];
		}
		if ($version==null || $version=="") {$version="?";}

		if ($ub=='Chrome' || $ub=='Firefox' || $ub=='Opera' ){
			$webp = '1';
		} else {
			$webp = '0';
		}

		return array(
		  'userAgent' => $u_agent,
		  'code'	  => $ub,
		  'webp'	  => $webp,
		  'name'      => $bname,
		  'version'   => $version,
		  'platform'  => $platform,
		  'pattern'   => $pattern
		);
	  } 
	  
	private function setWebp($cookie_name, $value, $cookie_time, $cookie_path, $cookie_domain, $secure='secure') { 
	  	$cookie_write = $this->config->get('module_webpimages_cookie');
	  	if (isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE'] == 'LiteSpeed') {
	  		//$cookie_write = false;
	  	} 
	  	
	  	if ($cookie_write) {
	  		setcookie($cookie_name, $value, $cookie_time, $cookie_path, $cookie_domain, $secure);
	  	}
	  	return;
	  }
	  
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return DIR_IMAGE . $image_old;
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}  
}
?>