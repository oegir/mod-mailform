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
class ModMailformHelper
{
	
	private $module;
	
	public function __construct($module) {
		$this->module = $module;
	}
	
	/**
	 * Sends a mail to recipient.
	 *
	 * @param   Mixed  $post  The module options.
	 * @param   Mixed  $enquryText  The module options.
	 *
	 * @return  array
	 *
	 * @since   1.5
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
		$javascript = 'function mod_mailform_sendMail() {';
		$javascript .= 'jQuery.ajax({type: "POST", url: "' . JFactory::getURI(). 'modules/' . $this->module->module . '/sendformajx.php", data:somedata,';
		$javascript .= 'dataType:"text", timeout:30000, async:false,';
		$javascript .= 'error: function(xhr) {';
		$javascript .= 'console.log(\'Ошибка!\'+xhr.status+\' \'+xhr.statusText);';
		$javascript .= '},';
		$javascript .= 'success: function(a) {';
		$javascript .= 'document.getElementById("my-content").innerHTML=a;';
		$javascript .= '}';
		$javascript .= '});';
		$javascript .= '};';
		return $javascript;
	}
}
