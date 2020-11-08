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

		// Product Tabs
		$(document).on('click', '[view-ajax]', function (e) {
			e.preventDefault();
			let postID = $(this).attr('id');
			let postData = $(this).attr('data-post');
			let thisText = $(this).text();
			let me = this;
			$(this).text('...');


			$.post(lsdc_pub.ajax_url, {
				action: 'lsdcommerce_member_view_' + postData,
				nonce: $('#member-nonce').val(),
				postid: postID,
				postdata: postData,
				security: lsdc_pub.ajax_nonce,
			}, function (response) {
				if (response == false) {
					$( '.lsdc-ajax-response[data-post="'+ postData +'"]').html();
				} else {
					$( '.lsdc-ajax-response[data-post="'+ postData +'"]').html( response) ;
				}
				thisText
				$(me).text( thisText );
				// Cookie Remove
			}).fail(function () {
				alert('Failed, please check your internet');
			});

		});

		// DeepLinking
		let url = location.href.replace(/\/$/, "");
		let tab = $('.tabs-component');
		if( tab.length ){
			
			if (location.hash) {
				const hash 			= url.split("#"); //split url
				if( hash ){
						tab.find('input[name="tab"]').prop('checked', false); // reset
						tab.find('input[name="tab"]#'  + hash[1]).prop('checked', true); //set
						url = location.href.replace(/\/#/, "#");
						history.replaceState(null, null, url);
						setTimeout(() => {
							$(window).scrollTop(0);
						}, 400);
				}
			}
		}

		// Handle Click Tab Deeplinking
		$(document).on("click",".tabs-component label",function( e ) {
			let newUrl;
			const hash = $(this).attr("data-linking");
			if(hash == "#tab1") {
				newUrl = url.split("#")[0];
			} else {
				newUrl = url.split("#")[0] + '#' + hash;
			}
			newUrl += "/";
			history.replaceState(null, null, newUrl);
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
			$('#alert-password').removeClass('lsdp-hide');
			$('#alert-password').text('Please Input Old and New Password...');
		} else {
			if (newpassword != repeatpassword) {
				$('#alert-password').removeClass('lsdp-hide');
				$('#alert-password').text('You Repeat Password not Match...')
			} else {
				$('#alert-password').addClass('lsdp-hide');
				$(this).addClass('loading');

				$.post(lsdc_pub.ajax_url, {
					action: 'lsdcommerce_member_change_password',
					nonce: $('#member-nonce').val(),
					old: oldpassword,
					new: newpassword,
					security: lsdc_pub.ajax_nonce,
				}, function (response) {
					$(that).removeClass('loading');
					if (response == false) {
						$('#alert-password').removeClass('lsdp-hide');
						$('#alert-password').text("Your Old Password didn't match...");
					} else {
						$('#alert-password').removeClass('lsdp-hide');
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