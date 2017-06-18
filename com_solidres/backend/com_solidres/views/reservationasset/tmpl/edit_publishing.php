<?php 
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

?>
<fieldset>
	<?php foreach ($this->form->getFieldset('menu_fields') as $field): ?>
		<?php echo $field->renderField(); ?>
	<?php endforeach; ?>

	<?php if ($menuId = (int) $this->form->getValue('menu_id')): ?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('SR_MENU_ITEM'); ?>
			</label>
			<div class="controls">
				<a href="<?php echo JUri::root(true); ?>/administrator/index.php?option=com_menus&task=item.edit&id=<?php echo $menuId; ?>"
				   target="_blank">
					<i class="fa fa-pencil-square-o"></i>
					<?php echo JText::_('SR_MANAGE_MENU_ITEM'); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>
	<div class="control-group">
		<?php echo $this->form->getLabel('state'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('state'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('default'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('default'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('approved'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('approved'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('rating'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('rating'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('id'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('id'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('created_by'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('created_by'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('created_date'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('created_date'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('modified_date'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('modified_date'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('access'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('access'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label"><span class="spacer"><span class="before"></span><span><hr></span><span class="after"></span></span></div>

	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('deposit_required'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('deposit_required'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('deposit_is_percentage'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('deposit_is_percentage'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('deposit_amount'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('deposit_amount'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('deposit_by_stay_length'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('deposit_by_stay_length'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('deposit_include_extra_cost'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('deposit_include_extra_cost'); ?>
		</div>
	</div>
</fieldset>

