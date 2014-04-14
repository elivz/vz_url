/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren - http://elivz.com
 *
 * Depends on: jQuery
 */

var vzUrl = {
    regex : new RegExp("^((https?|ftp)://[\\w\\-_]+(\\.[\\w\\-_]+)+|/)([\\w\\-\\.,@?^=%&amp;:/~\\+#]*[\\w\\-\\@?^=%!&amp;/~\\+#])?$", "gi"),

    /*
     * Set up the VZ URL fields with the styling and events they need to function
     */
    init : function(fields) {
        $('#publishForm')
            // Check URLs whenever the field changes
            .on('keyup paste', '.vz_url_field', function(e) {
                vzUrl.check_field.call(e.target, true);
            });

        // Check existing URLs when the page loads
        $('.vz_url_field').each(vzUrl.check_field);

        // Extra initializer for Grid fields
        if (typeof Grid !== 'undefined') {
            Grid.bind('vz_url', 'display', function(cell) {
                vzUrl.check_field.call(cell.find('input'));
            });
        }

        // Extra initializer for Matrix fields
        if (typeof Matrix !== 'undefined') {
            Matrix.bind('vz_url', 'display', function(cell) {
                vzUrl.check_field.call(this.find('input'));
            });
        }

    },

    /*
     * Event handler for changes to the field
     */
    check_field : function(delay) {
        $field = $(this);

        // Clear the timeout
        if (delay && vzUrl.timer) clearTimeout(vzUrl.timer);

        // Don't bother checking the default value of http://
        if ($field.val() === 'http://' || $field.val() === '') {
            vzUrl.set_status($field, 'empty');
            return;
        } else {
            // Show the "spinner"
            vzUrl.set_status($field, 'checking');
        }

        // Use a timeout to prevent an ajax call on every keystroke
        if (delay) {
            vzUrl.timer = setTimeout(function() {
                vzUrl.validate($field);
            }, 500);
        } else {
            vzUrl.validate($field);
        }
    },

    /*
     * Actually send a request the the target URL to see if it exists
     */
    validate : function($field) {
        var url = vzUrl.encode_uri($field.val());

        // Make sure it's even a valid url
        if (!url.match(vzUrl.regex)) {
            vzUrl.set_status($field, 'invalid');
            return;
        }

        // If it needs to be a local url, see that it is
        if ($field.hasClass('local') && url.substr(0, 1) != '/' && url.indexOf(document.domain) == -1) {
            vzUrl.set_status($field, 'nonlocal');
            return;
        }

        // Ajax call to proxy to check the url
        var safeurl = url.replace('http', ''); // Mod_security doesn't like "http://" in posted data
        $.getJSON(
            window.location.href.split('?')[0] + '?callback=?',
            {
                caller: 'vz_url',
                url: safeurl
            },
            function (data) {
                // Make sure the URL we are checking is still there
                if (data.original != vzUrl.encode_uri($field.val())) return;

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
        // Reset class
        $field.removeClass('empty checking invalid valid nonlocal redirect');
        $field.prev().remove();

        var $msg = $field.next().empty();

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
                            vzUrl.validate($field);
                            return false;
                        }
                    }).appendTo($msg);
                } else {
                    $field.removeClass('redirect').addClass('valid');
                }
                break;
        }

        $field.addClass(status);

        // Add a "Open Page link"
        if (status === 'valid' || status === 'redirect') {
            $field.before('<a href="'+$field.val()+'" class="vz_url_visit" target="_blank">' + vzUrl_settings.openText + '</a>');
        }
    },

    encode_uri : function(str) {
        return encodeURI(str).replace(/%25/g, '%').replace(/%5B/g, '[').replace(/%5D/g, ']');
    }
};

vzUrl.init();