var ur115_20X = 'index.php?route=module/compgafad/';
var url23X_30X = 'index.php?route=extension/module/compgafad/';
var url401X = 'index.php?route=extension/compgafad/module/compgafad|';
var url402X = 'index.php?route=extension/compgafad/module/compgafad.';

$(document).delegate('#button-cart, [data-quick-buy]', 'click', function() {
	postdata = $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea')

	if($('#product .button-group-page').length) {
		/*for J3*/
		postdata = $(
		'#product .button-group-page input[type=\'text\'], #product .button-group-page input[type=\'hidden\'], #product .button-group-page input[type=\'radio\']:checked, #product .button-group-page input[type=\'checkbox\']:checked, #product .button-group-page select, #product .button-group-page textarea, ' +
		'#product .product-options input[type=\'text\'], #product .product-options input[type=\'hidden\'], #product .product-options input[type=\'radio\']:checked, #product .product-options input[type=\'checkbox\']:checked, #product .product-options select, #product .product-options textarea, ' +
		'#product select[name="recurring_id"]'
		);
	} 
	if($('.product-info .cart').length) {
		/*for OC 1.5.X*/
		postdata = $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea');
	}
	$.ajax({
		url: url23X_30X + 'addtocart',
		async: true,
		type: 'post',
		dataType: 'json',
		data: postdata,
		success: function(json) {
			if (json['script']) {
				$('body').append(json['script']);
			}
		}
	});
});
$(document).delegate("[onclick*='cart.add'], [onclick*='addToCart'], button[formaction*='checkout/cart']", 'click', function() {
	if($(this).closest('form').find("[name*='product_id']").length) {
		var product_id = $(this).closest('form').find("[name*='product_id']").val();	
		var quantity = $(this).closest('.form').find("input[name*='quantity']").val();
	} else {
		var product_id = $(this).attr('onclick').match(/[0-9]+/).toString();
		var quantity = $(this).closest('.product-thumb').find("input[name*='quantity']").val();
	}
	quantity = quantity || 1;

	$.ajax({
		url: url23X_30X + 'addtocart',
		async: true,
		type: 'post',
		dataType: 'json',
		data: {product_id:product_id,quantity:quantity},
		success: function(json) {
			if (json['script']) {
				$('body').append(json['script']);
			}
		}
	});
});
$(document).delegate("[onclick*='wishlist.add'],[onclick*='addToWishList'], button[formaction*='account/wishlist']", 'click', function() {
	if($(this).closest('form').find("[name*='product_id']").length) {
		var product_id = $(this).closest('form').find("[name*='product_id']").val();	
		var quantity = $(this).closest('.form').find("input[name*='quantity']").val();
	} else {
		var product_id = $(this).attr('onclick').match(/[0-9]+/).toString();
		var quantity = $(this).closest('.product-thumb').find("input[name*='quantity']").val();
	}
	quantity = quantity || 1;

	$.ajax({
		url: url23X_30X + 'addtowishlist',
		async: true,
		type: 'post',
		dataType: 'json',
		data: {product_id:product_id,quantity:quantity},
		success: function(json) {
			if (json['script']) {
				$('body').append(json['script']);
			}
		}
	});
});