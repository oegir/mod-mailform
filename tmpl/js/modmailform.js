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
ModMailform.FORM_REVERT_BUTTON = "modMailformRevert";
ModMailform.FORM_CAPTCHA_HOLDER = "modMailformCaptchaHolder";

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
	// Открытие окна отправки собщений
	jQuery("#" + this.FORM_OPEN_BUTTON_ID + "_" + moduleId).on(
			"click",
			function() {
				ModMailform.getCaptcha(moduleId, baseUri, moduleName);
				// Перенос блока системных сообщений в модальное окно
				var spacer = jQuery("<div />", {
					id : ModMailform.FORM_SPACER_ID + "_" + moduleId
				}).css("display", "none");

				var messageContainer = jQuery("#system-message-container");
				messageContainer.replaceWith(spacer);
				messageContainer.prependTo(jQuery("#"
						+ ModMailform.FORM_MODAL_BODY_ID + "_" + moduleId));
			});
	// Сброс внесенных изменений
	jQuery("#" + this.FORM_WINDOW + "_" + moduleId).on(
			"hide",
			function() {
				Joomla.removeMessages();
				ModMailform.hideButtons(moduleId);
				ModMailform.hideSpinner(moduleId);
				jQuery("#" + ModMailform.FORM_ID + "_" + moduleId).trigger('reset');
				ModMailform.showForm(moduleId);
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
	// Возврат на экран формы
	jQuery("#" + this.FORM_REVERT_BUTTON + "_" + moduleId).on("click",
			function() {
				ModMailform.hideButtons(moduleId);
				Joomla.removeMessages();
				ModMailform.showForm(moduleId);
			});
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
				url : baseUri + "index.php?option=com_ajax&module="
						+ moduleName + "&format=raw",
				data : form_data,
				dataType : "text",
				timeout : 30000,
				async : true,

				error : function(xhr) {
					ModMailform.hideSpinner(moduleId);
					ModMailform.showButtons(moduleId);
					Joomla.renderMessages({ error : { 0 : ModMailform.SERVER_NOT_RESPONDING } });
					console.log(ModMailform.SERVER_NOT_RESPONDING + ": " + xhr.status + ' ' + xhr.statusText);
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

					if (r.message == ModMailform.FORM_RESULT_FIELDS_ERROR) {
						ModMailform.setErrorFields(moduleId, r.data);
						ModMailform.showForm(moduleId);
						messages = r.messages;
					} else if (r.message == ModMailform.FORM_RESULT_SEND_ERROR) {
						ModMailform.showButtons(moduleId);
						messages = r.messages;
					} else if (r.message == ModMailform.FORM_RESULT_SUCCES) {
						ModMailform.showButtons(moduleId);
						messages = r.messages;
					} else {
						messages = ModMailform.FORM_WEIRD_STATUS;
						var index = 1;

						if ('messages' in r) {
							messages.error[index++] = ModMailform.FORM_WEIRD_STATUS_NEXT;

							jQuery.each(r.messages, function(message_type,
									values) {

								jQuery.each(values, function(key, value) {
									messages[message_type][index++] = value;
								});
							});
						}
						ModMailform.showButtons(moduleId);
					}
					Joomla.renderMessages(messages);
				}
			});
}


/**
 * Запрашивает код captcha для размещения его на форме
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
ModMailform.getCaptcha = function(moduleId, baseUri, moduleName) {
	
	jQuery
			.ajax({
				type : "POST",
				url : baseUri + "index.php?option=com_ajax&module="
						+ moduleName + "&format=raw&action=captcha",
				timeout : 30000,
				async : true,

				error : function(xhr) {
					Joomla.renderMessages({ error : { 0 : ModMailform.CAPTCHA_NOT_RECEIVED } });
					console.log(ModMailform.CAPTCHA_NOT_RECEIVED + ": " + xhr.status + ' ' + xhr.statusText);
				},

				success : function(msg) {
//					alert(jQuery("#" + ModMailform.FORM_CAPTCHA_HOLDER + "_" +moduleId ).text());
					alert(msg);
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
 * Скрывает блок с финальными кнопками
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns void
 */
ModMailform.hideButtons = function(moduleId) {
	jQuery("#" + this.FORM_FINAL_BUTTONS + "_" + moduleId).css({
		display : 'none'
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