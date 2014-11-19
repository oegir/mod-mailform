/**
 * 
 */

var ModMailform = {}

ModMailform.FORM_ID = "modMailform"; // префикс id тега формы
ModMailform.FORM_WINDOW = "modMailformWindow";
ModMailform.FORM_OPEN_BUTTON_ID = "modMailformOpenButton";
ModMailform.FORM_SPACER_ID = "modMailformSpacer";
ModMailform.FORM_MODAL_BODY_ID = "modMailformModalBody";
ModMailform.FORM_LABEL_PREFIX = "modMailformLabel";
ModMailform.FORM_FIELD_PREFIX = "modMailformField";
ModMailform.FORM_SPINNER = "modMailformSpinner";
ModMailform.FORM_FINAL_BUTTONS = "modMailformFinal";

ModMailform.spinners = Array();

/**
 * Регистрирует обработчики для событий формы
 * 
 * @param moduleId
 *            int - id текущего модуля
 * @param baseUri
 *            String - URL сайта
 * @param moduleName
 *            String - идентификатор модуля
 * 
 * @returns void
 */
ModMailform.addEvents = function(moduleId, baseUri, moduleName) {
	// Отправка формы на сервер
	jQuery("#" + ModMailform.FORM_ID + "_" + moduleId).on("submit", function() {
		ModMailform.sendMessage(moduleId, baseUri, moduleName);
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
	var spinner = Spinners.create(jQuery("#" + ModMailform.FORM_SPINNER + "_"
			+ moduleId), spinner_settings);
	ModMailform.spinners[ModMailform.FORM_SPINNER + "_" + moduleId] = spinner;
}

/**
 * Выполняет отправку формы на сервер и другие действия, связанные с реакцией на
 * кнопку "Отправить"
 * 
 * @param moduleId
 *            int - id текущего модуля
 * @param baseUri
 *            String - URL сайта
 * @param moduleName
 *            String - идентификатор модуля
 * 
 * @returns void
 */
ModMailform.sendMessage = function(moduleId, baseUri, moduleName) {
	Joomla.removeMessages();
	ModMailform.hideForm(moduleId);
	ModMailform.showSpinner(moduleId);

	var form_data = jQuery("#" + ModMailform.FORM_ID + "_" + moduleId)
			.serialize();

	jQuery
			.ajax({
				type : "POST",
				url : baseUri + "index.php?option=com_ajax&module=" + moduleName + "&format=raw",
				data : form_data,
				dataType : "text",
				timeout : 30000,
				async : true,

				error : function(xhr) {
					console.log('Ошибка!' + xhr.status + ' ' + xhr.statusText);
				},

				success : function(msg) {
					var messages;
					ModMailform.hideSpinner(moduleId);
					
					try {
						var r = jQuery.parseJSON(msg);
					} catch (e) {
						messages = ModMailform.FORM_WEIRD_STATUS;
						ModMailform.showButtons(moduleId);
						Joomla.renderMessages(messages);
						return;
					}

					if (r.summary == ModMailform.FORM_RESULT_ERROR) {
						ModMailform.setErrorFields(moduleId, r.error_fields);
						ModMailform.showForm(moduleId);
						messages = r.messages;
					} else if (r.summary == ModMailform.FORM_RESULT_SUCCES) {
						ModMailform.showButtons(moduleId);
						messages = r.messages;
					} else {
						messages = ModMailform.FORM_WEIRD_STATUS;

						if ('messages' in r) {
							messages.error[ModMailform.FORM_WEIRD_STATUS_NEXT] = ModMailform.FORM_WEIRD_STATUS_NEXT;

							jQuery.each(r.messages, function(key, value) {
								messages[key] = value;
							});
						}
						ModMailform.showButtons(moduleId);
					}
					Joomla.renderMessages(messages);
				}
			});
}

/**
 * Скрывает форму отправки сообщения
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns void
 */
ModMailform.hideForm = function(moduleId) {
	jQuery("#" + ModMailform.FORM_ID + "_" + moduleId).css({
		display : 'none'
	});
}

/**
 * Отображает форму отправки сообщения
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns void
 */
ModMailform.showForm = function(moduleId) {
	jQuery("#" + ModMailform.FORM_ID + "_" + moduleId).css({
		display : 'block'
	});
}

/**
 * Отображает финальные кнопки
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns void
 */
ModMailform.showButtons = function(moduleId) {
	jQuery("#" + this.FORM_FINAL_BUTTONS + "_" + moduleId).css({
		display : 'block'
	});
}

/**
 * Отображает прогресс-бар в виде вращаюещегося колеса
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns Element
 */
ModMailform.showSpinner = function(moduleId) {
	var spinner_name = this.FORM_SPINNER + "_" + moduleId;

	this.spinners[spinner_name].play();
	jQuery("#" + spinner_name).css({
		display : 'block'
	});
}

/**
 * Скрывает прогресс-бар
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns Element
 */
ModMailform.hideSpinner = function(moduleId) {
	var spinner_name = this.FORM_SPINNER + "_" + moduleId;

	this.spinners[spinner_name].stop();
	jQuery("#" + spinner_name).css({
		display : 'none'
	});
}

/**
 * Назначает полям формы CSS-стили ошибок
 * 
 * @param moduleId
 *            int - id текущего модуля
 * @param fields
 *            Array - массив строк с именами полей
 * 
 * @returns void
 */
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