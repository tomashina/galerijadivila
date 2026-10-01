

$(document).on('init_after.poip', function(e, poip){ // initially use the jQuery events, internally extension own events
	poiu.init();
});

var poiu = {
	
	ro_extension: {}, // store locally to be able to run functions within init (before the global variable is set)
	
	ro_image_cnt: 0,
	
	init: function(){
		if ( typeof(poip) != 'undefined' ) {
			
			poip.onEvent('addProductOptionImage_after', function(params){
				poiu.enableSortingForContainer(params.$grid, 'input[name*="[srt]"]');
			});
			
			$(function(){
				poiu.enableSortingForContainer($('#images tbody'), 'input[name*="[sort_order]"]');
			});
			
			poip.onEvent('addImage_after', function(){
				poiu.updateSortOrderForLastItemInContainer($('#images tbody'), 'input[name*="[sort_order]"]');
			});
			
			if ( poip.global_settings.images_for_ro ) {
				$(document).on('init_before.ro', function(e, ro_extension){ // initially use the jQuery events, internally extension own events
					poiu.ro_extension = ro_extension;
					ro_extension.onEvent('addCombination_after', function(tab_num, ro_num, params){
						poiu.displayImagesForROComb(tab_num, ro_num, params);
					});
					ro_extension.onEvent('addTabTable_after', function(tab_num){
						poiu.displayImagesColumnForRO(tab_num);
					});
				});
			}
			
			poiu.enableFilemenagerEvents();
			
		}
	},
	
	enableFilemenagerEvents : function(){
		let url_tpl = 'index.php?route=common/filemanager';
		let container_selector = '#images, [id^="option_images"], [id^="ro-images-"]';
		$(document).ajaxComplete(function( event, xhr, settings ) {
			if ( settings.url.indexOf(url_tpl) !== false ) {
				
				let target_id = poiu.getURLParamValue(settings.url, 'target');
				let $target = $('#'+target_id);
				let $container = $target.is(container_selector) ? $target : $target.closest(container_selector);
				
				if ( $container.length ) {
					let html_button = ' <button class="btn btn-success" id="poiu-select-images" title="'+poip.texts.button_select_images+'"><i class="fa fa-hand-pointer-o"></i></button> ';
				
					$('#modal-image #filemanager #button-parent').before(html_button);
					$('#modal-image #filemanager #poiu-select-images').click(function(){
						poiu.selectImagesInFilemanager($('#modal-image'), $container, $target);
					});
					
					if ( $target.is(container_selector) ) { // filemanager opened to select many images for product images or product option value images or RO images
						$('#modal-image').find('a.thumbnail').off('click'); // disable std
						$('#modal-image').find('a.thumbnail').on('click', function(e){
							e.preventDefault();
							$(this).closest('div').find(':checkbox').prop('checked', true);
							poiu.selectImagesInFilemanager($('#modal-image'), $container, $target);
						});
					}
				}
				
				
				
			}
		});
		
		let html_button_add_many_images = ' <button type="button" id="poiu-add-product-images" class="btn btn-success"><i class="fa fa-plus-circle"></i><i class="fa fa-plus-circle"></i></button> ';
		$('#images button[onclick="addImage();"]').after(html_button_add_many_images);
		$('#poiu-add-product-images').click(function(){
			poiu.openImageFilemanager($(this), 'images', '');
		});
		
		poiu.enabledCustomFilemanagerEvents();
	
	},
	
	enabledCustomFilemanagerEvents: function(){ // comp with custom image manager (by GWS)
		
		let url_tpl = 'index.php?route=common/filemanager';
		let container_selector = '#images, [id^="option_images"], [id^="ro-images-"]';
		$(document).ajaxComplete(function( event, xhr, settings ) {
			if ( settings.url.indexOf(url_tpl) !== false ) {
				
				let target_id = poiu.getURLParamValue(settings.url, 'target');
				let poiumulti = poiu.getURLParamValue(settings.url, 'poiumulti');
				if ( target_id && poiumulti ) {
					
					let $target = $('#'+target_id);
					let $container = $target.is(container_selector) ? $target : $target.closest(container_selector);
					let $elfinder = $('#elfinder');
					
					if ( $container.length && $elfinder.length && $elfinder.elfinder('instance') ) {
						
						$elfinder.elfinder('instance').options.getFileCallback = function(e,a){
						
							$.each(e,function(e,a){
								poiu.addImageToContainer($target, a.path, a.tmb);
							});
							$("#modal-image").modal("hide");
						};
						$elfinder.elfinder('instance')._commands.getfile.callback = $elfinder.elfinder('instance').options.getFileCallback; // hacky
					}
				}
			}
		});
	},
	
	openImageFilemanager: function($button, target, thumb) {
		$('#modal-image').remove();
		
		$.ajax({
			url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token') + '&target=' + target + '&thumb=' + thumb + '&poiumulti=true',
			dataType: 'html',
			beforeSend: function() {
				$button.prop('disabled', true);
			},
			complete: function() {
				$button.prop('disabled', false);
			},
			success: function(html) {
				$('body').append('<div id="modal-image" class="modal">' + html + '</div>');

				$('#modal-image').modal('show');
				
			}
		});
	},
	
	selectImagesInFilemanager: function($modal_image, $container, $target){
		
		let $checkboxes = $('#modal-image #filemanager :checkbox[name="path[]"]:checked');
		
		let checkboxes = [];
		$checkboxes.each(function(){ // remove folders
			let $checkbox = $(this);
			if ( $checkbox.closest('div').find('a.thumbnail img').length ) {
				checkboxes.push($checkbox);
			}
		});
		
		if ( checkboxes.length ) {
			let inputs = [];
			if ( $target.is('input') ) {
				inputs.push($target);
			}

			while (inputs.length < checkboxes.length) {
				inputs.push(poiu.addImageToContainer($container));
			}
			
			for (let checkbox_i=0;checkbox_i<checkboxes.length; checkbox_i++){
				let $checkbox = checkboxes[checkbox_i];
				let $input = inputs[checkbox_i];
				$input.val($checkbox.val());
				$input.prev('a').find('img').attr('src', $checkbox.closest('div').find('a.thumbnail img').attr('src'));
			}
			
			$modal_image.modal('hide');
			
		}
	},
	
	addImageToContainer: function($container, image, src){
		if ( $container.is('#images') ) { // product images
			addImage();
			$input = $container.find('input[type="hidden"][id^="input-image"]').last();
		} else if ( $container.is('[id^="ro-images-"]') ) {	 // RO images
			poiu.addROImage( $container.attr('data-ro-num') );
			$input = $container.find('input[type="hidden"][id^="ro_image"]').last();
		} else { // option images
			poip.addProductOptionImage($container.attr('data-poip-option-row'), $container.attr('data-poip-option-value-row'));
			$input = $container.find('input[type="hidden"][id^="option_image"]').last();
		}
		
		if ( image ) {
			$input.val(image);
			if (src) {
				$input.prev('a').find('img').attr('src', src);
			}
		}
		return $input;
	},
	
	parseURLParams: function(url) {
		let parts = url.split('?');
		let search = parts.length == 2 ? parts[1] : parts[0];
		
		let params = {};
		
		let parts_search = search.split('&');
		$.each(parts_search, function(part_i, part) {
			let param = part.split('=');
			params[ decodeURIComponent(param[0]) ] = (param.length == 2 ? decodeURIComponent(param[1]) : '' );
		});
		return params;
	},
	
	getURLParamValue: function(url, param_name) {
		let params = poiu.parseURLParams(url);
		if ( params[param_name] ) {
			return params[param_name];
		}
	},
	
	displayImagesColumnForRO: function(tab_num) {
		let html = '';
		html+= '<td class="poip-container-option-images">'+poip.texts.entry_image+'</td>';
		$('#ro-table-'+tab_num+' thead tr:first td:last').before(html);
	},
	
	displayImagesForROComb: function(tab_num, ro_num, params) {
		
		let $tr = poiu.ro_extension.getROTrByNum(ro_num);
		let html = '';
		
		html+= '<td>';
		html+= '<button type="button" class="btn btn-default" onclick="poiu.addROImage('+ro_num+')" title="'+poip.texts.button_add_image+'">';
		html+= '<i class="fa fa-plus-circle"></i>';
		html+= '</button> ';
		html+= '<button type="button" class="btn btn-default" onclick="poiu.openImageFilemanager($(this), \'ro-images-'+ro_num+'\', \'\')" title="'+poip.texts.button_add_images+'">';
		html+= '<i class="fa fa-plus-circle"></i><i class="fa fa-plus-circle"></i>';
		html+= '</button>';
		html+= '<div id="ro-images-'+ro_num+'" class="poip-option-images-grid" data-ro-num="'+ro_num+'"></div>';
		html+= '</td>';
		
		
		$tr.find('td:last').before(html);
		
		// display RO images
		if ( params && params.images ) {
			poip.each(params.images, function(image_data){
				poiu.addROImage(ro_num, image_data.thumb, image_data.image, image_data.srt );
			});
		}
		
		let $container = $('#ro-images-'+ro_num);
		poiu.enableSortingForContainer($container, 'input[name*="[srt]"]');
		
	},
	
	addROImage: function(ro_num, thumb, image, srt) {
		
		let $container = $('#ro-images-'+ro_num);
		
		let $tr = poiu.ro_extension.getROTrByNum(ro_num);
		let tab_num = poiu.ro_extension.getROTabNumByElement($tr);


		let html = '';
		
		html += '<div id="div-ro-image'+poiu.ro_image_cnt+'" class="poip-image-to-option">';
		
		let current_thumb = thumb || poip.image_placeholder;
		let current_image = image || '';
		let current_srt = srt || 0;
		
		if ( !current_srt ) {
			
			$container.find('[name*="[srt]"]').each(function() {
				current_srt = Math.max(current_srt, ( parseInt($(this).attr('value')) || 0 ) );
			});
			current_srt++;
		}
		
		let name_prefix = 'ro_data['+tab_num+'][ro]['+ro_num+'][images]['+poiu.ro_image_cnt+']';
		
		html += '<a href="" id="thumb-ro-image'+poiu.ro_image_cnt+'" data-toggle="image" class="img-thumbnail" >';
		html += '<img height="100" width="100" src="'+current_thumb+'" alt="" title="" data-placeholder="'+poip.image_placeholder+'">';
		html += '</a>';
		html += '<input type="hidden" id="ro_image'+poiu.ro_image_cnt+'" name="'+name_prefix+'[image]" value="'+current_image+'">';
		html += '<div class="input-group">';
		html += '<input type="text" class="form-control"  title="'+poip.texts.entry_sort_order+'" name="'+name_prefix+'[srt]" value="'+current_srt+'" size="3">';
		html += '<button class="btn btn-default" title="'+poip.texts.button_remove+'" onclick="$(\'#div-ro-image' + poiu.ro_image_cnt + '\').remove();"><i class="fa fa-trash-o"></i></button>';
		html += '</div>';
		
		html += '</div>';
		
		$container.append(html);
		
		poiu.ro_image_cnt++;
	},
	
	updateSortOrderForLastItemInContainer: function($container, sort_order_input_selector){
		let max_srt = 0;
		let $items = $container.find(sort_order_input_selector);
		$items.each(function(item_index){
			if ( item_index == ($items.length-1) ) { // last
				$(this).val( max_srt + 1 );
			} else {
				let srt = parseInt( $(this).val() );
				if ( isNaN(srt) ) {
					srt = 1;
				}
				max_srt = Math.max(max_srt, srt );
			}
		});
	},
	
	enableSortingForContainer: function($container, sort_order_input_selector){
		if ( !$container.data('ui-sortable') ) {
			$container.sortable({
				// opacity: 0.5,
				start: function(e, ui){
					ui.placeholder.height(ui.item.height());
				},
				tolerance: "pointer",
				update: function(){
					if ( sort_order_input_selector ) {
						poiu.resortItemsInContainer($container, sort_order_input_selector);
					}
				},
			});
		}
	},
	
	resortItemsInContainer: function($container, sort_order_input_selector) {
		
		let min_srt = false;
		let $items = $container.find(sort_order_input_selector);
		$items.each(function(){
			let srt = parseInt( $(this).val() );
			if ( isNaN(srt) ) {
				srt = 1;
			}
			min_srt = min_srt === false ? srt : Math.min(min_srt, srt );
		});
		
		if ( min_srt !== false) {
			let current_srt = min_srt;
			$items.each(function(){
				$(this).val(current_srt);
				current_srt++;
			});
		}
	},
	
};

