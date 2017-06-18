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
$config = JFactory::getConfig();
$timezone = new DateTimeZone($config->get('offset'));
?>
<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=coupons'); ?>" method="post" name="adminForm" id="adminForm">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
                            <th width="1%" class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_ID', 'u.id', $listDirn, $listOrder); ?>
                            </th>
							<th class="title">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_NAME', 'u.coupon_name', $listDirn, $listOrder); ?>
							</th>
							<th class="center hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_PUBLISHED', 'u.state', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_RESERVATIONASSET', 'reservationasset', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_CODE', 'u.coupon_code', $listDirn, $listOrder); ?>
							</th>							
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_AMOUNT', 'u.amount', $listDirn, $listOrder); ?>
							</th>							
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_PERCENT', 'u.is_percent', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_QUANTITY', 'u.quantity', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_VALID_FROM', 'u.valid_from', $listDirn, $listOrder); ?>
							</th>							
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_COUPON_VALID_TO', 'u.valid_to', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$canCreate	= $user->authorise('core.create', 'com_solidres.coupon.'.$item->id);
						$canEdit	= $user->authorise('core.edit',	'com_solidres.coupon.'.$item->id);
						$canChange	= $user->authorise('core.edit.state', 'com_solidres.coupon.'.$item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
                            <td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
                            </td>
							<td>
								<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=coupon.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->coupon_name); ?></a>
								</a>
							</td>
							<td class="center hidden-phone">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'coupons.', $canChange);?>
							</td>
							<td class="hidden-phone">
								<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.edit&id='.(int) $item->reservation_asset_id); ?>">
									<?php echo $item->reservationasset; ?>
								</a>
							</td>
							<td>
								<span class="label label-info"><?php echo $item->coupon_code; ?></span>
							</td>							
							<td class="hidden-phone">
								<?php echo $item->amount; ?>
							</td>							
							<td class="hidden-phone">
								<?php echo $this->escape( (int) $item->is_percent == 1 ? JText::_('JYES') : JText::_('JNO') ); ?>
							</td>
							<td class="hidden-phone">
								<?php echo is_numeric($item->quantity) ? $item->quantity : JText::_('SR_COUPON_QUANTITY_UNLIMITED') ; ?>
							</td>
							<td class="hidden-phone">
								<?php
								echo JFactory::getDate($item->valid_from, 'UTC')
								             ->setTimezone($timezone)
								             ->format('Y-m-d', true, false);
								?>
							</td>							
							<td class="hidden-phone">
								<?php
								echo JFactory::getDate($item->valid_to, 'UTC')
								             ->setTimezone($timezone)
								             ->format('Y-m-d', true, false);
								?>
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

