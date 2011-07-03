/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren - http://elivz.com
 *
 * Depends on: jQuery
 */

var vzUrl = {
    'init' : function() {
        // Initialize the fields that are there on page-load
        vzUrl.setup('.vz_url_field');
        
        // Re-initialize every time a row is added
        if (typeof(Matrix) != 'undefined') {
            Matrix.bind(
                'vz_url',
                'display',
                function(cell) {
                    vzUrl.setup(cell.dom.$inputs);
                }
            );
        }
    },
	
	/*
	 * Set up the VZ URL fields with the styling and events they need to function
	 */
    'setup' : function(fields) {
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
            
            // Check the initial value
            vzUrl.check_field($field);
        });
    },
  
    /*
     * Event handler for changes to the field
     */
    'check_field' : function(field, delay) {
        $field = jQuery(field);
        
        // Clear the timeout
        if (vzUrl.$timer && delay) clearTimeout(vzUrl.$timer);
        
        // Show the "spinner"
        vzUrl.set_status($field, 'checking');
        
        // Don't bother checking the default value of http://
        if ($field.val() == 'http://' || $field.val() == '') {
            vzUrl.set_status($field, 'empty');
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
  
    /*
     * Actually send a request the the target URL to see if it exists
     */
    'ajax_call' : function($field) {
        var url = $field.val();
        
        // Make sure it's even a valid url
        if (!url.match(/^((https?|ftp):\/\/[\w\-_]+(\.[\w\-_]+)+)?([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?$/gi)) {
        	vzUrl.set_status($field, 'invalid');
        	return false;
        }
        
        // If it needs to be a local url, see that it is
        if ($field.hasClass('local') && url.substr(0, 1) != '/' && url.indexOf(document.domain) == -1) {
        	vzUrl.set_status($field, 'nonlocal');
        	return false;
        }
        
        // Ajax call to proxy.php to check the url
        jQuery.getJSON( 
        	vzUrl.proxyUrl + '?callback=?', 
        	{ url: url }, 
        	function (data) {
                // Make sure the URL we are checking is still there
                if (data.original != url) return;
                
                // Show or hide the error message, as needed
                if ((data.original == data.final) && (data.http_code >= 200) && (data.http_code < 400)) {
                    // The URL is valid
                    vzUrl.set_status($field, 'valid');
                } else if (data.original != data.final) {
                    // The URL is a redirect
                    vzUrl.set_status($field, 'redirect', data);
                } else {
                    vzUrl.set_status($field, 'invalid');
                }
        	}
        );
    },
  
    /*
     * Set the styling and error message as needed
     */
    'set_status' : function($field, status, response) {
        $msg = $field.next('.vz_url_msg');
        
        $field
            .removeClass('empty checking invalid valid redirect nonlocal')
            .addClass(status);
        
        switch (status) {
            case 'empty' : case 'checking' : case 'valid' :
            	$msg.slideUp(200);
            	break;
            case 'invalid' :
            	$msg
                	.html(vzUrl.errorText)
                	.slideDown(800);
                break;
            case 'nonlocal' :
            	$msg
                	.html(vzUrl.nonlocalText)
                	.slideDown(800);
                break;
            case 'redirect' :
                var msgText = vzUrl.redirectText
                	.replace(/{{old_url}}/g, response.original)
                	.replace(/{{new_url}}/g, response.final)
                	.replace(/{{update="(.+?)"}}/g, '<a href="#" class="update_url">$1</a>');
                
                $msg
                	.html(msgText)
                	.slideDown(800)
                	.children('.update_url')
                        .click(function() {
                    	  	// Replace the field value with the redirect target
                    	    $field.val(response.final);
                    	    vzUrl.ajax_call($field);
                    	    return false;
                		});
                break;
        }	
    }
  
};