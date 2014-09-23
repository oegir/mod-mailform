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
	const SEND_JSON = 2;
	
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
	 *							@see JFilterInput::clean()
	 * 		- required - признак обязательности заполнения поля,
	 * 		- field_label - Название поля, отобаржаемое в форме
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
				'field_label' => '',
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%name%',
		),
		'email' => array(
				'type' => 'email',
				'filter' => 'string',
				'required' => true,
				'field_label' => '',
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%email%',
		),
		'subject' => array(
				'type' => 'text',
				'filter' => 'string', 
				'required' => false,
				'field_label' => '',
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%subject%',
		),
		'text' => array(
				'type' => 'text',
				'filter' => 'string',
				'required' => true,
				'field_label' => '',
				'value' => Null,
				'default_value' => '',
				'placeholder' => '%message%',
		),
		'email_copy' => array(
				'type' => 'bool',
				'filter' => 'bool',
				'required' => false,
				'field_label' => '',
				'value' => Null,
				'default_value' => false,
		),
		'recaptcha_response_field' => array(
				'type' => 'captcha',
				'filter' => 'string',
				'required' => false,
				'field_label' => '',
				'value' => Null,
				'default_value' => '',
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
	 * Массив с данными, возвращаемыми клиенту
	 * - success boolean - признак успешности отправки формы,
	 * - errorFields array - список полей с ошибками,
	 * 
	 * @see ModMailformHelper::testFormFields()
	 * @access private
	 * @var string
	 */
	private $formData = array(
			'success' => true,
			'errorFields' => array(),
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
			$field_ok = true;
			
			switch ($field_data['type']) {
				
				case 'text':
					// Удалим HTML-теги и проверим заполненность
					$field_data['value'] = trim( htmlspecialchars( $field_data['value'] ) );
					
					if ( $field_data['required'] && ( strlen( $field_data['value'] ) == 0 ) ) {
						$field_ok = false;
						JError::raiseWarning( $field_data['field_label'], JText::_('MOD_MAILFORM_REQUIRED_TEXTFIELD_ISEMPTY') );
					}
					break;
					
				case 'email':
					// Проверим заполненность и соответсвие шаблону электронной почты
					$field_data['value'] = trim( $field_data['value'] );
					$pattern = '/^[0-9A-zА-Яа-яЁё\.\-_]+@[0-9A-zА-Яа-яЁё\-_]+\.[0-9A-zА-Яа-яЁё\-_]+$/u';
					
					if ($field_data['required'] && ( strlen( $field_data['value'] ) == 0 ) ) {
						$field_ok = false;
						JError::raiseWarning( $field_data['field_label'], JText::_('MOD_MAILFORM_REQUIRED_MAILFIELD_ISEMPTY') );
					} elseif ( ( strlen( $field_data['value'] ) > 0 ) && !preg_match($pattern, $field_data['value']) ) {
						$field_ok = false;
						JError::raiseWarning( $field_data['field_label'], JText::_('MOD_MAILFORM_MAILFIELD_NOTVALID') );
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
					if ( !$captcha_is_valid ) {
						$field_ok = false;
						JError::raiseWarning( $field_data['field_label'], JText::_('MOD_MAILFORM_CAPTHCA_ISINVALID') );
					}
					break;
			}
			if (!$field_ok) {
				$form_ok = false;
				$this->formData['success'] = $form_ok;
				$this->formData['errorFields'][] = $field_name;
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
	 * Подготавливает данные для майлера и передает их для отправки. Возвращает False в случае неудачи
	 *
	 * @return  boolean
	 */
	private function sendemail() {
		$result = true;
		$mailer_data = array(
				'to_mail',
				'from_sender_data',
				'subject',
				'text',
		);
		$app		= JFactory::getApplication();
		// Подготовим данные для отправки администрации сайта
		$mailer_data['to_mail'] = $this->params->get( 'sending_mail', $app->getCfg( 'mailfrom' ) );
		$mailer_data['from_sender_data'] = array(
				$this->form_fields['email']['value'],
				$this->form_fields['name']['value'],
		);
		$mailer_data['subject'] = $this->form_fields['subject']['value'];
		$mailer_data['text'] = $this->getContent();
		
		$result &= $this->doMailer( $mailer_data );
		
		if ( $this->form_fields['email_copy']['value'] ) {
			// Подготовим данные для отправки пользователю сайта
			$sender_mail = $this->params->get( 'sender_mail', self::NO_VALUE );
			
			if ( $sender_mail == self::NO_VALUE ) {
				$sender_mail = $this->params->get( 'sending_mail', $app->getCfg( 'mailfrom' ) );
			}
			
			$mailer_data['to_mail'] = $this->form_fields['email']['value'];
			$mailer_data['from_sender_data'] = array(
					$sender_mail,
					$this->params->get( 'sender_name', $app->getCfg( 'fromname' ) ),
			);
			
			$result &= $this->doMailer( $mailer_data );
		}
		
		return $result;
	}
	
	/**
	 * Производит отправку писем с помощью JMail. Возвращает False при ошибке.
	 *
	 * @param array $data Данные для отправки.
	 *
	 * @return boolean
	 */
	private function doMailer($data) {
		$mailer = JFactory::getMailer();
		
		$mailer->addRecipient($data['to_mail']);
		$mailer->setSender($data['from_sender_data']);
		$mailer->setSubject($data['subject']);
		$mailer->setBody($data['text']);
		
		$send = $mailer->Send();
		$mailer = null;
		
		return !is_object($send);
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
	 * Возвращает через пробел имена CSS-классов соответственно имни поля
	 *
	 * @param   sting  $name имя поля
	 *
	 * @return  string
	 */
	public function getFieldClasses($name) {
		$result = '';
		
		if ( isset($this->form_fields[$name]) ) {
// 			$result = $this->form_fields[$name]['required'] ? 'required' : '' ;
			$result .= $this->form_fields[$name]['is_error'] ? ' invalid' : '' ;
		}
		return $result;
	}
	
	/**
	 * Возвращает через пробел имена CSS-классов соответственно имни поля
	 *
	 * @param   sting  $name имя поля
	 *
	 * @return  string
	 */
	public function getLabelClasses($name) {
		$result = '';
	
		if ( isset($this->form_fields[$name]) ) {
			$result = $this->form_fields[$name]['is_error'] ? 'invalid' : '' ;
		}
		return $result;
	}
	
	/**
	 * Возвращает jQuery-скрипт для подключения обработчиков событий броузера
	 *
	 * @return  string
	 */
	public function getEventsScript() {
		$javascript  =	'jQuery( document ).ready(function () {';
		$javascript .=		'modMailformAddEvents(
								"' . $this->module->id . '",
								"' . JFactory::getURI()->base(). '",
								"' . $this->module->module . '"
							);'; // Конец списка параметров функции 'modMailformAddEvents'
		$javascript .=	'});'; // Конец списка параметров функции 'ready'
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
		$formState = self::DISPLAY_EMPTY_FORM;
		
		if ( !$cufaction == 'sendmail' ) {
			// Если не было сабмита - отобразим пустую форму
			$formState = self::DISPLAY_EMPTY_FORM;
		} else {
			// Подготовим данные формы для проверки
			$this->getFormData();
			
			if ( $this->testFormFields() ) {
				$this->sendemail();
			}
			$formState = self::SEND_JSON;
		}
		
		return $formState;
	}
	
	/**
	 * Возвращает объект для отправки клиенту
	 *
	 * @return  JResponseJson
	 */
	public function getJsonData() {
		return new JResponseJson( $this->formData['errorFields'], '', !$this->formData['success'] );
	}
}
