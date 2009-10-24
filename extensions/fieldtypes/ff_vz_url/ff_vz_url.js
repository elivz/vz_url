/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren (http://elivz.com)
 */

// jQuery plugin to check the url and display the result
var vzUrl = {
  'init' : function() {
    jQuery('.vz_url_field').each(function() {
      // Cache the field
      var $field = $(this);
      
      // Make sure it isn't already set up
      if ($field.next('.vz_url_msg').length > 0) return;
      
      $field
        .wrap('<div class="vz_url_wrapper" />')
        .after('<div class="vz_url_msg"><p>'+vzUrl.errorText+'</p></div>');
      $field.next('.vz_url_msg')
        .hide()
        .click(function() {
          $(this).fadeOut(500);
        });
      
      // Seup event handlers
      $field.keyup(function(){ vzUrl.check_field(this) })
      
      // Check it
      vzUrl.check_field($field);
    });
  },
  
  'check_field' : function(field) {
    // Cache the field
    var $field = jQuery(field)
      		  .removeClass('empty invalid valid')
      		  .addClass('checking');
    
    // Don't bother checking the default value of http://
  	if ($field.val() == 'http://') {
  		$field
  		  .removeClass('valid invalid checking')
  		  .addClass('empty')
  		  .next('.vz_url_msg').fadeOut(500);
  		return;
  	} else {
  		$field.removeClass('empty');
  	}

    // Use a timeout to prevent an ajax call on every keystroke
    if (vzUrl.$timer) clearTimeout(vzUrl.$timer);
    vzUrl.$timer = setTimeout('vzUrl.ajax_call("'+$field.attr('id')+'")', 350);
  },
  
  'ajax_call' : function(field) {
    // Cache the field and the url
    var $field = jQuery('#'+field);
    var urlToCheck = $field.val();
    
		// Ajax call to proxy.php to check the url
		jQuery.get( 
			FT_URL+'ff_vz_url/proxy.php', 
			{ path: urlToCheck }, 
			function (response) {
				// Show or hide the error message, as needed
				if ( response ) {
					$field
      		  .removeClass('empty invalid checking')
      		  .addClass('valid')
					  .next('.vz_url_msg').fadeOut(500);
				} else {
					$field
      		  .removeClass('empty valid checking')
      		  .addClass('invalid')
					  .next('.vz_url_msg').fadeIn(800);
				}
			}
		);

  }
};

jQuery(document).ready(function() {
  vzUrl.init();
  
  // Re-initialize every time a row is added
  if (jQuery.isFunction(jQuery.fn.ffMatrix)) {
  	jQuery.fn.ffMatrix.onDisplayCell.ff_vz_url = function(td) {
      vzUrl.init();
  	};
  }
})