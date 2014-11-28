<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 *
 * @copyright   Copyright © All rights reserved.
 * @license     GNU General Public License version 2 or later;
 */
defined ( '_JEXEC' ) or die ();

/**
 * Helper for mod_menu
 *
 * @package Joomla.Site
 * @subpackage mod_mailform
 * @since 3.x
 */
class ModMailformHelper {
	const NO_VALUE = 'no value';
	const DISPLAY_EMPTY_FORM = 1;
	const SEND_JSON = 2;
	const SUMMARY_SUCCES = 3;
	const SUMMARY_FIELDS_ERROR = 4;
	const SUMMARY_SEND_ERROR = 5;
	const ACTION_CAPTCHA = 'captcha';
	const ACTION_SEND_MAIL = 'sendmail';
	const CAPTCHA_BLOCK_ID = 'modMailformCaptcha';
	
	/**
	 * Объект текущего модуля
	 *
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var stdClass
	 */
	private $module;
	
	/**
	 * Параметры текущего модуля
	 *
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var Joomla\Registry\Registry
	 */
	private $params;
	
	/**
	 * Сообщения с результатами обработки формы
	 *
	 * @see ModMailformHelper::checkForm()
	 * @access private
	 * @var Array
	 */
	private $message;
	
	/**
	 * Объект с post-данными запроса
	 *
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var JInput
	 */
	private $post;
	
	/**
	 * Объект JApplication
	 *
	 * @see ModMailformHelper::__construct()
	 * @access private
	 * @var JApplication
	 */
	private $application;
	
	/**
	 * Массив с данными формы.
	 * Поля массива:
	 * - Имя поля
	 * - type - тип поля для проверки правильности заполнения,
	 *
	 * @see ModMailformHelper::setFormFields() - Начальное заполнение массива полей,
	 * @see ModMailformHelper::testFormFields() - filter - фильтер для получения поля,
	 * @see JFilterInput::clean() - required - признак обязательности заполнения поля,
	 *      - field_label - Название поля, отобаржаемое в форме
	 *      - value - значение поля, загружаемое из формы
	 *      - default_value - значение по-умолчанию, до загрузки формы
	 *      - placeholder - символьная строка, на которая будет заменена в тектсте e-mail сообщения на содержимое поля формы
	 *     
	 * @see ModMailformHelper::getFormData()
	 * @access private
	 * @var Array
	 */
	private $form_fields = array ();
	
	/**
	 * Массив дополнительных плейсхолдеров их значений для подстановки в текст e-mail-сообщения:
	 * %title% - заголовок страницы,
	 * %url% - адрес страницы,
	 *
	 * @see ModMailformHelper::getAdditInfo()
	 * @access private
	 * @var Array
	 */
	private $additInfo = array (
			'%title%' => '',
			'%url%' => '' 
	);
	
	/**
	 * Массив с данными, возвращаемыми клиенту
	 * - status int - признак успешности отправки формы,
	 * - errorFields array - список полей с ошибками,
	 *
	 * @see ModMailformHelper::testFormFields()
	 * @access private
	 * @var string
	 */
	private $formData = array (
			'status' => self::SUMMARY_SUCCES,
			'errorFields' => array () 
	);
	
	/**
	 * Загружает данные формы
	 */
	private function getFormData() {
		foreach ( $this->form_fields as $field_name => &$field_data ) {
			$field_data ['value'] = $this->post->get ( $field_name, $field_data ['default_value'], $field_data ['filter'] );
		}
	}
	
	/**
	 * Проверяет правильность заполнения полей формы.
	 * Возвращает False если неправильно
	 *
	 * @return boolean
	 */
	private function testFormFields() {
		$form_ok = true;
		
		foreach ( $this->form_fields as $field_name => &$field_data ) {
			$field_ok = true;
			
			switch ($field_data ['type']) {
				
				case 'text' :
					// Удалим HTML-теги и проверим заполненность
					$field_data ['value'] = trim ( htmlspecialchars ( $field_data ['value'] ) );
					
					if ($field_data ['required'] && (strlen ( $field_data ['value'] ) == 0)) {
						$field_ok = false;
						$this->application->enqueueMessage ( JText::_ ( 'MOD_MAILFORM_FIELD_REQUIRED_EMPTY' ) . ': ' . $field_data ['field_label'], 'error' );
					}
					break;
				
				case 'email' :
					// Проверим заполненность и соответсвие шаблону электронной почты
					$field_data ['value'] = trim ( $field_data ['value'] );
					$pattern = '/^[0-9A-zА-Яа-яЁё\.\-_]+@[0-9A-zА-Яа-яЁё\-_]+\.[0-9A-zА-Яа-яЁё\-_]+$/u';
					
					if ($field_data ['required'] && (strlen ( $field_data ['value'] ) == 0)) {
						$field_ok = false;
						$this->application->enqueueMessage ( JText::_ ( 'MOD_MAILFORM_FIELD_REQUIRED_EMPTY' ) . ': ' . $field_data ['field_label'], 'error' );
					} elseif ((strlen ( $field_data ['value'] ) > 0) && ! preg_match ( $pattern, $field_data ['value'] )) {
						$field_ok = false;
						$this->application->enqueueMessage ( JText::_ ( 'MOD_MAILFORM_FIELD_INVALID' ) . ': ' . $field_data ['field_label'], 'error' );
					}
					break;
				
				case 'captcha' :
					// Если используется каптча проверим ее с помощью плагина и диспетчера событий
					if ($this->params->get ( 'captcha' )) {
						$response = $this->post->get ( 'recaptcha_response_field' );
						
						if (strlen ( $response ) > 0) {
							JPluginHelper::importPlugin ( 'captcha' );
							$dispatcher = $this->getDispatcher ();
							$res = $dispatcher->trigger ( 'onCheckAnswer', $response );
							$captcha_is_valid = ($res [0]) ? true : false;
						} else {
							$captcha_is_valid = false;
						}
					} else {
						$captcha_is_valid = true;
					}
					if (! $captcha_is_valid) {
						$field_ok = false;
						$this->application->enqueueMessage ( JText::_ ( 'MOD_MAILFORM_FIELD_INVALID' ) . ': ' . $field_data ['field_label'], 'error' );
					}
					break;
			}
			if (! $field_ok) {
				$form_ok = false;
				$this->formData ['status'] = $form_ok ? self::SUMMARY_SUCCES : self::SUMMARY_FIELDS_ERROR;
				$this->formData ['errorFields'] [] = $field_name;
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
		foreach ( $this->form_fields as $field_name => &$field_data ) {
			$param = $this->params->get ( 'req_' . $field_name, Null );
			
			if (isset ( $param )) {
				$field_data ['required'] = ( bool ) $param;
			}
		}
	}
	
	/**
	 * Подготавливает текст отправляемого сообщения
	 *
	 * @return string
	 */
	private function getAdditInfo() {
		$this->additInfo ['%title%'] = JFactory::getApplication ()->getParams ()->get ( 'page_title', '' );
		$this->additInfo ['%url%'] = isset ( $_SERVER ['HTTP_REFERER'] ) ? htmlspecialchars ( $_SERVER ['HTTP_REFERER'] ) : 'not present';
		return $this->additInfo;
	}
	
	/**
	 * Возвращает html-код каптчи
	 *
	 * @return string
	 */
	private static function getCaptcha() {
		$document = JFactory::getDocument ();
		$lang = JFactory::getLanguage ();
		$lang->load ( 'plg_captcha_recaptcha', JPATH_ADMINISTRATOR, $document->language, true );
		
		JPluginHelper::importPlugin ( 'captcha' );
		
		$dispatcher = JEventDispatcher::getInstance ();
		$dispatcher->trigger ( 'onInit', 'modMailformCaptcha' );
		
		$captcha_html = $dispatcher->trigger ( 'onDisplay', array (
				'CUF_CAPTCHA',
				'modMailformCaptcha',
				null 
		) );
		
		$head = '<head>' . PHP_EOL;
		
		foreach ( $document->_scripts as $link => $data ) {
			$head .= '<script src="' . $link . '" type="' . $data ['mime'] . '"></script>' . PHP_EOL;
		}
		
		foreach ( $document->_script as $type => $script ) {
			$head .= '<script type="' . $type . '">' . PHP_EOL;
			$head .= $script . PHP_EOL;
			$head .= '</script>' . PHP_EOL;
		}
		$head .= '</head>';
		
		$body = '<body>' . PHP_EOL;
		$body .= '<div id="' . self::CAPTCHA_BLOCK_ID . '">' . PHP_EOL;
		$body .= $captcha_html [0] . PHP_EOL;
		$body .= '</div>' . PHP_EOL;
		$body .= '</body>';
		
		$html = '<html>' . PHP_EOL;
		$html .= $head . PHP_EOL;
		$html .= $body . PHP_EOL;
		$html .= '</html>';
		
		return $html;
	}
	
	/**
	 * Подготавливает текст отправляемого сообщения
	 *
	 * @return string
	 */
	private function getContent() {
		$searches = array ();
		$replacements = array ();
		// Соберем массивы поиска и подстановки из массива с полями
		foreach ( $this->form_fields as $field ) {
			
			if (isset ( $field ['placeholder'] )) {
				$searches [] = $field ['placeholder'];
				$replacements [] = $field ['value'];
			}
		}
		// Добавим дополнительные плейсхолдеры и их значения в массивы поиска и подстановки
		$add_info = $this->getAdditInfo ();
		foreach ( $add_info as $search => $replace ) {
			$searches [] = $search;
			$replacements [] = $replace;
		}
		
		$content = str_replace ( $searches, $replacements, $this->params->get ( 'message', '' ) );
		return $content;
	}
	
	/**
	 * Подготавливает данные для майлера и передает их для отправки.
	 * Возвращает False в случае неудачи
	 *
	 * @return boolean
	 */
	private function sendemail() {
		$result = true;
		$mailer_data = array (
				'to_mail',
				'from_sender_data',
				'subject',
				'text' 
		);
		$app = JFactory::getApplication ();
		// Подготовим данные для отправки администрации сайта
		$mailer_data ['to_mail'] = $this->params->get ( 'sending_mail', $app->getCfg ( 'mailfrom' ) );
		$mailer_data ['from_sender_data'] = array (
				$this->form_fields ['email'] ['value'],
				$this->form_fields ['name'] ['value'] 
		);
		$mailer_data ['subject'] = $this->form_fields ['subject'] ['value'];
		$mailer_data ['text'] = $this->getContent ();
		
		$result &= $this->doMailer ( $mailer_data );
		
		if ($this->form_fields ['email_copy'] ['value']) {
			// Подготовим данные для отправки пользователю сайта
			$sender_mail = $this->params->get ( 'sender_mail', self::NO_VALUE );
			
			if ($sender_mail == self::NO_VALUE) {
				$sender_mail = $this->params->get ( 'sending_mail', $app->getCfg ( 'mailfrom' ) );
			}
			
			$mailer_data ['to_mail'] = $this->form_fields ['email'] ['value'];
			$mailer_data ['from_sender_data'] = array (
					$sender_mail,
					$this->params->get ( 'sender_name', $app->getCfg ( 'fromname' ) ) 
			);
			
			$result &= $this->doMailer ( $mailer_data );
		}
		
		return $result;
	}
	
	/**
	 * Производит отправку писем с помощью JMail.
	 * Возвращает False при ошибке.
	 *
	 * @param array $data
	 *        	Данные для отправки.
	 *        	
	 * @return boolean
	 */
	private function doMailer($data) {
		$mailer = JFactory::getMailer ();
		
		$mailer->addRecipient ( $data ['to_mail'] );
		$mailer->setSender ( $data ['from_sender_data'] );
		$mailer->setSubject ( $data ['subject'] );
		$mailer->setBody ( $data ['text'] );
		
		$send = $mailer->Send ();
		$mailer = null;
		
		return $send === true;
	}
	
	/**
	 * Заполняет информацию о полях формы
	 *
	 * @return void
	 */
	private function setFormFields() {
		$this->form_fields = array (
				'name' => array (
						'type' => 'text',
						'filter' => 'string',
						'required' => true,
						'field_label' => JText::_ ( 'MOD_MAILFORM_FIELD_NAME_LABEL' ),
						'value' => Null,
						'default_value' => '',
						'placeholder' => '%name%' 
				),
				'email' => array (
						'type' => 'email',
						'filter' => 'string',
						'required' => true,
						'field_label' => JText::_ ( 'JGLOBAL_EMAIL' ),
						'value' => Null,
						'default_value' => '',
						'placeholder' => '%email%' 
				),
				'subject' => array (
						'type' => 'text',
						'filter' => 'string',
						'required' => false,
						'field_label' => JText::_ ( 'MOD_MAILFORM_FIELD_SUBJECT_LABEL' ),
						'value' => Null,
						'default_value' => '',
						'placeholder' => '%subject%' 
				),
				'text' => array (
						'type' => 'text',
						'filter' => 'string',
						'required' => true,
						'field_label' => JText::_ ( 'MOD_MAILFORM_FIELD_TEXT_LABEL' ),
						'value' => Null,
						'default_value' => '',
						'placeholder' => '%message%' 
				),
				'email_copy' => array (
						'type' => 'bool',
						'filter' => 'bool',
						'required' => false,
						'field_label' => JText::_ ( 'MOD_MAILFORM_FIELD_EMAIL_COPY_LABEL' ),
						'value' => Null,
						'default_value' => false 
				),
				'recaptcha_response_field' => array (
						'type' => 'captcha',
						'filter' => 'string',
						'required' => false,
						'field_label' => JText::_ ( 'MOD_MAILFORM_FIELD_CAPTCHA' ),
						'value' => Null,
						'default_value' => '' 
				) 
		);
	}
	
	/**
	 * Конструктор класса.
	 *
	 * @param stdClass $module
	 *        	Объект текущего модуля
	 * @param Joomla\Registry\Registry $params
	 *        	Параметры текущего модуля
	 *        	
	 * @return void
	 */
	public function __construct($module, $params) {
		$this->setFormFields ();
		$this->module = $module;
		$this->params = $params;
		$this->application = JFactory::getApplication ();
		$this->post = $this->application->input->post;
		$this->readRequiredFiedsFromParams ();
	}
	
	/**
	 * Точка входа при Ajax-запросе
	 *
	 * @return void
	 */
	public static function getAjax() {
		$joomla_app = JFactory::getApplication ( 'site' );
		$action = $joomla_app->input->get ( 'action', '', 'string' );
		
		switch ($action) {
			
			case ModMailformHelper::ACTION_SEND_MAIL :
				$module_title = htmlspecialchars ( $joomla_app->input->post->get ( 'title', '', 'string' ) );
				
				if ($module_title != '') {
					$module = JModuleHelper::getModule ( 'mod_mailform', $module_title );
					
					if ($module->id > 0) {
						echo JModuleHelper::renderModule ( $module );
					} else {
						echo (JText::_ ( 'MOD_MAILFORM_MODULE_NOT_FOUND' ));
						return;
					}
				}
				break;
			
			case ModMailformHelper::ACTION_CAPTCHA :
				echo ModMailformHelper::getCaptcha ();
				break;
			
			default :
				echo (JText::_ ( 'MOD_MAILFORM_MODULE_NOT_FOUND' ));
				return;
		}
	}
	
	/**
	 * Устанавливает обязательные для заполнения поля
	 *
	 * @param array $required
	 *        	массив со списком имен обязательных полей
	 *        	
	 * @return void
	 */
	public function setRequiredFields($required) {
		foreach ( $required as $field_name ) {
			$param = $this->params->get ( 'req_' . $field_name, Null );
			$param = ! isset ( $param );
			
			if ($param || ! isset ( $this->form_fields [$field_name] )) {
				throw new Exception ( JText::_ ( 'MOD_MAILFORM_NOT_SUPPORT_FIELD_EXCEPTION' ) . ': ' . $field_name, $this->module->id );
			} else {
				$this->form_fields [$field_name] ['required'] = true;
			}
		}
	}
	
	/**
	 * Возвращает через пробел имена CSS-классов соответственно имни поля
	 *
	 * @param sting $name
	 *        	имя поля
	 *        	
	 * @return string
	 */
	public function getFieldClasses($name) {
		$result = '';
		
		if (isset ( $this->form_fields [$name] )) {
			$result = $this->form_fields [$name] ['required'] ? 'required' : '';
		}
		return $result;
	}
	
	/**
	 * Возвращает текстовое значение метки поля по его имени
	 *
	 * @param sting $name
	 *        	имя поля
	 *        	
	 * @return string
	 */
	public function getFiledLabel($name) {
		if (isset ( $this->form_fields [$name] )) {
			return $this->form_fields [$name] ['field_label'];
		} else {
			return '';
		}
	}
	
	/**
	 * Возвращает jQuery-скрипт для подключения обработчиков событий броузера
	 *
	 * @return string
	 */
	public function getSettingsScript() {
		$javascript = 'jQuery( document ).ready(function () {' . PHP_EOL;
		$javascript .= '		ModMailform.addEvents(';
		$javascript .= '"' . $this->module->id . '",';
		$javascript .= '"' . JFactory::getURI ()->base () . '",';
		$javascript .= '"' . str_replace ( 'mod_', '', $this->module->module ) . '"';
		$javascript .= ');' . PHP_EOL; // Конец списка параметров функции 'modMailformAddEvents'
		$javascript .= '	});' . PHP_EOL; // Конец списка параметров функции 'ready'
		return $javascript;
	}
	
	/**
	 * Возвращает jQuery-скрипт для подключения обработчиков событий броузера
	 *
	 * @return string
	 */
	public function getConstantsScript() {
		$javascript .= 'ModMailform.FORM_RESULT_SUCCES = ' . self::SUMMARY_SUCCES . ';' . PHP_EOL;
		$javascript .= 'ModMailform.FORM_RESULT_FIELDS_ERROR = ' . self::SUMMARY_FIELDS_ERROR . ';' . PHP_EOL;
		$javascript .= 'ModMailform.FORM_RESULT_SEND_ERROR = ' . self::SUMMARY_SEND_ERROR . ';' . PHP_EOL;
		$javascript .= 'ModMailform.FORM_WEIRD_STATUS = {' . PHP_EOL;
		$javascript .= '	error : {' . PHP_EOL;
		$javascript .= '		0 : "' . JText::_ ( 'MOD_MAILFORM_WEIRD_STATUS' ) . '"' . PHP_EOL;
		$javascript .= '	}' . PHP_EOL;
		$javascript .= '}' . PHP_EOL;
		$javascript .= 'ModMailform.FORM_WEIRD_STATUS_NEXT = "' . JText::_ ( 'MOD_MAILFORM_WEIRD_STATUS_NEXT' ) . '"' . PHP_EOL;
		$javascript .= 'ModMailform.SERVER_NOT_RESPONDING = "' . JText::_ ( 'MOD_MAILFORM_SERVER_NOT_RESPONDING' ) . '"' . PHP_EOL;
		$javascript .= 'ModMailform.FORM_MODULE_NAME = "' . $this->module->name . '"' . PHP_EOL;
		$javascript .= 'ModMailform.FORM_BASE_URI = "' . JURI::base () . '"' . PHP_EOL;
		$javascript .= 'ModMailform.FRAME_CAPTCHA_BLOCK_ID = "' . self::CAPTCHA_BLOCK_ID . '"';
		return $javascript;
	}
	
	/**
	 * Возвращает библиотечный класс Joomla в зависимости от версии
	 *
	 * @return JEventDispatcher
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
	 * @return integer
	 */
	public function checkForm() {
		$action = $this->post->get ( 'action', null );
		$formState = self::DISPLAY_EMPTY_FORM;
		
		if (! $action == self::ACTION_SEND_MAIL) {
			// Если не было сабмита - отобразим пустую форму
			$formState = self::DISPLAY_EMPTY_FORM;
		} else {
			// Подготовим данные формы для проверки
			$this->getFormData ();
			
			if ($this->testFormFields ()) {
				$result = $this->sendemail ();
				$this->formData ['status'] = $result ? self::SUMMARY_SUCCES : self::SUMMARY_SEND_ERROR;
			}
			$formState = self::SEND_JSON;
		}
		
		return $formState;
	}
	
	/**
	 * Возвращает строку ответа, закодированную в JSON
	 *
	 * @return string
	 */
	public function getJsonData() {
		$summary = ! ($this->formData ['status'] == self::SUMMARY_SUCCES);
		$data = new JResponseJson ( $this->formData ['errorFields'], $this->formData ['status'], $summary );
		
		if (($this->formData ['status'] == self::SUMMARY_SEND_ERROR) && (! $this->params->get ( 'show_joomla_send_error', false ))) {
			$data->messages = array (
					'error' => array (
							JText::_ ( 'MOD_MAILFORM_SEND_MAIL_SERVER_ERR0R' ) 
					) 
			);
		}
		
		return $data->__toString ();
	}
	
	/**
	 * Возвращает значение из входящих POST-данных
	 *
	 * @param sting $name
	 *        	имя поля
	 * @param mixed $default
	 *        	значение по умолчанию
	 * @param string $filter
	 *        	фильтр, применяемый к значению
	 *        	
	 * @return mixed Отфильтрованное значение
	 */
	public function getPost($name, $default = Null, $filter = 'cmd') {
		return $this->post->get ( $name, $default, $filter );
	}
}
