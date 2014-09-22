<?php
/**
 * @package     Joomla.Site
* @subpackage  mod_mailform
*
* @copyright   Copyright ©. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

// echo $moduleHelper->getMessage();
?>
<form action="javascript:void(null);" method="post" name="emailForm_<?php echo $module->id ?>" id="emailForm_<?php echo $module->id ?>" class="form-validate form-horizontal">
	<div id="write_us_div">
		<fieldset id="write_us_fieldset">
			<div class="control-group">
				<div class="control-label">
					<label for="contact_name" class="<?php echo $moduleHelper->getLabelClasses('name'); ?>">&nbsp;<?php echo JText::_( 'COM_CONTACT_CONTACT_EMAIL_NAME_LABEL' ); ?>:</label>
				</div>
				<div class="controls">
					<input type="text" name="name" id="contact_name" size="30" class="inputbox input-xlarge <?php echo $moduleHelper->getFieldClasses('name'); ?>" value="" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label id="contact_emailmsg" for="contact_email" class="<?php echo $moduleHelper->getLabelClasses('email'); ?>">&nbsp;<?php echo JText::_( 'JGLOBAL_EMAIL' ); ?>:</label>
				</div>
				<div class="controls">
					<input type="text" id="contact_email" name="email" size="30" class="inputbox <?php echo $moduleHelper->getFieldClasses('email'); ?> validate-email input-xlarge" maxlength="100" value="" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="contact_subject" class="<?php echo $moduleHelper->getLabelClasses('subject'); ?>">&nbsp;<?php echo JText::_( 'COM_CONTACT_CONTACT_MESSAGE_SUBJECT_LABEL' ); ?>:</label>
				</div>
				<div class="controls">
					<input type="text" name="subject" id="contact_subject" size="30" class="inputbox input-xlarge <?php echo $moduleHelper->getFieldClasses('subject'); ?>" value="" />
				</div>
			</div>
			<div class="control-group">
				<label id="contact_textmsg" class="control-label <?php echo $moduleHelper->getLabelClasses('text'); ?>" for="contact_text">&nbsp;<?php echo JText::_( 'COM_CONTACT_CONTACT_ENTER_MESSAGE_LABEL' ); ?>:</label>
				<div class="controls">
					<textarea cols="50" rows="10" name="text" id="contact_text" class="inputbox input-xlarge <?php echo $moduleHelper->getFieldClasses('text'); ?>"></textarea>
				</div>
			</div>
<?php if ($params->get('captcha')) {
	JPluginHelper::importPlugin ( 'captcha' );
	$moduleHelper->getDispatcher()->trigger('onInit','CUF_CAPTCHA');
	$captcha_html =  $moduleHelper->getDispatcher()->trigger('onDisplay', array('CUF_CAPTCHA','CUF_CAPTCHA',null) );
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
	<input type="hidden" name="mtitle" value="<?php echo $module->title ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>