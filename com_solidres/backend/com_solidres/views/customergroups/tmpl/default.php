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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$user	= JFactory::getUser();
$userId	= $user->get('id');
?>

<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<?php
			if (SRPlugin::isEnabled('user')) :
				$listOrder	= $this->state->get('list.ordering');
				$listDirn	= $this->state->get('list.direction');
			?>
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=customergroups'); ?>" method="post" name="adminForm" id="adminForm">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
		                    <th width="1%" class="nowrap">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
							<th class="title">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
							</th>
		                    <th width="15%">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering	= ($listOrder == 'r.ordering');						
						$canCreate	= $user->authorise('core.create',		'com_solidres.customergroup.'.$item->id);
						$canEdit	= $user->authorise('core.edit',			'com_solidres.customergroup.'.$item->id);
						$canChange	= $user->authorise('core.edit.state',	'com_solidres.customergroup.'.$item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
		                    <td class="center">
								<?php echo (int) $item->id; ?>
							</td>
							<td>
								<?php if ($canCreate || $canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=customergroup.edit&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
										<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
							</td>
		                    <td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'customergroups.', $canChange);?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php echo $this->pagination->getListFooter(); ?>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
			<?php else : ?>
				<div class="alert alert-info">
					This feature allows your guest to register an account at your website while making reservation. When a guest has an account at your website, you can manage them in backend, create tariffs specified for them. In addition, with an account the reservation will be faster because many guest's info will be auto-filled.
				</div>

				<div class="alert alert-success">
					<strong>Notice:</strong> plugin User is not installed or enabled. <a target="_blank" href="https://www.solidres.com/subscribe/levels">Become a subscriber and download it now.</a>
				</div>
			<?php endif ?>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>