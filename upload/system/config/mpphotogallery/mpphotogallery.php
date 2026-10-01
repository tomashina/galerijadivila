<?php

$settings = <<<settings
{"module_mpphotogallery_status":"0","module_mpphotogallery_social_status":"1","module_mpphotogallery_menu_at":["header","footer","header_menu"],"module_mpphotogallery_menu_at_header":"album_photo","module_mpphotogallery_menuheader_album":"","module_mpphotogallery_menu_at_footer":"album","module_mpphotogallery_menufooter_album":"","module_mpphotogallery_popup_width":"500","module_mpphotogallery_popup_height":"729","module_mpphotogallery_albumphoto_width":"213","module_mpphotogallery_albumphoto_height":"310","module_mpphotogallery_album_description":"1","module_mpphotogallery_album_limit":"20","module_mpphotogallery_album_width":"213","module_mpphotogallery_album_height":"310","module_mpphotogallery_photo_cursive_font":"0","module_mpphotogallery_show_videos":"1","module_mpphotogallery_albumphoto_cursive_font":"0","module_mpphotogallery_albumphoto_description":"1","module_mpphotogallery_albumphoto_limit":"20","module_mpphotogallery_albumvideo_cursive_font":"0","module_mpphotogallery_albumvideo_description":"1","module_mpphotogallery_albumvideo_limit":"20","module_mpphotogallery_albumvideo_width":"213","module_mpphotogallery_albumvideo_height":"310","module_mpphotogallery_galleryproduct_status":"1","module_mpphotogallery_galleryproduct_description":{"[[LANGUAGE_ID]]":{"title":"Photo Galleries"}},"module_mpphotogallery_galleryproduct_viewas":"GAP","module_mpphotogallery_galleryproduct_override_video":"0","module_mpphotogallery_galleryproduct_override_image":"0","module_mpphotogallery_galleryproduct_width":"213","module_mpphotogallery_galleryproduct_height":"310","module_mpphotogallery_galleryproduct_photo_width":"200","module_mpphotogallery_galleryproduct_photo_height":"200","module_mpphotogallery_galleryproduct_carousel":"0","module_mpphotogallery_galleryproduct_extitle":"0","module_mpphotogallery_color":{"title_text":"","album_title_bg":"","albumtitle_text":"","photo_tilte_color":"","photo_zoomicon_color":"","photo_hoverbg_color":"","albumsapge_title_text":"","albums_detail_text":"","albums_wrapbg":"","sharethis_bg":"","sharethis_color":"","extitle_bgcolor":"","extitle_textcolor":"","extitle_bordercolor":"","exview_all_color":"","carousel_arrow_bgcolor":"","carousel_arrow_color":""}}
settings;

// Thanks to stack overflow
// https://stackoverflow.com/questions/34486346/new-lines-and-tabs-in-json-decode-php-7
// https://wiki.php.net/rfc/jsond
// http://www.ecma-international.org/publications/files/ECMA-ST/ECMA-404.pdf
// either sacrifice readbility or remove \\n \\r \t from json string.
// https://stackoverflow.com/questions/53030698/php-5-and-7-json-last-error-difference
// validate json
// echo json_last_error();
// https://jsonlint.com/

$_['module_mpphotogallery_settings'] = json_decode($settings, 1);
