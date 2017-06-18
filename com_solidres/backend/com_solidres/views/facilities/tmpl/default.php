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
$user   = JFactory::getUser();
$userId = $user->get('id');
?>
<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<?php
			if (SRPlugin::isEnabled('hub')) :
				$listOrder = $this->state->get('list.ordering');
				$listDirn  = $this->state->get('list.direction');
				$saveOrder = $listOrder == 'r.ordering';
				if ($saveOrder):
					$saveOrderingUrl = 'index.php?option=com_solidres&task=facilities.saveOrderAjax&tmpl=component';
					JHtml::_('sortablelist.sortable', 'facilityList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
					/*Unset JUI Core to resolve conflict with Solidres JUI*/
					$document = JFactory::getDocument();
					$rootUrl  = JUri::root(true);
					unset($document->_scripts[$rootUrl . '/media/jui/js/jquery.ui.core.min.js']);
					unset($document->_scripts[$rootUrl . '/media/jui/js/jquery.ui.sortable.min.js']);
				endif;
				?>
				<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=facilities'); ?>" method="post"
				      name="adminForm" id="adminForm">
					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<table class="table table-striped" id="facilityList">
						<thead>
						<tr>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_('searchtools.sort', '', 'r.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th>
							<th width="1%">
								<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)"/>
							</th>
							<th width="1%" class="nowrap">
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_ID', 'r.id', $listDirn, $listOrder); ?>
							</th>
							<th class="title">
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_TITLE', 'r.title', $listDirn, $listOrder); ?>
							</th>
							<th class="center">
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_PUBLISHED', 'r.state', $listDirn, $listOrder); ?>
							</th>
							<th class="title">
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_SCOPE', 'r.scope_id', $listDirn, $listOrder); ?>
							</th>

						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$ordering  = ($listOrder == 'r.ordering');
							$canCreate = $user->authorise('core.create', 'com_solidres.facility.' . $item->id);
							$canEdit   = $user->authorise('core.edit', 'com_solidres.facility.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_solidres.facility.' . $item->id);
							?>
							<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->scope_id ?>">
								<td class="order nowrap center hidden-phone">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
								</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5"
										       value="<?php echo $item->ordering ?>" class="width-20 text-area-order "/>
									<?php endif; ?>
								</td>
								<td class="center">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="center">
									<?php echo (int) $item->id; ?>
								</td>
								<td>
									<?php if ($canCreate || $canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=facility.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</td>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'facilities.', $canChange); ?>
								</td>
								<td>
									<?php echo $item->scope_id == 0 ? JText::_('SR_FACILITY_SCOPE_RESERVATION_ASSET') : JText::_('SR_FACILITY_SCOPE_ROOM_TYPE') ?>
								</td>

							</tr>
						<?php endforeach; ?>
						</tbody>
						<?php echo $this->pagination->getListFooter(); ?>
					</table>
					<input type="hidden" name="task" value=""/>
					<input type="hidden" name="boxchecked" value="0"/>
					<?php echo JHtml::_('form.token'); ?>
				</form>
			<?php else : ?>
				<div class="alert alert-info">
					<?php echo JText::_('SR_FACILITY_INTRO2') ?>
				</div>

				<div class="alert alert-success">
					<?php echo JText::_('SR_FACILITY_NOTICE') ?>
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