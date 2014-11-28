<?php
/**
 * @package     Joomla.Site
* @subpackage  mod_mailform
*
* @copyright   Copyright ©. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

?>
<form action="javascript:void(null);" method="post" name="modMailform_<?php echo $module->id ?>" id="modMailform_<?php echo $module->id ?>" class="form-validate form-horizontal">
	<div id="write_us_div">
		<fieldset id="write_us_fieldset">
			<div class="control-group">
				<div class="control-label">
					<label id="modMailformLabel_name" for="modMailformField_name" class="">&nbsp;<?php echo $moduleHelper->getFiledLabel('name'); ?>:</label>
				</div>
				<div class="controls">
					<input type="text" name="name" id="modMailformField_name" size="30" class="inputbox input-xlarge <?php echo $moduleHelper->getFieldClasses('name'); ?>" value="" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label id="modMailformLabel_email" for="modMailformField_email" class="">&nbsp;<?php echo $moduleHelper->getFiledLabel('email'); ?>:</label>
				</div>
				<div class="controls">
					<input type="text" id="modMailformField_email" name="email" size="30" class="inputbox <?php echo $moduleHelper->getFieldClasses('email'); ?> validate-email input-xlarge" maxlength="100" value="" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label  id="modMailformLabel_subject" for="modMailformField_subject" class="">&nbsp;<?php echo $moduleHelper->getFiledLabel('subject'); ?>:</label>
				</div>
				<div class="controls">
					<input type="text" name="subject" id="modMailformField_subject" size="30" class="inputbox input-xlarge <?php echo $moduleHelper->getFieldClasses('subject'); ?>" value="" />
				</div>
			</div>
			<div class="control-group">
				<label id="modMailformLabel_text" class="control-label" for="modMailformField_text">&nbsp;<?php echo $moduleHelper->getFiledLabel('text'); ?>:</label>
				<div class="controls">
					<textarea cols="50" rows="10" name="text" id="modMailformField_text" class="inputbox input-xlarge <?php echo $moduleHelper->getFieldClasses('text'); ?>"></textarea>
				</div>
			</div>
<?php if ($params->get('captcha')) : ?>
			<div id="modMailformCaptchaHolder_<?php echo $module->id ?>" class="modMailformCaptchaHolder"></div>
<?php endif; ?>
			<div class="control-group">
				<label id="modMailformLabel_email_copy" class="control-label" for="modMailformField_email_copy"><?php echo $moduleHelper->getFiledLabel('email_copy'); ?></label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" name="email_copy" id="modMailformField_email_copy" value="1" />
					</label>
				</div>
			</div>
			<button class="btn btn-primary validate" type="submit"><i class="icon-envelope"></i> <?php echo JText::_('MOD_MAILFORM_BUTTON_SEND'); ?></button>
		</fieldset>
	</div>
	<input type="hidden" name="action" value="sendmail" />
	<input type="hidden" name="title" value="<?php echo $module->title ?>" />
</form>