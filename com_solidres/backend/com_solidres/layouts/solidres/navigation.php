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
jimport('solidres.string.inflector');

$updateInfo    = $displayData['updateInfo'];
$menuStructure = $displayData['menuStructure'];
$iconMap       = $displayData['iconMap'];
$inflector     = SRInflector::getInstance();
if ($inflector->isPlural($displayData['viewName']))
{
	$viewName = array($displayData['viewName'], $inflector->toSingular($displayData['viewName']));
}
else
{
	$viewName = array($displayData['viewName'], $inflector->toPlural($displayData['viewName']));
}
?>
<div id="sr_panel_left" class="span2">
	<ul id="sr_side_navigation" class="<?php echo $inflector->isSingular($displayData['viewName']) ? 'disabled' : ''; ?>">
		<li class="sr_tools">
			<a href="#" id="sr-toggle">
				<i class="fa fa-chevron-circle-left"></i>
			</a>
			<a id="sr_dashboard" href="<?php echo JRoute::_('index.php?option=com_solidres', false); ?>"
			   title="<?php echo JText::_('SR_SUBMENU_DASHBOARD', true); ?>">
				<img src="<?php echo JUri::root(true); ?>/media/com_solidres/assets/images/logo.png" alt="Solidres"
				     title="Solidres"/>	
			</a>
			<span id="sr_current_ver">
				<?php echo SRVersion::getShortVersion(); ?>
				<?php if (isset($updateInfo['com_solidres']) && version_compare(SRVersion::getBaseVersion(), $updateInfo['com_solidres'], 'lt')): ?>
					<a href="https://www.solidres.com/download/show-all-downloads/solidres"
					   id="sr-update-note"
					   target="_blank"
					   title="New update (v<?php echo $updateInfo['com_solidres']; ?>) is available">
						<i class="fa fa-warning"></i>
					</a>
				<?php else: ?>
					<i title="You are using the latest version" class="fa fa-check"></i>
				<?php endif; ?>
			</span>
		</li>
		<?php foreach ($menuStructure as $menuName => $menuDetails):
			$name = strtolower(substr($menuName, 11));
			?>
			<li class="sr_toggle" id="sr_sn_<?php echo $name; ?>">
				<a class="sr_indicator" style="cursor: pointer">Open</a>
				<a class="sr_title">
					<i class="<?php echo $iconMap[$name]; ?>"></i>
					<span><?php echo JText::_($menuName); ?></span>
				</a>
				<ul>
					<?php foreach ($menuDetails as $menu):
						$parts = parse_url($menu[1]);
						parse_str($parts['query'], $query);
						?>
						<li class="<?php echo @in_array($query['view'], $viewName) ? 'active' : ''; ?>">
							<a href="<?php echo JRoute::_($menu[1]); ?>"
							   id="<?php echo strtolower($menu[0]); ?>">
								<?php echo JText::_($menu[0]); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</li>
		<?php endforeach; ?>
	</ul>
</div>