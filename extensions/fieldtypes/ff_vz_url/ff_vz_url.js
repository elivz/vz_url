// Ajax link validator for VZ URL fieldtype
// by Eli Van Zoeren (http://elivz.com)
jQuery(document).ready(function() {
	jQuery('.vz_url_field').blur(function() {
		field = $(this);
		jQuery.get( 
			FT_URL+'ff_vz_url/proxy.php', 
			{ path: field.val() }, 
			function(response) { 
				if (response) { 
					field.next().slideUp();
				} else { 
					field.next().slideDown();
				}
			}
		);
	});
});