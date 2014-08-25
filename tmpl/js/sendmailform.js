/**
 * 
 */
jQuery( document ).ready(function () {
	jQuery("#emailForm").on( "submit", function() {
		form_data = jQuery("#emailForm").serialize();
		mod_mailform_sendMail(form_data);
		return false;
	});
})