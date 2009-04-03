/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren (http://elivz.com)
 */

jQuery(document).ready(function() {
	jQuery('.vz_url_field').vzCheckUrl();
	
	// Hook into the FF Matrix onDisplayCell event
	$.fn.ffMatrix.onDisplayCell['ff_vz_url'] = function($td) { 
		$td.children(':input').vzCheckUrl();
	};
});


// jQuery plugin to check the url and display the result
(function($) {

	$.fn.vzCheckUrl = function (field) {
		return this.each(function() {
			// Bind to the check function
			$(this).keyup(function() { vzCheck(this); });
			
			// Initial check
			vzCheck(this);
		});
	};

	vzCheck = function(field) {
		var $this = $(field);
		var urlToCheck = $this.val();
		
		// Don't bother checking the default value of http://
		if (urlToCheck == 'http://') {
			$this.css('background-image', 'none').next('.highlight').fadeOut(500);
			return;
		}
		
		// Clear the timer
		if (this.timer) clearTimeout(this.timer);
		
		// Set a timer so we don't check after every keypress
		this.timer = setTimeout(function () {
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
		}, 200);
	};

})(jQuery);