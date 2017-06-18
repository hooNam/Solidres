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

$dayMapping                = array('0' => JText::_('SUN'), '1' => JText::_('MON'), '2' => JText::_('TUE'), '3' => JText::_('WED'), '4' => JText::_('THU'), '5' => JText::_('FRI'), '6' => JText::_('SAT'));
$tariffBreakDownNetOrGross = $this->showTaxIncl == 1 ? 'net' : 'gross';
$isFresh                   = !isset($this->checkin) && !isset($this->checkout);
$showTariffs = true;
$assetShowTariffs = !isset($this->item->params['show_tariffs']) ? 1 : 0; // Per asset option
if (!$this->showFrontendTariffs || ($this->showFrontendTariffs == 2 && $isFresh))
{
	$showTariffs = false;
}

$disableOnlineBooking = false;
if (isset($this->item->params['disable_online_booking']) && 1 == $this->item->params['disable_online_booking'])
{
	$disableOnlineBooking = true;
	$assetShowTariffs = !isset($this->item->params['show_tariffs']) ?  : 0; // Per asset option
	if (!isset($this->item->params['show_tariffs']) || (isset($this->item->params['show_tariffs']) && 0 == $this->item->params['show_tariffs']))
	{
		$showTariffs = false;
	}
	elseif ( 1 == $this->item->params['show_tariffs'])
	{
		$showTariffs = true;
	}
}

if (!isset($this->item->params['enable_coupon'])) :
	$this->item->params['enable_coupon'] = 0;
endif;

$totalRoomTypeCount = count($this->item->roomTypes);

if ( $this->item->params['enable_coupon'] == 1 ) :
	if (!$isFresh) :
?>
	<div class="coupon">
		<div class="input-append">
			<input type="text" name="coupon_code" class="" id="coupon_code" placeholder="<?php echo JText::_('SR_COUPON_ENTER') ?>"/>
			<button id="coupon_code_check" class="btn btn-default" type="button"><?php echo JText::_('SR_COUPON_CHECK') ?></button>
		</div>
		<?php if (isset($this->coupon)) : ?>
		<span>
			<?php echo JText::_('SR_APPLIED_COUPON') ?>
			<span class="label label-success">
			<?php echo $this->coupon['coupon_name']	?>
			</span>&nbsp;
			<a id="sr-remove-coupon" href="javascript:void(0)" data-couponid="<?php echo $this->coupon['coupon_id'] ?>">
				<?php echo JText::_('SR_REMOVE') ?>
			</a>
		</span>
		<?php endif ?>
	</div>
	<script>
		Solidres.jQuery(function($) {
			$('#coupon_code_check').click(function() {
				var self = $('input#coupon_code');
				var coupon_code = self.val();
				if (coupon_code) {
					$.ajax({
						type: 'POST',
						url: window.location.pathname,
						data: 'option=com_solidres&format=json&task=coupon.isValid&coupon_code=' + coupon_code + '&raid=' + $('input[name="id"]').val(),
						success: function(response) {
							self.parent().next('span').remove();
							self.parent().after(response.message);
							if (!response.status) {
								$('#apply-coupon').attr('disabled', 'disabled');
							} else {
								$('#apply-coupon').removeAttr('disabled');
							}
						},
						dataType: 'JSON'
					});
				}
			});
		});
	</script>
<?php
	endif;
endif;
?>
<a name="form"></a>

<?php if (!empty($this->item->email) || !empty($this->item->params['show_inquiry_form'])): ?>
	<?php echo $this->loadTemplate('inquiry_form'); ?>
<?php endif; ?>
<?php if (isset($this->item->params['show_inline_checkavailability_form'])
	&& $this->item->params['show_inline_checkavailability_form'] == 1
	&& !$disableOnlineBooking
) : ?>

<div id="asset-checkavailability-form">
	<h4><?php echo JText::_('SR_YOUR_STAY')?></h4>
	<?php echo $this->loadTemplate('checkavailability' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : '')); ?>
</div>
<?php endif ?>

<div id="availability-search">
	<?php echo $this->loadTemplate('searchinfo' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : '')); ?>
</div>

<div class="wizard wizard-style">
	<ul class="steps">
		<li data-target="#step1" class="active reservation-tab reservation-tab-room <?php echo SR_UI_GRID_COL_4 ?>">
			<span class="badge">1</span>
			<h5><?php echo JText::_('SR_STEP_ROOM_AND_RATE') ?></h5>
		</li>
		<li data-target="#step2" class="reservation-tab reservation-tab-guestinfo <?php echo SR_UI_GRID_COL_4 ?>">
			<span class="badge">2</span>
			<h5><?php echo JText::_('SR_STEP_GUEST_INFO_AND_PAYMENT') ?></h5>
		</li>
		<li data-target="#step3" class="reservation-tab reservation-tab-confirmation <?php echo SR_UI_GRID_COL_4 ?>">
			<span class="badge">3</span>
			<h5><?php echo JText::_('SR_STEP_CONFIRMATION') ?></h5>
		</li>
	</ul>
</div>

<div class="step-content">
	<div class="step-pane active" id="step1">
	<!-- Tab 1 -->
	<div class="reservation-single-step-holder room room-grid">
	<form enctype="multipart/form-data"
		  id="sr-reservation-form-room"
		  class="sr-reservation-form"
		  action="<?php echo JUri::base() ?>index.php?option=com_solidres&task=reservation.process&step=room&format=json"
		  method="POST">
	<?php if ($totalRoomTypeCount > 0) : ?>
		<?php if (!$isFresh) : ?>
			<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-top">
				<div class="<?php echo SR_UI_GRID_COL_8 ?>">
					<div class="inner">
						<strong><?php echo JText::_('SR_ROOMINFO_STEP_NOTICE_MESSAGE') ?></strong>
					</div>
				</div>
				<div class="<?php echo SR_UI_GRID_COL_4 ?>">
					<div class="inner">
						<div class="btn-group">
							<button data-step="room" type="submit" class="btn btn-success">
								<i class="fa fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php endif ?>

		<?php
		$count = 1;
		foreach($this->item->roomTypes as $roomTypeIdx => $roomType ) :
			if (isset($roomType->defaultTariffBreakDown)) :
				$defaultTariffBreakDownHtml = '<table class=\"tariff-break-down\">';
				foreach ($roomType->defaultTariffBreakDown as $key => $breakDownDetails ) :
					if ($key % 7 == 0 && $key == 0) :
						$defaultTariffBreakDownHtml .= '<tr>';
					elseif ($key % 7 == 0) :
						$defaultTariffBreakDownHtml .= '</tr><tr>';
					endif;
					$tmpKey = key($breakDownDetails);
					$defaultTariffBreakDownHtml .= '<td><p>'.$dayMapping[$tmpKey].'</p><span class=\"'.$tariffBreakDownNetOrGross.'\">'.$breakDownDetails[$tmpKey][$tariffBreakDownNetOrGross]->format().'</span>';
				endforeach;
				$defaultTariffBreakDownHtml .= '</tr></table>';

				$this->document->addScriptDeclaration('
					Solidres.jQuery(function($){
						$(".default_tariff_break_down_'.$roomType->id.'").popover({
							html: true,
							content: "'.$defaultTariffBreakDownHtml.'",
							title: "'.JText::_('SR_TARIFF_BREAK_DOWN').'",
							placement: "bottom",
							trigger: "click"
						});
					});
				');
			endif;

			if (isset($roomType->complexTariffBreakDown)) :
				$complexTariffBreakDownHtml = '<table class=\"tariff-break-down\">';
				foreach ($roomType->complexTariffBreakDown as $key => $breakDownDetails ) :
					if ($key % 7 == 0 && $key == 0) :
						$complexTariffBreakDownHtml .= '<tr>';
					elseif ($key % 7 == 0) :
						$complexTariffBreakDownHtml .= '</tr><tr>';
					endif;
					$tmpKey = key($breakDownDetails);
					$complexTariffBreakDownHtml .= '<td><p>'.$dayMapping[$tmpKey].'</p><span class=\"'.$tariffBreakDownNetOrGross.'\">'.$breakDownDetails[$tmpKey][$tariffBreakDownNetOrGross]->format().'</span>';
				endforeach;

				$complexTariffBreakDownHtml .= '</tr></table>';
				$this->document->addScriptDeclaration('
					Solidres.jQuery(function($){
						$(".complex_tariff_break_down_'.$roomType->id.'").popover({
							html: true,
							content: "'.$complexTariffBreakDownHtml.'",
							title: "'.JText::_('SR_TARIFF_BREAK_DOWN').'",
							placement: "bottom",
							trigger: "click"
						});
					});
				');
			endif;

			$this->document->addScriptDeclaration('
				Solidres.jQuery(function($){
					$(".sr-photo-'.$roomType->id.'").colorbox({rel:"sr-photo-'.$roomType->id.'", transition:"fade", width: "98%", height: "98%", className: "colorbox-w"});
					$(".carousel").carousel();
				});
			');

			$rowCSSClass = ($count % 2) ? 'even' : 'odd';
			$rowCSSClass .= $roomType->featured == 1 ? ' featured' : '';
			$currentSelectedRoomNumberPerTariff = array();

			if (!is_array($roomType->params)) :
				$roomType->params = json_decode($roomType->params, true);
			endif;

			$skipRoomForm = false;
			if (isset($roomType->params['skip_room_form']) && $roomType->params['skip_room_form'] == 1) :
				$skipRoomForm = true;
			endif;

			$isExclusive = false;
			if (isset($roomType->params['is_exclusive']) && $roomType->params['is_exclusive'] == 1) :
				$isExclusive = true;
			endif;

			$showRemainingRooms = true;
			if (isset($roomType->params['show_number_remaining_rooms']) && $roomType->params['show_number_remaining_rooms'] == 0) :
				$showRemainingRooms = false;
			endif;

			$roomType->text = $roomType->description;
			$this->dispatcher->trigger('onContentPrepare', array('com_solidres.roomtype', &$roomType, &$roomType->params));

			$roomTypeColumns = 2;
			?>

			<?php if ($roomTypeIdx % $roomTypeColumns == 0) : ?>
			<div class="<?php echo SR_UI_GRID_CONTAINER; ?> room_grid_row">
			<?php endif ?>

				<div class="<?php echo SR_UI_GRID_COL_6 ?>">
					<div class="<?php echo $rowCSSClass ?> room_type_row" id="room_type_row_<?php echo $roomType->id ?>">

						<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
							<div class="<?php echo SR_UI_GRID_COL_12 ?>">
								<div class="room_type_gallery">
									<?php
									if( !empty($roomType->media) ) :
										echo '<div id="carousel'.$roomType->id.'" class="carousel slide">';
										echo '<div class="carousel-inner">';
										$countMedia = 0;
										$active = '';
										foreach ($roomType->media as $media) :
											$active = ($countMedia == 0) ? 'active' : '';
											?>
											<div class="item <?php echo $active ?>">
												<a class="sr-photo-<?php echo $roomType->id ?>"
												   href="<?php echo $this->solidresMedia->getMediaUrl( $media->value ); ?>">
													<img src="<?php echo $this->solidresMedia->getMediaUrl( $media->value, 'roomtype_medium' ); ?>"
														 alt="<?php echo $roomType->name ?>"/>
												</a>
											</div>
											<?php
											$countMedia ++;
										endforeach;
										echo '</div>';
										echo '<a class="carousel-control left" href="#carousel'.$roomType->id.'" data-slide="prev">&lsaquo;</a>';
										echo '<a class="carousel-control right" href="#carousel'.$roomType->id.'" data-slide="next">&rsaquo;</a>';
										echo '</div>';
									endif;
									?>
								</div>

								<div class="room_type_details">
									<div class="roomtype_name" id="room_type_details_handler_<?php echo $roomType->id ?>">
										<span class="label label-default">
											<?php echo $roomType->occupancy_max > 0 ? $roomType->occupancy_max : (int)$roomType->occupancy_adult + (int)$roomType->occupancy_child ?>
											<i class="fa fa-user"></i>
										</span>
										<h4><?php echo $roomType->name; ?>
											<?php if ($roomType->featured == 1) : ?>
												<span class="label label-info"><i class="fa fa-certificate"></i> <?php echo JText::_('SR_FEATURED_ROOM_TYPE') ?></span>
											<?php endif ?>
										</h4>
									</div>

									<div class="roomtype_more_desc">
										<p><i class="fa fa-arrows-alt fa-fw"></i> <?php echo JText::_('SR_ROOM_SIZE') .': <strong>'. $roomType->roomtype_custom_fields['room_size'] .'</strong>' ?></p>
										<p><i class="fa fa-bed fa-fw"></i> <?php echo JText::_('SR_BED_SIZE') .': <strong>'. $roomType->roomtype_custom_fields['bed_size'] .'</strong>' ?></p>
										<p><i class="fa fa-users fa-fw"></i> <?php echo JText::_('SR_MAX_GUESTS') .': <strong>'. $roomType->occupancy_adult . ' adult(s) - ' .$roomType->occupancy_child .' child(ren)' .'</strong>' ?></p>
									</div>

									<!-- Room available message -->
									<?php
									if ( !$isFresh && !empty($roomType->availableTariffs) && $showRemainingRooms ) :
										if (isset($roomType->totalAvailableRoom)) :
											?>
											<p><span class="num_rooms_available_msg" id="num_rooms_available_msg_<?php echo $roomType->id ?>"
												  data-original-text="<?php echo JText::plural('SR_WE_HAVE_X_ROOM_LEFT', $roomType->totalAvailableRoom) ?>">
											<?php echo JText::plural('SR_WE_HAVE_X_ROOM_LEFT', $roomType->totalAvailableRoom) ?>
											</span></p>
											<?php
										endif;
									endif;
									?>

									<button type="button" class="btn btn-default toggle_more_desc" data-target="<?php echo $roomType->id ?>">
										<i class="fa fa-eye"></i>
										<?php echo JText::_('SR_SHOW_MORE_INFO') ?>
									</button>

									<?php if ($this->config->get('availability_calendar_enable', 1)) : ?>
										<button type="button" data-roomtypeid="<?php echo $roomType->id ?>" class="btn btn-default load-calendar">
											<i class="fa fa-calendar"></i> <?php echo JText::_('SR_AVAILABILITY_CALENDAR_VIEW') ?>
										</button>
									<?php endif ?>

									<?php if (SRPlugin::isEnabled('complextariff') && $showTariffs) : ?>
										<button type="button" data-roomtypeid="<?php echo $roomType->id ?>"
												class="btn btn-default toggle-tariffs">
											<?php if ($showTariffs) : ?>
												<i class="fa fa-compress"></i> <?php echo JText::_('SR_HIDE_TARIFFS') ?>
											<?php else : ?>
												<i class="fa fa-expand"></i> <?php echo JText::_('SR_SHOW_TARIFFS') ?>
											<?php endif ?>
										</button>
									<?php endif ?>

									<div class="unstyled more_desc" id="more_desc_<?php echo $roomType->id ?>" style="display: none">

										<div class="roomtype_desc">
											<?php echo '<strong>'. JText::_('SR_ROOM_DESCRIPTION') .':</strong> '. $roomType->text ?>
										</div>

										<?php
										if (!empty($roomType->roomtype_custom_fields['room_facilities'])) :
											echo '<p><strong>'. JText::_('SR_ROOM_FACILITIES') .':</strong> '.  $roomType->roomtype_custom_fields['room_facilities'] .'</p>';
										endif;

										if (!empty($roomType->roomtype_custom_fields['taxes'])) :
											echo '<p><strong>'. JText::_('SR_TAXES') .':</strong> '.  $roomType->roomtype_custom_fields['taxes'] .'</p>';
										endif;

										if (!empty($roomType->roomtype_custom_fields['prepayment'])) :
											echo '<p><strong>'. JText::_('SR_PREPAYMENT') .':</strong> '.  $roomType->roomtype_custom_fields['prepayment'] .'</p>';
										endif;
										?>
									</div>
								</div>
							</div> <!-- end of span12 -->
						</div> <!-- end of row-fluid -->

						<?php if ($this->config->get('availability_calendar_enable', 1)) : ?>
							<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
								<div class="<?php echo SR_UI_GRID_COL_12 ?>">
									<div class="availability-calendar" id="availability-calendar-<?php echo $roomType->id ?>" style="display: none">
									</div>
								</div>
							</div>
						<?php endif ?>

						<div class="<?php echo SR_UI_GRID_CONTAINER ?>" id="tariff-holder-<?php echo $roomType->id ?>"
							 style="<?php echo !$disableOnlineBooking || $showTariffs ? '' : 'display: none' ?>">
							<div class="<?php echo SR_UI_GRID_COL_12 ?>">
								<?php
								$hasMatchedTariffs = true;
								if ((!$isFresh)) :
									$hasMatchedTariffs = false;

									if (!empty($roomType->availableTariffs)) :
										foreach ($roomType->availableTariffs as $tariffKey => $tariffInfo) :
											?>

											<div id="tariff-box-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" data-targetcolor="FF981D" class="tariff-box <?php //echo $tariffIsSelected ?>">
												<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
													<div class="<?php echo !$disableOnlineBooking ? SR_UI_GRID_COL_12 : SR_UI_GRID_COL_12 ; ?>">
														<div class="tariff-value normal_tariff">
															<?php if ($tariffInfo['val_original']->getValue() > $tariffInfo['val']->getValue()) : ?>
																<span class="sr-strikethrough">
															<?php echo $tariffInfo['val_original']->format() ?>
																</span>
															<?php endif ?>
															<?php echo $tariffInfo['val']->format() ?>

															<?php
															if ($tariffInfo['tariffType'] == 0):
																$tariffType = JText::_('SR_TARIFF_PER_ROOM_PER_NIGHT');
															endif;
															if ($tariffInfo['tariffType'] == 1):
																$tariffType = JText::_('SR_TARIFF_PER_PERSON_PER_NIGHT');
															endif;
															if ($tariffInfo['tariffType'] == 2):
																$tariffType = JText::_('SR_TARIFF_PACKAGE_PER_ROOM');
															endif;
															if ($tariffInfo['tariffType'] == 3):
																$tariffType = JText::_('SR_TARIFF_PACKAGE_PER_PERSON');
															endif;
															?>
															<p class="tariff_type"><?php echo $tariffType ?></p>
														</div>

														<div class="tariff-title-desc">
															<strong>
																<?php
																if (!empty($tariffInfo['tariffTitle'])) :
																	echo $tariffInfo['tariffTitle'];
																else :
																	if ($this->item->booking_type == 0) :
																		echo JText::plural('SR_PRICE_IS_FOR_X_NIGHT', $this->stayLength);
																	else :
																		echo JText::plural('SR_PRICE_IS_FOR_X_DAY', $this->stayLength + 1);
																	endif;
																endif;
																?>
															</strong>
															<?php
															if (!empty($tariffInfo['tariffDescription'])) :
																echo '<p>' . $tariffInfo['tariffDescription'] . '</p>';
															endif;
															?>
														</div>
														<?php if (!$disableOnlineBooking): ?>
															<div class="tariff-button">
																<?php
																if (isset ($roomType->totalAvailableRoom)) :
																	if ($roomType->totalAvailableRoom == 0) :
																		echo JText::_('SR_NO_ROOM_AVAILABLE');
																	else :
																		if (!$isExclusive) :
																			?>
																			<select
																				name="solidres[ign<?php echo rand() ?>]"
																				data-raid="<?php echo $this->item->id ?>"
																				data-rtid="<?php echo $roomType->id ?>"
																				data-tariffid="<?php echo $tariffKey ?>"
																				data-adjoininglayer="<?php echo $tariffInfo['tariffAdjoiningLayer'] ?>"
																				data-totalroomsleft="<?php echo $roomType->totalAvailableRoom ?>"
																				class="<?php echo SR_UI_GRID_COL_12 ?> roomtype-quantity-selection quantity_<?php echo $roomType->id ?>">
																				<option value="0"><?php echo JText::_('SR_ROOMTYPE_QUANTITY') ?></option>
																				<?php
																				for($i = 1; $i <= $roomType->totalAvailableRoom; $i ++) :
																					$selected = '';
																					if (isset($this->selectedRoomTypes['room_types'][$roomType->id][$tariffKey])) :
																						$selected = ($i == count($this->selectedRoomTypes['room_types'][$roomType->id][$tariffKey])) ? 'selected="selected"': '';
																					endif;

																					echo '<option '.$selected.' value="'.$i.'">'. JText::plural($roomType->is_private ? 'SR_SELECT_ROOM_QUANTITY' : 'SR_SELECT_BED_QUANTITY', $i) . '</option>';
																				endfor;
																				?>
																			</select>
																			<?php
																		else :
																			?>
																			<button <?php echo $skipRoomForm ? 'data-step="room"' : '' ?> type="button"
																																		  data-raid="<?php echo $this->item->id ?>"
																																		  data-rtid="<?php echo $roomType->id ?>"
																																		  data-tariffid="<?php echo $tariffKey ?>"
																																		  data-adjoininglayer="<?php echo $tariffInfo['tariffAdjoiningLayer'] ?>"
																																		  data-totalroomsleft="<?php echo $roomType->totalAvailableRoom ?>"
																																		  class="btn btn-default <?php echo SR_UI_GRID_COL_12 ?> <?php echo $skipRoomForm ? 'roomtype-reserve-exclusive' : 'roomtype-reserve' ?> quantity_<?php echo $roomType->id ?>">
																				<?php echo JText::_('SR_RESERVE') ?>
																			</button>
																		<?php endif ?>
																		<input type="hidden"
																			   name="jform[selected_tariffs][<?php echo $roomType->id ?>][]"
																			   value="<?php echo $tariffKey ?>"
																			   id="selected_tariff_<?php echo $roomType->id ?>_<?php echo $tariffKey ?>"
																			   class="selected_tariff_hidden_<?php echo $roomType->id ?>"
																			   disabled
																			/>
																		<div class="processing" style="display: none"></div>

																		<?php if ($isExclusive && $skipRoomForm) : ?>
																		<input type="hidden"
																			   data-raid="<?php echo $this->item->id ?>"
																			   data-roomtypeid="<?php echo $roomType->id ?>"
																			   data-tariffid="<?php echo $tariffKey ?>"
																			   data-adjoininglayer="<?php echo $tariffInfo['tariffAdjoiningLayer'] ?>"
																			   data-roomindex="1"
																			   name="jform[room_types][<?php echo $roomType->id ?>][<?php echo $tariffKey ?>][1][adults_number]"
																			   value="1"
																			   disabled
																			/>
																	<?php endif ?>

																		<?php
																	endif;
																endif;
																?>
															</div>
														<?php endif; ?>
													</div>
												</div>

												<!-- check in form -->
												<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
													<div class="<?php echo SR_UI_GRID_COL_12 ?> checkinoutform" id="checkinoutform-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" style="display: none">

													</div>
												</div>
												<!-- /check in form -->


												<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
													<div class="<?php echo SR_UI_GRID_COL_12 ?> room-form-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" id="room-form-<?php echo $roomType->id ?>-<?php echo $tariffKey ?>" style="display: none">

													</div>
												</div>

											</div>
											<?php
										endforeach;
									else :
										$link = JRoute::_('index.php?option=com_solidres&view=reservationasset&id=' . $this->item->id . '#form');
										echo '<div class="alert alert-notice">'.JText::sprintf('SR_NO_TARIFF_MATCH_CHECKIN_CHECKOUT', $this->checkinFormatted, $this->checkoutFormatted, $link) .'</div>';
									endif;
								endif;

								if ($isFresh && $showTariffs == 1) :
									if (isset($roomType->tariffs) && is_array($roomType->tariffs)) :
										foreach ($roomType->tariffs as $tariff) :
											$tariffIsSelected = '';

											if (isset($this->selectedTariffs[$roomType->id])) :
												$tariffIsSelected = in_array($tariff->id, $this->selectedTariffs[$roomType->id]) ? 'selected' : '';
											endif;

											if ( isset($this->selectedRoomTypes['room_types'][$roomType->id][$tariff->id])) :
												$currentSelectedRoomNumberPerTariff[$tariff->id] = count($this->selectedRoomTypes['room_types'][$roomType->id][$tariff->id]);
											endif;

											$min = 0;
											?>
											<div id="tariff-box-<?php echo $roomType->id ?>-<?php echo $tariff->id ?>" data-targetcolor="FF981D"  class="tariff-box <?php echo $tariffIsSelected ?>">
												<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
													<div class="<?php echo !$disableOnlineBooking ? SR_UI_GRID_COL_12 : SR_UI_GRID_COL_12 ; ?>">
														<div class="tariff-value">
															<?php echo $this->getMinPrice($tariff) ?>
														</div>
														<div class="tariff-title-desc">
															<strong><?php echo empty($tariff->title) ? JText::_('SR_STANDARD_TARIFF'): $tariff->title ?></strong>
															<p><?php echo $tariff->description ?></p>
														</div>
														<?php if (!$disableOnlineBooking): ?>
															<div class="tariff-button">
																<?php
																if ($tariff->type == 0):
																	$tariffType = JText::_('SR_TARIFF_PER_ROOM_PER_NIGHT');
																endif;
																if ($tariff->type == 1):
																	$tariffType = JText::_('SR_TARIFF_PER_PERSON_PER_NIGHT');
																endif;
																if ($tariff->type == 2):
																	$tariffType = JText::_('SR_TARIFF_PACKAGE_PER_ROOM');
																endif;
																if ($tariff->type == 3):
																	$tariffType = JText::_('SR_TARIFF_PACKAGE_PER_PERSON');
																endif;
																?>
																<span class="tariff_type"><?php echo $tariffType ?></span>

																<?php if ($isFresh) : ?>
																	<button class="btn btn-default btn-block trigger_checkinoutform" type="button"
																			data-roomtypeid="<?php echo $roomType->id ?>"
																			data-itemid="<?php echo $this->itemid ?>"
																			data-assetid="<?php echo $this->item->id ?>"
																			data-tariffid="<?php echo $tariff->id ?>"
																		><?php echo JText::_('SR_SELECT_TARIFF') ?></button>
																<?php else :
																	if (isset ($roomType->totalAvailableRoom)) :
																		if ($roomType->totalAvailableRoom == 0) :
																			echo JText::_('SR_NO_ROOM_AVAILABLE');
																		else :
																			?>
																			<select
																				name="solidres[ign<?php echo $tariff->id ?>]"
																				data-raid="<?php echo $this->item->id ?>"
																				data-rtid="<?php echo $roomType->id ?>"
																				data-tariffid="<?php echo $tariff->id ?>"
																				data-totalroomsleft="<?php echo $roomType->totalAvailableRoom ?>"
																				class="<?php echo SR_UI_GRID_COL_12 ?> roomtype-quantity-selection quantity_<?php echo $roomType->id ?>">
																				<option value="0"><?php echo JText::_('SR_ROOMTYPE_QUANTITY') ?></option>
																				<?php
																				for($i = 1; $i <= $roomType->totalAvailableRoom; $i ++) :
																					$selected = '';
																					if (isset($currentSelectedRoomNumberPerTariff[$tariff->id])) :
																						$selected = ($i == $currentSelectedRoomNumberPerTariff[$tariff->id]) ? 'selected': '';
																					endif;

																					echo '<option '.$selected.' value="'.$i.'">'. JText::plural($roomType->is_private ? 'SR_SELECT_ROOM_QUANTITY' : 'SR_SELECT_BED_QUANTITY', $i) . '</option>';
																				endfor;
																				?>
																			</select>

																			<input type="hidden"
																				   name="jform[selected_tariffs][<?php echo $roomType->id ?>][]"
																				   value="<?php echo $tariff->id ?>"
																				   id="selected_tariff_<?php echo $roomType->id ?>_<?php echo $tariff->id ?>"
																				   class="selected_tariff_hidden_<?php echo $roomType->id ?>"
																				   disabled
																				/>
																			<div class="processing" style="display: none"></div>
																			<?php
																		endif;
																	endif;
																endif;
																?>
															</div>
														<?php endif; ?>
													</div>
												</div>

												<!-- check in form -->
												<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
													<div class="<?php echo SR_UI_GRID_COL_12 ?> checkinoutform" id="checkinoutform-<?php echo $roomType->id ?>-<?php echo $tariff->id ?>" style="display: none">

													</div>
												</div>
												<!-- /check in form -->

												<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
													<div class="<?php echo SR_UI_GRID_COL_12 ?> room-form-<?php echo $roomType->id ?>-<?php echo $roomType->id ?>" id="room-form-<?php echo $roomType->id ?>-<?php echo $tariff->id ?>" style="display: none">

													</div>
												</div>
											</div>
											<?php
										endforeach; // end foreach of complex tariffs
									endif;
								endif // end if in line 274 ?>
							</div> <!-- end of span12 -->
						</div> <!-- end of row-fluid -->
					</div> <!-- end of row-fluid -->

				</div>

			<?php if (($roomTypeIdx + 1) % $roomTypeColumns == 0 || ($roomTypeIdx + 1 == $totalRoomTypeCount)) : ?>
			</div>
			<?php endif ?>

			<?php
			$count ++;
		endforeach
		?>
	<?php
		else :
	?>
		<div class="alert alert-warning">
			<?php
			echo JText::sprintf('SR_NO_ROOM_TYPES_MATCHED_SEARCH_CONDITIONS',
				JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
				JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true)
			);
			?>
			<a class="" href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.startOver&id='. $this->item->id ) ?>"><i class="fa fa-refresh"></i> <?php echo JText::_('SR_SEARCH_RESET')?></a>
		</div>
	<?php
		endif;
	?>

	<?php if (!$isFresh && $totalRoomTypeCount > 0) : ?>
		<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-bottom">
			<div class="<?php echo SR_UI_GRID_COL_8 ?>">
				<div class="inner">
					<strong><?php echo JText::_('SR_ROOMINFO_STEP_NOTICE_MESSAGE') ?></strong>
				</div>
			</div>
			<div class="<?php echo SR_UI_GRID_COL_4 ?>">
				<div class="inner">
					<div class="btn-group">
						<button data-step="room" type="submit" class="btn btn-success">
							<i class="fa fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>

	<input type="hidden" name="jform[raid]" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="jform[next_step]" value="guestinfo" />
	<input type="hidden" name="jform[bookingconditions]" value="<?php echo $this->item->params['termsofuse'] ?>" />
	<input type="hidden" name="jform[privacypolicy]" value="<?php echo $this->item->params['privacypolicy'] ?>" />

	<?php echo JHtml::_('form.token'); ?>
	</form>
	</div>
	<!-- /Tab 1 -->

	</div>

	<div class="step-pane" id="step2">
		<!-- Tab 2 -->
		<div class="reservation-single-step-holder guestinfo nodisplay">
		</div>
		<!-- /Tab 2 -->
	</div>

	<div class="step-pane" id="step3">
		<!-- Tab 3 -->
		<div class="reservation-single-step-holder confirmation nodisplay">
		</div>
		<!-- /Tab 3 -->
	</div>

</div>
