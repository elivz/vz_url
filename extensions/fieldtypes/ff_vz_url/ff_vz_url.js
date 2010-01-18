/*
 * Ajax link validator for VZ Url fieldtype
 * by Eli Van Zoeren - http://elivz.com
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
        .after('<div class="vz_url_msg"><p></p></div>');
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
    // Clear the timeout
    if (vzUrl.$timer) clearTimeout(vzUrl.$timer);
    
    // Cache the field
    var $field = jQuery(field)
  	  .removeClass('empty invalid valid')
  	  .addClass('checking');
  	  
    // Hide the message box
    $field.next('.vz_url_msg').fadeOut(200);
    
    // Don't bother checking the default value of http://
  	if ($field.val() == 'http://' || $field.val() == '') {
  		$field
  		  .removeClass('valid invalid checking')
  		  .addClass('empty')
  		  .next('.vz_url_msg').fadeOut(200);
  		return;
  	} else {
  		$field.removeClass('empty');
  	}

    // Use a timeout to prevent an ajax call on every keystroke
    vzUrl.$timer = setTimeout(function(){ vzUrl.ajax_call($field) }, 350);
  },
  
  'ajax_call' : function($field) {
    // Make sure it's even a valid url
    if (!$field.val().match(/^(https?|ftp):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?$/gi)) {
		  $field
  		  .removeClass('empty valid checking')
  		  .addClass('invalid')
			  .next('.vz_url_msg')
		      .children('p')
		        .html(vzUrl.errorText)
		      .parent()
		        .fadeIn(800);
		    return false;
		}
		
		// Ajax call to proxy.php to check the url
		jQuery.getJSON( 
			FT_URL + 'ff_vz_url/proxy.php?callback=?', 
			{ path: $field.val() }, 
			function (data) {
		    // Make sure the url we are checking is still there
		    if (data.original != $field.val()) return;
		    
				// Show or hide the error message, as needed
				if ((data.original == data.final) && (data.http_code >= 200) && (data.http_code < 300)) {
				  // The url is valid
					$field
      		  .removeClass('empty invalid checking')
      		  .addClass('valid')
					  .next('.vz_url_msg').fadeOut(200);
				} else if (data.original != data.final) {
				  // The url is a redirect
					$field
      		  .removeClass('empty valid checking')
      		  .addClass('invalid')
					  .next('.vz_url_msg')
				      .children('p')
				        .html('The url '+data.original+' forwards to '+data.final+'.<br/><a href="#">Update to the new url</a>')
				          .children('a').click(function() { 
				            $field
				              .val(data.final)
				              .next('.vz_url_msg').fadeOut(200);
				            vzUrl.ajax_call($field);
				            return false;
				          })
				        .parent()
				      .parent()
				        .fadeIn(800);
				} else {
				  // Invalid
					$field
      		  .removeClass('empty valid checking')
      		  .addClass('invalid')
					  .next('.vz_url_msg')
				      .children('p')
				        .html(vzUrl.errorText)
				      .parent()
				        .fadeIn(800);
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