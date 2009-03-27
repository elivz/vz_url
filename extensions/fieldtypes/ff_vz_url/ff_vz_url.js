/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren (http://elivz.com)
 */

jQuery(document).ready(function() {
	jQuery('.vz_url_field').vzCheckUrl().blur(function() { $(this).vzCheckUrl() });
});


// jQuery plugin to check the url and display the result
(function($) {

	$.fn.vzCheckUrl = function (field) {
		return this.each(function() {
			var $this = $(this);
			var urlToCheck = $this.val();
			 alert(urlToCheck);
			// Don't bother checking the default value of http://
			if (urlToCheck == 'http://') {
				$this.css('background-image', 'none').next('.highlight').fadeOut(500);
				return;
			}
			
			// Ajax call to proxy.php to check the url
			jQuery.get( 
				FT_URL+'ff_vz_url/proxy.php', 
				{ path: urlToCheck }, 
				function (response) {
					// Show or hide the error message, as needed
					if ( response ) { 
						$this.css('background', '#fff url('+FT_URL+'ff_vz_url/valid.png) no-repeat right').next('.highlight').fadeOut(500);
					} else { 
						$this.css('background', '#fff url('+FT_URL+'ff_vz_url/invalid.png) no-repeat right').next('.highlight').fadeIn(800);
					}
				}
			);
		});
	};

})(jQuery);