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
			
			displayResult($this, checkIt($this.val()));
			$this.blur(function() { displayResult($this, checkIt($this.val())) });
		});
		
	};
	
	// Ajax call to proxy.php to check the url
	function checkIt (urlToCheck) {
		if (urlToCheck == 'http://') return true;
		
		jQuery.get( 
			FT_URL+'ff_vz_url/proxy.php', 
			{ path: urlToCheck }, 
			function(response) { return response; }
		);
	};
	
	// Modify the field to show the validity of the url
	function displayResult (field, result) {
		$field = $(field);
	
		if ( result ) { 
			$field.css('background', '#fff url('+FT_URL+'ff_vz_url/valid.png) no-repeat right').next('.highlight').fadeOut(500);
		} else { 
			$field.css('background', '#fff url('+FT_URL+'ff_vz_url/invalid.png) no-repeat right').next('.highlight').fadeIn(800);
		}
	};

})(jQuery);