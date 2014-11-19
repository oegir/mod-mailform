<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 *
 * @copyright   Copyright ©. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined ( '_JEXEC' ) or die ();

JHtml::_ ( 'bootstrap.framework' );
JHtml::_ ( 'bootstrap.loadCss' );
JHtml::_ ( 'behavior.formvalidation' );

$doc = JFactory::getDocument ();
$doc->addStyleSheet ( 'modules/mod_mailform/tmpl/css/default.css' );
$doc->addScript ( 'modules/mod_mailform/tmpl/js/modmailform.js', 'text/javascript' );
// Проверим, что скрипты еще не подключеы на случай более одного модуля на странице
$head_data = $doc->getHeadData ();
$settings_script = $moduleHelper->getSettingsScript ();
if (! strpos ( $head_data ['script'] ['text/javascript'], $settings_script )) {
	$doc->addScriptDeclaration ( $settings_script );
}
$excanvas = '<!--[if lte IE 9]> <script src="' . JFactory::getURI ()->base () . 'modules/mod_mailform/tmpl/js/excanvas.js" type="text/javascript"></script> <![endif]-->';
if (! in_array ( $excanvas, $head_data ['custom'] )) {
	$doc->addCustomTag ( $excanvas );
}

$doc->addScript ( 'modules/mod_mailform/tmpl/js/spinners.min.js', 'text/javascript' );
?>
<a href="#modMailformWindow_<?php echo $module->id ?>" role="button"
	class="btn" data-toggle="modal"
	id="modMailformOpenButton_<?php echo $module->id ?>">Click Me</a>
<!-- <div class="modal hide fade" id="mod_mailform_<?php echo $module->id ?>" > -->
<div class="modal" id="modMailformWindow_<?php echo $module->id ?>">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"
			aria-hidden="true">×</button>
		<h3><?php echo JText::_( 'COM_CONTACT_EMAIL_FORM' ); ?></h3>
	</div>
	<div class="modal-body"
		id="modMailformModalBody_<?php echo $module->id ?>">
			<?php require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default').'_form'); ?>
			<div class="modMailform-spinner"
			id="modMailformSpinner_<?php echo $module->id ?>"></div>
		<div class="modMailform-final"
			id="modMailformFinal_<?php echo $module->id ?>">
			<button type="button" class="btn btn-inverse" data-dismiss="modal"
				aria-hidden="true"><?php echo JText::_( 'MOD_MAILFORM_BUTTON_BACK' ) ?></button>
			<button type="button" class="btn btn-info" data-dismiss="modal"
				aria-hidden="true"><?php echo JText::_( 'MOD_MAILFORM_BUTTON_CLOSE' ) ?></button>
		</div>
	</div>
</div>
