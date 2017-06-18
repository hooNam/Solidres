<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_solidres&task=extras.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'extraList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	/*Unset JUI Core to resolve conflict with Solidres JUI*/
	$document = JFactory::getDocument();
	$rootUrl  = JUri::root(true);
	unset($document->_scripts[$rootUrl . '/media/jui/js/jquery.ui.core.min.js']);
	unset($document->_scripts[$rootUrl . '/media/jui/js/jquery.ui.sortable.min.js']);
}
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=extras'); ?>" method="post"
			      name="adminForm" id="adminForm">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table table-striped" id="extraList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_EXTRA_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class="center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_RESERVATIONASSET', 'a.reservationasset', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_EXTRA_PRICES', 'a.price', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_EXTRA_MANDATORY', 'a.mandatory', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_EXTRA_CHARGE_TYPE', 'a.charge_type', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering  = ($listOrder == 'a.ordering');
						$canCreate = $user->authorise('core.create', 'com_solidres.roomtype.' . $item->id);
						$canEdit   = $user->authorise('core.edit', 'com_solidres.roomtype.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_solidres.roomtype.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>"
						    sortable-group-id="<?php echo $item->reservation_asset_id ?>">
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
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
							<td>
								<?php if ($canCreate || $canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=extra.edit&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
										<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
							</td>
		                    <td class="center hidden-phone">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'extras.', $canChange);?>
							</td>
							<td class="hidden-phone">
		                        <a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.edit&id='.(int) $item->reservation_asset_id); ?>">
								<?php echo $item->reservationasset; ?>
		                        </a>
							</td>
							<td class="center hidden-phone">
								<?php echo $this->escape($item->price); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo $item->mandatory == 1 ? JText::_('JYES') : JText::_('JNO') ; ?>
							</td>
							<td class="hidden-phone">
								<?php echo JText::_(SRExtra::$chargeTypes[$item->charge_type]); ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php echo $this->pagination->getListFooter(); ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>