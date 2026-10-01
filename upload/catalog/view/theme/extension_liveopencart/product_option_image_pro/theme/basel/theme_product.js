
function setPoipProductCustomMethods(poip_product) {
	
	poip_product.custom_methods.getStoredImagesContainer = function($carousel_elem) {
		var selector = '[data-poip="images"]';
		if ( !poip_product.getElement(selector).length ) {
			$carousel_elem.parent().before('<div data-poip="images" style="display:none;!important"></div>');
			$carousel_elem.find('a[href]').each(function(){
				poip_product.getElement(selector).append( poip_common.getOuterHTML($(this)) );
			});
		}
		return poip_product.getElement(selector);
	};
	
	poip_product.custom_methods.updateShouldBeProcessed = function($carousel_elem, images, counter, carousel_is_ready) {
		
		if ( !poip_product.custom_data.set_visible_images_is_called ) {
			if ( !carousel_is_ready ) {
				poip_product.timers.set_visible_images = setTimeout(function(){
					poip_product.custom_methods['setVisibleImages.instead'](images, counter+1);
				}, 100);
				return false;
			}
			poip_product.custom_data.set_visible_images_is_called = true;
		} else {
		
			var current_imgs = [];
			$carousel_elem.find('a[href]').each( function(){
				current_imgs.push($(this).attr('href'));
			});
			
			if ( current_imgs.toString() == images.toString() ) {
				poip_product.works.set_visible_images = false;
				return false; // nothing to change
			}
		}
		
		return true;
	};
	
	poip_product.custom_methods.itIsQuickview = function() {
		return poip_product.$container.is('.quickview-info');
	};
	
	poip_product.custom_methods.reInitCloudZoom = function() {
		
		if (! poip_product.custom_methods.itIsQuickview() ) {
		
			if ( poip_product.getElement('.cloud-zoom').data('zoom') ) {
				poip_product.getElement('.cloud-zoom').data('zoom').destroy();
			}
			if ( poip_product.getElement('.cloud-zoom-gallery').data('zoom') ) {
				poip_product.getElement('.cloud-zoom-gallery').data('zoom').destroy();
			}
			poip_product.getElement('.cloud-zoom-wrap .mousetrap').remove();
			poip_product.getElement('.cloud-zoom-wrap .cloud-zoom-big').remove();
			poip_product.getElement('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
		}
	};
	
	
	poip_product.custom_methods['updatePopupImages.instead'] = function() {
		
		if (! poip_product.custom_methods.itIsQuickview() ) {
			if ( poip_product.getElement("#gallery").data('lightGallery') ) {
					poip_product.getElement("#gallery").data('lightGallery').destroy(true);
			}
			
			// Image Gallery
			poip_product.getElement("#gallery").lightGallery({
				selector: '.link',
				download:false,
				hideBarsDelay:99999
			});
		}
	};
	
	poip_product.custom_methods['setVisibleImages.instead'] = function(images, counter) {
		
		poip_product.works.set_visible_images = true;
		
		clearTimeout(poip_product.timers.set_visible_images);
		if (!counter) counter = 1;
		if (counter == 1000) {
			poip_product.works.set_visible_images = false;
			return;
		}
		
		if ( poip_product.custom_methods.itIsQuickview() ) { // quickview
			
			let carousel_selector = '.qv_image';
			let $carousel_elem = poip_product.getElement(carousel_selector);
			
			if ( $carousel_elem.length ) {
				
				$stored_images = poip_product.theme_adaptation.getStoredImagesContainer($carousel_elem.parent(), $carousel_elem.find('img'));
				
				let is_ready = $carousel_elem.find('.slick-list').length && $carousel_elem.is('.slick-slider') && poip_product.getDocumentReadyState();
				if ( !poip_product.theme_adaptation.updateShouldBeProcessed($carousel_elem.find('img'), 'src', images, images, counter, is_ready) ) {
				//if ( !poip_product.custom_methods.updateShouldBeProcessed($carousel_elem, images, counter, is_ready ) ) {
					return; // will the image updating by continued or not - set in the function
				}
				
				if ( images.length ) {
					
					let elems_cnt = $carousel_elem.find('img').length;
					for (let i = 1; i<=elems_cnt; i++ ) {
						$carousel_elem.slick('slickRemove',0);
					}
					
					poip_common.each(images, function(image){
					
						let $poip_img_elem = $stored_images.find('img[src="'+image+'"]:first');
						
						if ( $poip_img_elem.length ) {
							$carousel_elem.slick('slickAdd', poip_common.getOuterHTML($poip_img_elem) );
						}
					});
				}
			}
			
		} else { // product page
		
			let carousel_selector = '.image-additional';
			let $carousel_elem = poip_product.getElement(carousel_selector);
			
			if ( $carousel_elem.length ) {
				
				$stored_images = poip_product.custom_methods.getStoredImagesContainer($carousel_elem);
				
				if ( !poip_product.custom_methods.updateShouldBeProcessed($carousel_elem, images, counter, ($carousel_elem.find('.slick-list').length && $carousel_elem.is('.slick-slider') && poip_product.getDocumentReadyState() ) ) ) {
					return; // will the image updating by continued or not - set in the function
				}
				
				let elems_cnt = $carousel_elem.find('.slick-slide a').length;
				for (let i = 1; i<=elems_cnt; i++ ) {
					try {
						$carousel_elem.slick('slickRemove',0);
					} catch(e) {
						// slick err, ignore
					}
				}
				
				poip_common.each(images, function(image){
				
					var $poip_img_elem = $stored_images.find('a[href="'+image+'"]:first');
					
					if ( $poip_img_elem.length ) {
						$carousel_elem.slick('slickAdd', '<li>'+poip_common.getOuterHTML($poip_img_elem)+'</li>' );
					}
				});
				
				poip_product.custom_methods.reInitCloudZoom();
				
				poip_product.getElement('.image-additional a.link.active').removeClass('active');
				poip_product.getElement('.image-additional a.link:first').addClass('active');
				

				
				
				poip_product.getElement('.image-additional a.link img').click(function (e) {
					let $a = $(this).closest('a');
					
					if ($a.hasClass("locked")) {
						e.stopImmediatePropagation();
						e.preventDefault();
					}
					
					poip_product.eventAdditionalImageMouseover(e, $(this));
					
					//poip_product.getElement('.image-additional a.link.active').removeClass('active');
					//$a.addClass('active');
				});
				
				//if ( poip_product.getElement("#gallery").data('lightGallery') ) {
				//	poip_product.getElement("#gallery").data('lightGallery').destroy(true);
				//}
				//
				//// Image Gallery
				//poip_product.getElement("#gallery").lightGallery({
				//	selector: '.link',
				//	download:false,
				//	hideBarsDelay:99999
				//});
	
			}
		}
	
		poip_product.works.set_visible_images = false;
	};
	
	poip_product.custom_methods['getImagesBelowOptionHTML.instead'] = function(images) {
		let html = '';
		poip_common.each(images, function(image){
			let poip_img = poip_product.getImageBySrc(image, 'popup');
			html+= '<img src="'+poip_img.option_thumb+'" >';
		});
		return html;
	};
	
	poip_product.custom_methods['getMainImage.instead'] = function(){
		return poip_product.getElement('#main-image img:first');
	};
	
	poip_product.custom_methods['setMainImage.after'] = function(image){
		
		if ( poip_product.custom_methods.itIsQuickview() ) {
			
			let $carousel = poip_product.getElement('.main-image');
			let $img = $carousel.find('img.slick-slide:not(.slick-cloned)[src="'+image+'"]');
			if ( $img.length ) {
				$carousel.slick('slickGoTo', $img.attr('data-slick-index'), true);
			}
			
		} else {
			poip_product.custom_methods.reInitCloudZoom();
			
			poip_product.getElement('.image-additional a.link.active').removeClass('active');
			
			poip_product.getElement('.image-additional a.link[href="'+image+'"]:first').addClass('active');
		}
	};
	
	poip_product.custom_methods['getAdditionalImagesBlock.instead'] = function(){
		
		return poip_product.getElement('.image-additional li');
	};
	
	
}