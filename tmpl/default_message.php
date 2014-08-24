<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 *
 * @copyright   Copyright ©. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.loadCss');
JHtml::_('behavior.formvalidation');

// Note. It is important to remove spaces between elements.
$doc = JFactory::getDocument();
$doc->addStyleSheet('modules/mod_mailform/tmpl/css/default.css');



//Captcha is valid?	Session token is valid?
if ($captcha_is_valid || !JSession::checkToken() ):
//YES -> Call the send mail routine and show the thanks messange
require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default').'_message');
		plgContentContactusform::_sendemail($post,$enquryText); ?>
		<div class="plg_contactus_main_div" id="plg_contactus_<?php echo $row->id ?>" >
				<div id="thank_message">
						<?php echo JText::_( 'COM_CONTACT_EMAIL_THANKS'); ?>
				</div>
		</div>
	<?php else:
		//NO -> Show a captcha error message ?>
	<div class="plg_contactus_main_div" id="plg_contactus_<?php echo $row->id ?>" >
		<div id="thank_message">
			<?php echo JText::_( 'PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL'); ?>
		</div>
	</div>
	<?php endif;