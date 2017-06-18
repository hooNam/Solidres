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

JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

// The current tariff to be displayed
$tariff = $displayData['tariff'];
$isStandardTariff = $tariff->valid_from == '00-00-0000' && $tariff->valid_to == '00-00-0000';

// The current check in and check out date
$checkIn = $displayData['checkin'];
$checkOut = $displayData['checkout'];
$showDateInfo = !empty($checkIn) && !empty($checkOut);

// Some configuration data
$minDaysBookInAdvance = $displayData['minDaysBookInAdvance'];
$maxDaysBookInAdvance = $displayData['maxDaysBookInAdvance'];
$minLengthOfStay = $displayData['minLengthOfStay'];
$datePickerMonthNum = $displayData['datePickerMonthNum'];
$weekStartDay = $displayData['weekStartDay'];
$timezone = $displayData['timezone'];
$bookingType = $displayData['bookingType'];

// An integer array of all enabled checkin days in a week
$enabledCheckinDays = $tariff->limit_checkin;

// If valid from is in the past, switch valid from to now
// (need to take min days book in advance into consideration)
$now = strtotime("now");
if ($now > strtotime($tariff->valid_from)) :
	$tariff->valid_from = date('d-m-Y', $now);
endif;

if (!$isStandardTariff) :
	$dayDiff = SRUtilities::calculateDateDiff(JDate::getInstance('now', $timezone)->format('d-m-Y'), $tariff->valid_from);
	if ( $dayDiff < $minDaysBookInAdvance ) :
		$dateCheckIn = JDate::getInstance($tariff->valid_from, $timezone)->add(new DateInterval('P' . ( $minDaysBookInAdvance - $dayDiff ) . 'D'));
	else :
		$dateCheckIn = JDate::getInstance($tariff->valid_from, $timezone);
	endif;
	$dateCheckOut = JDate::getInstance($tariff->valid_from, $timezone);
else :
	$dateCheckIn = JDate::getInstance('now', $timezone)->add(new DateInterval('P' . ( $minDaysBookInAdvance ) . 'D'));
	$dateCheckOut = JDate::getInstance('now', $timezone);
endif;

// Try to find the minimum default check in date
$defaultMinCheckInDate = $dateCheckIn;
if (!empty($enabledCheckinDays)) :
	$tempDayInfo = getdate($defaultMinCheckInDate->format('U'));
	while (!in_array($tempDayInfo['wday'], $enabledCheckinDays)) :
		$defaultMinCheckInDate->add(new DateInterval('P1D'));
		$tempDayInfo = getdate($defaultMinCheckInDate->format('U'));
	endwhile;
endif;

// Try to find the minimum default check out date
// Switch to the new default min check in date, for Package
// $defaultMinCheckInDate already contains $minDaysBookInAdvance
$defaultMinCheckOutDate = clone $defaultMinCheckInDate;
if (!is_null($tariff->d_min)) :
	$defaultMinCheckOutDate->add(new DateInterval('P'.($bookingType == 0 ? $tariff->d_min : $tariff->d_min - 1).'D'));
else : // For standard tariff
	$defaultMinCheckOutDate->add(new DateInterval('P1D'));
endif;

$defaultMaxCheckOutDateString = '';
if (!is_null($tariff->d_max)) :
	$defaultMaxCheckOutDate = clone $defaultMinCheckInDate;
	$defaultMaxCheckOutDate->add(new DateInterval('P'.($bookingType == 0 ? $tariff->d_max : $tariff->d_max - 1).'D'));
	$defaultMaxCheckOutDateString = $defaultMaxCheckOutDate->format('Y-m-d', true);
endif;

JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
?>

<div class="inner">
	<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
		<div class="<?php echo SR_UI_GRID_COL_4 ?>">
			<label for="checkin_roomtype">
				<?php echo JText::_('SR_SEARCH_CHECKIN_DATE')?>
			</label>
			<div class="checkin_roomtype datefield">
				<?php echo $defaultMinCheckInDate->format($displayData['dateFormat'], true) ?>
				<i class="fa fa-calendar"></i>
			</div>
			<div class="checkin_datepicker_inline datepicker_inline" style="display: none"></div>
			<?php // this field must always be "Y-m-d" as it is used internally only ?>
			<input type="hidden" name="checkin" value="<?php echo $defaultMinCheckInDate->format('Y-m-d', true) ?>" />
		</div>
		<div class="<?php echo SR_UI_GRID_COL_4 ?>">
			<label for="checkout_roomtype">
				<?php echo JText::_('SR_SEARCH_CHECKOUT_DATE')?>
			</label>
			<div class="checkout_roomtype datefield">
				<?php echo $defaultMinCheckOutDate->format($displayData['dateFormat'], true); ?>
				<i class="fa fa-calendar"></i>
			</div>
			<div class="checkout_datepicker_inline datepicker_inline" style="display: none"></div>
			<?php // this field must always be "Y-m-d" as it is used internally only ?>
			<input type="hidden" name="checkout"
			       value="<?php echo $defaultMinCheckOutDateString = $defaultMinCheckOutDate->format('Y-m-d', true); ?>" />
		</div>
		<div class="<?php echo SR_UI_GRID_COL_4 ?>">
			<div class="action">
				<input type="hidden" name="fts" value="<?php echo time() ?>" />
				<label>&nbsp;</label>
				<button class="btn btn-block searchbtn" data-roomtypeid="<?php echo $displayData['roomTypeId'] ?>" data-tariffid="<?php echo $tariff->id ?>" type="button">
					<i class="fa fa-search "></i> <?php echo JText::_('SR_SEARCH')?>
				</button>
			</div>
		</div>
	</div>

	<script>
		<?php
			if (!$isStandardTariff) :
				$validFrom = array_reverse(explode('-', $tariff->valid_from));
				$validFrom[1] -= 1;
				$validTo = array_reverse(explode('-', $tariff->valid_to));
				$validTo[1] -= 1;

				$datePickerMinDateCheckout = explode('-', $defaultMinCheckOutDateString);
				$datePickerMinDateCheckout[1] -= 1; // In JS, the month index starts from 0, not 1.

				if (!is_null($tariff->d_max)) :
					$datePickerMaxDateCheckout = explode('-', $defaultMaxCheckOutDateString);
					$datePickerMaxDateCheckout[1] -= 1; // In JS, the month index starts from 0, not 1.
				endif;

				echo '
				Solidres.jQuery(function ($) {
					var minLengthOfStay = '.(!is_null($tariff->d_min) ? ($bookingType == 0 ? $tariff->d_min : $tariff->d_min - 1) : 1) .';
					var maxLengthOfStay = '.(!is_null($tariff->d_max) ? ($bookingType == 0 ? $tariff->d_max : $tariff->d_max - 1) : -1) . ';
					var intervalLengthOfStay = ' . ($tariff->d_interval) . ';
					var bookingType = ' . $bookingType . ';
					if (maxLengthOfStay > 0) {
						var periodMinMax = maxLengthOfStay - minLengthOfStay;
					}
					
					if (intervalLengthOfStay > 0 && minLengthOfStay > 0 && maxLengthOfStay > 0) {
						if (bookingType == 0) {
							var threshold = Math.floor(maxLengthOfStay / intervalLengthOfStay);
						} else {
							var threshold = Math.floor((maxLengthOfStay + 1) / intervalLengthOfStay);
						}
						
						var steps = [];
						for (i = 0; i <= threshold; i++) {
							steps.push(i*intervalLengthOfStay);
						}				
					}

					var enabledCheckinDays = '.(!empty($enabledCheckinDays) ? json_encode($enabledCheckinDays, JSON_NUMERIC_CHECK) : '[]' ).';
					var getDefaultCheckInDate = function(checkInMinDate) {
						var day = checkInMinDate.getDay();
						while(!isValidCheckInDate(day) && checkInMinDate.getTime() < checkInMaxDate.getTime()) {
							checkInMinDate.setDate(checkInMinDate.getDate() + 1);
							day = checkInMinDate.getDay();
						}
						return checkInMinDate;
					};

					var isValidCheckInDate = function(day) {
						if (enabledCheckinDays.length == 0) {
							return false;
						}

						if ($.inArray(day, enabledCheckinDays) > -1) {
							return true;
						} else {
							return false;
						}
					};
					
					var checkInMinDate = new Date('.implode(', ', $validFrom).');
					if ( '.$dayDiff.' < ' . $minDaysBookInAdvance . ' ) {
						checkInMinDate.setDate(checkInMinDate.getDate() + '.($minDaysBookInAdvance - $dayDiff).');
					} else {
						checkInMinDate.setDate(checkInMinDate.getDate());
					}
					var checkInMaxDate = new Date('.implode(', ', $validTo).');
					checkInMaxDate.setDate(checkInMaxDate.getDate() - minLengthOfStay);

					var checkout_roomtype = $(".checkout_datepicker_inline").datepicker({
						minDate : new Date('.implode(', ', $datePickerMinDateCheckout).'),
						'.((!is_null($tariff->d_max)) ? 'maxDate : new Date('.implode(', ', $datePickerMaxDateCheckout).'),' : '') .'
						numberOfMonths : '.$datePickerMonthNum.',
						showButtonPanel : true,
						dateFormat : "'.$displayData['jsDateFormat'].'",
						firstDay: '.$weekStartDay.',
						onSelect: function() {
							$("#sr-reservation-form-room input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
							$(".checkout_roomtype").text($.datepicker.formatDate("'.$displayData['jsDateFormat'].'", $(this).datepicker("getDate")));
							$(".checkout_datepicker_inline").slideToggle();
							$(".checkin_roomtype").removeClass("disabledCalendar");
						},
						beforeShowDay: function(date) {
							var currentSelectedDate = $(".checkin_datepicker_inline").datepicker("getDate");
							if (!currentSelectedDate) {
								currentSelectedDate = getDefaultCheckInDate(checkInMinDate, checkInMaxDate)
							}
							
							var testDate3 = new Date('.implode(', ', $validTo).');
							if (date > testDate3) {
								return [false, "notbookable2"];
							}
							
							var day = date.getDay();
							if (intervalLengthOfStay > 0 && minLengthOfStay > 0 && maxLengthOfStay > 0) {
								var diffInDays = Math.round((date - currentSelectedDate)/(24*60*60*1000));
								if (bookingType == 1) {
									diffInDays += 1;
								}
								if (diffInDays >= 0 && $.inArray(diffInDays,steps) > -1) {
									return [true, "bookable"];
								} else {
									return [false, "notbookable"];
								}
							} else {
								return [true, "bookable"];
							}							
						}
					});

					var checkin_roomtype = $(".checkin_datepicker_inline").datepicker({
						minDate : checkInMinDate,
						maxDate : checkInMaxDate,
						'.($maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($maxDaysBookInAdvance) . '",' : '' ).'
						numberOfMonths : '.$datePickerMonthNum.',
						showButtonPanel : true,
						dateFormat : "'.$displayData['jsDateFormat'].'",
						onSelect : function() {
							var currentSelectedDate = $(this).datepicker("getDate");
							var checkoutMinDate = $(this).datepicker("getDate", "+1d");
							var checkoutMaxDate = $(this).datepicker("getDate", "+1d");
							checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
							if (maxLengthOfStay > 0) {
								var testDate1 = checkoutMaxDate; 
								testDate1.setDate(testDate1.getDate() + maxLengthOfStay);
								var testDate2 = new Date('.implode(', ', $validTo).');

								if (testDate1 <= testDate2) {
									checkout_roomtype.datepicker( "option", "maxDate", new Date(testDate1) );
								} else {
									checkout_roomtype.datepicker( "option", "maxDate", testDate2 );
								}
							}
							checkout_roomtype.datepicker( "option", "minDate", checkoutMinDate );
							checkout_roomtype.datepicker( "setDate", checkoutMinDate);

							$("#sr-reservation-form-room input[name=\'checkin\']").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
							$("#sr-reservation-form-room input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));

							$(".checkin_roomtype").text($.datepicker.formatDate("'.$displayData['jsDateFormat'].'", currentSelectedDate));
							$(".checkout_roomtype").text($.datepicker.formatDate("'.$displayData['jsDateFormat'].'", checkoutMinDate));
							$(".checkin_datepicker_inline").slideToggle();
							$(".checkout_roomtype").removeClass("disabledCalendar");
						},
						firstDay: '.$weekStartDay.',
						defaultDate: getDefaultCheckInDate(checkInMinDate, checkInMaxDate),
						beforeShowDay: function(day) {
							var day = day.getDay();
							if (isValidCheckInDate(day)) {
								return [true, "bookable"];
							} else {
								return [false, "notbookable"];
							}
						}
					});

					$(".ui-datepicker").addClass("notranslate");
				});
				';

			else : // For standard tariff
				echo '
				Solidres.jQuery(function ($) {
					var minLengthOfStay = '.$minLengthOfStay.';
					var checkout_roomtype = $(".checkout_datepicker_inline").datepicker({
						minDate : "+' . ( $minDaysBookInAdvance + $minLengthOfStay ). '",
						numberOfMonths : '.$datePickerMonthNum.',
						showButtonPanel : true,
						dateFormat : "'.$displayData['jsDateFormat'].'",
						firstDay: '.$weekStartDay.',
						onSelect: function() {
							$("#sr-reservation-form-room input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
							$(".checkout_roomtype").text($.datepicker.formatDate("'.$displayData['jsDateFormat'].'", $(this).datepicker("getDate")));
							$(".checkout_datepicker_inline").slideToggle();
							$(".checkin_roomtype").removeClass("disabledCalendar");
						}
					});
					var checkin_roomtype = $(".checkin_datepicker_inline").datepicker({
						minDate : "+' . ($minDaysBookInAdvance ) . 'd",
						'.($maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($maxDaysBookInAdvance) . '",' : '' ).'
						numberOfMonths : '.$datePickerMonthNum.',
						showButtonPanel : true,
						dateFormat : "'.$displayData['jsDateFormat'].'",
						onSelect : function() {
							var currentSelectedDate = $(this).datepicker("getDate");
							var checkoutMinDate = $(this).datepicker("getDate", "+1d");
							checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
							checkout_roomtype.datepicker( "option", "minDate", checkoutMinDate );
							checkout_roomtype.datepicker( "setDate", checkoutMinDate);

							$("#sr-reservation-form-room input[name=\'checkin\']").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
							$("#sr-reservation-form-room input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));

							$(".checkin_roomtype").text($.datepicker.formatDate("'.$displayData['jsDateFormat'].'", currentSelectedDate));
							$(".checkout_roomtype").text($.datepicker.formatDate("'.$displayData['jsDateFormat'].'", checkoutMinDate));
							$(".checkin_datepicker_inline").slideToggle();
							$(".checkout_roomtype").removeClass("disabledCalendar");
						},
						firstDay: '.$weekStartDay.'
					});
					$(".ui-datepicker").addClass("notranslate");
				});
				';
			endif;
		?>
	</script>
</div>