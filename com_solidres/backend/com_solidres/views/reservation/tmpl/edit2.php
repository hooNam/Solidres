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
$lang->load('plg_solidrespayment_'.$this->form->getValue('payment_method_id'), JPATH_PLUGINS . '/solidrespayment/' . $this->form->getValue('payment_method_id'));

JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
$checkin = $this->form->getValue('checkin');
$checkout = $this->form->getValue('checkout');
if ($this->form->getValue('id') > 0)
{
	$isDiscountPreTax = $this->form->getValue('discount_pre_tax');

	$baseCurrency = new SRCurrency(0, $this->form->getValue('currency_id'));
	$totalExtraPriceTaxIncl = $this->form->getValue('total_extra_price_tax_incl');
	$totalExtraPriceTaxExcl = $this->form->getValue('total_extra_price_tax_excl');
	$totalExtraTaxAmount = $totalExtraPriceTaxIncl - $totalExtraPriceTaxExcl;
	$totalPaid = $this->form->getValue('total_paid');
	$deposit = $this->form->getValue('deposit_amount');

	$subTotal = clone $baseCurrency;
	$subTotal->setValue($this->form->getValue('total_price_tax_excl'));

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
}


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
		var checkin, checkout, reservation_id, assetid, requesturl, available_rooms_holder, state, payment_status;
		available_rooms_holder = $(".room");
		var doValidate = function() {
			checkin = $("#checkin").val();
			checkout = $("#checkout").val();
			state = $("#state").val();
			payment_status = $("#payment_status").val();
			reservation_id = '.($this->form->getValue('id') > 0 ? $this->form->getValue('id') : 0 ).';
			assetid = $("#reservation_asset_id").val();
			customer_id = 0;
			if ($("#customer_id").length) {
				customer_id = $("#customer_id").val();
			}			
			requesturl = "index.php?option=com_solidres&task=reservation" + (Solidres.context == "frontend" ? "" : "base") + ".getAvailableRooms&checkin=" + checkin + "&checkout="+ checkout + "&id=" + reservation_id + "&assetid=" + assetid + "&state=" + state + "&payment_status=" + payment_status + "&customer_id=" + customer_id;
			if (checkin.length == 0 || checkout.length == 0 || assetid.length == 0) {
				alert("Please make sure that you selected reservation asset, start date and end date.");
				return false;
			} else {
				return true;
			}
		};

		$("#reservation_load_available_rooms").click(function() {
			var isFormValid;
			isFormValid = doValidate();
			if (isFormValid) {
			$(".reservation-single-step-holder").removeClass("nodisplay").addClass("nodisplay");
				available_rooms_holder.addClass("nodisplay");
				$(".processing").removeClass("nodisplay");
				$.ajax({
					url : requesturl,
					success : function(html) {
						available_rooms_holder.empty().html(html);
						available_rooms_holder.find("input.reservation_room_select").each(function() {
							var self = $(this);
							/*if (self.is(":checked")) {
								self.parents(".room_selection_wrapper").find("select.tariff_selection").trigger("change");
							}*/
						});
						$(".processing").addClass("nodisplay");
						available_rooms_holder.removeClass("nodisplay");
						isAtLeastOneRoomSelected();
					}
				});
			}
		});
	});';
JFactory::getDocument()->addScriptDeclaration($script);

$config = JFactory::getConfig();
$solidresConfig = JComponentHelper::getParams('com_solidres');
$minDaysBookInAdvance = $solidresConfig->get('min_days_book_in_advance', 0);
$maxDaysBookInAdvance = $solidresConfig->get('max_days_book_in_advance', 0);
$minLengthOfStay = $solidresConfig->get('min_length_of_stay', 1);
$datePickerMonthNum = $solidresConfig->get('datepicker_month_number', 3);
$weekStartDay = $solidresConfig->get('week_start_day', 1);
$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');

JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
$tzoffset = $config->get('offset');
$timezone = new DateTimeZone($tzoffset);
$dateCheckIn = JDate::getInstance();
if (!isset($checkin)) :
	$dateCheckIn->add(new DateInterval('P'.($minDaysBookInAdvance).'D'))->setTimezone($timezone);
endif;
$dateCheckOut = JDate::getInstance();
if (!isset($checkout)) :
	$dateCheckOut->add(new DateInterval('P'.($minDaysBookInAdvance + $minLengthOfStay).'D'))->setTimezone($timezone);
endif;

$jsDateFormat = SRUtilities::convertDateFormatPattern($dateFormat);
//$roomsOccupancyOptionsCount = count($roomsOccupancyOptions);
/*$maxRooms = $params->get('max_room_number', 10);
$maxAdults = $params->get('max_adult_number', 10);
$maxChildren = $params->get('max_child_number', 10);*/

$defaultCheckinDate = '';
$defaultCheckoutDate = '';
if (isset($checkin)) {
	$checkinModule = JDate::getInstance($checkin, $timezone);
	$checkoutModule = JDate::getInstance($checkout, $timezone);
	// These variables are used to set the defaultDate of datepicker
	$defaultCheckinDate = $checkinModule->format('Y-m-d', true);
	$defaultCheckoutDate = $checkoutModule->format('Y-m-d', true);
}

if (!empty($defaultCheckinDate)) :
	$defaultCheckinDateArray = explode('-', $defaultCheckinDate);
	$defaultCheckinDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

if (!empty($defaultCheckoutDate)) :
	$defaultCheckoutDateArray = explode('-', $defaultCheckoutDate);
	$defaultCheckoutDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

$doc = JFactory::getDocument();
JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
$doc->addScriptDeclaration('
	Solidres.jQuery(function($) {
		var minLengthOfStay = '.$minLengthOfStay.';
		var checkout = $(".checkout_datepicker_inline_module").datepicker({
			minDate : "+' . ( $minDaysBookInAdvance + $minLengthOfStay ). '",
			numberOfMonths : '.$datePickerMonthNum.',
			showButtonPanel : true,
			dateFormat : "'.$jsDateFormat.'",
			firstDay: '.$weekStartDay.',
			' . (isset($checkout) ? 'defaultDate: new Date(' . implode(',' , $defaultCheckoutDateArray) .'),' : '') . '
			onSelect: function() {
				$("#item-form input#checkout").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
				$("#item-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", $(this).datepicker("getDate")));
				$(".checkout_datepicker_inline_module").slideToggle();
				$(".checkin_module").removeClass("disabledCalendar");
			}
		});
		var checkin = $(".checkin_datepicker_inline_module").datepicker({
			minDate : "+' .  $minDaysBookInAdvance . 'd",
			'.($maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($maxDaysBookInAdvance) . '",' : '' ).'
			numberOfMonths : '.$datePickerMonthNum.',
			showButtonPanel : true,
			dateFormat : "'.$jsDateFormat.'",
			'. (isset($checkin) ? 'defaultDate: new Date(' . implode(',' , $defaultCheckinDateArray) .'),' : '') . '
			onSelect : function() {
				var currentSelectedDate = $(this).datepicker("getDate");
				var checkoutMinDate = $(this).datepicker("getDate", "+1d");
				checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
				checkout.datepicker( "option", "minDate", checkoutMinDate );
				checkout.datepicker( "setDate", checkoutMinDate);

				$("#item-form input#checkin").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
				$("#item-form input#checkout").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));

				$("#item-form .checkin_module").text($.datepicker.formatDate("'.$jsDateFormat.'", currentSelectedDate));
				$("#item-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", checkoutMinDate));
				$(".checkin_datepicker_inline_module").slideToggle();
				$(".checkout_module").removeClass("disabledCalendar");
			},
			firstDay: '.$weekStartDay.'
		});
		$(".ui-datepicker").addClass("notranslate");
		$(".checkin_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$(".checkin_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$(".checkout_module").removeClass("disabledCalendar");
					} else {
						$(".checkout_module").addClass("disabledCalendar");
					}
				});
			}
		});

		$(".checkout_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$(".checkout_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$(".checkin_module").removeClass("disabledCalendar");
					} else {
						$(".checkin_module").addClass("disabledCalendar");
					}
				});
			}
		});

		$(".room_quantity").change(function() {
			var curQuantity = $(this).val();
			$(".room_num_row").each(function( index ) {
				var index2 = index + 1;
				if (index2 <= curQuantity) {
					$("#room_num_row_" + index2).show();
					$("#room_num_row_" + index2 + " select").removeAttr("disabled");
				} else {
					$("#room_num_row_" + index2).hide();
					$("#room_num_row_" + index2 + " select").attr("disabled", "disabled");
				}
			});
		});

		if ($(".room_quantity").val() > 0) {
			$(".room_quantity").trigger("change");
		}
		
		$( "#customer_autocomplete" ).autocomplete({
			source: "index.php?option=com_solidres&task=customers.find&format=json",
			minLength: 2,
				select: function( event, ui ) {
				var a = $("#customer_id");
	            if( a.length ) {
	                a.val(ui.item.user_id);
	            } else {
	                var b = $("<input />", {
	                    "type"  : "hidden",
	                    "value" : ui.item.user_id,
	                    "name"  : "jform[customer_id]",
	                    "id"    : "customer_id"
	                });
	                b.insertAfter( $( this ) );
	            }
			}
		});
    });
');

JFactory::getDocument()->addScriptDeclaration('
	Solidres.jQuery(document).ready(function($) {
		$("#item-form").validate({onsubmit: false});
	});

	Joomla.submitbutton = function(task)
	{
		if (task == "reservationbase.cancel")
		{
			Solidres.jQuery("#item-form").validate().resetForm();
			Joomla.submitform(task, document.getElementById("item-form"), false);
		} else {
			alert("' . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '");
		}
	}
');


?>

<div id="solidres" class="<?php echo SR_UI ?>">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_form_view span10">
			<div class="row-fluid">
				<div class="span12">
					<form enctype="multipart/form-data" action="<?php JRoute::_('index.php?option=com_solidres&view=reservations'); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
						<fieldset>
							<legend>General info</legend>
							<div class="row-fluid">
								<div class="span6">
									<div class="control-group">
										<label class="control-label" for="inputEmail"><?php echo JText::_("SR_CHECKIN")?></label>
										<div class="controls">
											<div class="checkin_module datefield">
												<?php echo isset($checkin) ?
													$checkinModule->format($dateFormat, true) :
													$dateCheckIn->format($dateFormat, true) ?>
												<i class="fa fa-calendar"></i>
											</div>
											<div class="checkin_datepicker_inline_module datepicker_inline" style="display: none"></div>
											<?php // this field must always be "Y-m-d" as it is used internally only ?>
											<input type="hidden" name="jform[checkin]" id="checkin" value="<?php echo isset($checkin) ?
												$checkinModule->format('Y-m-d', true) :
												$dateCheckIn->format('Y-m-d', true) ?>" />
										</div>
									</div>
									<div class="control-group">
										<label class="control-label" for="inputPassword"><?php echo JText::_("SR_CHECKOUT")?></label>
										<div class="controls">
											<div class="checkout_module datefield">
												<?php echo isset($checkout) ?
													$checkoutModule->format($dateFormat, true) :
													$dateCheckOut->format($dateFormat, true)
												?>
												<i class="fa fa-calendar"></i>
											</div>
											<div class="checkout_datepicker_inline_module datepicker_inline" style="display: none"></div>
											<?php // this field must always be "Y-m-d" as it is used internally only ?>
											<input type="hidden" name="jform[checkout]" id="checkout" value="<?php echo isset($checkout) ?
												$checkoutModule->format('Y-m-d', true) :
												$dateCheckOut->format('Y-m-d', true) ?>" />
										</div>
									</div>
									<div class="control-group">
										<label class="control-label" for="inputPassword"><?php echo JText::_("SR_ASSET_NAME")?></label>
										<div class="controls">
											<select required id="reservation_asset_id" name="jform[reservation_asset_id]" class="input-block-level">
												<?php echo JHtml::_('select.options', SolidresHelper::getReservationAssetOptions(), 'value', 'text', $this->form->getValue('reservation_asset_id'));?>
											</select>
										</div>
									</div>

									<div class="control-group">
										<label class="control-label"></label>
										<div class="controls">
											<button type="button" data-limit-booking-id="<?php echo $this->form->getValue('id') ?>" class="btn btn-info" id="reservation_load_available_rooms"><?php echo JText::_('SR_RESERVATION_RELOAD_AVAILABLE_ROOMS') ?></button>
										</div>
									</div>
								</div>
								<div class="span6">
									<div class="control-group">
										<label class="control-label"><?php echo JText::_("SR_STATUS")?></label>
										<div class="controls">
											<select id="state" name="jform[state]" class="input-block-level">
												<?php echo JHtml::_('select.options', SRUtilities::getReservationStatusList(), 'value', 'text', $this->form->getValue('state'), true);?>
											</select>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label"><?php echo JText::_("SR_RESERVATION_PAYMENT_STATUS")?></label>
										<div class="controls">
											<select id="payment_status" name="jform[payment_status]" class="input-block-level">
												<?php echo JHtml::_('select.options', SRUtilities::getReservationPaymentStatusList(), 'value', 'text', $this->form->getValue('payment_status'), true);?>
											</select>
										</div>
									</div>
									<?php if (SRPlugin::isEnabled('user')) : ?>
									<div class="control-group">
										<label class="control-label"><?php echo JText::_("SR_RESERVATION_CUSTOMER")?></label>
										<div class="controls">
											<input type="text" id="customer_autocomplete" class="input-block-level" value="<?php echo $this->customerIdentification ?>"/>
											<input type="hidden" id="customer_id" name="jform[customer_id]" value="<?php echo $this->customer_id ?>">
										</div>
									</div>
									<?php endif ?>
								</div>
							</div>

						</fieldset>
						<input type="hidden" name="task" value="" />
						<?php echo JHtml::_('form.token'); ?>
					</form>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12">
					<div class="control-group">
						<fieldset>
							<legend><?php echo JText::_('SR_RESERVATION_PROGRESS_ROOM_RATE_INFO') ?></legend>
						</fieldset>
						<div class="processing nodisplay"></div>
						<div class="reservation-single-step-holder backend room"></div>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12">
					<div class="control-group">
						<fieldset>
							<legend><?php echo JText::_('SR_RESERVATION_PROGRESS_GUEST_INFO') ?></legend>
						</fieldset>
						<div class="reservation-single-step-holder guestinfo backend nodisplay"></div>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12">
					<div class="control-group">
						<fieldset>
							<legend><?php echo JText::_('SR_RESERVATION_CONFIRMATION') ?></legend>
						</fieldset>
						<div class="reservation-single-step-holder backend confirmation nodisplay"></div>
					</div>
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