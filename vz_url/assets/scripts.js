/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren - http://elivz.com
 *
 * Depends on: jQuery
 */
jQuery(function($) {

var vzUrl = {
    regex : new RegExp(/\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/i),

	/*
     * Set up the VZ URL fields with the styling and events they need to function
     */
    init : function(fields) {
        $('.vz_url_field')
            .each(vzUrl.check_field)
            .live('keyup', vzUrl.check_field)
            .live('paste', function(e) {
                setTimeout(function() {
                    vzUrl.check_field.call(e.target);
                }, 0);
            });
    },
  
    /*
     * Event handler for changes to the field
     */
    check_field : function(e) {
        $field = $(this);
        
        // Clear the timeout
        if (vzUrl.timer) clearTimeout(vzUrl.timer);
        
        // Show the "spinner"
        vzUrl.set_status($field, 'checking');
        
        // Don't bother checking the default value of http://
        if ($field.val() === 'http://' || $field.val() === '') {
            vzUrl.set_status($field, 'empty');
            return;
        } else {
            $field.removeClass('empty');
        }
        
        // Use a timeout to prevent an ajax call on every keystroke
        vzUrl.timer = setTimeout(function() {
            vzUrl.ajax_call($field);
        }, 350);
    },
  
    /*
     * Actually send a request the the target URL to see if it exists
     */
    ajax_call : function($field) {
        var url = $field.val();
        
        // Make sure it's even a valid url
        if (!vzUrl.regex.test(url)) {
            vzUrl.set_status($field, 'invalid');
            return false;
        }
        
        // If it needs to be a local url, see that it is
        if ($field.hasClass('local') && url.substr(0, 1) != '/' && url.indexOf(document.domain) == -1) {
            vzUrl.set_status($field, 'nonlocal');
            return false;
        }
        
        // Ajax call to proxy to check the url
        $.getJSON(
            EE.BASE + '&callback=?',
            {
                caller: 'vz_url',
                url: url
            },
            function (data) {
                // Make sure the URL we are checking is still there
                if (data.original != url) return;
                
                // Show or hide the error message, as needed
                if ((data.original == data.final_url) && (data.http_code >= 200) && (data.http_code < 400)) {
                    // The URL is valid
                    vzUrl.set_status($field, 'valid');
                } else if (data.original != data.final_url) {
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
    set_status : function($field, status, response) {
        var $msg = $field.next().empty();
        
        // Reset class
        $field.removeClass('empty checking invalid valid nonlocal redirect').addClass(status);
        
        switch (status) {
            case 'empty' : case 'checking' : case 'valid' :
                break;
            case 'invalid' :
                $msg.html(vzUrl_settings.errorText);
                break;
            case 'nonlocal' :
                $msg.html(vzUrl_settings.nonlocalText);
                break;
            case 'redirect' :
                if ($field.hasClass('show_redirect')) {
                    $msg.html(vzUrl_settings.redirectText + ' ' + response.final_url);
                    $('<a/>', {
                        text: vzUrl_settings.redirectUpdate,
                        href: '#',
                        'data-final_url': response.final_url,
                        click: function(e) {
                            // Replace the field value with the redirect target
                            $field.val( $(this).attr('data-final_url') );
                            vzUrl.ajax_call($field);
                            return false;
                        }
                    }).appendTo($msg);
                } else {
                    $field.removeClass('redirect').addClass('valid');
                }
                break;
        }
    },

    /*
     * Test for native browser support of url validation
     */
    not_natively_supported : (function() {
        var input_element = document.createElement('input');
        return !('pattern' in input_element);
    })()
  
};

vzUrl.init();

});