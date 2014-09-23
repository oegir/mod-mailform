/**
 * 
 */

var MOD_MAILFORM_ID = "modMailform";
var MOD_MAILFORM_OPEN_BUTTON_ID = "modMailformOpenButton";
var MOD_MAILFORM_SPACER_ID = "modMailformSpacer";
var MOD_MAILFORM_MODAL_BODY_ID = "modMailformModalBody";

function modMailformAddEvents(moduleId, baseUri, moduleName) {
	// Отправка формы на сервер
	jQuery("#" + MOD_MAILFORM_ID + "_" + moduleId).on( "submit", function() {
		var form_data = jQuery("#" + MOD_MAILFORM_ID + "_" + moduleId).serialize();
		
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
				modMailformSetErrorFields(moduleId, r.data);
			}
		});
	});
	// Перенос блока системных сообщений в модальное окно
	jQuery("#" + MOD_MAILFORM_OPEN_BUTTON_ID + "_" + moduleId).on( "click", function() {
		
		var spacer = jQuery("<div />", {
			id: MOD_MAILFORM_SPACER_ID + "_" + moduleId
		}).css("display", "none");
	
		var messageContainer = jQuery("#system-message-container");
		messageContainer.replaceWith(spacer);
		messageContainer.prependTo( jQuery("#" + MOD_MAILFORM_MODAL_BODY_ID + "_" + moduleId) );
	});
	// Перенос блока системных сообщений обратно в тело страницы
	jQuery("#" + MOD_MAILFORM_ID + "_" + moduleId).on( "hide", function() {
		jQuery("#" + MOD_MAILFORM_SPACER_ID + "_" + moduleId).replaceWith( jQuery("#system-message-container") );
	});
}

function modMailformSetErrorFields(moduleId, fields) {
	var a = 0;
}