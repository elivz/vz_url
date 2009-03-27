/*
 * Ajax link validator for VZ URL fieldtype
 * by Eli Van Zoeren (http://elivz.com)
 */

jQuery(document).ready(function() {
	jQuery('.vz_url_field').vzCheckUrl();
});


// jQuery plugin to check the url and display the result
(function($) {

	$.fn.vzCheckUrl = function (field) {
		return this.each(function() {
			var $this = $(this);
			$this.blur(function() {
				if ( checkIt($this.val()) ) { 
					$this.next().slideUp();
				} else { 
					$this.next().slideDown();
				}
			});
		});
		
	}

	function checkIt (urlToCheck) {
		jQuery.get( 
			FT_URL+'ff_vz_url/proxy.php', 
			{ path: urlToCheck }, 
			function(response) { return response; }
		);
	};

})(jQuery);