/*
	Admin Settings Page Scripts
*/
(function($) {
    //upload script
    if(typeof(wp) == "undefined" || typeof(wp.media) != "function"){
        return;
    }
	var _custom_media = true,
	  _orig_send_attachment = wp.media.editor.send.attachment;

	$('#background_image_button').click(function(e) {
	var send_attachment_bkp = wp.media.editor.send.attachment;
	var button = $(this);
	var id = button.attr('id').replace('_button', '');
	_custom_media = true;
	wp.media.editor.send.attachment = function(props, attachment){
	  if ( _custom_media ) {
	    $( "#"+ id ).val(attachment.url);
	    $( "#"+ id + '_id').val(attachment.id);
	    $( "#fortyfourwp_bg_img" ).html('<img src="'+ attachment.url +'" style="width:100%;">');
	    button.hide();
	    $('#background_image_remove').show();
	  } else {
	    return _orig_send_attachment.apply( this, [props, attachment] );
	  };
	}

	wp.media.editor.open(button);
	return false;
	});

	$('.add_media').on('click', function(){
	_custom_media = false;
	});

	$('#background_image_remove').on('click',function(){
		$('#background_image').val('');
		$("#fortyfourwp_bg_img").html('');
		$(this).hide();
		$('#background_image_button').show();
	});
	//layout selection
	$('.fortyfourwp-layout-selector label').on('click',function(){
		$('.fortyfourwp-layout-selector label').removeClass('selected');
		$(this).addClass('selected');
	});

	//color picker
	$('.fortyfourwp-color-field').wpColorPicker();

  $(document).on( 'click', '.fortyfourwp_add-redirect a', function(e){
  	// alert( fortyfourwp_vars.ajaxurl );
  	if( $(this).hasClass('fortyfourwp_save') ){
  		var dis = $(this);
  		var selected = 301;
  		var data = {
                action: 'fortyfourwp_saveredirect',
                id: $(this).parent('td').parent('.fortyfourwp_add-redirect').find('#fortyfourwp-redirect-id').val(),
                redirect: $(this).parent('td').parent('.fortyfourwp_add-redirect').find('#fortyfourwp-redirect-url').val(),
                type: $(this).parent('td').parent('.fortyfourwp_add-redirect').find('#fortyfourwp-redirect-type').val(),
                url: $(this).parent('td').parent('.fortyfourwp_add-redirect').parent('tbody').parent('.fortyfourwp-stats-head').find('.fortyfourwp-v-url').attr('data-url')
            };

            $.post( fortyfourwp_vars.ajaxurl , data, function(response) {
                if(response){
                	selected = dis.parent('td').parent('.fortyfourwp_add-redirect').find('#fortyfourwp-redirect-type :selected').val();
                	if( selected.length < 1 ){
                		selected = 301;
                	}
                	console.log( selected );
                	dis.parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-redirect span i').html( selected );
                    dis.parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-redirect select').hide();
			  		dis.parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-redirect span').show();
			  		dis.parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-save').hide();
			  		dis.parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-add').show();
				    dis.parent('td').parent('.fortyfourwp_add-redirect').find('#fortyfourwp-redirect-url').attr( 'readonly', 'readonly' );
			  		dis.removeClass('fortyfourwp_save');
                }
                else{
                    return false;
                }
            });
        	// alert( r );
  		
  	}else{
  		$(this).addClass('fortyfourwp_save');
  		$(this).parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-redirect span').hide();
	  	$(this).parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-redirect select').show();
	  	$(this).parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-add').hide();
	    $(this).parent('td').parent('.fortyfourwp_add-redirect').find('.fortyfourwp-save').show();
	    $(this).parent('td').parent('.fortyfourwp_add-redirect').find('#fortyfourwp-redirect-url').removeAttr( 'readonly' ).focus();
  	}
    e.preventDefault();
    e.stopPropagation();
  } );

})(jQuery);
