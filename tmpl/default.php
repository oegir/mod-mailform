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
$doc->addScriptDeclaration($mopduleHelper->getSendScript());
// $doc->addScript('modules/mod_mailform/tmpl/js/sendmailform.js');
// 
?>
	<a href="#mod_mailform_<?php echo $module->id ?>" role="button" class="btn" data-toggle="modal">Click Me</a>
	<!-- <div class="modal hide fade" id="mod_mailform_<?php echo $module->id ?>" > -->
	<div class="modal" id="mod_mailform_<?php echo $module->id ?>" >
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3><?php echo JText::_( 'COM_CONTACT_EMAIL_FORM' ); ?></h3>
		</div>
		<div class="modal-body">
			<form action="javascript:void(null);" method="post" name="emailForm_<?php echo $module->id ?>" id="emailForm_<?php echo $module->id ?>" class="form-validate form-horizontal">
				<div id="write_us_div">
					<fieldset id="write_us_fieldset">
						<div class="control-group">
							<div class="control-label">
								<label for="contact_name" class="<?php echo $req_name; ?>">&nbsp;<?php echo JText::_( 'COM_CONTACT_CONTACT_EMAIL_NAME_LABEL' ); ?>:</label>
							</div>
							<div class="controls">
								<input type="text" name="name" id="contact_name" size="30" class="inputbox input-xlarge" aria-required="<?php if ($req_name == 'required') echo 'true'; else echo 'false'  ?>" <?php echo $req_name; ?> value="" />
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<label id="contact_emailmsg" for="contact_email" class="required">&nbsp;<?php echo JText::_( 'JGLOBAL_EMAIL' ); ?>:</label>
							</div>
							<div class="controls">
								<input type="text" id="contact_email" name="email" size="30" value="" class="inputbox required validate-email input-xlarge" maxlength="100" />
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<label for="contact_subject">&nbsp;<?php echo JText::_( 'COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL' ); ?>:</label>
							</div>
							<div class="controls">
								<input type="text" name="subject" id="contact_subject" size="30" class="inputbox input-xlarge <?php echo $req_subject; ?>" value="" />
							</div>
						</div>
						<div class="control-group">
							<label id="contact_textmsg" class="control-label" for="contact_text">&nbsp;<?php echo JText::_( 'COM_CONTACT_CONTACT_ENTER_MESSAGE_LABEL' ); ?>:</label>
							<div class="controls">
								<textarea cols="50" rows="10" name="text" id="contact_text" class="inputbox input-xlarge required"></textarea>
							</div>
						</div>
		<?php if ($params->get('captcha')) {
			$dispatcher->trigger('onInit','CUF_CAPTCHA');
			$captcha_html =  $dispatcher->trigger('onDisplay', array('CUF_CAPTCHA','CUF_CAPTCHA',null) );
			echo $captcha_html[0].'<br />';
		} ?>
						<div class="control-group">
							<label class="control-label" for="contact_email_copy"><?php echo JText::_( 'COM_CONTACT_CONTACT_EMAIL_A_COPY_LABEL' ); ?></label>
							<div class="controls">
								<label class="checkbox">
									<input type="checkbox" name="email_copy" id="contact_email_copy" value="1" />
									Additional_Text
								</label>
							</div>
						</div>
						<button class="btn btn-primary validate" type="submit"><i class="icon-envelope"></i> <?php echo JText::_('COM_CONTACT_CONTACT_SEND'); ?></button>
					</fieldset>
				</div>
				<input type="hidden" name="cufaction" value="sendmail" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		</div>
	</div>
