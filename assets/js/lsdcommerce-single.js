/*
Javascript Code in Single Product
*/
(function ($) {
	'use strict';

	// Initialize Variable 
	let productID,
		productPrice,
		productThumb,
		productTitle,
		productLimit,
		productQty;

	let single, cart, cartTemplate, cartItem, cartPopup = false; //State Flag
	let controlQty, controlCartQty, total = false;
	let order_object = {};

	function lsdcommerce_single_cartmanager(counter, text) {
		$('.cart-footer-info h4').text(text);
		$('.cart-manager span').text(counter);
	}


	/* Ready function, Initialize */
	$(document).ready(function () {
		// Cart Manager - Show Total and Counter Item
		cart = new LSDCCookie('_lsdcommerce_cart');
		total = cart.get('total');
		let totalText = null;
		if (total == undefined) {
			total = {
				'total_qty': 0
			};
			totalText = lsdc_pub.translation.cart_empty; // Empty
		} else {
			totalText = lsdcommerce_currency_format(true, total.total_price);
		}
		lsdcommerce_single_cartmanager(total.total_qty, totalText);

		single = $('#product-detail');
		cartTemplate = $('#item-template').html();
		cartItem = $('#cart-items');
		cartPopup = $('#cart-popup');
		controlQty = $('.cart-qty-float');

		// Set Product Variable
		if (single.length) {
			productID = parseInt(single.attr('data-id'));
			productPrice = parseInt(single.attr('data-price'));
			productThumb = single.attr('data-thumbnail');
			productTitle = single.attr('data-title');
			productLimit = single.attr('data-limit') == null ? 1 : parseInt(single.attr('data-limit'));
		}


	});

	// Cart Manager :: Show Product Lists
	$(document).on('click', '.cart-manager', function (e) {
		e.preventDefault();

		var data = {};
		var dataFormatted = cart.get('formatted');
		data['items'] = dataFormatted;

		if (dataFormatted.length) {
			// Add Overlay
			cartPopup.addClass('overlay');
			cartPopup.find('.cart-body').removeClass('hidden');
			controlQty.hide();

			if (cartTemplate && data) {
				// Templating
				var render = Mustache.to_html(cartTemplate, data);
				jQuery(cartItem).html(render);

				setTimeout(() => {
					// Showing Up
					jQuery(cartItem).fadeIn('fast');
				}, 1000);
			} else {
				jQuery(cartItem).hide().html('Error Load Data...').fadeIn('slow'); // FallBack
			}
		}
	});

	// Cart Manager :: Hide Product Lists by Click Overlay
	$(document).on('click', '.overlay', function (e) {
		if (e.target == this) {
			e.preventDefault();
			// controlQty.show();
			cartPopup.removeClass('overlay');
			cartPopup.find('.cart-body').addClass('hidden');
		}
	});


	// ------------- Add to Cart ----------------- //
	function lsdcommerce_addto_cart(inType, inID, inQTY, inPrice, inTitle) {
		let cartProduct = cart.get('product', productID);
		let inputable = false;

		// Set Qty to Zero if Undefined
		if (cartProduct == undefined) {
			cartProduct = {}
			cartProduct['qty'] = 0;
		}

		let singleQtyControl = controlQty.find('.lsdc-qty input[name="qty"]'); // get single qrt
		let singleQty = parseInt(singleQtyControl.val()) == null ? 0 : cartProduct.qty;

		if (inType == 'add') {
			if (cartProduct.qty < parseInt(productLimit)) {
				productQty = singleQty + 1;
				inputable = true;
			}

			// console.log( input_qty );
			// if( input_type == 'sub' ){
			// 	productQty = productQty - 1;
			// }

			/* PRO Code -- Ignore, But Don't Delete */
			// let variant 		= $('.product-variant .container .variant-item');
			// let variantChoose 	= {};
			// let variationsID 	= '';
			// let variationsName 	= '';
			// let variationsPrice = '';

			// // // Iterate Variations
			// // variant.each(function (i, obj) {
			// // 	let variantID = $(obj).find('.variant-name').attr('data-id');
			// // 	let variantSelect = $(obj).find('input[type="radio"][name="' + variantID + '"]:checked');

			// // 	let variantSelectedID = variantSelect.attr('id');
			// // 	let variantName = $(obj).find('label[for="' + variantSelectedID + '"]').text();
			// // 	let variantSelected = {
			// // 		'id': variantSelectedID,
			// // 		'name': variantName,
			// // 		'price': variantSelect.attr('price'),
			// // 		'qty': variantSelect.attr('qty')
			// // 	}

			// 	// Populate Variation
			// 	variationsID 				+= variantSelectedID + '-';
			// 	variationsPrice 			= parseInt(productPrice) + parseInt(variantSelect.attr('price'));
			// 	variantChoose[variantID] 	= variantSelected;
			// 	variationsName 				= ' - ' + variantName;
			// });

			// // Removing Last Char '-' from add recrusive
			// variationsID = variationsID.slice(0, -1);

			// // Redefine Product ID if Variation Exists
			// if( variationsID ){
			// 	let variationID = productID + '-' + variationsID;
			// }else{
			// 	let variationID = null;
			// }
			/* PRO Code -- Ignore, But Don't Delete */

			/* PRO Code -- START - Ignore, But Don't Delete */
			// if ( lsdc_checkVal(variantChoose) ) {
			// 	// Set ID with Variation { productid-variationid }
			// 	cart.set( input_type != null ? input_type : 'add', {
			// 		"root" : variationID != null  ? variationID : productID,
			// 		"id": input_id != null ? input_id : productID,
			// 		"qty": input_qty != null ? input_qty : productQty,
			// 		"title": input_title != null ? input_price : productTitle + variationsName,
			// 		"price": input_price != null ? input_price : variationsPrice,
			// 		"thumbnail": productThumb,
			// 		"variations": variantChoose
			// 	});
			// /* PRO Code -- END - Ignore, But Don't Delete */
			// } else {

			// }
		} else if (inType == 'sub') {
			inputable = true;
		}

		if (inputable) {
			inType = inType != null ? inType : 'add';
			productID = inID != null ? inID : productID;
			let variationsID = null;

			cart.set(inType, {
				"id": productID,
				"qty": inQTY != null ? inQTY : productQty,
				"title": inTitle != null ? inTitle : productTitle,
				"price": inPrice != null ? inPrice : productPrice,
				"thumbnail": productThumb,
				"variations": variationsID // Variation ID #Pro
			});
		}

		return productID;
	}

	// AddtoCart via Button
	$(document).on('click', '.lsdc-addto-cart', function (e) {
		e.preventDefault();

		// Reset Cart Manager
		cartPopup.addClass('show');
		cartPopup.removeClass('overlay');
		cartPopup.find('.cart-body').addClass('hidden');
		controlQty.show(); // Show Qty

		// Adding to Cart
		productID = lsdcommerce_addto_cart('add');
		let carts = cart.get('product', productID);
		let total = cart.get('total');

		// Set Qty and CartManager by Carts Quantity
		controlQty.find('.lsdc-qty input[name="qty"]').val(carts.qty);
		lsdcommerce_single_cartmanager(total.total_qty, lsdcommerce_currency_format(true, total.total_price));
	});

	//  AddtoCart via QTY Add
	$(document).on('click', '.plus', function (e) {
		let plusInCart = null; // Add Qty on Cart
		let plusInFloat = null; //Add Qty on FLoting Qty
		controlCartQty = $(this).closest('.lsdc-qty').find('input[name="qty"]');
		productQty = controlCartQty.val() == null ? 1 : parseInt(controlCartQty.val()); // Force set 1 if empty
		productID = $(this).closest('.item').attr('id'); // Get Product ID

		if (productID == undefined) // Minus in Float Qty not on Cart Manager
		{
			plusInFloat = true;
			productID = $(this).closest('.cart-qty-float').attr('product-id'); // Get Product ID
		} else {
			plusInCart = true;
		}


		if (productQty < productLimit) { // Limit Order
			controlCartQty.val(++productQty); // Increase Quantity
			let carts = cart.get('product', productID); //Get Detail Product by ID
			controlQty.find('input[name="qty"]').val(productQty); // Sync and Set Qty

			$(this).closest('.cart-basket')
				.find('.item[id="' + productID + '"] .price')
				.text(lsdcommerce_currency_format(true, product_cart.price * productQty)); // Refersh New Price based On Qty

			// Update Cart
			lsdcommerce_addto_cart('add', productID, productQty, product_cart.price, productTitle);

			// ---> Updating Cart Manager
			let total = cart.get('total'); // Getting Total Cart
			lsdcommerce_single_cartmanager(total.total_qty, lsdcommerce_currency_format(true, total.total_price));
		}
	});

	// Qty Sub - Buggy
	$(document).on('click', '.minus', function (e) {
		let minusInCart = null;
		let minusInFloat = null;
		controlCartQty = $(this).closest('.lsdc-qty').find('input[name="qty"]');
		productQty = parseInt((controlCartQty.val()) == null ? 1 : controlCartQty.val()); // Force set 1 if empty
		productID = $(this).closest('.item').attr('id'); // Get Product ID

		// Minus in Float Qty not on Cart Manager
		if (productID == undefined) {
			minusInFloat = true;
			productID = $(this).closest('.cart-qty-float').attr('product-id'); // Get Product ID
		} else {
			minusInCart = true;
		}

		// Decrease Qty on click
		controlCartQty.val(--productQty);

		let product_cart = cart.get('product', productID); //Get Detail Product by ID

		if (minusInFloat && productQty == 0) { // Hold Product if Minus in Float

			controlCartQty.val(1); // Updating Quantity UI
			lsdcommerce_addto_cart('hold', productID, productQty, productPrice, productTitle);
			$(this).closest('.item').find('.price').text(lsdcommerce_currency_format(true, product_cart.price * 1)); // find price text set new product qty

		} else {

			controlCartQty.val(productQty); // Sync and Set Qty
			$(this).closest('.item').find('.price').text(lsdcommerce_currency_format(true, product_cart.price * productQty)); // Refersh New Price based On Qty
			lsdcommerce_addto_cart('sub', productID, productQty, product_cart.price, productTitle); // Decrease Qty
			// Minus in Cart -> Remove Product
			if (minusInCart && productQty == 0) {
				cart.delete(productID);
				$('.cart-qty-float input').val(0);
				$(this).closest('.item').remove();
			}
		}

		// ---> Updating UI
		let total = cart.get('total');

		if (total == undefined) {
			total = {
				'total_qty': 0
			};
			cartPopup.removeClass('show');
			$('#cart-popup.overlay').trigger('click');
			lsdcommerce_single_cartmanager(total.total_qty, lsdc_pub.translation.cart_empty );
			controlQty.hide();
		} else {
			lsdcommerce_single_cartmanager(total.total_qty, lsdcommerce_currency_format(true, total.total_price));
		}
	});

})(jQuery);