<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 *
 * @copyright   Copyright © All rights reserved.
 * @license     GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_menu
 *
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 * @since       3.x
 */
class ModMailformHelper {
	const DISPLAY_EMPTY_FORM = 1;
	const SEND_MAIL_OK = 2;
	const FORM_VALIDATION_ERROR = 3;
	const SEND_MAIL_FAILED = 4;
	
	/**
	 * Объект текущего модуля
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var stdClass
	 */
	private $module;
	
	/**
	 * Параметры текущего модуля
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var Joomla\Registry\Registry
	 */
	private $params;
	
	/**
	 * Сообщения с результатами обработки формы
	 * @see ModMailformHelper::checkForm()
	 * @access private
	 * @var Array
	 */
	private $message;
	
	/**
	 * Объект с post-данными запроса
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var JInput
	 */
	private $post;
	
	/**
	 * Массив с данными формы. Поля массива: 
	 * 	- Имя поля
	 * 		- type - тип поля для проверки правильности заполнения,
	 *							@see ModMailformHelper::testFormFields()
	 * 		- filter - фильтер для получения поля,
	 *								@see JFilterInput::clean()
	 * 		- required - признак обязательности заполнения поля,
	 * 		- value - значение поля, загружаемое из формы
	 * 		- default_value - значение по-умолчанию, до загрузки формы
	 * 		- placeholder - символьная строка, на которая будет заменена в тектсте e-mail сообщения на содержимое поля формы
	 * 
	 * @see ModMailformHelper::getFormData()
	 * @access private
	 * @var Array
	 */
	private $form_fields = array(
		'name' => array(
				'type' => 'text',
				'filter' => 'string',
				'required' => true,
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%name%',
		),
		'email' => array(
				'type' => 'email',
				'filter' => 'string',
				'required' => true,
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%email%',
		),
		'subject' => array(
				'type' => 'text',
				'filter' => 'string', 
				'required' => false,
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%subject%',
		),
		'text' => array(
				'type' => 'text',
				'filter' => 'string',
				'required' => true,
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%message%',
		),
		'email_copy' => array(
				'type' => 'bool',
				'filter' => 'bool',
				'required' => false,
				'value' => Null,
				'default_value' => false,
		),
		'recaptcha_response_field' => array(
				'type' => 'captcha',
				'filter' => 'string',
				'required' => false,
				'value' => Null,
				'default_value' => ''
		),
	);
	
	/**
	 * Массив дополнительных плейсхолдеров их значений для подстановки в текст e-mail-сообщения: 
	 * - %title%,
	 * 		- заголовок страницы,
	 * - %url%,
	 * 		- адрес страницы,
	 * 
	 * @see ModMailformHelper::getAdditInfo()
	 * @access private
	 * @var Array
	 */
	private $additInfo = array(
			'%title%' => '',
			'%url%' => '',
	);
	
	/**
	 * Загружает данные формы
	 */
	private function getFormData() {
	
		foreach ($this->form_fields as $field_name => &$field_data) {
			$field_data['value'] = $this->post->get( $field_name, $field_data['default_value'], $field_data['filter'] );
		}
	}
	
	/**
	 * Проверяет правильность заполнения полей формы. Возвращает False если неправильно
	 *
	 * @return boolean
	 */
	private function testFormFields() {
		$form_ok = true;
		
		foreach ($this->form_fields as $field_name => &$field_data) {
			
			switch ($field_data['type']) {
				
				case 'text':
					// Удалим HTML-теги и проверим заполненность
					$field_data['value'] = trim( htmlspecialchars( $field_data['value'] ) );
					$form_ok &= ( ( strlen( $field_data['value'] ) > 0 ) && $field_data['required'] ) || ( ! $field_data['required'] );
					break;
					
				case 'email':
					// Проверим заполненность и соответсвие шаблону электронной почты
					$field_data['value'] = trim( $field_data['value'] );
					$pattern = '/^[0-9A-zА-Яа-яЁё\-_]+@[0-9A-zА-Яа-яЁё\-_]+\.[0-9A-zА-Яа-яЁё\-_]+$/u';
					
					if ($field_data['required']) {
						$form_ok &= preg_match($pattern, $field_data['value']);
					} else {
						$form_ok &= strlen( $field_data['value'] ) > 0 ? preg_match($pattern, $field_data['value']) : true;
					}
					break;
					
				case 'captcha':
					// Если используется каптча проверим ее с помощью плагина и диспетчера событий
					if ( $this->params->get('captcha') ) {
						$response = $this->post->get('recaptcha_response_field');
						
						if (strlen( $response ) > 0 ) {
							JPluginHelper::importPlugin ( 'captcha' );
							$dispatcher = $this->getDispatcher();
							$res = $dispatcher->trigger('onCheckAnswer',  $response);
							$captcha_is_valid = ($res[0]) ? true : false ;
						} else {
							$captcha_is_valid = false;
						}
					} else {
						$captcha_is_valid = true;
					}
					$form_ok &= $captcha_is_valid;
					break;
			}
		}
		
		
		return $form_ok;
	}
	
	/**
	 * Устанавливает признак обязательных полей на основе параметров модуля
	 *
	 * @return void
	 */
	private function readRequiredFiedsFromParams() {
		
		foreach ($this->form_fields as $field_name => &$field_data) {
			$param = $this->params->get( 'req_' . $field_name, Null );
			
			if ( isset( $param ) ) {
				$field_data['required'] = (bool) $param;
			}
		}
	}
	
	/**
	 * Подготавливает текст отправляемого сообщения
	 *
	 * @return string
	 */
	private function getAdditInfo() {
		$this->additInfo['%title%'] = JFactory::getApplication()->getParams()->get('page_title', '');
		$this->additInfo['%url%'] = isset( $_SERVER['HTTP_REFERER'] ) ? htmlspecialchars( $_SERVER['HTTP_REFERER'] ) : 'not present';
		return $this->additInfo;
	}
	
	/**
	 * Подготавливает текст отправляемого сообщения
	 *
	 * @return string
	 */
	private function getContent() {
		$searches = array();
		$replacements = array();
		// Соберем массивы поиска и подстановки из массива с полями
		foreach ($this->form_fields as $field) {
			
			if ( isset( $field['placeholder'] ) ) {
				$searches[] = $field['placeholder'];
				$replacements[] = $field['value'];
			}
		}
		// Добавим дополнительные плейсхолдеры и их значения в массивы поиска и подстановки
		$add_info = $this->getAdditInfo();
		foreach ($add_info as $search => $replace) {
			$searches[] = $search;
			$replacements[] = $replace;
		}
		
		$content = str_replace( $searches, $replacements, $this->params->get( 'message', '' ) );
		return $content;
	}
	
	/**
	 * Отправляет письмо адресату. Возвращает False в случае неудачи
	 *
	 * @param   Mixed  $post  The module options.
	 * @param   Mixed  $enquryText  The module options.
	 *
	 * @return  boolean
	 */
	private function sendemail() {
		echo $this->getContent();
		return true;
		
		$post = Null;
		$enquryText = Null;
		
		$owner_email = 	$post->get('recipient',null,'string');
		$sender = 		$post->get('email',null,'string');
		$name = 		$post->get('name',null,'string');
		$subject = 		$post->get('subject',null,'string');
		$text = 		$post->get('text',null,'string');
		$email_copy = 	$post->get('email_copy',false,'boolean');

		$body =  		str_replace('%s',JURI::current(), JText::_( 'COM_CONTACT_ENQUIRY_TEXT'))."\n".$name."  <".$sender.">\n\n".$text;
		$owner_email = 	str_replace( '#'  , '@' , $owner_email );
		$owner_email = 	str_replace( '"'  , '' , $owner_email );
		$recipient = explode(";",$owner_email);

		if ($email_copy ) {
			$app		= JFactory::getApplication();
			$mailfrom	= $app->getCfg('mailfrom');
			$fromname	= $app->getCfg('fromname');
			$sitename	= $app->getCfg('sitename');
				
			$copytext		= JText::sprintf('COM_CONTACT_COPYTEXT_OF', $fromname, $sitename);
			$copytext		.= "\r\n\r\n".$body;
			$copysubject	= JText::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

			$mail = JFactory::getMailer();
			$mail->addRecipient($sender);
			$mail->addReplyTo(array($sender, $name));
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		$body =  str_replace('%s',JURI::current(), $enquryText)."\n\n".$name."  <".$sender.">\n\n".$text;

		$mailer = JFactory::getMailer();
		foreach ($recipient as $r) $mailer->addRecipient($r);
		$mailer->setSender($sender);
		$mailer->setSubject($subject);
		$mailer->isHTML(false);
		$mailer->setBody($body);
		$send = $mailer->Send();

		$mailer = null;
	}
	
	/**
	 * Конструктор класса.
	 *
	 * @param   stdClass  $module  Объект текущего модуля
	 * @param   Joomla\Registry\Registry  $params  Параметры текущего модуля
	 *
	 * @return  void
	 */
	public function __construct($module, $params) {
		$this->module = $module;
		$this->params = $params;
		$this->post = JFactory::getApplication()->input->post;
		$this->readRequiredFiedsFromParams();
	}
	
	/**
	 * Устанавливает обязательные для заполнения поля
	 * 
	 * @param   array  $required массив со списком имен обязательных полей
	 *
	 * @return  void
	 */
	public function setRequiredFields($required) {
		
		foreach ($required as $field_name) {
			$param = $this->params->get( 'req_' . $field_name, Null );
			$param = !isset( $param );
			
			if ( $param || !isset( $this->form_fields[$field_name] ) ) {
				throw new Exception(JText::_('MOD_MAILFORM_NOT_SUPPORT_FIELD_EXCEPTION') . ' ' . $field_name, $this->module->id);
			} else {
				$this->form_fields[$field_name]['required'] = true;
			}
		}
	}
	
	/**
	 * Возвращает имя CSS-класса, если поле обязательное или пустую строку
	 *
	 * @param   array  $required массив со списком имен обязательных полей
	 *
	 * @return  string
	 */
	public function getRequiredClass($name) {
		$result = $this->form_fields[$name]['required'] ? 'required' : '' ;
		return $result;
	}
	
	/**
	 * Возвращает jQuery-скрипт для Ajax-отправки формы
	 *
	 * @return  string
	 */
	public function getSendScript() {
		$javascript = 'jQuery( document ).ready(function () {';
		$javascript .= 'jQuery("#emailForm_' . $this->module->id . '").on( "submit", function() {';
		$javascript .= 'form_data = jQuery("#emailForm_' . $this->module->id . '").serialize();';
		$javascript .= 'jQuery.ajax({';
		$javascript .= 'type: "POST",';
		$javascript .= 'url: "' . JFactory::getURI()->base(). 'modules/' . $this->module->module . '/sendformajx.php",';
		$javascript .= 'data:form_data,';
		$javascript .= 'dataType:"text",';
		$javascript .= 'timeout:30000,';
		$javascript .= 'async:false,';
		$javascript .= 'error: function(xhr) {';
		$javascript .= 'console.log(\'Ошибка!\'+xhr.status+\' \'+xhr.statusText);';
		$javascript .= '},';
		$javascript .= 'success: function(msg) {';
		$javascript .= 'jQuery("#mod_mailform_' . $this->module->id . ' div.modal-body").html("<p>" + msg + "</p>");';
		$javascript .= '}';
		$javascript .= '});';
		$javascript .= '});';
		$javascript .= '});';
		return $javascript;
	}
	
	/**
	 * Возвращает библиотечный класс Joomla в зависимости от версии
	 *
	 * @return  JEventDispatcher
	 */
	public function getDispatcher() {
		$jv = ( int ) substr ( JVERSION, 0, 1 );
		
		switch ($jv) {
			case 2 :
				return JDispatcher::getInstance ();
				break;
			case 3 :
				return JEventDispatcher::getInstance ();
				break;
		}
	}
	
	/**
	 * Выбирает режим отображения модуля
	 *
	 * @return  integer
	 */
	public function checkForm() {
		$cufaction = $this->post->get ( 'cufaction', null );
		
		if ( !$cufaction == 'sendmail' ) {
			// Если не было сабмита - отобразим пустую форму
			return self::DISPLAY_EMPTY_FORM;
		} else {
			// Подготовим данные формы для проверки
			$this->getFormData();
			
			if ( $this->testFormFields() ) {
				
				if ( $this->sendemail() ) {
					return self::SEND_MAIL_OK;
				} else {
					return self::SEND_MAIL_FAILED;
				}
			} else {
				return self::FORM_VALIDATION_ERROR;
			}
		}
	}
}
