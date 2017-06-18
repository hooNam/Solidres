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

SRHtml::_('jquery.editable');
$user   = JFactory::getUser();
$userId	= $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'r.id';
$config = JFactory::getConfig();
$timezone = new DateTimeZone($config->get('offset'));

$statuses = array(
	0 => JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL'),
	1 => JText::_('SR_RESERVATION_STATE_CHECKED_IN'),
	2 => JText::_('SR_RESERVATION_STATE_CHECKED_OUT'),
	3 => JText::_('SR_RESERVATION_STATE_CLOSED'),
	4 => JText::_('SR_RESERVATION_STATE_CANCELED'),
	5 => JText::_('SR_RESERVATION_STATE_CONFIRMED'),
	-2 => JText::_('JTRASHED')
);

$paymentStatuses = array(
	0 => JText::_('SR_RESERVATION_PAYMENT_STATUS_UNPAID'),
	1 => JText::_('SR_RESERVATION_PAYMENT_STATUS_COMPLETED'),
	2 => JText::_('SR_RESERVATION_PAYMENT_STATUS_CANCELLED'),
	3 => JText::_('SR_RESERVATION_PAYMENT_STATUS_PENDING'),
);

$badges = array(
	0 => 'label-pending',
	1 => 'label-info',
	2 => 'label-inverse',
	3 => '',
	4 => 'label-warning',
	5 => 'label-success',
	-2 => 'label-important'
);

$script =
	' Solidres.jQuery(function($) {
		$.fn.editable.defaults.mode = "inline";
		$( ".state_edit" ).editable({
			url: "' .  JRoute::_('index.php?option=com_solidres&task=reservationbase.save&format=json', false) . '",
			source: [
				{value: 0, text: "'. JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL') . '"},
				{value: 1, text: "'. JText::_('SR_RESERVATION_STATE_CHECKED_IN') . '"},
				{value: 2, text: "'. JText::_('SR_RESERVATION_STATE_CHECKED_OUT') . '"},
				{value: 3, text: "'. JText::_('SR_RESERVATION_STATE_CLOSED') . '"},
				{value: 4, text: "'. JText::_('SR_RESERVATION_STATE_CANCELED') . '"},
				{value: 5, text: "'. JText::_('SR_RESERVATION_STATE_CONFIRMED') . '"},
				{value: -2, text: "'. JText::_('JTRASHED') . '"}
			],
			success: function(response, newValue) {
				var parent = $(this).parents("tr"); 
		        var span = parent.find("td.reservation-code-row span")
		        span.removeClass(span.attr("class")).addClass("reservation-code reservation-code-" + newValue);
		    }
		});


		$( ".state_edit" ).on("save", function(e, params) {
			'.((SRPlugin::isEnabled('channelmanager')) ? 'showARIUpdateStatus($(this).data("editable").options.assetid);' : '' ).'
		});
	});';
JFactory::getDocument()->addScriptDeclaration($script);

?>

<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=reservations'); ?>" method="post" name="adminForm" id="adminForm">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_ID', 'r.id', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_RESERVATION_CODE', 'r.code', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort',  'SR_HEADING_RESERVATIONASSET', 'reservationasset', $listDirn, $listOrder); ?>
							</th>
                            <th>
								<?php echo JHtml::_('searchtools.sort',  'SR_RESERVATION_STATUS', 'r.state', $listDirn, $listOrder); ?>
                            </th>
							<th class="hidden-phone">
								<?php echo JText::_('SR_RESERVATION_PAYMENT_STATUS'); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_RESERVATION_CUSTOMER', 'customer_fullname', $listDirn, $listOrder); ?>
							</th>
                            <th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_RESERVATION_CHECKIN', 'r.checkin', $listDirn, $listOrder); ?>
                            </th>
                            <th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_RESERVATION_CHECKOUT', 'r.checkout', $listDirn, $listOrder); ?>
                            </th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_CUSTOM_FIELD_RESERVATION_CREATE_DATE', 'r.created_date', $listDirn, $listOrder); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JHtml::_('searchtools.sort',  'SR_RESERVATION_ORIGIN', 'r1.origin', $listDirn, $listOrder); ?>
							</th>
						</tr>		                
					</thead>					
					<tbody>
					<?php
						foreach ($this->items as $i => $item) :
						$ordering	= ($listOrder == 'a.ordering');
						$canCreate	= $user->authorise('core.create',       'com_solidres.reservation.'.$item->id);
						$canEdit	= $user->authorise('core.edit',	        'com_solidres.reservation.'.$item->id);
						$canChange	= $user->authorise('core.edit.state',   'com_solidres.reservation.'.$item->id);
						$editLink	= JRoute::_('index.php?option=com_solidres&task=reservationbase.edit&id='.(int) $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?> <?php echo $item->accessed_date == '0000-00-00 00:00:00' ? 'warning' : '' ?>">
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="hidden-phone">
								<?php echo $item->id; ?>
							</td>
							<td class="reservation-code-row">
								<span class="reservation-code-<?php echo $item->state ?> reservation-code">
									<a href="<?php echo $editLink ?>">
										<?php echo $this->escape($item->code); ?>
									</a>
								</span>
							</td>
							<td>
								<?php echo $item->reservation_asset_name; ?>
							</td>
                            <td>
								<a href="#"
								      id="state<?php echo $item->id ?>"
								      class="state_edit"
								      data-type="select"
								      data-name="state"
								      data-pk="<?php echo $item->id ?>"
								      data-value="<?php echo $item->state ?>"
								      data-assetid="<?php echo $item->reservation_asset_id ?>"
								      data-original-title=""><?php echo $statuses[$item->state]; ?></a>
                            </td>
							<td class="hidden-phone">
								<?php echo $paymentStatuses[$item->payment_status]; ?><br />
								<?php echo $item->payment_method_txn_id; ?>
							</td>
                            <td class="hidden-phone">
								<?php echo $item->customer_firstname .' '. $item->customer_middlename .' '. $item->customer_lastname ?>
							</td>
							<td class="hidden-phone">
								<?php
								//echo JHtml::_('date', $item->checkin, $this->dateFormat);
								echo $item->checkin;
								?>
							</td>
							<td class="hidden-phone">
								<?php
								//echo JHtml::_('date', $item->checkout, $this->dateFormat);
								echo $item->checkout;
								?>
							</td>
                            <td class="hidden-phone">
								<?php
								//echo JHtml::_('date', $item->created_date, $this->dateFormat);
								echo $item->created_date;
								?>
                            </td>
							<td class="hidden-phone">
								<?php echo $item->origin; ?>
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
