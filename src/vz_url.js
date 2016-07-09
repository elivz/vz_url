/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren - http://elivz.com
 *
 * Depends on: jQuery
 */

(($, window) => {
    function encodeUri(str) {
        return encodeURI(str).replace(/%25/g, '%').replace(/%5B/g, '[').replace(/%5D/g, ']');
    }

    function VzUrl($field) {
        this.timer = false;
        this.delay = 500;
        this.regex = /^(https?|ftp):\/\/[^\s/$.?#].[^\s]*$/i;

        // Store elements we will work with
        this.$field = $field;
        this.$wrapper = this.$field.parent();
        this.$msg = this.$field.next();

        // Import settings
        this.actionUrl = window.vzUrlSettings.actionUrl;
        this.lang = window.vzUrlSettings.lang;

        // Check URLs whenever the field changes
        this.$field.on('keyup paste', () => {
            this.checkField();
        });

        // Check existing URLs when the page loads
        this.checkField(true);
    }

    VzUrl.prototype.checkField = function checkField(immediate) {
        // Clear the timeout
        if (this.timer) {
            clearTimeout(this.timer);
        }

        // Don't bother checking if it's empty
        if (this.$field.val() === '') {
            return;
        }

        // Use a timeout to prevent an ajax call on every keystroke
        if (!immediate) {
            this.timer = setTimeout($.proxy(this.validate, this), this.delay);
        } else {
            this.validate(this.$field);
        }
    };

    /*
     * Actually send a request the the target URL to see if it exists
     */
    VzUrl.prototype.validate = function validate() {
        const url = this.$field.val();

        // Show the "spinner"
        this.setStatus('checking');

        // In-page links should always be considered valid
        if (url.charAt(0) === '#' || url.charAt(0) === '?') {
            this.setStatus('valid');
            return;
        }

        // Make sure it's even a valid url
        if (!url.match(this.regex)) {
            this.setStatus('invalid');
            return;
        }

        // Ajax call to proxy to check the url
        const safeUrl = url.replace('http', 'ht^tp'); // Mod_security doesn't like "http://" in posted data
        $.getJSON(this.actionUrl + '&callback=?', { url: safeUrl })
            .done((data) => {
                // Make sure the URL we are checking is still there
                if (data.original !== this.$field.val()) {
                    return;
                }

                // Show or hide the error message, as needed
                if (data.http_code >= 200 && data.http_code < 400) {
                    if (data.original === data.final_url) {
                        // The URL is valid
                        this.setStatus('valid');
                    } else {
                        // The URL is a redirect
                        this.setStatus('redirect', data);
                    }
                } else {
                    this.setStatus('invalid');
                }
            })
            .fail(() => {
                this.setStatus('invalid');
            });
    };

    /*
     * Set the styling and error message as needed
     */
    VzUrl.prototype.setStatus = function setStatus(status, response) {
        // Reset field
        this.$field.prev().remove();
        this.$wrapper.removeClass('empty checking invalid valid redirect');

        // Reset message
        this.$msg.empty();

        if (status === 'invalid') {
            this.$msg.text(this.lang.errorText);
            this.$wrapper.addClass('invalid');
        } else if (status === 'redirect') {
            if (this.$field.hasClass('follow-redirects')) {
                this.$wrapper.addClass('warning');
                this.$msg.text(`${this.lang.redirectText} ${response.final_url} `);
                $('<a/>', {
                    text: this.lang.redirectUpdate,
                    href: '',
                    class: 'btn action',
                    click: (event) => {
                        // Replace the field value with the redirect target
                        this.$field.val(response.final_url);
                        this.validate(this.$field);
                        event.preventDefault();
                    },
                }).appendTo(this.$msg);
            } else {
                status = 'valid';
            }
        }

        this.$wrapper.addClass(status);

        // Add a "Open Page link"
        if (status !== 'empty' && status !== 'checking') {
            const $visitLink = $('<a/>', {
                href: this.$field.val(),
                class: 'vzurl-visit',
                target: '_blank',
                title: `${this.lang.openText}: ${this.$field.val()}`,
            });
            this.$field.before($visitLink);
        }
    };

    // Export the main function
    window.VzUrl = VzUrl;

    // Automatically instantiate new grid fields
    window.Grid.bind('vz_url', 'display', (cell) => {
        const $field = cell.find('.vzurl-field');
        new VzUrl($field);
    });
})(window.jQuery, window);
