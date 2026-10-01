

poip_list.custom_methods['displayOneProductImages.instead'] = function($product_image_element, poip_data) {
	
	$product_image_element.attr('data-poip-status', 'loaded'); // with or without images
		
	if ( !poip_data || $.isEmptyObject(poip_data) ) {
		return;
	}
	
	var $product_anchor = $product_image_element.is('img') ? $product_image_element.closest('a') : $product_image_element; 
	var product_href 		= encodeURI( $product_anchor.attr('href') );
	
	var current_product_index = poip_list.product_count++; // increments the variable but returns an old value (all in one step)
	$product_image_element.attr('data-poip-product-index', current_product_index );

	
		
	for (var product_option_id in poip_data) {
		if ( !poip_data.hasOwnProperty(product_option_id) ) continue;
	
		var html = '';
		for (var poip_data_i in poip_data[product_option_id]) {
			if ( !poip_data[product_option_id].hasOwnProperty(poip_data_i) ) continue;
		
			var option_image = poip_data[product_option_id][poip_data_i];
			var product_option_value_id = option_image.product_option_value_id;
			
			var title = (typeof(option_image.title)!='undefined' && option_image.title) ? option_image.title : '';
			var current_href = product_href+(product_href.indexOf('?')==-1?'?':'&amp;')+'poip_ov='+product_option_value_id;
			
			html+='<a onmouseover="poip_list.eventThumbMouseOver(this)" onclick="return poip_list.eventThumbClick(this);" ';
			//html+='<a onmouseover="poip_list.changeProductImageByThumb(this);" ';
			html+=' href="'+current_href+'"';
			
			html+=' data-toggle="tooltip"';
			html+=' data-title="'+title+'"';
			//html+=' title="'+title+'"';
			
			html+=' data-poip-thumb="'+option_image.thumb+'"';
			html+=' data-poip-product-index="'+current_product_index+'"';
			html+=' style="display:inline;"';
			html+='>';
			html+='<img class="img-thumbnail"';
			html+=' src="'+option_image.icon+'" ';
			html+=' alt="'+title+'"';
			html+=' style="width:'+option_image.width+'px; height:'+option_image.height+'px;display:inline;"';
			html+='>';
			html+='</a>';

			
		}
		if ( html ) {
	
			html='<div data-poip_id="poip_img" style="  text-align: center; margin-top: 3px;">'+html;
			html+='</div>';
			
			$product_anchor.closest('.image').siblings('.caption').prepend(html);
			//$product_anchor.closest('.image').after(html);
			$product_anchor.closest('.image').parent().find('[data-toggle="tooltip"]').tooltip();
		}
	}
	
	
};