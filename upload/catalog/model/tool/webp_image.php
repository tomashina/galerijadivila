<?php
class ModelToolWebpImage extends Model {
  
	public function convert($filename, $reload = false, $pathMode = false) {
    $basePath = DIR_IMAGE;
    
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
      if (is_file(DIR_SYSTEM . '../' . $filename)) {
        $basePath = DIR_SYSTEM . '../';
      } else {
        return $filename;
      }
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    if ($extension == 'webp') {
      return $filename;
    }
    
    $extension = 'webp';
    $webpQuality = $this->config->get('webp_image_quality') ? $this->config->get('webp_image_quality') : 90;
		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '.' . $extension;

		if ($reload || !is_file(DIR_IMAGE . $image_new) || (filemtime($basePath . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize($basePath . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return $basePath . $image_old;
			}
			
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

				$image = new Image($basePath . $image_old);
				//$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new, $webpQuality);
		}
		
		$image_new = str_replace(' ', '%20', $image_new);
		    
		if ($pathMode) {
      return $image_new;
		} else if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
  
  public function convertInHtml($html) {
    preg_match_all('~<img.+src=(?:(?:\"|\')(.+?)(?:\"|\')(?:.+?)|(.+?))>~i', $html, $images);
    
    foreach ($images[0] as $k => $image) {
      $convert = true;
      
      $imgSrc = $origImgSrc =$images[1][$k];
      
      $imgType = pathinfo($imgSrc, PATHINFO_EXTENSION);
      
      if (!in_array(strtolower($imgType), array('png', 'jpg', 'jpeg', 'gif'))) {
        continue;
      }
      
      $imgUrl = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', HTTP_SERVER . 'image/');
      $imgNewSrc = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', $imgSrc);

      if (strpos($imgNewSrc, $imgUrl) !== false) {
        $imgSrc = str_replace($imgUrl, '', $imgNewSrc);
      /*
      if (strpos($imgSrc, HTTP_SERVER . 'image/') !== false) {
        $imgSrc = str_replace(HTTP_SERVER . 'image/', '', $imgSrc);
      } else if (strpos($imgSrc, HTTPS_SERVER . 'image/') !== false) {
        $imgSrc = str_replace(HTTPS_SERVER . 'image/', '', $imgSrc);
      */
      } else if (substr($imgSrc, 0, 8) == 'catalog/') {
        
      } else if (substr($imgSrc, 0, 6) == 'image/') {
        $imgSrc = substr($imgSrc, 6);
      } else if (substr($imgSrc, 0, 7) == '/image/') {
        $imgSrc = substr($imgSrc, 7);
      } else {
        $convert = false;
      }
      
      if ($convert) {
        $imgSrcConverted = $this->convert($imgSrc);
        if ($imgSrcConverted && $imgSrcConverted != $imgSrc) {
          $imageConverted = str_replace($origImgSrc, $imgSrcConverted, $image);
          
          $html = str_replace($image, $imageConverted, $html);
        }
      }
    }
    
    preg_match_all('~url\((?:"|\')?(.+?)(?:"|\')?\)~', $html, $images);
    
    foreach ($images[0] as $k => $image) {
      $convert = true;
      
      $imgSrc = $origImgSrc =$images[1][$k];
      
      $imgType = pathinfo($imgSrc, PATHINFO_EXTENSION);
      
      if (!in_array(strtolower($imgType), array('png', 'jpg', 'jpeg', 'gif'))) {
        continue;
      }
      
      $imgUrl = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', HTTP_SERVER . 'image/');
      $imgNewSrc = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', $imgSrc);

      if (strpos($imgNewSrc, $imgUrl) !== false) {
        $imgSrc = str_replace($imgUrl, '', $imgNewSrc);
      /*
      if (strpos($imgSrc, HTTP_SERVER . 'image/') !== false) {
        $imgSrc = str_replace(HTTP_SERVER . 'image/', '', $imgSrc);
      } else if (strpos($imgSrc, HTTPS_SERVER . 'image/') !== false) {
        $imgSrc = str_replace(HTTPS_SERVER . 'image/', '', $imgSrc);
      */
      } else if (substr($imgSrc, 0, 8) == 'catalog/') {
        
      } else if (substr($imgSrc, 0, 6) == 'image/') {
        $imgSrc = substr($imgSrc, 6);
      } else if (substr($imgSrc, 0, 7) == '/image/') {
        $imgSrc = substr($imgSrc, 7);
      } else {
        $convert = false;
      }
      
      if ($convert) {
        $imgSrcConverted = $this->convert($imgSrc);
        
        if ($imgSrcConverted && $imgSrcConverted != $imgSrc) {
          $imageConverted = str_replace($origImgSrc, $imgSrcConverted, $image);
          
          $html = str_replace($image, $imageConverted, $html);
        }
      }
    }
    
    return $html;
  }
  
  public function convertInHtmlDom($html) {
    $dom = new DOMDocument();
    //@$dom->loadHTML($html);
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); //load in utf-8
    $images = $dom->getElementsByTagName('img');
    
    $changed = false;
    
    foreach ($images as $image) {
      $convert = true;
      
      $imgSrc = $image->getAttribute('src');
      
      $imgType = pathinfo($imgSrc, PATHINFO_EXTENSION);
      
      if (!in_array(strtolower($imgType), array('png', 'jpg', 'jpeg', 'gif'))) {
        continue;
      }
      
      $imgUrl = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', HTTP_SERVER . 'image/');
      $imgNewSrc = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', $imgSrc);

      if (strpos($imgNewSrc, $imgUrl) !== false) {
        $imgSrc = str_replace($imgUrl, '', $imgNewSrc);
      /*
      if (strpos($imgSrc, HTTP_SERVER . 'image/') !== false) {
        $imgSrc = str_replace(HTTP_SERVER . 'image/', '', $imgSrc);
      } else if (strpos($imgSrc, HTTPS_SERVER . 'image/') !== false) {
        $imgSrc = str_replace(HTTPS_SERVER . 'image/', '', $imgSrc);
      */
      } else if (substr($imgSrc, 0, 6) == 'image/') {
        $imgSrc = substr($imgSrc, 6);
      } else if (substr($imgSrc, 0, 7) == '/image/') {
        $imgSrc = substr($imgSrc, 7);
      } else if (substr($imgSrc, 0, 8) == 'catalog/') {
      } else {
        $convert = false;
      }
      
      if ($convert) {
        $image->setAttribute('src', $this->convert($imgSrc)); 
      }
      
      $changed = true;
    }
    
    if (!$changed) {
      return $html;
    }
    
    return $dom->saveHTML();
    //return utf8_decode($dom->saveHTML($dom->documentElement)); // save in utf-8, to use if load method is not working
  }
  
  function isAnimatedGif($filename) {
    if(!($fh = @fopen($filename, 'rb')))
      return false;
    
    $count = 0;
    //an animated gif contains multiple "frames", with each frame having a
    //header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    $chunk = false;
    while(!feof($fh) && $count < 2) {
      //add the last 20 characters from the previous string, to make sure the searched pattern is not split.
      $chunk = ($chunk ? substr($chunk, -20) : "") . fread($fh, 1024 * 100); //read 100kb at a time
      $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
    }

    fclose($fh);
    return $count > 1;
  }

  public function clearCache($images) {
    foreach ((array) $images as $image) {
      if (isset($image['image'])) {
        $image = $image['image'];
      }
      
      foreach (glob(DIR_IMAGE.'cache/'.dirname($image).'/'.basename($image, '.'.pathinfo($image, PATHINFO_EXTENSION)).'*.webp') as $file) {
        unlink($file);
      }
    }
  }
  
  public function getPageList() {
    $list = array();
    
    // products
    $items = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE status = 1")->rows;
    
    foreach ($items as $item) {
      $list[] = HTTP_CATALOG.'index.php?route=product/product&product_id='.$item['product_id'];
    }
    
    // brands
    $items = $this->db->query("SELECT manufacturer_id FROM `" . DB_PREFIX . "manufacturer`")->rows;
    
    $list[] = HTTP_CATALOG.'index.php?route=product/manufacturer';
    
    foreach ($items as $item) {
      $list[] = HTTP_CATALOG.'index.php?route=product/manufacturer/info&manufacturer_id='.$item['manufacturer_id'];
    }
    
    // categories
    $items = $this->db->query("SELECT category_id, parent_id FROM `" . DB_PREFIX . "category` WHERE status = 1")->rows;
    
    foreach ($items as $item) {
      $list[] = HTTP_CATALOG.'index.php?route=product/category&path='.$item['category_id'];
    }
    
		return $list;
	}
}
