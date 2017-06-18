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
	<?php echo JText::_('SR_FACILITY_INTRO') ?>
</div>

<?php if (SRPlugin::isEnabled('hub')) : ?>
	<div id="facility-selection-holder">
		<?php echo $this->form->getInput('facility_id'); ?>
	</div>
<?php else : ?>
	<div class="alert alert-success">
		<?php echo JText::_('SR_FACILITY_NOTICE') ?>
	</div>
<?php endif ?>



