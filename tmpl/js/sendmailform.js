/**
 * 
 */
jQuery( document ).ready(function () {
	jQuery("#emailForm").on( "submit", function() {
//		alert("Ку-Ку!");
		mod_mailform_sendMail();
		return false;
	});
})