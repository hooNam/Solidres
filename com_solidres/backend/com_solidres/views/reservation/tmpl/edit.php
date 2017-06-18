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
$lang = JFactory::getLanguage();
$paymentMethodId = $this->form->getValue('payment_method_id', '');

if (!empty($paymentMethodId))
{
	$lang->load('plg_solidrespayment_'.$paymentMethodId, JPATH_PLUGINS . '/solidrespayment/' . $paymentMethodId);
}

JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');

$isDiscountPreTax = $this->form->getValue('discount_pre_tax');

$baseCurrency = new SRCurrency(0, $this->form->getValue('currency_id'));
$totalExtraPriceTaxIncl = $this->form->getValue('total_extra_price_tax_incl');
$totalExtraPriceTaxExcl = $this->form->getValue('total_extra_price_tax_excl');
$totalExtraTaxAmount = $totalExtraPriceTaxIncl - $totalExtraPriceTaxExcl;
$totalPaid = $this->form->getValue('total_paid');
$deposit = $this->form->getValue('deposit_amount');

$subTotal = clone $baseCurrency;
$subTotal->setValue($this->form->getValue('total_price_tax_excl') - $this->form->getValue('total_single_supplement'));

$totalSingleSupplement = clone $baseCurrency;
$totalSingleSupplement->setValue($this->form->getValue('total_single_supplement'));

$totalDiscount = clone $baseCurrency;
$totalDiscount->setValue($this->form->getValue('total_discount'));

$tax = clone $baseCurrency;
$tax->setValue($this->form->getValue('tax_amount'));
$totalExtraPriceTaxExclDisplay = clone $baseCurrency;
$totalExtraPriceTaxExclDisplay->setValue($totalExtraPriceTaxExcl);
$totalExtraTaxAmountDisplay = clone $baseCurrency;
$totalExtraTaxAmountDisplay->setValue($totalExtraTaxAmount);
$grandTotal = clone $baseCurrency;

if ($isDiscountPreTax) :
	$grandTotal->setValue($this->form->getValue('total_price_tax_excl') - $this->form->getValue('total_discount') + $this->form->getValue('tax_amount') + $totalExtraPriceTaxIncl);
else :
	$grandTotal->setValue($this->form->getValue('total_price_tax_excl') + $this->form->getValue('tax_amount') - $this->form->getValue('total_discount') + $totalExtraPriceTaxIncl);
endif;


$depositAmount = clone $baseCurrency;
$depositAmount->setValue(isset($deposit) ? $deposit : 0);
$totalPaidAmount = clone $baseCurrency;
$totalPaidAmount->setValue(isset($totalPaid) ? $totalPaid : 0);

$couponCode = $this->form->getValue('coupon_code');
$reservationId = $this->form->getValue('id');
$reservationState = $this->form->getValue('state');
$paymentStatus = $this->form->getValue('payment_status');
$bookingType = $this->form->getValue('booking_type', 0);

$badges = array(
	0 => 'label-pending',
	1 => 'label-info',
	2 => 'label-inverse',
	3 => '',
	4 => 'label-warning',
	5 => 'label-success',
	-2 => 'label-important'
);

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

SRHtml::_('jquery.editable');

$script =
	' Solidres.jQuery(function($) {
		$.fn.editable.defaults.mode = "inline";
		$( "#state" ).editable({
			url: "' .  JRoute::_('index.php?option=com_solidres&task=reservationbase.save&format=json', false) . '",
			source: [
				{value: 0, text: "'. JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL') . '"},
				{value: 1, text: "'. JText::_('SR_RESERVATION_STATE_CHECKED_IN') . '"},
				{value: 2, text: "'. JText::_('SR_RESERVATION_STATE_CHECKED_OUT') . '"},
				{value: 3, text: "'. JText::_('SR_RESERVATION_STATE_CLOSED') . '"},
				{value: 4, text: "'. JText::_('SR_RESERVATION_STATE_CANCELED') . '"},
				{value: 5, text: "'. JText::_('SR_RESERVATION_STATE_CONFIRMED') . '"},
				{value: -2, text: "'. JText::_('JTRASHED') . '"}
			]
		});

		$("#state").on("save", function(e, params) {
		    ' . ((SRPlugin::isEnabled('channelmanager')) ? 'showARIUpdateStatus(' . $this->form->getValue('reservation_asset_id') . ')' : '') . ';
		});

		$( "#payment_status" ).editable({
			url: "' .  JRoute::_('index.php?option=com_solidres&task=reservationbase.save&format=json', false) . '",
			source: [
				{value: 0, text: "'. JText::_('SR_RESERVATION_PAYMENT_STATUS_UNPAID') . '"},
				{value: 1, text: "'. JText::_('SR_RESERVATION_PAYMENT_STATUS_COMPLETED') . '"},
				{value: 2, text: "'. JText::_('SR_RESERVATION_PAYMENT_STATUS_CANCELLED') . '"},
				{value: 3, text: "'. JText::_('SR_RESERVATION_PAYMENT_STATUS_PENDING') . '"}
			]
		});

		$( "#total_paid" ).editable({
			url: "' .  JRoute::_('index.php?option=com_solidres&task=reservationbase.save&format=json', false) . '",
			display: function (value, response) {
				if (response) {
					if (response.success == true) {
						$(this).text(response.newValue);
					}
				}
			}
		});
		$( "#payment_method_txn_id" ).editable({
			url: "' .  JRoute::_('index.php?option=com_solidres&task=reservationbase.save&format=json', false) . '",
			display: function (value, response) {
				if (response) {
					if (response.success == true) {
						$(this).text(response.newValue);
					}
				}
			}
		});
		$( "#origin" ).editable({
			url: "' .  JRoute::_('index.php?option=com_solidres&task=reservationbase.save&format=json', false) . '",
			display: function (value, response) {
				if (response) {
					if (response.success == true) {
						$(this).text(response.newValue);
					}
				}
			}
		});
	});';
JFactory::getDocument()->addScriptDeclaration($script);

$config = JFactory::getConfig();
$timezone = new DateTimeZone($config->get('offset'));
$id = $this->form->getValue('id');
$paymentMethodTxnId = $this->form->getValue('payment_method_txn_id');
$origin = $this->form->getValue('origin');

JFactory::getDocument()->addScriptDeclaration('
	Solidres.jQuery(document).ready(function($) {
		$("#item-form").validate({onsubmit: false});
	});

	Joomla.submitbutton = function(task)
	{
		if (task == "reservationbase.cancel" || task == "reservationbase.amend")
		{
			Solidres.jQuery("#item-form").validate().resetForm();
			Joomla.submitform(task, document.getElementById("item-form"), false);
		} else {
			alert("' . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '");
		}
	}
');

if ( $paymentMethodId && $paymentStatus == '1' && (float) $totalPaid > 0.01 && $this->form->getValue('payment_method_txn_id'))
{
	JPluginHelper::importPlugin('solidrespayment', $paymentMethodId);
	$dispatcher = JEventDispatcher::getInstance();
	$refund     = join("\n", $dispatcher->trigger('onSolidresPaymentRefundDisplay', array($this->form)));
}
?>

<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_form_view span10">
			<div class="row-fluid">
				<div class="span12 reservation-detail-box">
                    <h3><?php echo JText::_("SR_GENERAL_INFO")?></h3>
					<div class="row-fluid">
                        <div class="span6">

                            <ul class="reservation-details">
                                <li><label><?php echo JText::_("SR_CODE")?></label>  <span class="label <?php echo $badges[$reservationState] ?>"><?php echo $this->form->getValue('code') ?></span> </li>
								<li>
									<label><?php echo JText::_("SR_RESERVATION_ASSET_NAME")?></label>
									<?php
									$assetLink = JRoute::_('index.php?option=com_solidres&view=reservationasset&layout=edit&id=' . $this->form->getValue('reservation_asset_id'));
									echo "<a href=\"$assetLink\">" . $this->form->getValue('reservation_asset_name') ."</a>" ?>
								</li>
                                <li>
	                                <label><?php echo JText::_("SR_CHECKIN")?></label>
	                                <?php
	                                //echo JHtml::_('date', $this->form->getValue('checkin'), $this->dateFormat);
	                                echo $this->form->getValue('checkin');
	                                ?>
                                </li>
                                <li>
	                                <label><?php echo JText::_("SR_CHECKOUT")?></label>
	                                <?php
	                                //echo JHtml::_('date', $this->form->getValue('checkout'), $this->dateFormat);
	                                echo $this->form->getValue('checkout');
	                                ?>
                                </li>
								<li>
									<label><?php echo JText::_("SR_LENGTH_OF_STAY")?></label>
									<?php
									if ($bookingType == 0) :
										echo JText::plural('SR_NIGHTS', $this->lengthOfStay);
									else :
										echo JText::plural('SR_DAYS', $this->lengthOfStay + 1);
									endif;
									?>
								</li>
	                            <li>
		                            <label><?php echo JText::_("SR_STATUS")?></label>
		                            <a href="#"
		                               id="state"
		                               data-type="select"
		                               data-pk="<?php echo $reservationId ?>"
		                               data-value="<?php echo $reservationState ?>"
		                               data-original-title=""><?php echo $statuses[$reservationState] ?></a>
	                            </li>
	                            <li>
		                            <label><?php echo JText::_("SR_RESERVATION_ORIGIN")?></label>
		                            <a href="#"
		                               id="origin"
		                               data-type="text"
		                               data-pk="<?php echo $reservationId ?>"
		                               data-value="<?php echo isset($origin) ? $origin : '' ?>"
		                               data-original-title=""><?php echo isset($origin) ? $origin : '' ?></a>
	                            </li>
                                <li>
	                                <label><?php echo JText::_("SR_CREATED_DATE")?></label>
	                                <?php
	                                //echo JHtml::_('date', $this->form->getValue('created_date'), $this->dateFormat);
	                                echo $this->form->getValue('created_date');
	                                ?>

                                </li>
                                <li><label><?php echo JText::_("SR_PAYMENT_TYPE")?></label> <?php echo !empty($paymentMethodId) ? JText::_('SR_PAYMENT_METHOD_' . $paymentMethodId) : 'N/A' ?></li>
	                            <li><label><?php echo JText::_('SR_RESERVATION_COUPON_CODE') ?></label> <?php echo !empty($couponCode) ? $couponCode : 'N/A' ?></li>
                            </ul>
                        </div>

						<div class="span6">
                            <ul class="reservation-details">
	                            <li>
		                            <label><?php echo JText::_("SR_RESERVATION_PAYMENT_STATUS")?></label>
		                            <span><a href="#"
		                               id="payment_status"
		                               data-type="select"
		                               data-pk="<?php echo $reservationId ?>"
		                               data-value="<?php echo $paymentStatus ?>"
		                               data-original-title=""><?php echo isset($paymentStatuses[$paymentStatus]) ? $paymentStatuses[$paymentStatus] : 'N/A' ?></a>
			                        </span>
	                            </li>
	                            <li>
		                            <label><?php echo JText::_("SR_RESERVATION_PAYMENT_TXN_ID")?></label>
		                            <span>
			                        <a href="#"
			                           id="payment_method_txn_id"
			                           data-type="text"
			                           data-pk="<?php echo $reservationId ?>"
			                           data-value="<?php echo $paymentMethodTxnId ?>"
			                           data-original-title=""><?php echo isset($paymentMethodTxnId) ? $paymentMethodTxnId : '' ?></a>
		                            </span>
	                            </li>
                                <li><label><?php echo JText::_('SR_RESERVATION_SUB_TOTAL') ?></label> <span><?php echo $subTotal->format() ?></span></li>
	                            <?php if ($this->form->getValue('total_single_supplement', 0) >0 ) : ?>
		                            <li><label><?php echo JText::_('SR_RESERVATION_TOTAL_SINGLE_SUPPLEMENT') ?></label> <span><?php echo $totalSingleSupplement->format() ?></span></li>
	                            <?php endif ?>
								<?php if (isset($isDiscountPreTax) && $isDiscountPreTax == 1 ) : ?>
									<li><label><?php echo JText::_('SR_RESERVATION_TOTAL_DISCOUNT') ?></label> <span><?php echo '-' . $totalDiscount->format() ?></span></li>
								<?php endif ?>
                                <li><label><?php echo JText::_('SR_RESERVATION_TAX') ?></label> <span><?php echo $tax->format() ?></span></li>
								<?php if (isset($isDiscountPreTax) && $isDiscountPreTax == 0) : ?>
									<li><label><?php echo JText::_('SR_RESERVATION_TOTAL_DISCOUNT') ?></label> <span><?php echo '-' . $totalDiscount->format() ?></span></li>
								<?php endif ?>
								<li><label><?php echo JText::_('SR_RESERVATION_EXTRA_TAX_EXCL') ?></label> <span><?php echo $totalExtraPriceTaxExclDisplay->format() ?></span></li>
								<li><label><?php echo JText::_('SR_RESERVATION_EXTRA_TAX_AMOUNT') ?></label> <span><?php echo $totalExtraTaxAmountDisplay->format() ?></span></li>
                                <li><label><?php echo JText::_('SR_RESERVATION_GRAND_TOTAL') ?></label> <span><?php echo $grandTotal->format() ?></span></li>
								<li><label><?php echo JText::_('SR_RESERVATION_DEPOSIT_AMOUNT') ?></label> <span><?php echo $depositAmount->format() ?></span></li>
								<li>
									<label><?php echo JText::_('SR_RESERVATION_TOTAL_PAID') ?></label>
									<span>
									<a
										href="#"
										id="total_paid"
										data-type="text"
										data-pk="<?php echo $reservationId ?>"
										data-value="<?php echo $this->form->getValue('total_paid') ?>">
										<?php echo $totalPaidAmount->format() ?>
									</a>
									</span>
								</li>
                            </ul>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12 reservation-detail-box">
					<h3><?php echo JText::_("SR_CUSTOMER_INFO") ?></h3>
					<?php
					$context = 'com_solidres.customer.' . (int) $this->form->getValue('id');
					if (SRPlugin::isEnabled('customfield') && ($customFields = SRCustomFieldHelper::getValues(array('context' => $context)))):
						$customFieldLength = count($customFields);
						$partialNumber     = ceil($customFieldLength / 2);
						?>
						<div class="row-fluid">
							<div class="span6">
								<ul class="reservation-details">
									<?php for ($i = 0; $i <= $partialNumber; $i++): ?>
										<li>
											<label><?php echo JText::_($customFields[$i]->title); ?></label> <?php echo trim($customFields[$i]->value); ?>
										</li>
									<?php endfor; ?>
								</ul>
							</div>
							<div class="span6">
								<ul class="reservation-details">
									<?php for ($i = $partialNumber + 1; $i < $customFieldLength; $i++): ?>
										<li>
											<label><?php echo JText::_($customFields[$i]->title); ?></label> <?php echo trim($customFields[$i]->value); ?>
										</li>
									<?php endfor; ?>
								</ul>
							</div>
						</div>
					<?php else: ?>
						<div class="row-fluid">
							<div class="span6">
								<ul class="reservation-details">
									<li>
										<label><?php echo JText::_("SR_CUSTOMER_TITLE") ?></label> <?php echo $this->form->getValue('customer_title') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_FIRSTNAME") ?></label> <?php echo $this->form->getValue('customer_firstname') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_MIDDLENAME") ?></label> <?php echo $this->form->getValue('customer_middlename') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_LASTNAME") ?></label> <?php echo $this->form->getValue('customer_lastname') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_EMAIL") ?></label> <?php echo $this->form->getValue('customer_email') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_PHONE") ?></label> <?php echo $this->form->getValue('customer_phonenumber') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_MOBILEPHONE") ?></label> <?php echo $this->form->getValue('customer_mobilephone') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_COMPANY") ?></label> <?php echo $this->form->getValue('customer_company') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_CUSTOMER_IP") ?></label> <?php echo $this->form->getValue('customer_ip', '') ?>
									</li>
								</ul>
							</div>
							<div class="span6">
								<ul class="reservation-details">
									<li>
										<label><?php echo JText::_("SR_CUSTOMER_ADDRESS1") ?></label> <?php echo $this->form->getValue('customer_address1') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_CUSTOMER_ADDRESS2") ?></label> <?php echo $this->form->getValue('customer_address2') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_CUSTOMER_CITY") ?></label> <?php echo $this->form->getValue('customer_city') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_CUSTOMER_ZIPCODE") ?></label> <?php echo $this->form->getValue('customer_zipcode') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_FIELD_COUNTRY_LABEL") ?></label> <?php echo $this->form->getValue('customer_country_name') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_FIELD_GEO_STATE_LABEL") ?></label> <?php echo $this->form->getValue('customer_geostate_name') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_VAT_NUMBER") ?></label> <?php echo $this->form->getValue('customer_vat_number') ?>
									</li>
									<li>
										<label><?php echo JText::_("SR_NOTES") ?></label><?php echo $this->form->getValue('note') ?>
									</li>
								</ul>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php
			$paymentData = $this->form->getValue('payment_data');
			if (!empty($paymentData) && $paymentMethodId == 'offline') :
				$paymentData = json_decode($paymentData);
			?>
			<div class="row-fluid">
				<div class="span12 reservation-detail-box">
					<h3><?php echo JText::_("SR_CUSTOMER_PAYMENT_INFO")?>
						<a href="<?php echo JRoute::_( 'index.php?option=com_solidres&task=reservationbase.deletePaymentData&id=' . $reservationId . '&' . JSession::getFormToken() . '=1' ) ?>"
						   id="payment-data-delete-btn"
							class="btn btn-mini"><i class="fa fa-times" aria-hidden="true"></i> <?php echo JText::_( 'SR_DELETE_RESERVATION_PAYMENT_DATA' ) ?></a>
					</h3>
					<div class="row-fluid">
						<div class="span12">
							<ul>
								<?php
								foreach ($paymentData as $key => $value) :
									if ($key == 'cardnumber') :
										$value = str_pad($value, 16, 'X', STR_PAD_RIGHT);
									endif;
									echo '<li>' . JText::_('PLG_SOLIDRESPAYMENT_OFFLINE_' . $key) . ': ' . $value . '</li>';
								endforeach;
								?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<?php endif ?>

			<div class="row-fluid">
				<div class="span12 reservation-detail-box booked_room_extra_info">

					<h3><?php echo JText::_("SR_ROOM_EXTRA_INFO")?></h3>

					<?php
					$reservedRoomDetails = $this->form->getValue('reserved_room_details');
					foreach($reservedRoomDetails as $room) :
						$totalRoomCost = 0;
						?>
						<div class="row-fluid">
							<div class="span6">
								<?php
								$roomTypeLink = JRoute::_('index.php?option=com_solidres&view=roomtype&layout=edit&id=' . $room->room_type_id);
								echo '<h4><a href="' . $roomTypeLink . '">' . $room->room_type_name . ' (' . $room->room_label . ')</a></h4>'
								?>
								<ul>
									<li><label><?php echo JText::_("SR_GUEST_FULLNAME") ?></label> <?php echo $room->guest_fullname ?></li>
									<li>
										<?php
										if (is_array($room->other_info)) :
											foreach ($room->other_info as $info) :
												if (substr($info->key, 0, 7) == 'smoking') :
													echo '<label>' . JText::_('SR_'.$info->key) . '</label> ' . ($info->value == '' ? JText::_('SR_NO_PREFERENCES') : ($info->value == 1 ? JText::_('SR_YES'): JText::_('SR_NO') )  ) ;
												endif;
											endforeach;
										endif
										?>
									</li>
									<li><label><?php echo JText::_("SR_ADULT_NUMBER") ?></label> <?php echo $room->adults_number ?></li>
									<li>
										<label class="toggle_child_ages"><?php echo JText::_("SR_CHILDREN_NUMBER") ?> <?php echo $room->children_number > 0 ? '<i class="icon-plus-2 fa fa-plus"></i>' : '' ?> </label> <?php echo $room->children_number ?>
										<?php
										if (is_array($room->other_info)) :
											echo '<ul class="unstyled" id="booked_room_child_ages" style="display: none">';
											foreach ($room->other_info as $info) :
												if (substr($info->key, 0, 5) == 'child') :
													echo '<li><label>' . JText::_('SR_'.$info->key) . '</label> ' . JText::plural('SR_CHILD_AGE_SELECTION', $info->value) .'</li>';
												endif;
											endforeach;
											echo '</ul>';
										endif;
										?>
									</li>

								</ul>
							</div>
							<div class="span6">
								<div class="booked_room_cost_wrapper">
								<?php
								$roomPriceCurrency = clone $baseCurrency;
								$roomPriceCurrency->setValue( $room->room_price_tax_incl );
								$totalRoomCost += $room->room_price_tax_incl;
								?>
								<ul class="unstyled">
									<li>
										<label>
											<?php echo JText::_('SR_BOOKED_ROOM_COST') ?>
											<span class="icon-help"
											      title="<?php echo $room->tariff_title . ' - ' . $room->tariff_description ?>">
											</span>
										</label>
										<span class="booked_room_cost"><?php echo $roomPriceCurrency->format() ?></span>
									</li>
									<?php
									if ( isset( $room->extras ) ) :
										foreach ( $room->extras as $extra ) :
									?>
									<li>
										<label><?php echo '<a href="' . JRoute::_( 'index.php?option=com_solidres&view=extra&layout=edit&id=' . $extra->extra_id ) . '">' . $extra->extra_name . ' (x' . $extra->extra_quantity . ')</a>' ?></label>
										<?php
										$extraPriceCurrency = clone $baseCurrency;
										$extraPriceCurrency->setValue( $extra->extra_price );
										$totalRoomCost += $extra->extra_price;
										echo '<span class="booked_room_extra_cost">' . $extraPriceCurrency->format( ) . '</span>';
										?>
									</li>
									<?php
                                        endforeach;
									endif; ?>
									<li>
										<label><strong><?php echo JText::_('SR_BOOKED_ROOM_COST_TOTAL') ?></strong></label>
										<span class="booked_room_cost">
											<strong>
											<?php
											$totalRoomCostCurrency = clone $baseCurrency;
											$totalRoomCostCurrency->setValue( $totalRoomCost );
											echo $totalRoomCostCurrency->format();
											?>
											</strong>
										</span>
									</li>
								</ul>
								</div>
							</div>
						</div>
					<?php endforeach ?>

				</div>
			</div>
			<?php
			if (SRPlugin::isEnabled('invoice')):
				$displayData = array(
					'invoiceTable' => $this->invoiceTable[0],
					'form'         => $this->form,
					'returnPage'   => ''
				);
				SRLayoutHelper::addIncludePath(SR_PLUGIN_INVOICE_PATH . '/layouts');
				echo SRLayoutHelper::render('invoices.invoice', $displayData);
			else :?>
				<div class="row-fluid">
					<div class="span12 reservation-detail-box">
							<h3>Invoice</h3>
						<div class="alert alert-info">
							This feature allows you to create pdf attachment, generate invoices, manage invoices and send them to your customers.
							</div>
							<div class="alert alert-success">
								<strong>Notice:</strong> plugin Solidres Invoice is not installed or enabled. <a target="_blank" href="https://www.solidres.com/subscribe/levels">Become a subscriber and download it now.</a>
							</div>
					</div>
				</div>
				<?php endif; ?>
			<div class="row-fluid">
				<div class="span12 reservation-detail-box">
					<h3><?php echo JText::_('SR_RESERVATION_OTHER_INFO') ?></h3>
					<?php
					$extras = $this->form->getValue('extras');
					if (isset($extras)) :
						echo '
						<table class="table table-condensed">
							<thead>
								<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_NAME") .'</th>
								<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_QUANTITY") .'</th>
								<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_PRICE") .'</th>
							</thead>
							<tbody>
											';
						foreach($extras as $extra) :
							echo '<tr>';
							?>
							<td><?php echo $extra->extra_name ?></td>
							<td><?php echo $extra->extra_quantity ?></td>
							<td>
								<?php
								$extraPriceCurrencyPerBooking = clone $baseCurrency;
								$extraPriceCurrencyPerBooking->setValue($extra->extra_price);
								echo $extraPriceCurrencyPerBooking->format();
								?>
							</td>
							<?php
							echo '</tr>';
						endforeach;
						echo '
							</tbody>
						</table>';
					endif;
					?>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12 reservation-detail-box">
					<h3><?php echo JText::_('SR_RESERVATION_NOTE_BACKEND') ?></h3>
					<div class="span6">
                        <form id="reservationnote-form" action="index.php?option=com_solidres&task=reservationnote.save&format=json">
                            <textarea rows="5" name="text" class="span12" placeholder="Type your message here"></textarea>
                            <label class="checkbox">
                                <input type="checkbox" name="notify_customer" value="1">
								<?php echo JText::_("SR_RESERVATION_NOTE_NOTIFY_CUSTOMER")?>
                            </label>
                            <label class="checkbox">
                                <input type="checkbox" name="visible_in_frontend" value="1">
								<?php echo JText::_("SR_RESERVATION_NOTE_DISPLAY_IN_FRONTEND")?>
                            </label>
							<div class="processing nodisplay"></div>
                            <button type="submit" class="btn"><?php echo JText::_("SR_SUBMIT")?></button>
                            <input name="reservation_id" type="hidden" value="<?php echo $reservationId ?>" />
							<?php echo JHtml::_('form.token'); ?>

                        </form>
					</div>
                    <div class="span6 reservation-note-holder">
						<?php
						$notes = $this->form->getValue('notes');
						if (!empty($notes)) :
							foreach ($notes as $note) :
								?>
                                <div class="reservation-note-item">
                                    <p class="info">
										<?php echo $note->created_date ?> by <?php echo $note->username ?>
                                    </p>
                                    <p>
										<?php echo JText::_("SR_RESERVATION_NOTE_NOTIFY_CUSTOMER")?>: <?php echo $note->notify_customer == 1 ? JText::_('JYES') : JText::_('JNO') ?>
                                        |
										<?php echo JText::_("SR_RESERVATION_NOTE_DISPLAY_IN_FRONTEND")?>: <?php echo $note->visible_in_frontend == 1 ? JText::_('JYES') : JText::_('JNO') ?></p>
                                    <p>
										<?php echo $note->text ?>
                                    </p>
                                </div>
								<?php
							endforeach;
						endif;
						?>
                    </div>
				</div>
			</div>
			<?php if (!empty($refund)): ?>
				<div class="row-fluid">
					<div class="span12 reservation-detail-box">
						<h3><?php echo JText::_('SR_REFUND'); ?></h3>
						<?php echo $refund; ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="row-fluid">
				<div class="span12 reservation-detail-box">
					<h3><?php echo JText::_('SR_STREAM'); ?></h3>
					<?php if (SRPlugin::isEnabled('stream')): ?>
						<?php SolidresStreamHelper::displayByScope('reservation', $this->form->getValue('id')); ?>
					<?php else: ?>
						<div class="alert alert-info">
							This feature allows you listen to all Solidres's events and record them
						</div>
						<div class="alert alert-success">
							<strong>Notice:</strong> plugin <strong>Stream</strong> is not installed or enabled.
							<a target="_blank"
							   href="https://www.solidres.com/subscribe/levels">Become
								a subscriber and download it now.</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>
<form action="<?php JRoute::_('index.php?option=com_solidres&view=reservations'); ?>" method="post" name="adminForm" id="item-form" class="">
    <input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $id > 0 ? $id : '' ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<!--<style>
	@media (min-width: 768px) {
		#solidres .row-fluid [class*="span"] {
			margin-left: 2.564102564102564%;
		}

		#solidres .row-fluid [class*="span"]:first-child {
			margin-left: 0;
		}
	}
</style>-->