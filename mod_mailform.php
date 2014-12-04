<?php
/**
 * @package     Joomla.Site
 * @subpackage  Modules.Mailform
 * @copyright   © 2014 Alexey Petrov
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$moduleHelper = new ModMailformHelper($module, $params);
$action = $moduleHelper->getPost('action', '');

switch ($action) {
	
	case ModMailformHelper::ACTION_SEND_MAIL:
		$form_state = $moduleHelper->checkForm();
		
		if ($form_state == ModMailformHelper::SEND_JSON) {
			echo $moduleHelper->getJsonData();
		} else {
			return;
		}
		break;
		
	case ModMailformHelper::ACTION_CAPTCHA:
		echo $moduleHelper->getCaptcha();
		break;
		
	default:
		require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default'));
}
