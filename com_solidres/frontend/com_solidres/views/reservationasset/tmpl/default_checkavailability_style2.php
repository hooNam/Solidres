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

$config               = JFactory::getConfig();
$minDaysBookInAdvance = $this->config->get( 'min_days_book_in_advance', 0 );
$maxDaysBookInAdvance = $this->config->get( 'max_days_book_in_advance', 0 );
$minLengthOfStay      = $this->config->get( 'min_length_of_stay', 1 );
$datePickerMonthNum   = $this->config->get( 'datepicker_month_number', 3 );
$weekStartDay         = $this->config->get( 'week_start_day', 1 );
$dateFormat           = $this->config->get( 'date_format', 'd-m-Y' );
$roomsOccupancyOptions = $this->app->getUserState($this->context.'.room_opt', array());
JLoader::register( 'SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php' );
$tzoffset    = $config->get( 'offset' );
$timezone    = new DateTimeZone( $tzoffset );
$dateCheckIn = JDate::getInstance();
if ( ! isset( $this->checkin ) ) :
	$dateCheckIn->add( new DateInterval( 'P' . ( $minDaysBookInAdvance ) . 'D' ) )->setTimezone( $timezone );
endif;
$dateCheckOut = JDate::getInstance();
if ( ! isset( $this->checkout ) ) :
	$dateCheckOut->add( new DateInterval( 'P' . ( $minDaysBookInAdvance + $minLengthOfStay ) . 'D' ) )->setTimezone( $timezone );
endif;

$jsDateFormat               = SRUtilities::convertDateFormatPattern( $dateFormat );
$roomsOccupancyOptionsCount = count( $roomsOccupancyOptions );
$maxRooms                   = isset($this->item->params['max_room_number']) ? $this->item->params['max_room_number'] : 10;
$maxAdults                  = isset($this->item->params['max_adult_number']) ? $this->item->params['max_adult_number'] : 10;
$maxChildren                = isset($this->item->params['max_child_number']) ? $this->item->params['max_child_number'] : 10;

$defaultCheckinDate  = '';
$defaultCheckoutDate = '';
if ( isset( $this->checkin ) ) {
	$this->checkinModule  = JDate::getInstance( $this->checkin, $timezone );
	$this->checkoutModule = JDate::getInstance( $this->checkout, $timezone );
// These variables are used to set the defaultDate of datepicker
	$defaultCheckinDate  = $this->checkinModule->format( 'Y-m-d', true );
	$defaultCheckoutDate = $this->checkoutModule->format( 'Y-m-d', true );
}

if ( ! empty( $defaultCheckinDate ) ) :
	$defaultCheckinDateArray = explode( '-', $defaultCheckinDate );
	$defaultCheckinDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

if ( ! empty( $defaultCheckoutDate ) ) :
	$defaultCheckoutDateArray = explode( '-', $defaultCheckoutDate );
	$defaultCheckoutDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

$doc = JFactory::getDocument();
JHtml::_( 'script', SRURI_MEDIA . '/assets/js/datePicker/localization/jquery.ui.datepicker-' . JFactory::getLanguage()->getTag() . '.js', false, false );
$doc->addScriptDeclaration( '
	Solidres.jQuery(function($) {
		var minLengthOfStay = ' . $minLengthOfStay . ';
		var checkout = $("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_datepicker_inline_module").datepicker({
			minDate : "+' . ( $minDaysBookInAdvance + $minLengthOfStay ) . '",
			numberOfMonths : ' . $datePickerMonthNum . ',
			showButtonPanel : true,
			dateFormat : "' . $jsDateFormat . '",
			firstDay: ' . $weekStartDay . ',
			' . ( isset( $this->checkout ) ? 'defaultDate: new Date(' . implode( ',', $defaultCheckoutDateArray ) . '),' : '' ) . '
			onSelect: function() {
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_module").text($.datepicker.formatDate("' . $jsDateFormat . '", $(this).datepicker("getDate")));
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_datepicker_inline_module").slideToggle();
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_module").removeClass("disabledCalendar");
			}
		});
		var checkin = $("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_datepicker_inline_module").datepicker({
			minDate : "+' . $minDaysBookInAdvance . 'd",
			' . ( $maxDaysBookInAdvance > 0 ? 'maxDate: "+' . ( $maxDaysBookInAdvance ) . '",' : '' ) . '
			numberOfMonths : ' . $datePickerMonthNum . ',
			showButtonPanel : true,
			dateFormat : "' . $jsDateFormat . '",
			' . ( isset( $this->checkin ) ? 'defaultDate: new Date(' . implode( ',', $defaultCheckinDateArray ) . '),' : '' ) . '
			onSelect : function() {
				var currentSelectedDate = $(this).datepicker("getDate");
				var checkoutMinDate = $(this).datepicker("getDate", "+1d");
				checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
				checkout.datepicker( "option", "minDate", checkoutMinDate );
				checkout.datepicker( "setDate", checkoutMinDate);
				
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' input[name=\'checkin\']").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));
				
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_module").text($.datepicker.formatDate("' . $jsDateFormat . '", currentSelectedDate));
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_module").text($.datepicker.formatDate("' . $jsDateFormat . '", checkoutMinDate));
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_datepicker_inline_module").slideToggle();
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_module").removeClass("disabledCalendar");
			},
			firstDay: ' . $weekStartDay . '
		});
		$(".ui-datepicker").addClass("notranslate");
		$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_module").removeClass("disabledCalendar");
					} else {
						$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_module").addClass("disabledCalendar");
					}
				});
			}
		});
		
		$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkout_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_module").removeClass("disabledCalendar");
					} else {
						$("#sr-checkavailability-form-asset-' . $this->item->id . ' .checkin_module").addClass("disabledCalendar");
					}
				});
			}
		});
		
		$("#sr-checkavailability-form-asset-' . $this->item->id . ' .room_quantity").change(function() {
			var curQuantity = $(this).val();
			$("#sr-checkavailability-form-asset-' . $this->item->id . ' .room_num_row").each(function( index ) {
				var index2 = index + 1;
				if (index2 <= curQuantity) {
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' #room_num_row_" + index2).show();
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' #room_num_row_" + index2 + " select").removeAttr("disabled");
			} else {
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' #room_num_row_" + index2).hide();
				$("#sr-checkavailability-form-asset-' . $this->item->id . ' #room_num_row_" + index2 + " select").attr("disabled", "disabled");
			}
			});
		});
		
		if ($("#sr-checkavailability-form-asset-' . $this->item->id . ' .room_quantity").val() > 0) {
			$("#sr-checkavailability-form-asset-' . $this->item->id . ' .room_quantity").trigger("change");
		}
	});
' );

$enableRoomQuantity = isset($this->item->params['enable_room_quantity_option']) ? $this->item->params['enable_room_quantity_option'] : 0;

?>

<form id="sr-checkavailability-form-asset-<?php echo $this->item->id ?>" action="<?php echo JRoute::_('index.php#form', false)?>" method="GET" class="form-stacked sr-validate">
	<fieldset>
		<input name="id" value="<?php echo $this->item->id ?>" type="hidden" />
		<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
			<div class="<?php echo SR_UI_GRID_COL_12 ?>">
				<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
					<div class="<?php echo $enableRoomQuantity == 0 ? SR_UI_GRID_COL_9 : SR_UI_GRID_COL_5 ?>">
						<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
							<div class="<?php echo SR_UI_GRID_COL_6 ?>">
								<label for="checkin">
									<?php echo JText::_('SR_SEARCH_CHECKIN_DATE')?>
								</label>
								<div class="checkin_module datefield">
									<?php echo isset($this->checkin) ?
										$this->checkinModule->format($dateFormat, true) :
										$dateCheckIn->format($dateFormat, true) ?>
									<i class="fa fa-calendar"></i>
								</div>
								<div class="checkin_datepicker_inline_module datepicker_inline" style="display: none"></div>
								<?php // this field must always be "Y-m-d" as it is used internally only ?>
								<input type="hidden" name="checkin" value="<?php echo isset($this->checkin) ?
									$this->checkinModule->format('Y-m-d', true) :
									$dateCheckIn->format('Y-m-d', true) ?>" />
							</div>
							<div class="<?php echo SR_UI_GRID_COL_6 ?>">
								<label for="checkout">
									<?php echo JText::_('SR_SEARCH_CHECKOUT_DATE')?>
								</label>
								<div class="checkout_module datefield">
									<?php echo isset($this->checkout) ?
										$this->checkoutModule->format($dateFormat, true) :
										$dateCheckOut->format($dateFormat, true)
									?>
									<i class="fa fa-calendar"></i>
								</div>
								<div class="checkout_datepicker_inline_module datepicker_inline" style="display: none"></div>
								<?php // this field must always be "Y-m-d" as it is used internally only ?>
								<input type="hidden" name="checkout" value="<?php echo isset($this->checkout) ?
									$this->checkoutModule->format('Y-m-d', true) :
									$dateCheckOut->format('Y-m-d', true) ?>" />
							</div>
						</div>
					</div>
					<div <?php echo $enableRoomQuantity == 0 ? 'style="display:none"' : '' ?> class="<?php echo SR_UI_GRID_COL_5 ?>">
						<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
							<div class="<?php echo SR_UI_GRID_COL_3 ?>">
								<label><?php echo JText::_('SR_SEARCH_ROOMS') ?></label>
								<select class="<?php echo SR_UI_GRID_COL_12 ?> room_quantity" name="room_quantity">
									<?php for ($room_num = 1; $room_num <= $maxRooms; $room_num ++) : ?>
										<option <?php echo $room_num == $roomsOccupancyOptionsCount ? 'selected' : '' ?> value="<?php echo $room_num  ?>"><?php echo $room_num  ?></option>
									<?php endfor ?>
								</select>
							</div>
							<div class="<?php echo SR_UI_GRID_COL_9 ?>">
								<?php for ($room_num = 1; $room_num <= $maxRooms; $room_num ++) : ?>
									<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
										<div class="<?php echo SR_UI_GRID_COL_12 ?> room_num_row" id="room_num_row_<?php echo $room_num ?>" style="<?php echo $room_num > 0 ? 'display: none' : '' ?>">
											<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
												<div class="<?php echo SR_UI_GRID_COL_4 ?>">
													<label>&nbsp;</label>
													<?php echo JText::_('SR_SEARCH_ROOM') ?> <?php echo $room_num  ?>
												</div>
												<div class="<?php echo SR_UI_GRID_COL_4 ?>">
													<label><?php echo JText::_('SR_SEARCH_ROOM_ADULTS') ?></label>
													<select <?php echo $room_num > 0 ? 'disabled': '' ?> class="<?php echo SR_UI_GRID_COL_12 ?>" name="room_opt[<?php echo $room_num ?>][adults]">
														<?php
														for ($a = 1; $a <= $maxAdults; $a ++) :
															$selected = '';
															if (isset($roomsOccupancyOptions[$room_num]['adults'])
															    &&
															    ($a == $roomsOccupancyOptions[$room_num]['adults'])
															) :
																$selected = 'selected';
															endif;
															?>
															<option <?php echo $selected ?> value="<?php echo $a ?>"><?php echo $a ?></option>
															<?php
														endfor
														?>
													</select>
												</div>
												<div class="<?php echo SR_UI_GRID_COL_4 ?>">
													<label><?php echo JText::_('SR_SEARCH_ROOM_CHILDREN') ?></label>
													<select <?php echo $room_num > 0 ? 'disabled': '' ?> class="<?php echo SR_UI_GRID_COL_12 ?>" name="room_opt[<?php echo $room_num ?>][children]">
														<?php
														for ($c = 0; $c <= $maxChildren; $c ++) :
															$selected = '';
															if (isset($roomsOccupancyOptions[$room_num]['children'])
															    &&
															    $c == $roomsOccupancyOptions[$room_num]['children']
															) :
																$selected = 'selected';
															endif;
															?>
															<option <?php echo $selected ?> value="<?php echo $c ?>"><?php echo $c ?></option>
															<?php
														endfor
														?>
													</select>
												</div>
											</div>
										</div>
									</div>
								<?php endfor; ?>
							</div>
						</div>
					</div>
					<div class="<?php echo $enableRoomQuantity == 0 ? SR_UI_GRID_COL_3 : SR_UI_GRID_COL_2 ?>">
						<div class="action">
							<label>&nbsp;</label>
							<button class="btn btn-default btn-block primary" type="submit"><i class="fa fa-search"></i> <?php echo JText::_('SR_SEARCH')?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="option" value="com_solidres" />
	<input type="hidden" name="task" value="reservationasset.checkavailability" />
	<input type="hidden" name="Itemid" value="<?php echo $this->itemid ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>