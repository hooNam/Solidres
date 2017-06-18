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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$loggeduser = JFactory::getUser();
?>

<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<?php if (SRPlugin::isEnabled('user')) : ?>
				<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=customers'); ?>" method="post" name="adminForm" id="adminForm">
					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<table class="table table-striped">
						<thead>
						<tr>
							<th width="1%">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th width="1%" class="nowrap">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
							</th>
							<th>
								<?php echo JText::_('SR_HEADING_CUSTOMER_FULLNAME'); ?>
							</th>
							<th>
								<?php echo JText::_('SR_HEADING_CUSTOMER_USERNAME'); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'SR_HEADING_CUSTOMER_ENABLED', 'u.block', $this->listDirn, $this->listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_CUSTOMER_GROUP_NAME', 'r.group_name', $this->listDirn, $this->listOrder); ?>
							</th>
							<th>
								<?php echo JText::_('SR_HEADING_CUSTOMER_EMAIL'); ?>
							</th>
							<th>
								<?php echo JText::_('SR_HEADING_CUSTOMER_REGISTER_DATE'); ?>
							</th>
							<th>
								<?php echo JText::_('SR_HEADING_CUSTOMER_LASTVISIT_DATE'); ?>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$ordering	= ($this->listOrder == 'r.ordering');
							$canCreate	= $this->user->authorise('core.create',		'com_solidres.customer.'.$item->id);
							$canEdit	= $this->user->authorise('core.edit',		'com_solidres.customer.'.$item->id);
							$canChange	= $this->user->authorise('core.edit.state',	'com_solidres.customer.'.$item->id);
							$customerGroupEditLink = '';
							if ($item->customer_group_id > 0) :
								$customerGroupEditLink = JRoute::_('index.php?option=com_solidres&task=customergroup.edit&id='.(int) $item->customer_group_id);
							endif;
							$customerEditLink = JRoute::_('index.php?option=com_solidres&task=customer.edit&id='.(int) $item->id);
							$fullName = $item->firstname. ' ' . $item->middlename .' '. $item->lastname;
							$groupName = is_null($item->group_name) ? JText::_('SR_GENERAL_CUSTOMER_GROUP') : $item->group_name;
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="center">
									<?php echo $item->id; ?>
								</td>
								<td>
									<?php if ($canCreate || $canEdit) : ?>
										<a href="<?php echo $customerEditLink; ?>">
											<?php echo $fullName ?>
										</a>
									<?php else : ?>
										<?php echo $fullName ?>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $item->jusername ?>
								</td>
								<!--<td>
								<?php /*if ($canCreate || $canEdit) : */?>
									<a href="<?php /*echo $customerEditLink; */?>">
										<?php /*echo $this->escape($item->customer_code); */?></a>
								<?php /*else : */?>
										<?php /*echo $this->escape($item->customer_code); */?>
								<?php /*endif; */?>
							</td>-->
								<td class="center">
									<?php if ($canChange) : ?>
										<?php
										$self = $loggeduser->id == $item->id;
										echo JHtml::_('jgrid.state', $this->blockStates($self), $item->jblock, $i, 'customers.', !$self);
										?>
									<?php else : ?>
										<?php echo JText::_($item->block ? 'JNO' : 'JYES'); ?>
									<?php endif; ?>
								</td>
								<td>
									<?php if( ($canCreate || $canEdit) && !empty($customerGroupEditLink)) : ?>
										<a href="<?php echo $customerGroupEditLink ?>">
											<?php echo $groupName ?>
										</a>
									<?php else :
										echo $groupName;
									endif; ?>
								</td>
								<td>
									<?php echo $item->jemail ?>
								</td>
								<td>
									<?php echo $item->jregisterDate ?>
								</td>
								<td>
									<?php echo $item->jlastvisitDate ?>
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
					This feature allows your guest to register an account at your website while making reservation. When a guest has an account at your website, you can manage them in backend, create tariffs specified for them. In addition, with an account the reservation process will be much faster because many guest's info will be auto-filled.
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
