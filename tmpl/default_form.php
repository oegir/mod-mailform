<?php
/**
 * @package     Joomla.Site
 * @subpackage  Modules.Mailform
 * @copyright   © 2014 Alexey Petrov
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined ( '_JEXEC' ) or die ();

?>
<form action="javascript:void(null);" method="post"
	name="modMailform_<?php echo $module->id ?>"
	id="modMailform_<?php echo $module->id ?>"
	class="form-validate form-horizontal">
	<div class="modMailform-writeUs">
		<fieldset id="write_us_fieldset">
			<div class="control-group row-fluid">
				<div class="control-label span<?php echo $col_indexes->labels ?>">
					<label id="modMailformLabel_name" for="modMailformField_name"
						class="">&nbsp;<?php echo $moduleHelper->getFiledLabel('name'); ?>:</label>
				</div>
				<div class="controls span<?php echo $col_indexes->fields ?>">
					<input type="text" name="name" id="modMailformField_name" size="30"
						class="inputbox modMailform-inputFluid <?php echo $moduleHelper->getFieldClasses('name'); ?>"
						value="" />
				</div>
			</div>
			<div class="control-group row-fluid">
				<div class="control-label span<?php echo $col_indexes->labels ?>">
					<label id="modMailformLabel_email" for="modMailformField_email"
						class="">&nbsp;<?php echo $moduleHelper->getFiledLabel('email'); ?>:</label>
				</div>
				<div class="controls span<?php echo $col_indexes->fields ?>">
					<input type="text" id="modMailformField_email" name="email"
						size="30"
						class="inputbox <?php echo $moduleHelper->getFieldClasses('email'); ?> validate-email modMailform-inputFluid"
						maxlength="100" value="" />
				</div>
			</div>
			<div class="control-group row-fluid">
				<div class="control-label span<?php echo $col_indexes->labels ?>">
					<label id="modMailformLabel_subject" for="modMailformField_subject"
						class="">&nbsp;<?php echo $moduleHelper->getFiledLabel('subject'); ?>:</label>
				</div>
				<div class="controls span<?php echo $col_indexes->fields ?>">
					<input type="text" name="subject" id="modMailformField_subject"
						size="30"
						class="inputbox modMailform-inputFluid <?php echo $moduleHelper->getFieldClasses('subject'); ?>"
						value="" />
				</div>
			</div>
			<div class="control-group row-fluid">
				<label id="modMailformLabel_text"
					class="control-label span<?php echo $col_indexes->labels ?>"
					for="modMailformField_text">&nbsp;<?php echo $moduleHelper->getFiledLabel('text'); ?>:</label>
				<div class="controls span<?php echo $col_indexes->fields ?>">
					<textarea cols="50" rows="10" name="text"
						id="modMailformField_text"
						class="inputbox modMailform-inputFluid <?php echo $moduleHelper->getFieldClasses('text'); ?>"></textarea>
				</div>
			</div>
<?php if ($params->get ( 'show_copy_box' )) : ?>
			<div class="control-group row-fluid">
				<label id="modMailformLabel_email_copy"
					class="control-label span<?php echo $col_indexes->labels ?>"
					for="modMailformField_email_copy"></label>
				<div class="controls span<?php echo $col_indexes->fields ?>">
					<label class="checkbox"><?php echo $moduleHelper->getFiledLabel('email_copy'); ?><input
						type="checkbox" name="email_copy" id="modMailformField_email_copy"
						value="1" /> </label>
				</div>
			</div>
<?php endif; ?>
<?php if ($params->get('captcha')) : ?>
			<div id="modMailformCaptchaHolder_<?php echo $module->id ?>"
				class="modMailformCaptchaHolder"></div>
<?php endif; ?>
		<div class="modMailform-right">
				<button class="btn btn-primary validate" type="submit">
					<i class="icon-envelope"></i> <?php echo JText::_('MOD_MAILFORM_BUTTON_SEND'); ?></button>
			</div>
		</fieldset>
	</div>
	<input type="hidden" name="action" value="sendmail" /> <input
		type="hidden" name="title" value="<?php echo $module->title ?>" />
</form>