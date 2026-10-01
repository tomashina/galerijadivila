<?php
require_once(DIR_SYSTEM . 'library/equotix/image_crop/controller/equotix.php');
require_once(DIR_SYSTEM . 'library/image_crop/image_crop.php');

class ControllerExtensionModuleImageCrop extends ControllerEquotix {
    public $version = '2.1.0';
    public $code = 'image_crop';
    public $extension = 'Hover Image';
    public $extension_id = '29';

    public function eventModelToolImageBefore(&$route, &$args) {
        if ($this->config->get('module_image_crop_status') && $this->validated($this->extension_id)) {
            $filename = $args[0];
            $width = $args[1];
            $height = $args[2];

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
                    $image = new ImageCrop(DIR_IMAGE . $image_old);
                    $image->resize($width, $height);
                    $image->save(DIR_IMAGE . $image_new);                    
                } else {
                    copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
                }
            }
        }        
    }
}