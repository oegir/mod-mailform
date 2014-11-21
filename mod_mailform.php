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

$moduleHelper = new ModMailformHelper($module, $params);

$lang = JFactory::getLanguage();
$lang->load('plg_captcha_recaptcha', JPATH_ADMINISTRATOR, JFactory::getDocument()->language, true);
$display = $moduleHelper->checkForm();

switch ($display) {
	case ModMailformHelper::SEND_JSON:
		$json = $moduleHelper->getJsonData();
		echo $json;
		break;
		
	default:
		require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default'));
}
