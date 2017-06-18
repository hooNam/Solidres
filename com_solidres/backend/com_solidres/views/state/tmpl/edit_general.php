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
	<div class="control-group">
		<?php echo $this->form->getLabel('name'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('name'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('country_id'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('country_id'); ?>
		</div>
	</div>	
	<div class="control-group">
		<?php echo $this->form->getLabel('code_2'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('code_2'); ?>
		</div>
	</div>	
		<div class="control-group">
		<?php echo $this->form->getLabel('code_3'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('code_3'); ?>
		</div>
	</div>
		<div class="control-group">
		<?php echo $this->form->getLabel('state'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('state'); ?>
		</div>
	</div>
	<div class="sr-clear"></div>
</fieldset>