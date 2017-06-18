<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die;
$isFrontEnd = JFactory::getApplication()->isSite();
?>

<form enctype="multipart/form-data"
	  id="sr-reservation-form-room"
	  class="sr-reservation-form"
	  action="index.php?option=com_solidres&task=reservation<?php echo $isFrontEnd ? '' : 'base' ?>.process&step=room&format=json"
	  method="POST">
	<?php
	foreach ( $displayData['room_types'] as $roomType ) :
		?>
		<h3>
			<span class="label label-info"><?php echo $roomType->occupancy_max > 0 ? $roomType->occupancy_max : (int)$roomType->occupancy_adult + (int)$roomType->occupancy_child ?><i class="fa fa-user"></i></span> <?php echo $roomType->name ?>
		</h3>
		<?php if ( ! empty( $roomType->rooms ) ) :
			$itemPerRow     = 2;
			$spanNum        = 12 / (int) $itemPerRow;
			$totalRoomCount = count( $roomType->rooms );
			for ( $count = 0; $count <= $totalRoomCount; $count++ ) :
				if ( $count % $itemPerRow == 0 && $count == 0 ) :
					echo '<div class="' . SR_UI_GRID_CONTAINER . '">';
				elseif ( $count % $itemPerRow == 0 && $count != $totalRoomCount ) :
					echo '</div><div class="' . SR_UI_GRID_CONTAINER . '">';
				elseif ( $count == $totalRoomCount ) :
					echo '</div>';
				endif;

				if ($count < $totalRoomCount) :
					$currentRoomIndex = NULL;
					$arrayHolder = 'xtariffidx';
					$room = $roomType->rooms[$count];
					if (isset($displayData['current_reservation_data']->reserved_room_details[$room->id])) :
						$currentRoomIndex = (array) $displayData['current_reservation_data']->reserved_room_details[$room->id];
						$arrayHolder = $currentRoomIndex['tariff_id'];
					endif;
					$identity = $roomType->id . '_' . (isset($currentRoomIndex['tariff_id']) ? $currentRoomIndex['tariff_id'] : $arrayHolder ) . '_' . $room->id;

					$checked  = '';
					$disabled = !$room->isAvailable && !$room->isReservedForThisReservation ? 'disabled' : '';

					if ( ! $room->isAvailable || $room->isReservedForThisReservation) :
						$checked = 'checked';
					endif;

					// Html for adult selection
					$htmlAdultSelection = '';
					$htmlAdultSelection .= '<option value="">' . JText::_( 'SR_ADULT' ) . '</option>';

					for ( $j = 1; $j <= $roomType->occupancy_adult; $j ++ ) :
						$selected = '';
						if ( isset( $currentRoomIndex['adults_number'] ) ) :
							$selected = $currentRoomIndex['adults_number'] == $j ? 'selected' : '';
						else :
							if ( $j == 1 ) :
								$selected = 'selected';
							endif;
						endif;
						$htmlAdultSelection .= '<option ' . $selected . ' value="' . $j . '">' . JText::plural( 'SR_SELECT_ADULT_QUANTITY', $j ) . '</option>';
					endfor;

					// Html for children selection
					$htmlChildSelection = '';
					$htmlChildrenAges   = '';
					if ( ! isset( $roomType->params['show_child_option'] ) ) :
						$roomType->params['show_child_option'] = 1;
					endif;

					// Only show child option if it is enabled and the child quantity > 0
					if ( $roomType->params['show_child_option'] == 1 && $roomType->occupancy_child > 0 ) :
						$htmlChildSelection .= '';
						$htmlChildSelection .= '<option value="">' . JText::_( 'SR_CHILD' ) . '</option>';

						for ( $j = 1; $j <= $roomType->occupancy_child; $j ++ ) :
							if ( isset( $currentRoomIndex['children_number'] ) ) :
								$selected = $currentRoomIndex['children_number'] == $j ? 'selected' : '';
							endif;
							$htmlChildSelection .= '
			<option ' . $selected . ' value="' . $j . '">' . JText::plural( 'SR_SELECT_CHILD_QUANTITY', $j ) . '</option>
		';
						endfor;

						// Html for children ages
						// Restructure to match front end
						if (is_array($currentRoomIndex['other_info'])) :
							foreach ($currentRoomIndex['other_info'] as $info) :
								if (substr($info->key, 0, 5) == 'child') :
									$currentRoomIndex['children_ages'][] = $info->value;
								endif;
							endforeach;
						endif;

						if ( isset( $currentRoomIndex['children_ages'] ) ) :
							for ( $j = 0; $j < count( $currentRoomIndex['children_ages'] ); $j ++ ) :
								$htmlChildrenAges .= '
				<li>
					' . JText::_( 'SR_CHILD' ) . ' ' . ( $j + 1 ) . '
					<select name="jform[room_types][' . $roomType->id . ']['.$arrayHolder.'][' . $room->id . '][children_ages][]"
						data-raid="' . $displayData['raid'] . '"
						data-roomtypeid="' . $roomType->id . '"
						data-roomid="' . $room->id . '"
						class="' . SR_UI_GRID_COL_6 . ' child_age_' . $roomType->id . '_'.$arrayHolder.'_' . $room->id . '_' . $j . ' trigger_tariff_calculating"
						required
					>';
								$htmlChildrenAges .= '<option value=""></option>';
								for ( $age = 1; $age <= $displayData['childMaxAge']; $age ++ ) :
									$selectedAge = '';
									if ( $age == $currentRoomIndex['children_ages'][ $j ] ) :
										$selectedAge = 'selected';
									endif;
									$htmlChildrenAges .= '<option ' . $selectedAge . ' value="' . $age . '">' . JText::plural( 'SR_CHILD_AGE_SELECTION', $age ) . '</option>';
								endfor;

								$htmlChildrenAges .= '
					</select>
				</li>';
							endfor;
						endif;
					endif;
				?>
				<div class="<?php echo constant('SR_UI_GRID_COL_' . $spanNum) ?>" id="room<?php echo $room->id ?>">
						<dl class="room_selection_wrapper">
							<dt>
								<label class="checkbox">
									<input type="checkbox"
										   value="<?php echo $room->id ?>"
										   class="reservation_room_select"
										   name="jform[reservation_room_select][]" <?php echo $checked ?> <?php echo $disabled ?> />
									<span class="label <?php echo $room->isReservedForThisReservation ? 'label-success' : '' ?>">
										<?php echo $room->label ?>
									</span>
								</label>
								<table class="table table-condensed table-bordered"
								       style="<?php echo $room->isReservedForThisReservation ? '' : 'display: none;' ?>">
									<tbody>
										<tr>
											<td>
												<?php echo JText::_( 'SR_AMEND_RESERVATION_TARIFF_CURRENT' ) ?>
											</td>
											<td class="sr-align-right">
												<?php
												if ($room->isReservedForThisReservation) :
													$tmpCurrency = clone $displayData['currency'];
													$tmpCurrency->setValue($currentRoomIndex['room_price_tax_incl']);
													echo $tmpCurrency->format();
												else :
													echo 0;
												endif;
												?>
											</td>
										</tr>
										<tr>
											<td>
												<?php echo JText::_( 'SR_AMEND_RESERVATION_TARIFF_NEW' ) ?>
											</td>
											<td class="sr-align-right">
												<a href="javascript:void(0)"
												   class="toggle_breakdown tariff_breakdown_<?php echo $room->id ?>"
												   data-target="<?php echo $roomType->id . '_'.$arrayHolder.'_' . $room->id ?>"
												   style="display: none"
													>
													<?php echo JText::_( 'SR_VIEW_TARIFF_BREAKDOWN' ) ?>
												</a>
												<span
													class="tariff_<?php echo $roomType->id . '_'.$arrayHolder.'_' . $room->id ?> tariff_breakdown_<?php echo $room->id ?>"
													style=""
													>
													0
												</span>
											</td>
										</tr>
									</tbody>
								</table>
								<span style="display: none"
									  class="breakdown"
									id="breakdown_<?php echo $roomType->id . '_'.$arrayHolder.'_' . $room->id ?>">

								</span>
							</dt>
							<dd class="room_selection_details" id="room_selection_details_<?php echo $room->id ?>"
								style="<?php echo $room->isReservedForThisReservation ? '' : 'display: none;' ?>">
								<select
									name="jform[ignore]"
									data-roomid="<?php echo $room->id ?>"
									class="<?php echo SR_UI_GRID_COL_6 ?> tariff_selection" <?php echo $room->isReservedForThisReservation ? '' : 'disabled' ?>
									<?php echo $room->isReservedForThisReservation ? '' : 'required' ?>
									>
									<option value=""><?php echo JText::_('SR_AMEND_RESERVATION_CHOOSE_TARIFF') ?></option>
									<?php
										foreach ( $roomType->availableTariffs as $tariffKey => $tariffInfo ) :
											$selected_tariff = '';
											if (isset($currentRoomIndex['tariff_id']) && $tariffKey == $currentRoomIndex['tariff_id']) :
												//$selected_tariff = 'selected';
											endif;
										?>
										<option data-adjoininglayer="<?php echo $tariffInfo['tariffAdjoiningLayer'] ?>"
											<?php echo $selected_tariff ?>
											value="<?php echo $tariffKey ?>"
											>
											<?php echo empty( $tariffInfo['tariffTitle'] ) ? JText::_( 'SR_STANDARD_TARIFF' ) : $tariffInfo['tariffTitle'] ?>
										</option>
									<?php endforeach ?>
								</select>
								<input type="text"
									   name="jform[room_types][<?php echo $roomType->id ?>][<?php echo $arrayHolder ?>][<?php echo $room->id ?>][guest_fullname]"
									   class="<?php echo SR_UI_GRID_COL_6 ?> guest_fullname" placeholder="<?php echo JText::_( 'SR_GUEST_NAME' ) ?>"
									   value="<?php echo $currentRoomIndex['guest_fullname'] ?>"
										<?php echo $room->isReservedForThisReservation ? '' : 'disabled' ?>
									/>
								<select
									data-roomtypeid="<?php echo $roomType->id ?>"
									data-tariffid="<?php echo isset($currentRoomIndex['tariff_id']) ? $currentRoomIndex['tariff_id'] : ''?>"
									data-adjoininglayer=""
									data-roomid="<?php echo $room->id ?>"
									data-max="<?php echo $roomType->occupancy_max ?>"
									name="jform[room_types][<?php echo $roomType->id ?>][<?php echo $arrayHolder ?>][<?php echo $room->id ?>][adults_number]"
									required
									data-identity="<?php echo $identity ?>"
									class="<?php echo SR_UI_GRID_COL_6 ?> adults_number occupancy_max_constraint occupancy_max_constraint_<?php echo $room->id ?>_<?php echo $arrayHolder ?>_<?php echo $roomType->id ?> occupancy_adult_<?php echo $roomType->id . '_' . $arrayHolder . '_' . $room->id ?> trigger_tariff_calculating"
									<?php echo $room->isReservedForThisReservation ? '' : 'disabled' ?>
									>
									<?php echo $htmlAdultSelection ?>
								</select>
								<?php if ( $roomType->params['show_child_option'] == 1 && $roomType->occupancy_child > 0 ) : ?>
									<select
										data-roomtypeid="<?php echo $roomType->id ?>"
										data-tariffid="<?php echo isset($currentRoomIndex['tariff_id']) ? $currentRoomIndex['tariff_id'] : ''?>"
										data-adjoininglayer=""
										data-roomid="<?php echo $room->id ?>"
										data-max="<?php echo $roomType->occupancy_max ?>"
										data-identity="<?php echo $identity ?>"
										name="jform[room_types][<?php echo $roomType->id ?>][<?php echo $arrayHolder ?>][<?php echo $room->id ?>][children_number]"
										class="<?php echo SR_UI_GRID_COL_6 ?> children_number occupancy_max_constraint occupancy_max_constraint_<?php echo $room->id ?>_<?php echo $arrayHolder ?>_<?php echo $roomType->id ?> reservation-form-child-quantity trigger_tariff_calculating occupancy_child_<?php echo $roomType->id . '_' . $arrayHolder . '_' . $room->id ?>"
										<?php echo $room->isReservedForThisReservation ? '' : 'disabled' ?>
										>
										<?php echo $htmlChildSelection ?>
									</select>
								<?php endif ?>

								<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
									<div
										class="<?php echo SR_UI_GRID_COL_6 ?> <?php echo SR_UI_GRID_OFFSET_6 ?> child-age-details <?php echo(empty($htmlChildrenAges) ? 'nodisplay' : '') ?>">
										<p><?php echo JText::_('SR_AGE_OF_CHILD_AT_CHECKOUT') ?></p>
										<ul class="unstyled list-unstyled"><?php echo $htmlChildrenAges ?></ul>
									</div>
								</div>

								<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
									<ul class="unstyled list-unstyled <?php echo SR_UI_GRID_COL_12 ?>">
										<?php
										foreach ( $roomType->extras as $extra ) :
											$extraInputCommonName = 'jform[room_types][' . $roomType->id . ']['.$arrayHolder.'][' . $room->id . '][extras][' . $extra->id . ']';
											$checked              = '';
											$disabledCheckbox     = '';
											$disabledSelect       = 'disabled="disabled"';
											$alreadySelected      = false;
											$canBeEnabled		  = true;
											if ( isset( $currentRoomIndex['extras'] ) ) :
												$alreadySelected = array_key_exists( $extra->id, (array) $currentRoomIndex['extras'] );
											endif;

											if ( $extra->mandatory == 1 || $alreadySelected ) :
												$checked = 'checked="checked"';
											endif;

											if ( $extra->mandatory == 1 ) :
												$disabledCheckbox = ''; // don't force mandatory for admin
												$canBeEnabled = false;
												//$disabledSelect   = ''; // don't force mandatory for admin
											endif;

											if ( $alreadySelected) :
												$disabledSelect = '';
											endif;
											?>
											<li class="extras_row_roomtypeform">
												<input <?php echo $checked ?> <?php echo $disabledCheckbox ?>
													type="checkbox" class="<?php echo $canBeEnabled ? '' : 'no_enable'  ?>"
													data-target="extra_<?php echo $arrayHolder ?>_<?php echo $room->id ?>_<?php echo $extra->id ?>"/>
												<?php if ( $extra->mandatory == 1 ) : ?>
													<input type="hidden"
													       name="<?php echo $extraInputCommonName ?>[quantity]"
													       value="1" <?php echo $disabledCheckbox ?>
													       class="<?php echo $canBeEnabled ? '' : 'no_enable'  ?>"
													       disabled
													/>
												<?php endif ?>

												<select
													class="<?php echo SR_UI_GRID_COL_2 ?> extra_<?php echo $arrayHolder ?>_<?php echo $room->id ?>_<?php echo $extra->id ?>"
													name="<?php echo $extraInputCommonName ?>[quantity]"
													<?php echo $disabledSelect ?>
												>
													<?php
													for ( $quantitySelection = 1; $quantitySelection <= $extra->max_quantity; $quantitySelection ++ ) :
														$checked = '';
														if ( isset( $currentRoomIndex['extras'][ $extra->id ]['quantity'] ) ) :
															$checked = ( $currentRoomIndex['extras'][ $extra->id ]['quantity'] == $quantitySelection ) ? 'selected' : '';
														endif;
														?>
														<option <?php echo $checked ?>
															value="<?php echo $quantitySelection ?>"><?php echo $quantitySelection ?></option>
														<?php
													endfor;
													?>
												</select>
												<span>
													<?php echo $extra->name ?>
													<a href="javascript:void(0)"
													   class="toggle_extra_details"
													   data-target="extra_details_<?php echo $arrayHolder ?>_<?php echo $room->id ?>_<?php echo $extra->id ?>">
														<?php echo JText::_( 'SR_EXTRA_MORE_DETAILS' ) ?>
													</a>
												</span>
												<span class="extra_details"
												      id="extra_details_<?php echo $arrayHolder ?>_<?php echo $room->id ?>_<?php echo $extra->id ?>"
												      style="display: none">
													<?php if ( $extra->charge_type == 3 || $extra->charge_type == 5 || $extra->charge_type == 6 ) : ?>
														<span>
														<?php echo JText::_( 'SR_EXTRA_PRICE_ADULT' ) . ': ' . $extra->currencyAdult->format() . ' (' . JText::_( SRExtra::$chargeTypes[ $extra->charge_type ] ) . ')' ?>
													</span>
														<span>
														<?php echo JText::_( 'SR_EXTRA_PRICE_CHILD' ) . ': ' . $extra->currencyChild->format() . ' (' . JText::_( SRExtra::$chargeTypes[ $extra->charge_type ] ) . ')' ?>
													</span>
													<?php else: ?>
														<span>
														<?php echo JText::_( 'SR_EXTRA_PRICE' ) . ': ' . $extra->currency->format() . ' (' . JText::_( SRExtra::$chargeTypes[ $extra->charge_type ] ) . ')' ?>
													</span>
													<?php endif; ?>

													<span>
														<?php echo $extra->description ?>
													</span>
												</span>
											</li>
											<?php
										endforeach;
										?>
									</ul>
								</div>

							</dd>
						</dl>
				</div>
				<?php
				endif;
			endfor;
		endif; ?>
	<?php endforeach; ?>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-bottom">
		<div class="<?php echo SR_UI_GRID_COL_8 ?>">
		</div>
		<div class="<?php echo SR_UI_GRID_COL_4 ?>">
			<div class="inner">
				<div class="btn-group">
					<button data-step="room" type="submit" class="btn btn-success">
						<i class="fa fa-arrow-right"></i> <?php echo JText::_( 'SR_NEXT' ) ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="jform[next_step]" value="guestinfo"/>
	<input type="hidden" name="jform[raid]" value="<?php echo $displayData['raid'] ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>