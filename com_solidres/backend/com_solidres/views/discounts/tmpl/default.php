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
$user     = JFactory::getUser();
$userId   = $user->get('id');
$config   = JFactory::getConfig();
$timezone = new DateTimeZone($config->get('offset'));

?>
<div id="solidres">
	<div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<?php
			if (SRPlugin::isEnabled('discount')) :
				$listOrder = $this->state->get('list.ordering');
				$listDirn  = $this->state->get('list.direction');
				$saveOrder = $listOrder == 'a.ordering';
				?>
				<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=discounts'); ?>" method="post"
				      name="adminForm" id="adminForm">
					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<table class="table table-striped">
						<thead>
						<tr>
							<th width="20">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th width="1%" class="nowrap">
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
							<th class="title">
								<?php echo JHtml::_('searchtools.sort', 'SR_FIELD_DISCOUNT_TITLE_LABEL', 'a.title', $listDirn, $listOrder); ?>
							</th>
							<th class="center">
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_RESERVATIONASSET', 'reservationasset', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'SR_FIELD_DISCOUNT_VALID_FROM_LABEL', 'a.valid_from', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'SR_FIELD_DISCOUNT_VALID_TO_LABEL', 'a.valid_to', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'SR_FIELD_DISCOUNT_PRIORITY_LABEL', 'a.priority', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'SR_FIELD_DISCOUNT_STOP_FURTHER_PROCESSING_LABEL', 'a.stop_further_processing', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$ordering  = ($listOrder == 'a.ordering');
							$canCreate = $user->authorise('core.create', 'com_solidres.reservationasset.' . $item->reservation_asset_id);
							$canEdit   = $user->authorise('core.edit', 'com_solidres.reservationasset.' . $item->reservation_asset_id);
							$canChange = $user->authorise('core.edit.state', 'com_solidres.reservationasset.' . $item->reservation_asset_id);
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
										<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=discount.edit&id='.(int) $item->id); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</td>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'discounts.', $canChange);?>
								</td>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.edit&id='.(int) $item->reservation_asset_id); ?>">
										<?php echo $item->reservationasset; ?>
									</a>
								</td>
								<td>
									<?php
									/*echo JFactory::getDate($item->valid_from, 'UTC')
									             ->setTimezone($timezone)
									             ->format('Y-m-d', true, false);*/

									echo $item->valid_from;
									?>
								</td>
								<td>
									<?php
									/*echo JFactory::getDate($item->valid_to, 'UTC')
									             ->setTimezone($timezone)
									             ->format('Y-m-d', true, false);*/
									echo $item->valid_to;
									?>
								</td>
								<td>
									<?php echo $item->priority; ?>
								</td>
								<td>
									<?php echo $item->stop_further_processing ? JText::_('JYES') : JText::_('JNO') ; ?>
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
				<?php else : ?>
					<div class="alert alert-info">
						This feature allows you to take some or all of your rooms out of service either for renovation or other reasons.
					</div>

					<div class="alert alert-success">
						<strong>Notice:</strong> plugin Discount is not installed or enabled. <a target="_blank" href="https://www.solidres.com/subscribe/levels">Become a subscriber and download it now.</a>
					</div>
				<?php endif ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12 powered">
				<p>Powered by <a href="http://wwww.solidres.com" target="_blank">Solidres</a></p>
			</div>
		</div>
	</div>