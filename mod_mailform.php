<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 *
 * @copyright   Copyright ©. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$captcha_is_valid = true;
$jv = (int) substr(JVERSION,0,1);

$req_subject = ( $params->get('req_subject','1') ) ? 'required' : '' ;
$req_name 	 = ( $params->get('req_name','1')    ) ? 'required' : '' ;
$emailLanguage 	 = $params->get('email_language')  ;
$mopduleHelper = new ModMailformHelper($module, $params);

// If captcha enabled, call the plugin and create a dispatcher (based on Joomla version)
if ( $params->get('captcha') ) {
	JPluginHelper::importPlugin('captcha');
	switch ($jv) {
		case 2:
			$dispatcher = JDispatcher::getInstance();
			break;
		case 3:
			$dispatcher = JEventDispatcher::getInstance();
			break;
	}
}

$lang = JFactory::getLanguage();
$siteLanguage = $lang->getTag();

// Get the email text for the site admin in his preferred language as in plugin parameter
$lang->load('com_contact', JPATH_SITE , $emailLanguage, true);
$enquryText = JText::_( 'COM_CONTACT_ENQUIRY_TEXT');

// Load the language file in the current site language
$lang->load('com_contact', JPATH_SITE , $siteLanguage , true );
$lang->load('plg_captcha_recaptcha', JPATH_ADMINISTRATOR , $siteLanguage   , true);

// Get the post variables
$post = JFactory::getApplication()->input->post;
$cufaction = $post->get('cufaction',null);

$display = $mopduleHelper->checkForm();

switch ($display) {
	case ModMailformHelper::DISPLAY_EMPTY_FORM:
		require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default'));
		break;
	case ModMailformHelper::SEND_MAIL_OK:
		var_dump($display);
		break;
	case ModMailformHelper::FORM_VALIDATION_ERROR:
		echo 'FORM_VALIDATION_ERROR';
		break;
	case ModMailformHelper::SEND_MAIL_FAILED:
		echo 'SEND_MAIL_FAILED';
		break;
}

// // Check if there are data coming from the submitted form...
// if ($cufaction=="sendmail") {
// 	// Captcha is enabled in the parameters? If so check in the post data if it is valid
// 	if ( $params->get('captcha') ) {
// 		$res = $dispatcher->trigger('onCheckAnswer', array( $post->get('recaptcha_response_field') ) );
// 		$captcha_is_valid = ( (bool)$res[0] ) ? true : false ;
// 	}	
// } else {
// 	// ...otherwise it shows the form
// 	require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default'));
// }
