/* Social Venues for Events Calendar Pro: Admin UI */
jQuery(document).ready(function ($) {

	$('.svecp-add').click( function() {

		// Add new services form fields
		$('#svecp-services').append(wp_data['network_template']);

		// Register click event on new delete link
		$('.svecp-delete').click( function() {
			$(this).parent().parent().remove();
			return false;
		});

		return false;
	});

	// Register click event on delete links displayed at page load
	$('.svecp-delete').click( function() {
		$(this).parent().parent().remove();
		return false;
	});

});