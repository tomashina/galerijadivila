<?php
class ImageCrop extends Image {
    private $file;
	private $image;
	private $width;
	private $height;
	private $bits;
	private $mime;

	public function __construct($file) {
		if (is_file($file)) {
			$this->file = $file;

			$info = getimagesize($file);

			$this->width  = $info[0];
			$this->height = $info[1];
			$this->bits = isset($info['bits']) ? $info['bits'] : '';
			$this->mime = isset($info['mime']) ? $info['mime'] : '';

			if ($this->mime == 'image/gif') {
				$this->image = imagecreatefromgif($file);
			} elseif ($this->mime == 'image/png') {
				$this->image = imagecreatefrompng($file);
			} elseif ($this->mime == 'image/jpeg') {
				$this->image = imagecreatefromjpeg($file);
			} elseif ($this->mime == 'image/webp') {
				$this->image = imagecreatefromwebp($file);
			}
		} else {
			error_log('Error: Could not load image ' . $file . '!');
		}
	}

	public function resize(int $width = 0, int $height = 0, $default = '') {
		if (!$this->width || !$this->height) {
			return;
		}

		$xpos = 0;
		$ypos = 0;
		$scale = 1;

		$scale_w = $width / $this->width;
		$scale_h = $height / $this->height;

		if ($default == 'w') {
			$scale = $scale_w;
		} elseif ($default == 'h') {
			$scale = $scale_h;
		} else {
			$scale = min($scale_w, $scale_h);
		}

		if ($scale == 1 && $scale_h == $scale_w && ($this->mime != 'image/png' && $this->mime != 'image/webp')) {
			return;
		}

		$new_width = (int)($this->width * $scale);
		$new_height = (int)($this->height * $scale);
		$xpos = (int)(($width - $new_width) / 2);
		$ypos = (int)(($height - $new_height) / 2);

        $image_crop_width = isset($this->info['width']) ? $this->info['width'] : $this->width;
        $image_crop_height = isset($this->info['height']) ? $this->info['height'] : $this->height;

        $new_width = (int)($image_crop_width * $scale);
        $new_height = (int)($image_crop_height * $scale);
        
        $original_aspect = $image_crop_width / $image_crop_height;
        $thumb_aspect = $width / $height;
        
        if ($original_aspect >= $thumb_aspect) {
            $new_height = $height;
            $new_width = $image_crop_width / ($image_crop_height / $height);
        } else {
            $new_width = $width;
            $new_height = $image_crop_height / ($image_crop_width / $width);
        }
            
        $xpos = (int)(0 - (($new_width - $width) / 2));
        $ypos = (int)(0 - (($new_height - $height) / 2));

		$image_old = $this->image;
		$this->image = imagecreatetruecolor($width, $height);

		if ($this->mime == 'image/png') {
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);

			$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);

			imagecolortransparent($this->image, $background);

		} else if ($this->mime == 'image/webp') {
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);

			$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);

			imagecolortransparent($this->image, $background);
		} else {
			$background = imagecolorallocate($this->image, 255, 255, 255);
		}

		imagefilledrectangle($this->image, 0, 0, $width, $height, $background);

		imagecopyresampled($this->image, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->width, $this->height);
		imagedestroy($image_old);

		$this->width = $width;
		$this->height = $height;
	}

	public function save($file, int $quality = 90) {
		$info = pathinfo($file);

		$extension = strtolower($info['extension']);

		if (is_object($this->image) || is_resource($this->image)) {
			if ($extension == 'jpeg' || $extension == 'jpg') {
				imagejpeg($this->image, $file, $quality);
			} elseif ($extension == 'png') {
				imagepng($this->image, $file);
			} elseif ($extension == 'gif') {
				imagegif($this->image, $file);
			} elseif ($extension == 'webp') {
				imagewebp($this->image, $file);
			}

			imagedestroy($this->image);
		}
	}
}