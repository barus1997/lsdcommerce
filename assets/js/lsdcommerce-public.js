/*
Public Javascript and JQuery Function
- Ready 
-- Input Handler
-- Tabs Handler
-- Auto Check Cart
- Collapse Handler
- CartManager
-- AddToCart Function
-- AddToCart Handler
-- Quantity Handler
-- Shipping Calculation
-- Recalculate Checkout
*/
(function ($) {
	'use strict';

	/* Ready function, Initialize */
	$(document).ready(function () {
		// Join Community Invitation
		setTimeout(console.log.bind(console, "%c" + 'Join dan Develop LSDCommerce ', "color:" + '#fff;background:#a70002;padding:5px;margin:0;width:100%;font-weight:600;' ));
		setTimeout(console.log.bind(console, 'https://forum.lsdplugins.com/t/lsdcommerce' ));

		// Product Tabs
		$('.lsdc-nav-tab').on('click', '[data-toggle="tab"]', function (e) {
			e.preventDefault();
			$(this).parents('.lsdc-nav-tab').find('.nav-link').removeClass('active');
			$(this).addClass('active');

			let target = $(this).attr('data-target');
			$('div[data-tab="' + target + '"]').parents('.lsdc-tab-content').find('.tab-pane').removeClass('show');
			$('div[data-tab="' + target + '"]').addClass('show');
		});

	});

	/**
	 * LSDCommerce - Member
	 * Change Password
	 */
	$(document).on("click", ".lsdcommerce-member .change-password", function (e) {
		e.preventDefault();
		var that = this;
		var oldpassword = $('#oldpassword').val();
		var newpassword = $('#newpassword').val();
		var repeatpassword = $('#repeatpassword').val();

		if ( lsdcommerce_empty(oldpassword) || lsdcommerce_empty(newpassword) || lsdcommerce_empty(repeatpassword) ) {
			$('#alert-password').removeClass('lsd-hide');
			$('#alert-password').text('Please Input Old and New Password...');
		} else {
			if (newpassword != repeatpassword) {
				$('#alert-password').removeClass('lsd-hide');
				$('#alert-password').text('You Repeat Password not Match...')
			} else {
				$('#alert-password').addClass('lsd-hide');
				$(this).addClass('loading');

				$.post(lsdc_pub.ajax_url, {
					action: 'lsdc_member_profile_password',
					nonce: $('#member-nonce').val(),
					old: oldpassword,
					new: newpassword,
					security: lsdc_pub.ajax_nonce,
				}, function (response) {
					$(that).removeClass('loading');
					if (response == false) {
						$('#alert-password').removeClass('lsd-hide');
						$('#alert-password').text("Your Old Password didn't match...");
					} else {
						$('#alert-password').removeClass('lsd-hide');
						$('#alert-password').text('Successfully Change your password...');
					}
					// Cookie Remove
				}).fail(function () {
					alert('Failed, please check your internet');
				});

			}
		}
	});

})(jQuery);