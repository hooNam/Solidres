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
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_solidres&task=reservationassets.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'reservationassetList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	/*Unset JUI Core to resolve conflict with Solidres JUI*/
	$document = JFactory::getDocument();
	$rootUrl = JUri::root(true);
	unset($document->_scripts[$rootUrl.'/media/jui/js/jquery.ui.core.min.js']);
	unset($document->_scripts[$rootUrl.'/media/jui/js/jquery.ui.sortable.min.js']);
}
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
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
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=reservationassets'); ?>" method="post" name="adminForm" id="adminForm">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table table-striped" id="reservationassetList">
					<thead>
						<tr>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th>
							<th width="1%" class="center">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
							</th>

							<th class="category_name hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_CATEGORY', 'category_name', $listDirn, $listOrder); ?>
							</th>

							<th class="center hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_NUMBERROOMTYPE', 'number_of_roomtype', $listDirn, $listOrder); ?>
							</th>
							<th class="city_name hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_CITY', 'a.city', $listDirn, $listOrder); ?>
							</th>
							<th class="country_name hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_COUNTRY', 'country_name', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_HITS', 'a.hits', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$canCreate	= $user->authorise('core.create',		'com_solidres.reservationasset.'.$item->id);
						$canEdit	= $user->authorise('core.edit',			'com_solidres.reservationasset.'.$item->id);
						$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
						$canChange	= $user->authorise('core.edit.state',	'com_solidres.reservationasset.'.$item->id);
						?>
						<tr class="row<?php echo $i % 2; ?> <?php echo $item->approved === '0' ? 'info' : '' ?>" sortable-group-id="<?php echo $item->category_id ?>">
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
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering ?>" class="width-20 text-area-order "/>
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
							<td style="width: 35%">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'reservationassets.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canCreate || $canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.edit&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
										<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
								<?php if ($item->default == 1) : ?>
                                <a href="#" title="<?php echo JText::_('SR_HEADING_DEFAULT') ?>"><i class="icon-star"></i></a>
								<?php endif ?>

								<?php if ($item->number_of_roomtype == 0) : ?>
									<span class="no-roomtype-warning"><i class="fa fa-exclamation-triangle"></i> <?php echo JText::_('SR_ASSET_WARNING_NO_ROOMTYPE') ?></span>
								<?php endif ?>
							</td>
							<td class="center hidden-phone">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'reservationassets.', $canChange);?>
							</td>
							<td class="hidden-phone">
								<a href="<?php echo JRoute::_('index.php?option=com_categories&extension=com_solidres&task=category.edit&id='.(int) $item->category_id); ?>">
								<?php echo $item->category_name;?>
								</a>
							</td>
							<td class="center hidden-phone">
								<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=roomtypes&filter_reservation_asset_id=' . $item->id) ?>">
									<?php echo $item->number_of_roomtype?>
								</a>
							</td>
							<td class="hidden-phone">
								<?php echo $item->city;?>
							</td>
							<td style="width: 15%" class="hidden-phone">
								<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=country.edit&id='.(int) $item->country_id); ?>">
								<?php echo $item->country_name;?>
								</a>
							</td>
							<td class="hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="hidden-phone">
								<?php echo $item->hits; ?>
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
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>
