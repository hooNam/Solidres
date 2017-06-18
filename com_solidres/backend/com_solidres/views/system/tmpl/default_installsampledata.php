<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die;

?>
<div class="row-fluid">
	<div class="span12">
		<h3>Sample data</h3>
		<div class="alert alert-block">
			<?php if ( $this->hasExistingData > 0 ) : ?>
				<p>Your Solidres tables already have data.</p>
			<?php else : ?>
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<h4><?php echo JText::_( 'SR_SYSTEM_INSTALL_SAMPLE_DATA_WARNING' ) ?></h4>
				<?php echo JText::_( 'SR_SYSTEM_INSTALL_SAMPLE_DATA_WARNING_MESSAGE' ) ?>
				<a href="<?php echo JRoute::_( 'index.php?option=com_solidres&task=system.installsampledata' ) ?>"
				   class="btn btn-large btn-info">
					<?php echo JText::_( 'SR_SYSTEM_INSTALL_SAMPLE_DATA_WARNING_BTN' ) ?>
				</a>
			<?php endif ?>
		</div>
	</div>
</div>
