/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren (http://elivz.com)
 */

// jQuery plugin to check the url and display the result
var vzUrl = {
  'init' : function() {
    jQuery('.vz_url_field')
      .after('<div class="vz_url_field_msg" />')
      .keyup(function(){ vzUrl.check_field(this) })
      .each(function(){ vzUrl.check_field(this) });
  },
  
  'check_field' : function(field) {
    // Cache the field
    vzUrl.$currentField = jQuery(field)
      		  .removeClass('empty invalid valid')
      		  .addClass('checking');
    
    // Don't bother checking the default value of http://
  	if (vzUrl.$currentField.val() == 'http://') {
  		vzUrl.$currentField
  		  .removeClass('valid invalid checking')
  		  .addClass('empty')
  		  .next('.vz_url_field_msg').fadeOut(500);
  		return;
  	} else {
  		vzUrl.$currentField.removeClass('empty');
  	}

    // Use a timeout to prevent an ajax call on every keystroke
    if (vzUrl.$timer) clearTimeout(vzUrl.$timer);
    vzUrl.$timer = setTimeout('vzUrl.ajax_call(vzUrl.$currentField)', 350);
    
    //
  },
  
  'ajax_call' : function($field) {
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
					  .next('.vz_url_field_msg').fadeOut(500);
				} else {
					$field
      		  .removeClass('empty valid checking')
      		  .addClass('invalid')
					  .next('.vz_url_field_msg').fadeIn(800);
				}
			}
		);

  }
};

jQuery(document).ready(function() {
  vzUrl.init();
  
  // Re-initialize every time a row is added
  if ($.isFunction($.fn.ffMatrix)) {
  	$.fn.ffMatrix.onDisplayCell.ff_vz_url = function(td) { 
  		vzUrl.init();
  	};
  }
})