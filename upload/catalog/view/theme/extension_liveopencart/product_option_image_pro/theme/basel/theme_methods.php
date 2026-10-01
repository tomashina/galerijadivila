<?php

class theme_methods extends model {
  
  public function getSettings() {
    return array(
      'add_option_images_to_additional_on_server_side' => true,
      'use_only_option_value_names_as_titles_for_category_images' => true,
      
      //'do_not_add_main_image_to_additional_images' => true, // when added by the theme itseft
    );
  }
  
  //public function resize($image, $width, $height) {
  //}
  
  //public function getProductListImageExtras($image, $pov_id) {
  //}
  
}

