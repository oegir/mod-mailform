/**
 * @package     Joomla.Site
 * @subpackage  Modules.Mailform
 * @copyright   © 2014 Alexey Petrov
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

var ModMailform = {};

ModMailform.FORM_ID = "modMailform"; // префикс id тега формы
ModMailform.FORM_WINDOW = "modMailformWindow";
ModMailform.FORM_OPEN_BUTTON_ID = "modMailformOpenButton";
ModMailform.FORM_SPACER_ID = "modMailformSpacer";
ModMailform.FORM_MODAL_BODY_ID = "modMailformModalBody";
ModMailform.FORM_LABEL_PREFIX = "modMailformLabel";
ModMailform.FORM_FIELD_PREFIX = "modMailformField";
ModMailform.FORM_LOAD_ANIMATION = "modMailformloadAnimation";
ModMailform.FORM_FINAL_BUTTONS = "modMailformFinal";
ModMailform.FORM_REVERT_BUTTON = "modMailformRevert";
ModMailform.FORM_CAPTCHA_HOLDER = "modMailformCaptchaHolder";

ModMailform.data = {};

ModMailform.loadAnimation = {
	cSpeed : 9,
	cWidth : 220,
	cHeight : 220,
	cTotalFrames : 12,
	cFrameWidth : 220,
	imageName : "sprites.png",
	cImageTimeout : false,
	cIndex : 0,
	cXpos : 0,
	cPreloaderTimeout : false,
	SECONDS_BETWEEN_FRAMES : 0
};

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
	this.data[moduleId] = {
		"baseUri" : baseUri,
		"moduleName" : moduleName
	}
	// Отправка формы на сервер
	jQuery("#" + ModMailform.FORM_ID + "_" + moduleId).on("submit", function() {
		ModMailform.sendMessage(moduleId, baseUri, moduleName);
	});
	// Открытие окна отправки собщений
	jQuery("#" + this.FORM_OPEN_BUTTON_ID + "_" + moduleId).on(
			"click",
			function() {
				ModMailform.showForm(moduleId);
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
				ModMailform.hideLoadAnimation(moduleId);
				jQuery("#" + ModMailform.FORM_ID + "_" + moduleId).trigger(
						'reset');
				jQuery("#" + ModMailform.FORM_SPACER_ID + "_" + moduleId)
						.replaceWith(jQuery("#system-message-container"));
			});
	// Настройка индикатора загрузки
	this.loadAnimation.imageUrl = this.data[moduleId].baseUri + "modules/mod_"
			+ this.data[moduleId].moduleName + "/tmpl/img/"
			+ this.loadAnimation.imageName;
	var preImg = jQuery("<img/>", {
		src : ModMailform.loadAnimation.imageUrl
	});
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
	ModMailform.importCaptcha(moduleId);
	ModMailform.showLoadAnimation(moduleId);

	var form_data = jQuery("#" + ModMailform.FORM_ID + "_" + moduleId)
			.serialize();

	jQuery
			.ajax({
				type : "POST",
				url : baseUri + "index.php?option=com_ajax&module="
						+ moduleName + "&format=raw&Itemid="
						+ ModMailform.FRAME_CAPTCHA_MENU_ID,
				data : form_data,
				dataType : "text",
				timeout : 30000,
				async : true,

				error : function(xhr) {
					ModMailform.hideLoadAnimation(moduleId);
					ModMailform.showButtons(moduleId);
					Joomla.renderMessages({
						error : {
							0 : ModMailform.SERVER_NOT_RESPONDING
						}
					});
					console.log(ModMailform.SERVER_NOT_RESPONDING + ": "
							+ xhr.status + ' ' + xhr.statusText);
				},

				success : function(msg) {
					var messages;
					ModMailform.hideLoadAnimation(moduleId);

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
 * Загружает код captcha и размещает его в окне формы
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns void
 */
ModMailform.loadCaptcha = function(moduleId) {
	var iFrame = jQuery("<iframe/>", {
		src : ModMailform.FORM_BASE_URI + "index.php?option=com_ajax&module="
				+ ModMailform.FORM_MODULE_NAME
				+ "&format=raw&action=captcha&Itemid="
				+ ModMailform.FRAME_CAPTCHA_MENU_ID
	});
	jQuery("#" + this.FORM_CAPTCHA_HOLDER + "_" + moduleId).html(iFrame);
}

/**
 * Переносит данные каптчи из iframe в отправляемую форму
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns void
 */
ModMailform.importCaptcha = function(moduleId) {
	var captcha_content = jQuery(
			"#" + this.FORM_CAPTCHA_HOLDER + "_" + moduleId + " iframe")
			.contents().find("#" + this.FRAME_CAPTCHA_BLOCK_ID);
	jQuery("#" + this.FORM_CAPTCHA_HOLDER + "_" + moduleId).append(
			captcha_content);
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
	this.loadCaptcha(moduleId);
	// Задание размеров формы на экране
	jQuery("#" + this.FORM_WINDOW + "_" + moduleId).css({
        'margin-left': function () {
            return -(jQuery(this).width() / 2);
        },
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
 * Запускает прогресс-бар в виде вращаюещегося колеса
 * 
 * @param moduleId
 *            int - id текущего модуля
 * @returns void
 */
ModMailform.showLoadAnimation = function(moduleId) {
	var animationBlock = jQuery("<div/>").css({
		"background-image" : "url(" + ModMailform.loadAnimation.imageUrl + ")",
		height : ModMailform.loadAnimation.cHeight,
		width : ModMailform.loadAnimation.cWidth,
		display : "inline-block"
	});
	var animationHolder = jQuery("#" + ModMailform.FORM_LOAD_ANIMATION + "_"
			+ moduleId);
	animationHolder.css({
		display : "block",
		"text-align": "center"
	});
	animationHolder.append(animationBlock);

	var FPS = Math.round(100 / this.loadAnimation.cSpeed);
	this.loadAnimation.SECONDS_BETWEEN_FRAMES = 1 / FPS;

	this.loadAnimation.cPreloaderTimeout = setTimeout(function() {
		ModMailform.continueLoadAnimation(animationBlock)
	}, this.loadAnimation.SECONDS_BETWEEN_FRAMES / 1000);
}

/**
 * Выполняет покадровую анимацию прогресс-бара в виде вращаюещегося колеса
 * 
 * @param moduleId
 *            int - id текущего модуля
 * @returns void
 */
ModMailform.continueLoadAnimation = function(animationBlock) {
	this.loadAnimation.cXpos += this.loadAnimation.cFrameWidth;
	// increase the index so we know which frame of our animation we are
	// currently on
	this.loadAnimation.cIndex += 1;

	// if our cIndex is higher than our total number of frames, we're at the end
	// and should restart
	if (this.loadAnimation.cIndex >= this.loadAnimation.cTotalFrames) {
		this.loadAnimation.cXpos = 0;
		this.loadAnimation.cIndex = 0;
	}

	animationBlock.css("background-position", (-this.loadAnimation.cXpos) + "px 0");

	this.loadAnimation.cPreloaderTimeout = setTimeout(function() {
		ModMailform.continueLoadAnimation(animationBlock)
	}, this.loadAnimation.SECONDS_BETWEEN_FRAMES * 1000);
}

/**
 * Скрывает прогресс-бар
 * 
 * @param moduleId
 *            int - id текущего модуля
 * 
 * @returns Element
 */
ModMailform.hideLoadAnimation = function(moduleId) {
	clearTimeout(this.loadAnimation.cPreloaderTimeout);
	this.loadAnimation.cPreloaderTimeout=false;
	
	var animationHolder = jQuery("#" + ModMailform.FORM_LOAD_ANIMATION + "_"
			+ moduleId);
	animationHolder.css({
		display : "block"
	});
	animationHolder.empty();
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