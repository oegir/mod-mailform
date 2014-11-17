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
$doc->addScriptDeclaration($moduleHelper->getEventsScript());
$doc->addScript('modules/mod_mailform/tmpl/js/script.js', 'text/javascript');
$doc->addCustomTag('<!--[if lte IE 9]>');
$doc->addScript('modules/mod_mailform/tmpl/js/excanvas.js', 'text/javascript');
$doc->addCustomTag('<![endif]-->');
$doc->addScript('modules/mod_mailform/tmpl/js/spinners.min.js', 'text/javascript');
?>
	<a href="#modMailformWindow_<?php echo $module->id ?>" role="button" class="btn" data-toggle="modal" id="modMailformOpenButton_<?php echo $module->id ?>">Click Me</a>
	<!-- <div class="modal hide fade" id="mod_mailform_<?php echo $module->id ?>" > -->
	<div class="modal" id="modMailformWindow_<?php echo $module->id ?>" >
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3><?php echo JText::_( 'COM_CONTACT_EMAIL_FORM' ); ?></h3>
		</div>
		<div class="modal-body" id="modMailformModalBody_<?php echo $module->id ?>">
			<?php require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default').'_form'); ?>
			<div id="modMailformSpinner_<?php echo $module->id ?>"></div>
		</div>
	</div>
