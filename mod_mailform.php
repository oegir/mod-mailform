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

$emailLanguage 	 = $params->get('email_language')  ;
$moduleHelper = new ModMailformHelper($module, $params);

$lang = JFactory::getLanguage();
$siteLanguage = $lang->getTag();

// Get the email text for the site admin in his preferred language as in plugin parameter
$lang->load('com_contact', JPATH_SITE , $emailLanguage, true);
$enquryText = JText::_( 'COM_CONTACT_ENQUIRY_TEXT');

// Load the language file in the current site language
$lang->load('com_contact', JPATH_SITE , $siteLanguage , true );
$lang->load('plg_captcha_recaptcha', JPATH_ADMINISTRATOR , $siteLanguage   , true);

$display = $moduleHelper->checkForm();

switch ($display) {
	case ModMailformHelper::SEND_JSON:
		$json = $moduleHelper->getJsonData();
		echo $json;
		break;
		
	default:
		require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default'));
}
