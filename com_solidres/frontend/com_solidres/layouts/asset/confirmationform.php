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

if (!isset($displayData['reservationDetails']->hub_dashboard)) :
	$displayData['reservationDetails']->hub_dashboard = 0;
endif;

$isGuestMakingReservation = JFactory::getApplication()->isSite() && !$displayData['reservationDetails']->hub_dashboard;

?>

<form
	id="sr-reservation-form-confirmation"
	class=""
	action="<?php echo JRoute::_("index.php?option=com_solidres&task=" . $displayData['task']) ?>"
	method="POST">

	<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-top">
		<div class="<?php echo SR_UI_GRID_COL_8 ?>">
			<div class="inner">
				<?php if ($isGuestMakingReservation) : ?>
				<p><?php echo JText::_("SR_RESERVATION_NOTICE_CONFIRMATION") ?></p>
				<?php endif ?>
			</div>
		</div>
		<div class="<?php echo SR_UI_GRID_COL_4 ?>">
			<div class="inner">
				<div class="btn-group">
					<button type="button" class="btn btn-default reservation-navigate-back" data-step="confirmation"
							data-prevstep="guestinfo">
						<i class="fa fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
					</button>
					<button <?php echo $isGuestMakingReservation ? 'disabled' : '' ?> data-step="confirmation" type="submit" class="btn btn-success">
						<i class="fa fa-check"></i> <?php echo JText::_('SR_BUTTON_RESERVATION_FINAL_SUBMIT') ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
		<div class="<?php echo SR_UI_GRID_COL_12 ?>">
			<div class="inner">
				<div id="reservation-confirmation-box">
					<?php if ($isGuestMakingReservation) : ?>
					<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
						<div class="<?php echo SR_UI_GRID_COL_6 ?>">
							<strong>
								<?php
								echo JText::_('SR_YOUR_SEARCH_INFORMATION_CHECKIN') . ' ' .
									 JDate::getInstance($displayData['reservationDetails']->checkin, $displayData['timezone'])
										  ->format($displayData['dateFormat'], true) ?>
							</strong>
						</div>
						<?php if (isset($displayData['reservationDetails']->guest['customer_lastname'])
									&&
						          isset($displayData['reservationDetails']->guest['customer_firstname'])
							) : ?>
						<div class="<?php echo SR_UI_GRID_COL_6 ?>">
							<strong>
								<?php
								echo JText::_('SR_CONFIRMATION_FULLNAME') . $displayData['reservationDetails']->guest['customer_firstname'] . ' ' .
									 $displayData['reservationDetails']->guest['customer_lastname']
								?>
							</strong>
						</div>
						<?php endif ?>
					</div>
					<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
						<div class="<?php echo SR_UI_GRID_COL_6 ?>">
							<strong>
								<?php
								echo JText::_('SR_YOUR_SEARCH_INFORMATION_CHECKOUT') . ' ' .
									 JDate::getInstance($displayData['reservationDetails']->checkout, $displayData['timezone'])
										  ->format($displayData['dateFormat'], true)  ?>
							</strong>
						</div>
						<div class="<?php echo SR_UI_GRID_COL_6 ?>">
							<strong>
								<?php echo JText::_('SR_CONFIRMATION_EMAIL') .
										   $displayData['reservationDetails']->guest['customer_email'] ?>
							</strong>
						</div>
					</div>
					<?php endif ?>

					<table class="table table-bordered">
						<tbody>
						<?php
						// Room cost
						$extraList = array();
						foreach ($displayData['roomTypes'] as $roomTypeId => $roomTypeDetails) :
							foreach ($roomTypeDetails['rooms'] as $tariffId => $roomDetails) :
								foreach ($roomDetails as $roomIndex => $cost) :
									$hasDiscount = false;
									if ($cost['currency']['total_discount'] > 0) :
										$hasDiscount = true;
									endif;

									$roomInfo = $displayData['reservationDetails']->room['room_types'][$roomTypeId][$tariffId][$roomIndex];

									// Build a per room extra list array
									if (isset($roomInfo['extras']) && is_array($roomInfo['extras'])) :
										foreach ($roomInfo['extras'] as $extraItemKey => $extraItemDetails) :
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['room_type_name'] = $roomTypeDetails['name'];
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['name'] = $extraItemDetails['name'];
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['quantity'] =  $extraItemDetails['quantity'];
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['currency'] = clone $displayData['currency'];
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['currency']->setValue($extraItemDetails['total_extra_cost_tax_excl']);
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['currency_tax'] = clone $displayData['currency'];
											$extraList[$roomTypeId][$tariffId][$roomIndex]['extras'][$extraItemKey]['currency_tax']->setValue($extraItemDetails['total_extra_cost_tax_incl'] - $extraItemDetails['total_extra_cost_tax_excl']);
										endforeach;
									endif;

									?>
									<tr>
										<td>
											<?php echo JText::_('SR_ROOM') . ': ' ?>
											<?php echo $roomTypeDetails["name"] ?>
											<a href="javascript:void(0)" class="toggle_room_confirmation" data-target="<?php echo $roomTypeId ?>_<?php echo $tariffId ?>_<?php echo $roomIndex ?>">
												<?php echo JText::_('SR_CONFIRMATION_ROOM_DETAILS') ?>
											</a>
											<p><?php echo !empty($cost['currency']['title']) ? '(' . $cost['currency']['title'] . ')' : ''  ?></p>
											<ul id="rc_<?php echo $roomTypeId ?>_<?php echo $tariffId ?>_<?php echo $roomIndex ?>" style="display: none">
												<?php if (!empty($roomInfo['guest_fullname'])) : ?>
												<li><?php echo JText::_('SR_CONFIRMATION_GUEST_NAME') . ': ' . $roomInfo['guest_fullname']?></li>
												<?php endif; ?>
												<li><?php echo JText::_('SR_CONFIRMATION_ADULT_NUMBER') . ': ' . (isset($roomInfo['adults_number']) ? $roomInfo['adults_number'] : 0) ?></li>
												<li><?php echo JText::_('SR_CONFIRMATION_CHILD_NUMBER') . ': ' . (!empty($roomInfo['children_number']) ? $roomInfo['children_number'] : 0)?></li>
											</ul>
										</td>
										<td>
											<?php
											if (0 == $displayData['booking_type']) :
												echo JText::plural("SR_NIGHTS", $displayData['stay_length']);
											else :
												echo JText::plural("SR_DAYS", $displayData['stay_length'] + 1);
											endif;
											?>
										</td>
										<td class="sr-align-right">
											<?php if (!$isGuestMakingReservation) : ?>
											<div class="<?php echo SR_UI_INPUT_APPEND ?>">
												<span class="add-on input-group-addon">Price (<?php echo $cost['currency']['total_price_tax_excl_formatted']->getCode() ?>)</span>
												<input type="text"
												       class="total_price_tax_excl_single_line <?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>"
												       value="<?php echo $cost['currency']['total_price_tax_excl_formatted']->getValue(); ?>"
												       name="jform[override_cost][room_types][<?php echo $roomTypeId ?>][<?php echo $tariffId ?>][<?php echo $roomIndex ?>][total_price_tax_excl]" />
											</div>
											<div class="<?php echo SR_UI_INPUT_APPEND ?>">
												<span class="add-on input-group-addon">Tax (<?php echo $cost['currency']['total_price_tax_excl_formatted']->getCode() ?>)</span>
												<input type="text" class="room_price_tax_amount_single_line <?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" value="<?php echo $cost['currency']['total_price_tax_incl_formatted']->getValue() - $cost['currency']['total_price_tax_excl_formatted']->getValue(); ?>" name="jform[override_cost][room_types][<?php echo $roomTypeId ?>][<?php echo $tariffId ?>][<?php echo $roomIndex ?>][tax_amount]" />
											</div>
											<?php else : ?>
												<?php echo $cost['currency']['total_price_tax_excl_formatted']->format(); ?>
											<?php endif ?>
										</td>
									</tr>
								<?php
								endforeach;
							endforeach;
						endforeach;

						// Total room cost
						$totalRoomCost = new SRCurrency($displayData['cost']['total_price_tax_excl'], $displayData['reservationDetails']->currency_id);
						$doesPriceIncludeTax = 0;
						if (isset($displayData['reservationDetails']->asset_params['price_includes_tax'])) :
							$doesPriceIncludeTax = $displayData['reservationDetails']->asset_params['price_includes_tax'];
						endif;
						?>

						<tr class="nobordered first">
							<td colspan="2" class="sr-align-right">
								<?php echo JText::_("SR_TOTAL_ROOM_COST_TAX_" . ($doesPriceIncludeTax ? 'INCL' : 'EXCL' )) ?>
							</td>
							<td class="sr-align-right noleftborder">
								<?php if (!$isGuestMakingReservation) : ?>
									<span class="add-on"><?php echo $totalRoomCost->getCode() ?></span>
									<span class="total_price_tax_excl grand_total_sub"><?php echo $totalRoomCost->getValue() ?></span>
								<?php else : ?>
									<?php echo $totalRoomCost->format() ?>
								<?php endif ?>
							</td>
						</tr>

						<?php
						// In case of pre tax discount
						if ($displayData['cost']['total_discount'] > 0 && $displayData['isDiscountPreTax']) :
							$totalDiscount = new SRCurrency($displayData['cost']['total_discount'], $displayData['reservationDetails']->currency_id);
							?>
							<tr class="nobordered">
								<td colspan="2" class="sr-align-right">
									<?php echo JText::_("SR_TOTAL_DISCOUNT") ?>
								</td>
								<td class="sr-align-right noleftborder">
									<?php if (!$isGuestMakingReservation) : ?>
									<div class="<?php echo SR_UI_INPUT_APPEND ?>">
										<span class="add-on input-group-addon"><?php echo $totalDiscount->getCode() ?></span>
										<input type="text" class="<?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" value="<?php echo '-' . $totalDiscount->getValue() ?>" name="jform[total_discount]" />
									</div>
									<?php else : ?>
										<?php echo '-' . $totalDiscount->format() ?>
									<?php endif ?>
								</td>
							</tr>
						<?php
						endif;

						// Imposed taxes
						if (!$doesPriceIncludeTax) :
						$taxItem = new SRCurrency($displayData['cost']['tax_amount'], $displayData['reservationDetails']->currency_id);
						?>
							<tr class="nobordered">
								<td colspan="2" class="sr-align-right">
									<?php echo JText::_('SR_TOTAL_ROOM_TAX') ?>
								</td>
								<td class="sr-align-right noleftborder">
									<?php if (!$isGuestMakingReservation) : ?>
										<span class="add-on"><?php echo $taxItem->getCode() ?></span>
										<span class="tax_amount grand_total_sub"><?php echo $taxItem->getValue() ?></span>
									<?php else : ?>
										<?php echo $taxItem->format() ?>
									<?php endif ?>
								</td>
							</tr>
						<?php
						endif;

						// In case of after tax discount
						if ($displayData['cost']['total_discount'] > 0 && !$displayData['isDiscountPreTax']) :
							$totalDiscount = new SRCurrency($displayData['cost']['total_discount'], $displayData['reservationDetails']->currency_id);
							?>
							<tr class="nobordered">
								<td colspan="2" class="sr-align-right">
									<?php echo JText::_("SR_TOTAL_DISCOUNT") ?>
								</td>
								<td class="sr-align-right noleftborder">
									<?php if (!$isGuestMakingReservation) : ?>
									<div class="<?php echo SR_UI_INPUT_APPEND ?>">
										<span class="add-on input-group-addon"><?php echo $totalDiscount->getCode() ?></span>
										<input type="text" class="<?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" value="<?php echo '-' . $totalDiscount->getValue() ?>" name="jform[total_discount]" />
									</div>
									<?php else : ?>
										<?php echo '-' . $totalDiscount->format() ?>
									<?php endif ?>
								</td>
							</tr>
						<?php
						endif;

						// Per room extra list
						if (!empty($extraList)) :
							foreach ($extraList as $extraRoomTypeId => $extraRoomTypeTariffs) :
								foreach ($extraRoomTypeTariffs as $extraTariffId => $extraRooms) :
									foreach ($extraRooms as $extraRoomIndex => $extraRoomExtras) :
										foreach ($extraRoomExtras as $extraRoomExtraKey => $extraRoomExtraDetails) :
											foreach ($extraRoomExtraDetails as $extraRoomExtraId => $extraRoomExtraIdDetails) :
						?>
							<tr class="extracost_confirmation" style="display: none">
								<td>
									<p>
										<?php echo JText::_('SR_EXTRA') . ': ' ?><?php echo $extraRoomExtraIdDetails['name'] ?>
									</p>
									<p>
										<?php echo JText::_('SR_ROOM') . ': ' ?><?php echo $extraRoomExtraIdDetails['room_type_name'] ?>
									</p>
								</td>
								<td>
									<?php echo $extraRoomExtraIdDetails['quantity'] ?>
								</td>
								<td class="sr-align-right ">
									<?php if (!$isGuestMakingReservation) : ?>
									<div class="<?php echo SR_UI_INPUT_APPEND ?>">
										<span class="add-on input-group-addon">Price (<?php echo $extraRoomExtraIdDetails['currency']->getCode() ?>)</span>
										<input class="extra_price_single_line <?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" type="text" value="<?php echo $extraRoomExtraIdDetails['currency']->getValue() ?>" name="jform[override_cost][room_types][<?php echo $extraRoomTypeId ?>][<?php echo $extraTariffId ?>][<?php echo $extraRoomIndex ?>][extras][<?php echo $extraRoomExtraId ?>][price]" />
									</div>
									<div class="<?php echo SR_UI_INPUT_APPEND ?>">
										<span class="add-on input-group-addon">Tax (<?php echo $extraRoomExtraIdDetails['currency_tax']->getCode() ?>)</span>
										<input class="extra_tax_single_line <?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" type="text" value="<?php echo $extraRoomExtraIdDetails['currency_tax']->getValue() ?>" name="jform[override_cost][room_types][<?php echo $extraRoomTypeId ?>][<?php echo $extraTariffId ?>][<?php echo $extraRoomIndex ?>][extras][<?php echo $extraRoomExtraId ?>][tax_amount]" />
									</div>
									<?php else : ?>
										<?php echo $extraRoomExtraIdDetails['currency']->format() ?>
									<?php endif ?>
								</td>
							</tr>
						<?php
											endforeach;
										endforeach;
									endforeach;
								endforeach;
							endforeach;
						endif;

						// Per booking extra list
						$perBookingExtraList = isset($displayData['reservationDetails']->guest['extras']) ? $displayData['reservationDetails']->guest['extras'] : array();

						foreach ($perBookingExtraList as  $perBookingExtraId => $perBookingExtraDetails ) :
						?>
							<tr class="extracost_confirmation" style="display: none">
								<td>
									<p>
										<?php echo JText::_('SR_EXTRA') . ': ' ?><?php echo $perBookingExtraDetails['name'] ?>
									</p>
									<p>
										<?php echo JText::_('SR_EXTRA_PER_BOOKING') ?>
									</p>
								</td>
								<td>
									<?php echo $perBookingExtraDetails['quantity'] ?>
								</td>
								<td class="sr-align-right ">
									<?php
									$perBookingExtraCurrency = clone $displayData['currency'];
									$perBookingExtraCurrency->setValue($perBookingExtraDetails['total_extra_cost_tax_excl']);
									$perBookingExtraCurrencyTax = clone $displayData['currency'];
									$perBookingExtraCurrencyTax->setValue($perBookingExtraDetails['total_extra_cost_tax_incl'] - $perBookingExtraDetails['total_extra_cost_tax_excl']);
									?>
									<?php if (!$isGuestMakingReservation) : ?>
									<div class="<?php echo SR_UI_INPUT_APPEND ?>">
										<span class="add-on input-group-addon">Price (<?php echo $perBookingExtraCurrency->getCode() ?>)</span>
										<input class="extra_price_single_line <?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" type="text" value="<?php echo $perBookingExtraCurrency->getValue() ?>" name="jform[override_cost][extras_per_booking][<?php echo $perBookingExtraId ?>][price]" />
									</div>
									<div class="<?php echo SR_UI_INPUT_APPEND ?>">
										<span class="add-on input-group-addon">Tax (<?php echo $perBookingExtraCurrencyTax->getCode() ?>)</span>
										<input class="extra_tax_single_line <?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" type="text" value="<?php echo $perBookingExtraCurrencyTax->getValue() ?>" name="jform[override_cost][extras_per_booking][<?php echo $perBookingExtraId ?>][tax_amount]" />
									</div>
									<?php else : ?>
										<?php echo $perBookingExtraCurrency->format() ?>
									<?php endif ?>
								</td>
							</tr>
						<?php
						endforeach;

						// Extra cost
						$totalExtraCostTaxExcl = new SRCurrency($displayData['totalRoomTypeExtraCostTaxExcl'], $displayData['reservationDetails']->currency_id);
						$totalExtraCostTaxAmount = new SRCurrency($displayData['totalRoomTypeExtraCostTaxIncl'] - $displayData['totalRoomTypeExtraCostTaxExcl'], $displayData['reservationDetails']->currency_id);

						if ($totalExtraCostTaxExcl->getValue() > 0) :
						?>
						<tr class="nobordered extracost_row">
							<td colspan="2" class="sr-align-right">
								<a href="javascript:void(0)" class="toggle_extracost_confirmation">
									<?php echo JText::_("SR_TOTAL_EXTRA_COST_TAX_EXCL") ?>
								</a>
							</td>
							<td id="total-extra-cost" class="sr-align-right noleftborder">
								<?php if (!$isGuestMakingReservation) : ?>
									<span class="add-on"><?php echo $totalExtraCostTaxExcl->getCode() ?></span>
									<span class="total_extra_price grand_total_sub"><?php echo $totalExtraCostTaxExcl->getValue() ?></span>
								<?php else : ?>
									<?php echo $totalExtraCostTaxExcl->format() ?>
								<?php endif ?>
							</td>
						</tr>

						<tr class="nobordered">
							<td colspan="2" class="sr-align-right">
								<?php echo JText::_("SR_TOTAL_EXTRA_COST_TAX_AMOUNT") ?>
							</td>
							<td id="total-extra-cost" class="sr-align-right noleftborder">
								<?php if (!$isGuestMakingReservation) : ?>
									<span class="add-on"><?php echo $totalExtraCostTaxAmount->getCode() ?></span>
									<span class="total_extra_tax grand_total_sub"><?php echo $totalExtraCostTaxAmount->getValue() ?></span>
								<?php else : ?>
									<?php echo $totalExtraCostTaxAmount->format() ?>
								<?php endif ?>
							</td>
						</tr>

						<?php
						endif;

						// Grand total cost
						if ($displayData['isDiscountPreTax']) :
							$grandTotal = new SRCurrency($displayData['cost']['total_price_tax_excl_discounted'] + $displayData['cost']['tax_amount'] + $displayData['totalRoomTypeExtraCostTaxIncl'], $displayData['reservationDetails']->currency_id);
						else :
							$grandTotal = new SRCurrency($displayData['cost']['total_price_tax_excl'] + $displayData['cost']['tax_amount'] - $displayData['cost']['total_discount'] + $displayData['totalRoomTypeExtraCostTaxIncl'], $displayData['reservationDetails']->currency_id);
						endif;

						?>
						<tr class="nobordered">
							<td colspan="2" class="sr-align-right">
								<strong><?php echo JText::_("SR_GRAND_TOTAL") ?></strong>
							</td>
							<td class="sr-align-right gra noleftborder">
								<?php if (!$isGuestMakingReservation) : ?>
									<span class="add-on"><?php echo $totalExtraCostTaxExcl->getCode() ?></span>
									<span class="grand_total"><?php echo $grandTotal->getValue() ?></span>
								<?php else : ?>
								<strong><?php echo $grandTotal->format() ?></strong>
								<?php endif ?>
							</td>
						</tr>

						<?php
						// Deposit amount, if enabled
						$isDepositRequired = $displayData['reservationDetails']->deposit_required;

						if ($isDepositRequired) :
							$depositAmountTypeIsPercentage = $displayData['reservationDetails']->deposit_is_percentage;
							$depositIncludeExtraCost = $displayData['reservationDetails']->deposit_include_extra_cost;
							if ($displayData['reservationDetails']->deposit_amount_by_stay_length <= 0) :
								$depositAmount = $displayData['reservationDetails']->deposit_amount;
								$depositTotal = $depositAmount;

								if ($depositAmountTypeIsPercentage) :
									$depositTotal = $displayData['cost']['total_price_tax_excl_discounted'] + $displayData['cost']['tax_amount'];
									if ($depositIncludeExtraCost) :
										$depositTotal += $displayData['totalRoomTypeExtraCostTaxIncl'];
									endif;
									$depositTotal = $depositTotal * ($depositAmount / 100);
								endif;
							else :
								$depositTotal = $displayData['reservationDetails']->deposit_amount_by_stay_length;
							endif;
							$depositTotalAmount = new SRCurrency($depositTotal, $displayData['reservationDetails']->currency_id);
							?>
							<tr class="nobordered">
								<td colspan="2" class="sr-align-right">
									<strong><?php echo JText::_("SR_DEPOSIT_AMOUNT") ?></strong>
								</td>
								<td class="sr-align-right gra noleftborder">
									<?php if (!$isGuestMakingReservation) : ?>
										<div class="<?php echo SR_UI_INPUT_APPEND ?>">
											<span class="add-on input-group-addon"><?php echo $depositTotalAmount->getCode() ?></span>
											<input type="text" class="<?php echo 'bs3' == SR_UI ? 'form-control' : '' ?>" value="<?php echo $depositTotalAmount->getValue() ?>" name="jform[override_cost][deposit_amount]" />
										</div>
									<?php else : ?>
										<strong><?php echo $depositTotalAmount->format() ?></strong>
									<?php endif ?>
								</td>
							</tr>
							<?php
							JFactory::getApplication()->setUserState($displayData['context'] . '.deposit', array('deposit_amount' => $depositTotal));
						endif;

						// Terms and conditions
						if ($isGuestMakingReservation) :
						$bookingConditionsLink = JRoute::_(ContentHelperRoute::getArticleRoute($displayData['reservationDetails']->booking_conditions));
						$privacyPolicyLink = JRoute::_(ContentHelperRoute::getArticleRoute($displayData['reservationDetails']->privacy_policy));
						?>
						<tr class="nobordered termsandconditions">
							<td colspan="3">
								<p>
									<input type="checkbox" id="termsandconditions" data-target="finalbutton"/>
									<?php echo JText::_('SR_I_AGREE_WITH') ?>
									<a target="_blank"
									   href="<?php echo $bookingConditionsLink ?>"><?php echo JText::_('SR_BOOKING_CONDITIONS') ?></a> <?php echo JText::_('SR_AND') ?>
									<a target="_blank"
									   href="<?php echo $privacyPolicyLink ?>"><?php echo JText::_('SR_PRIVACY_POLICY') ?></a>
								</p>
							</td>
						</tr>
						<?php else : ?>
						<tr class="nobordered sendoutgoingemails">
							<td colspan="3">
								<p>
									<input type="checkbox" name="jform[sendoutgoingemails]" id="sendoutgoingemails" checked/>
									<?php echo JText::_('SR_RESERVATION_AMEND_SEND_OUTGOING_EMAILS') ?>
								</p>
							</td>
						</tr>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			<input type="hidden" name="id" value="<?php echo $displayData['assetId'] ?>"/>
		</div>
	</div>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-bottom">
		<div class="<?php echo SR_UI_GRID_COL_8 ?>">
			<div class="inner">
				<?php if ($isGuestMakingReservation) : ?>
				<p><?php echo JText::_("SR_RESERVATION_NOTICE_CONFIRMATION") ?></p>
				<?php endif ?>
			</div>
		</div>
		<div class="<?php echo SR_UI_GRID_COL_4 ?>">
			<div class="inner">
				<div class="btn-group">
					<button type="button" class="btn btn-default reservation-navigate-back" data-step="confirmation"
							data-prevstep="guestinfo">
						<i class="fa fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
					</button>
					<button <?php echo $isGuestMakingReservation ? 'disabled ' : '' ?>  data-step="confirmation" type="submit" class="btn btn-default btn-success">
						<i class="fa fa-check"></i> <?php echo JText::_('SR_BUTTON_RESERVATION_FINAL_SUBMIT') ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<?php echo JHtml::_("form.token") ?>
</form>
