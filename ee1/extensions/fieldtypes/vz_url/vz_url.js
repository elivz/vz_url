/*
 * Ajax link validator for VZ Url fieldtype
 * by Eli Van Zoeren - http://elivz.com
 *
 * Depends on: jQuery
 *
 */

var vzUrl = {

  'init' : function(fields) {
    jQuery(fields).each(function() {
      var $field = jQuery(this);
      
      // Add a positioned wrapper for placing the message
      $field.wrap('<div style="position:relative" />');
      
      // Create a holder for the error message
      jQuery('<label class="vz_url_msg" for="' + $field.attr('id') + '"></label>')
        .hide()
      	.insertAfter($field)
        .click(function() {
        	// Hide on click
          jQuery(this).slideUp(500);
        });
      
      // Seup event handlers
      $field.keyup(function() {
      	vzUrl.check_field(this, true);
      });
      
      // Check it
      vzUrl.check_field($field);
    });
  },
  
  'check_field' : function(field, delay) {
    // Clear the timeout
    if (vzUrl.$timer && delay) clearTimeout(vzUrl.$timer);
    
    // Cache the field
    var $field = jQuery(field)
  	  .removeClass('empty invalid valid')
  	  .addClass('checking');
  	  
    // Hide the message box
    $field.next('.vz_url_msg').slideUp(200);
    
    // Don't bother checking the default value of http://
  	if ($field.val() == 'http://' || $field.val() == '') {
  		$field
  		  .removeClass('valid invalid checking')
  		  .addClass('empty')
  		  .next('.vz_url_msg')
  		  	.slideUp(200);
  		return;
  	} else {
  		$field.removeClass('empty');
  	}

    if (delay) {
      // Use a timeout to prevent an ajax call on every keystroke
      vzUrl.$timer = setTimeout(function(){ vzUrl.ajax_call($field) }, 350);
    } else {
      vzUrl.ajax_call($field)
    }
  },
  
  'ajax_call' : function($field) {
    // Make sure it's even a valid url
    if (!$field.val().match(/^(https?|ftp):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?$/gi)) {
		  $field
  		  .removeClass('empty valid checking')
  		  .addClass('invalid')
			  .next('.vz_url_msg')
		    	.html(vzUrl.errorText)
		    	.slideDown(800);
		    return false;
		}
		
		// Ajax call to proxy.php to check the url
		jQuery.getJSON( 
			FT_URL + 'vz_url/proxy.php?callback=?', 
			{ path: $field.val() }, 
			function (data) {
		    // Make sure the url we are checking is still there
		    if (data.original != $field.val()) return;
		    
				// Show or hide the error message, as needed
				if ((data.original == data.final) && (data.http_code >= 200) && (data.http_code < 400)) {
				  // The url is valid
					$field
      		  .removeClass('empty invalid checking')
      		  .addClass('valid')
					  .next('.vz_url_msg')
					  	.slideUp(200);
				} else if (data.original != data.final) {
				  // The url is a redirect
				  var msg = vzUrl.redirectText
				  	.replace(/{{old_url}}/g, data.original)
				  	.replace(/{{new_url}}/g,data.final)
				  	.replace(/{{update="(.+?)"}}/g, '<a href="#" class="update_url">$1</a>');
					$field
      		  .removeClass('empty invalid checking')
      		  .addClass('valid')
					  .next('.vz_url_msg')
			        .html(msg)
			        .slideDown(800)
		          .children('.update_url')
			          .click(function() { 
			            $field
			              .val(data.final)
			              .next('.vz_url_msg')
			              	.slideUp(200);
			            vzUrl.ajax_call($field);
			            return false;
			          });
				} else {
				  // Invalid
					$field
      		  .removeClass('empty valid checking')
      		  .addClass('invalid')
					  .next('.vz_url_msg')
			        .html(vzUrl.errorText)
			        .slideDown(800);
				}
			}
		);
  }
};

jQuery(document).ready(function() {
	// Initialize the fields that are there on page-load
  vzUrl.init('.vz_url_field');
  
  // Re-initialize every time a row is added
  if (typeof(Matrix) != 'undefined') {
	  Matrix.bind(
	  	'vz_url',
	  	'display',
	  	function(cell) {
	  		vzUrl.init(cell.dom.$inputs);
	  	}
	  );
  }
});