<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mailform
 *
 * @copyright   Copyright ©. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */
defined ( '_JEXEC' ) or die ();

JHtml::_ ( 'bootstrap.framework' );
JHtml::_ ( 'bootstrap.loadCss' );
JHtml::_ ( 'behavior.formvalidation' );

$doc = JFactory::getDocument ();
$doc->addStyleSheet ( 'modules/mod_mailform/tmpl/css/default.css' );
$doc->addScript ( 'modules/mod_mailform/tmpl/js/modmailform.js', 'text/javascript' );
// Проверим, что скрипты еще не подключеы (на случай более одного модуля на странице)
$head_data = $doc->getHeadData ();
$constants_script = $moduleHelper->getConstantsScript ();
if (! strpos ( $head_data ['script'] ['text/javascript'], $constants_script )) {
	$doc->addScriptDeclaration ( $constants_script );
}
$doc->addScriptDeclaration ( $moduleHelper->getSettingsScript() );
$excanvas = '<!--[if lte IE 9]> <script src="' . JFactory::getURI ()->base () . 'modules/mod_mailform/tmpl/js/excanvas.js" type="text/javascript"></script> <![endif]-->';
if (! in_array ( $excanvas, $head_data ['custom'] )) {
	$doc->addCustomTag ( $excanvas );
}

$doc->addScript ( 'modules/mod_mailform/tmpl/js/spinners.min.js', 'text/javascript' );
$headerTag      = htmlspecialchars($params->get('header_tag', 'h3'));
?>
<a href="#modMailformWindow_<?php echo $module->id ?>" role="button"
	class="btn" data-toggle="modal"
	id="modMailformOpenButton_<?php echo $module->id ?>"><?php echo $params->get('sender_button', '') ?></a>
<div class="modal hide fade" id="modMailformWindow_<?php echo $module->id ?>">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"
			id="modMailformTopClose_<?php echo $module->id ?>" aria-hidden="true">×</button>
		<?php if ((bool) $module->showtitle) :?>
			<<?php echo $headerTag . $headerClass . '>' . $module->title; ?></<?php echo $headerTag; ?>>
		<?php endif; ?>
		<div class="modMailform-clr"></div>
	</div>
	<div class="modal-body"
		id="modMailformModalBody_<?php echo $module->id ?>">
			<?php require JModuleHelper::getLayoutPath('mod_mailform', $params->get('layout', 'default').'_form'); ?>
			<div class="modMailform-spinner"
			id="modMailformSpinner_<?php echo $module->id ?>"></div>
		<div class="modMailform-final"
			id="modMailformFinal_<?php echo $module->id ?>">
			<button type="button" class="btn btn-inverse"
				id="modMailformRevert_<?php echo $module->id ?>" aria-hidden="true">
				<i class="icon-repeat"></i> <?php echo JText::_( 'MOD_MAILFORM_BUTTON_BACK' ) ?></button>
			<button type="button" class="btn btn-info" data-dismiss="modal"
				id="modMailformBigClose_<?php echo $module->id ?>"
				aria-hidden="true">
				<i class="icon-remove"></i> <?php echo JText::_( 'MOD_MAILFORM_BUTTON_CLOSE' ) ?></button>
		</div>
	</div>
</div>
