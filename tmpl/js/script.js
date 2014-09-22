/**
 * 
 */
function modMailformAddEvents(moduleId, baseUri, moduleName) {
	// Отправка формы на сервер
	jQuery("#emailForm_" + moduleId).on( "submit", function() {
		var form_data = jQuery("#emailForm_" + moduleId).serialize();
		
		jQuery.ajax({
			type: "POST",
			url: baseUri + "modules/" + moduleName + "/sendformajx.php",
			data: form_data,
			dataType: "text",
			timeout: 30000,
			async: false,
			
			error: function(xhr) {
				console.log('Ошибка!'+xhr.status+' '+xhr.statusText);
			},
			
			success: function(msg) {
				var r = jQuery.parseJSON(msg);
//				jQuery("#mod_mailform_" + moduleId + " div.modal-body").html("<p>" + msg + "</p>");
				Joomla.renderMessages(r.messages);
			}
		});
	});
	// Перенос блока системных сообщений в модальное окно
	jQuery("#mod_mailform_btn_" + moduleId).on( "click", function() {
		
		var spacer = jQuery("<div />", {
			id: "mod_mailform_spacer_" + moduleId
		}).css("display", "none");
	
		var messageContainer = jQuery("#system-message-container");
		messageContainer.replaceWith(spacer);
		messageContainer.prependTo( jQuery("#modal_body_" + moduleId) );
	});
	// Перенос блока системных сообщений обратно в тело страницы
	jQuery("#mod_mailform_" + moduleId).on( "hide", function() {
		jQuery("#mod_mailform_spacer_" + moduleId).replaceWith( jQuery("#system-message-container") );
	});
}