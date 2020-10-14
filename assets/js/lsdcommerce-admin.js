function validateEmail(email) {
	var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(String(email).toLowerCase());
}

(function( $ ) {
	'use strict';

	$(function() {
		$('.lsdc-color-picker').wpColorPicker();
		// $(".lsd-email-picker").wpColorPicker({
		// 	change: function (event, ui) {
		// 		var element = event.target;
		// 		var color = ui.color.toString();
		// 		var type = $(element).attr('data-type');
		// 		$('#lsd-editor-' + type ).find('table[role="presentation"]:first').css('background', color );
		// 	}
		// });
	});
	//=============== Admin - General ===============//
	$(document).on('keyup', 'input.currency', function( event ){
		// skip for arrow keys
		if(event.which >= 37 && event.which <= 40) return;

		let separator = ".";
		if( lsdc_adm.currency == 'USD' ) separator = ",";
		
		// currency_validate
		$(this).val(function(index, value) {
			return value
			.replace(/\D/g, "")
			.replace(/^0+/, '') // Removing Leading by Zero
			.replace(/\B(?=(\d{3})+(?!\d))/g, separator);
		});
	});

	// DeepLink Tab
	$( document ).ready(function() {

		let url = location.href.replace(/\/$/, "");
		if (location.hash) {
			const hash 			= url.split("#"); //split url
			const querystring 	= url.split("tab=");
			
			if( querystring ){
				let indentify = querystring.pop().split('#')[0];
				if( indentify  ){
					$( '#' + indentify ).find('input[name="sections"]').prop('checked', false); // reset
					$( '#' + indentify ).find('input[name="sections"]#'  + hash[1]).prop('checked', true); //set
					url = location.href.replace(/\/#/, "#");
					history.replaceState(null, null, url);
					setTimeout(() => {
						$(window).scrollTop(0);
					}, 400);
				}
			}
		
		}else{ // Set Default Tab
			if( url.split("tab=")[1] ){
				$( '#' + url.split("tab=")[1] + '.verticaltab' ).find('input:first').prop('checked', true); // reset
			}
		}

		// Handle Click Tab Deeplinking
		$(document).on("click",".verticaltab .tablabel",function( e ) {
			let newUrl;
			const hash = $(this).attr("data-linking");
			if(hash == "#home") {
				newUrl = url.split("#")[0];
			} else {
				newUrl = url.split("#")[0] + '#' + hash;
			}
			newUrl += "/";
			history.replaceState(null, null, newUrl);
		});

	});
	
	//=============== Admin - Global Save ===============//

	$(document).on("click",".lsdc-admin-save",function( e ) {
		e.preventDefault();
		$(this).addClass('loading');
		
		var that = this;

		$.post( lsdc_adm.ajax_url, { 
			action 		: 'lsdc_admin_save',
			id			: $(this).attr('id'),
			settings 	: $(this).closest('.form-horizontal').find( "form" ).serialize(),
			security 	: lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					$(that).removeClass('loading');
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});

	//=============== Admin - Store ===============//

	$(document).on("change","#country",function( e ) {
	
		$.get( lsdc_adm.plugin_url + 'core/cache/' + $(this).val() + '-states.json', function(data, status){
			// alert("Data: " + data + "\nStatus: " + status);
			console.log( data );
			$("#states option").remove();
			$("#cities option").remove();
			$.each(data, function(i , value) {
				var option = $('<option value="'+ value.province_id +'">'+ value.province +'</option>');
				$("#states").append(option);
		   });

		});
	});

	$(document).on("change","#states",function( e ) {
		$.get( lsdc_adm.plugin_url + 'core/cache/' + $('#country').find(":selected").val() + '-cities.json', function(data, status){
			$("#cities option").remove();
			$.each(data, function(i , value) {
				if( $('#states').find(":selected").val() == value.province_id ){
					var option = $('<option value="'+ value.city_id +'">'+ value.type + ' ' + value.city_name +'</option>');
					$("#cities").append(option);
				}
			});
		});
	});

	$(document).on("click","#lsdc_admin_store_save",function( e ) {
		$(this).addClass('loading');
		var that = this;
		
		let store = {};
		store['country'] 	= $('#country').find(":selected").val();
		store['state'] 		= $('#states').find(":selected").val();
		store['city'] 		= $('#cities').find(":selected").val();
		store['address'] 	= $('#address').val();
		store['postalcode']	= $('#postalcode').val();
		store['currency'] 	= $('#currency').find(":selected").val();

		$.post( lsdc_adm.ajax_url, { 
			action : 'lsdc_admin_store_save',
			store : store,
			security : lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					$(that).removeClass('loading');
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});

	//=============== Admin - Appearance ===============//

	$(document).on("click","#lsdc_admin_appearance_save",function( e ) {
		$(this).addClass('loading');
		var that = this;

		$.post( lsdc_adm.ajax_url, { 
			action 		: 'lsdc_admin_appearance_save',
			appearance 	: $("#appearance form" ).serialize(),
			security 	: lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					$(that).removeClass('loading');
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});


	/* LSDCOmmerce > Payments > Change Status */
	$(document).on("change",".lsdc-payment-change-status",function( e ) {
		// Passing ID and State ( On or OFF )
		$.post( lsdc_adm.ajax_url, { 
			action 	: 'lsdc_admin_payment_status',
			id 		: $(this).find('input[type="checkbox"]').attr('id'),
			state 	: $(this).find('input[type="checkbox"]').is(":checked") ? 'on' : 'off',
			security : lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					// give feedback
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);

	});

	/* LSDCOmmerce > Payments > Display Panel Manage */
	$(document).on("click",".lsdc-payment-manage",function( e ) {
		let method_id = $(this).attr('id');
		if ( $('form#' +  method_id + '_form' ).length == 0 ){ // Checking Cache DOM
			let html  = $( '#' + method_id + '_content' ).html();

			// Manipulate InnerHTML
			var $html = $('<div />',{html:html });
			$html.find('form').attr("id", method_id + '_form' ); // Change ID
			$('#payment-editor').html( $html.html() );
		}

		$('#payment-editor').closest('div.column').show();
		$('#payment-editor').closest('div.column').css('z-index','9999');
	});

	/* LSDCOmmerce > Payments > Close Panel Manage */
	$(document).on("click",".panel-close",function( e ) {
		$('#payment-editor').closest('div.column').hide();
		$('#payment-editor').closest('div.column').css('z-index','0');
		$('#payment-editor').html('');
	});

	/* LSDCOmmerce > Payments > Save Manage */
	$(document).on("click",".lsdc-payment-save",function( e ) {
		e.preventDefault();
		
		$(this).addClass('loading');
		var that		= this;
		var serialize 	= $(this).closest('#payment-editor').find('.panel-body form').serialize();
		var id 			= $(this).attr('id').replace('_payment', '');
		var method		= $(this).attr('method');
		
		$.post( lsdc_adm.ajax_url, { 
			action 		: 'lsdcommerce_payment_option',
			method		: method,
			id	 		: id,
			serialize 	: serialize,
			security 	: lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					$(that).removeClass('loading');
					$('#payment-editor').closest('div.column').hide();
					$('#payment-editor').closest('div.column').css('z-index','1');
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});

	// Handle Upload Image
	var file_frame;
	var attachment;
	$(document).on("click",".lsdc_admin_upload",function( event ) {
		event.preventDefault();
		var that = this;
		var frame = file_frame;
		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media.frames.frame = wp.media({
			// title: 'Upload Image',
			// 	button: {
			// 	text: 'Choose Image'
			// },
			multiple: false 
		});

		frame.on( 'select', function() {
			attachment = frame.state().get('selection').first().toJSON();

			let imagepreview = $(that).prev();
			$(imagepreview).attr('data-id', attachment.id);
			$(imagepreview).attr('src', attachment.url);
		});

		frame.open();
	});

	//=============== Admin - Notification ===============//
	// Enabled
	$(document).on("change",".lsdc-notification-status",function( e ) {

		let id = $(this).find('input[type="checkbox"]').attr('id');
		let state = ( $(this).find('input[type="checkbox"]').is(":checked") ) ? 'on' : 'off';

		$.post( lsdc_adm.ajax_url, { 
			action : 'lsdc_admin_notification_status',
			id : id,
			state : state,
			security : lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					// give feedback
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);

	});

	//=============== Admin - Shipping ===============//
	// Enabled
	$(document).on("change",".lsdc-shipping-status",function( e ) {

		let id = $(this).find('input[type="checkbox"]').attr('id');
		let state = ( $(this).find('input[type="checkbox"]').is(":checked") ) ? 'on' : 'off';

		$.post( lsdc_adm.ajax_url, { 
			action : 'lsdc_admin_shipping_status',
			id : id,
			state : state,
			security : lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					// give feedback
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);

	});
	
	//=============== Admin - Settings ===============//

	$(document).on("click","#lsdc_admin_settings_save",function( e ) {
		e.preventDefault();
		$(this).addClass('loading');
		var that = this;

		$.post( lsdc_adm.ajax_url, { 
			action 		: 'lsdc_admin_settings_save',
			settings 	: $("#settings form" ).serialize(),
			security 	: lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					$(that).removeClass('loading');
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});

	// ============= Admin - General Save Settings ============= //
	$(document).on("click",".lsdc_admin_option_save",function( e ) {
		e.preventDefault();
		$(this).addClass('loading');
		var that = this;
		var option = $(this).attr('option');
		var data = $(this).closest('form').serialize();
		var block = $(this).closest('form').attr('block');
	
		$.post( lsdc_adm.ajax_url, { 
			action 		: 'lsdc_admin_option_save',
			option		: option,
			settings 	: data,
			block		: block,
			security 	: lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					$(that).removeClass('loading');
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});
		

	// Metabox Handle
	$(document).on("click",".lsdc-action-button",function( e ) {
		e.preventDefault();
		$(this).addClass('loading');

		$.post( lsdc_adm.ajax_url, { 
			action 		: 'lsdc_admin_order_action',
			data		: $(this).attr('data-action'),
			orderid		: $(this).attr('data-id'),
			security 	: lsdc_adm.ajax_nonce,
			}, function( response ){
				if( response.trim() == 'action_success' ){
					location.reload();
				}
			}).fail(function(){
				alert('Failed, please check your internet');
			}
		);
	});

	

})( jQuery );
