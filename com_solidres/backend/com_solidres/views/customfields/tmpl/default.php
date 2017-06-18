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
<div id="solidres">
	<div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<div class="alert alert-info">
				This feature allows you manage custom field for customer
			</div>
			<div class="alert alert-success">
				<strong>Notice:</strong> plugin <strong>Custom Field</strong> is not installed or enabled.
				<a target="_blank"
				   href="https://www.solidres.com/subscribe/levels">Become
					a subscriber and download it now.</a>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>