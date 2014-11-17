/**
 * 
 */

var ModMailform = {}

ModMailform.FORM_ID = "modMailform";
ModMailform.FORM_WINDOW = "modMailformWindow";
ModMailform.FORM_OPEN_BUTTON_ID = "modMailformOpenButton";
ModMailform.FORM_SPACER_ID = "modMailformSpacer";
ModMailform.FORM_MODAL_BODY_ID = "modMailformModalBody";
ModMailform.FORM_LABEL_PREFIX = "modMailformLabel";
ModMailform.FORM_FIELD_PREFIX = "modMailformField";
ModMailform.SPINNER = "modMailformSpinner";

ModMailform.addEvents = function(moduleId, baseUri, moduleName) {
	// Отправка формы на сервер
	jQuery("#" + ModMailform.FORM_ID + "_" + moduleId)
			.on(
					"submit",
					function() {
						var form_data = jQuery(
								"#" + ModMailform.FORM_ID + "_" + moduleId)
								.serialize();

						jQuery.ajax({
							type : "POST",
							url : baseUri + "modules/" + moduleName
									+ "/sendformajx.php",
							data : form_data,
							dataType : "text",
							timeout : 30000,
							async : false,

							error : function(xhr) {
								console.log('Ошибка!' + xhr.status + ' '
										+ xhr.statusText);
							},

							success : function(msg) {
								var r = jQuery.parseJSON(msg);
								Joomla.renderMessages(r.messages);
								this.setErrorFields(moduleId, r.data);
							}
						});
					});
	// Перенос блока системных сообщений в модальное окно
	jQuery("#" + this.FORM_OPEN_BUTTON_ID + "_" + moduleId).on(
			"click",
			function() {

				var spacer = jQuery("<div />", {
					id : ModMailform.FORM_SPACER_ID + "_" + moduleId
				}).css("display", "none");

				var messageContainer = jQuery("#system-message-container");
				messageContainer.replaceWith(spacer);
				messageContainer.prependTo(jQuery("#"
						+ ModMailform.FORM_MODAL_BODY_ID + "_" + moduleId));
			});
	// Перенос блока системных сообщений обратно в тело страницы
	jQuery("#" + this.FORM_WINDOW + "_" + moduleId).on(
			"hide",
			function() {
				jQuery("#" + ModMailform.FORM_SPACER_ID + "_" + moduleId)
						.replaceWith(jQuery("#system-message-container"));
			});
	// Создание спиннера
	var spinner_settings = {
		radius : 70,
		height : 30,
		width : 4,
		dashes : 25,
		opacity : 1,
		padding : 0,
		rotation : 600,
		color : '#000000'
	}
	var spinner = Spinners.create(jQuery("#" + ModMailform.SPINNER + "_"
			+ moduleId), spinner_settings);
	spinner.center();
	spinner.play();
}

// Меняет вид окна перед отправкой формы
ModMailform.presendMessage = function() {
	Joomla.removeMessages();
}

ModMailform.setErrorFields = function(moduleId, fields) {

	for (var i = 0; i < fields.length; i++) {
		jQuery(
				"#" + this.FORM_ID + "_" + moduleId + " #"
						+ this.FORM_LABEL_PREFIX + "_" + fields[i]).addClass(
				"invalid");
		jQuery(
				"#" + this.FORM_ID + "_" + moduleId + " #"
						+ this.FORM_FIELD_PREFIX + "_" + fields[i]).addClass(
				"invalid");
	}
}