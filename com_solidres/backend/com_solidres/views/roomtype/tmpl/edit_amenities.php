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

<div class="alert alert-info">
	<?php echo JText::_('SR_TRIPCONNECT_AMENITIES_INTRO') ?>
</div>

<?php
if (SRPlugin::isEnabled('tripconnect')) :
	$fieldSets = $this->form->getFieldsets('amenities');
	foreach ($fieldSets as $fieldSet) :
?>
	<fieldset>
		<legend><?php echo JText::_($fieldSet->label) ?></legend>
		<?php foreach ($this->form->getFieldset($fieldSet->name) as $field) : ?>
			<div class="control-group">
				<?php echo $field->label; ?>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</fieldset>
<?php
	endforeach;
else : ?>
<div class="alert alert-success">
	<?php echo JText::_('SR_TRIPCONNECT_NOTICE') ?>
</div>
<?php
endif;
?>



