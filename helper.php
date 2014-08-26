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
	}
	
	/**
	 * Sends a mail to recipient.
	 *
	 * @param   Mixed  $post  The module options.
	 * @param   Mixed  $enquryText  The module options.
	 *
	 * @return  array
	 */
	public static function sendemail($post,$enquryText) {
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
	
	function getSendScript() {
		$javascript = 'jQuery( document ).ready(function () {';
		$javascript .= 'jQuery("#emailForm_' . $this->module->id . '").on( "submit", function() {';
		$javascript .= 'form_data = jQuery("#emailForm_' . $this->module->id . '").serialize();';
		$javascript .= 'jQuery.ajax({';
		$javascript .= 'type: "POST",';
		$javascript .= 'url: "' . JFactory::getURI(). 'modules/' . $this->module->module . '/sendformajx.php",';
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
	
	public function checkForm() {
		$post = JFactory::getApplication()->input->post;
		$cufaction = $post->get ( 'cufaction', null );
		
		if ( !$cufaction == 'sendmail' ) {
			// Если не было сабмита - отобразим пустую форму
			
			// If captcha enabled, call the plugin and create a dispatcher (based on Joomla version)
			if ( $this->params->get( 'captcha' ) ) {
				JPluginHelper::importPlugin ( 'captcha' );
				
				$jv = ( int ) substr ( JVERSION, 0, 1 );
				switch ($jv) {
					case 2 :
						$dispatcher = JDispatcher::getInstance ();
						break;
					case 3 :
						$dispatcher = JEventDispatcher::getInstance ();
						break;
				}
			}
			
			return self::DISPLAY_EMPTY_FORM;
		} else {
			// Сообщим об успешной отправке почты
			$captcha_is_valid = true;
			
			return self::SEND_MAIL_OK;
		}
	}
}
