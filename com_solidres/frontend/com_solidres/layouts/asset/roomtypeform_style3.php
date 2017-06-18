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

$roomTypeId = $displayData['roomTypeId'];
$roomType = $displayData['roomType'];
for ($i = 0; $i < $displayData['quantity']; $i++) :
	$currentRoomIndex = NULL;
	if (isset($displayData['reservationDetails']->room['room_types'][$roomTypeId][$displayData['tariffId']][$i])) :
		$currentRoomIndex = $displayData['reservationDetails']->room['room_types'][$roomTypeId][$displayData['tariffId']][$i];
	endif;
	$identity = $roomType->id . '_' . $displayData['tariffId'] . '_' . $i;

	// Html for adult selection
	$htmlAdultSelection = '';
	if (!isset($displayData['roomType']->params['show_adult_option'])) :
		$displayData['roomType']->params['show_adult_option'] = 1;
	endif;
	if ($displayData['roomType']->params['show_adult_option'] == 1) :
		//$htmlAdultSelection .= '<option value="">' . JText::_('SR_ADULT') . '</option>';
		for ($j = 1; $j <= $displayData['roomType']->occupancy_adult; $j++) :
			$disabled = '';
			$selected = '';
			if (isset($currentRoomIndex['adults_number'])) :
				$selected = $currentRoomIndex['adults_number'] == $j ? 'selected' : '';
			elseif (isset($displayData['reservationDetails']->room_opt[$i + 1])) :
				$selected = $displayData['reservationDetails']->room_opt[$i + 1]['adults'] == $j ? 'selected' : '';
			else :
				if (!empty($displayData['tariff']->p_min)) :
					if ($j == $displayData['tariff']->p_min) :
						$selected = 'selected';
					endif;
				else :
					if ($j == 1) :
						$selected = 'selected';
					endif;
				endif;
			endif;

			if (!empty($displayData['tariff']->p_min) && $j < $displayData['tariff']->p_min ) :
				$disabled = 'disabled';
			endif;

			if (!empty($displayData['tariff']->p_max) && $j > $displayData['tariff']->p_max ) :
				$disabled = 'disabled';
			endif;
			$htmlAdultSelection .= '<option ' . $disabled . ' ' . $selected . ' value="' . $j . '">' . JText::plural('SR_SELECT_ADULT_QUANTITY', $j) . '</option>';
		endfor;
	endif;

	// Html for children selection
	$htmlChildSelection = '';
	$htmlChildrenAges = '';
	if (!isset($displayData['roomType']->params['show_child_option'])) :
		$displayData['roomType']->params['show_child_option'] = 1;
	endif;

	// Only show child option if it is enabled and the child quantity > 0
	if ($displayData['roomType']->params['show_child_option'] == 1 && $displayData['roomType']->occupancy_child > 0) :
		$htmlChildSelection .= '<option value="">' . JText::_('SR_CHILD') . '</option>';

		for ($j = 1; $j <= $displayData['roomType']->occupancy_child; $j++) :
			$selected2 = '';
			if (isset($currentRoomIndex['children_number'])) :
				$selected2 = $currentRoomIndex['children_number'] == $j ? 'selected' : '';
			elseif (isset($displayData['reservationDetails']->room_opt[$i + 1])) :
				$selected2 = $displayData['reservationDetails']->room_opt[$i + 1]['children'] == $j ? 'selected' : '';
			endif;
			$htmlChildSelection .= '
				<option ' . $selected2 . ' value="' . $j . '">' . JText::plural('SR_SELECT_CHILD_QUANTITY', $j) . '</option>
			';
		endfor;

		// Html for children ages, show if there was previous session data or from room_opt variables
		if (isset($currentRoomIndex['children_ages']) || isset($displayData['reservationDetails']->room_opt[$i + 1])) :

			if (isset($currentRoomIndex['children_ages'])) :
				$childDropBoxCount = $currentRoomIndex['children_ages'];
			elseif (isset($displayData['reservationDetails']->room_opt[$i + 1])) :
				$childDropBoxCount = $displayData['reservationDetails']->room_opt[$i + 1]['children'];
			endif;

			for ($j = 0; $j < $childDropBoxCount; $j++) :
				$htmlChildrenAges .= '
					<li>
						' . JText::_('SR_CHILD') . ' ' . ($j + 1) . '
						<select name="jform[room_types][' . $roomTypeId . '][' . $displayData['tariffId'] .'][' . $i . '][children_ages][]"
							data-raid="' . $displayData['assetId'] . '"
							data-roomtypeid="' . $roomTypeId . '"
							data-tariffid="' . $displayData['tariffId'] . '"
							data-roomindex="' . $i . '"
							class="' . SR_UI_GRID_COL_6 . ' child_age_' . $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i . '_' . $j . ' trigger_tariff_calculating"
							required
						>';
				$htmlChildrenAges .= '<option value=""></option>';
				for ($age = 1; $age <= $displayData['childMaxAge']; $age ++) :
					$selectedAge = '';
					if (isset($currentRoomIndex['children_ages']) && $age == $currentRoomIndex['children_ages'][$j]) :
						$selectedAge = 'selected';
					endif;
					$htmlChildrenAges .= '<option '.$selectedAge.' value="'.$age.'">'.JText::plural('SR_CHILD_AGE_SELECTION', $age).'</option>';
				endfor;

				$htmlChildrenAges .= '
						</select>
					</li>';
			endfor;
		endif;
	endif;

	// Smoking
	$htmlSmokingOption = '';
	if (!isset($displayData['roomType']->params['show_smoking_option'])) :
		$displayData['roomType']->params['show_smoking_option'] = 1;
	endif;

	if ($displayData['roomType']->params['show_smoking_option'] == 1) :
		$selectedNonSmoking = '';
		$selectedSmoking = '';
		if (isset($currentRoomIndex['preferences']['smoking'])) :
			if ($currentRoomIndex['preferences']['smoking'] == 0) :
				$selectedNonSmoking = 'selected';
			else :
				$selectedSmoking = 'selected';
			endif;
		endif;
		$htmlSmokingOption = '
			<select class="form-control" name="jform[room_types][' . $roomTypeId . '][' . $displayData['tariffId'] . '][' . $i . '][preferences][smoking]">
				<option value="">' . JText::_('SR_SMOKING') . '</option>
				<option ' . $selectedNonSmoking . ' value="0">' . JText::_('SR_NON_SMOKING_ROOM') . '</option>
				<option ' . $selectedSmoking . ' value="1">' . JText::_('SR_SMOKING_ROOM') . '</option>
			</select>
		';
	endif;

	if (!isset($displayData['roomType']->params['show_guest_name_field'])) :
		$displayData['roomType']->params['show_guest_name_field'] = 1;
	endif;

	if (!isset($displayData['roomType']->params['guest_name_optional'])) :
		$displayData['roomType']->params['guest_name_optional'] = 0;
	endif;
	?>

	<div class="room-form">
		<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
			<div class="<?php echo SR_UI_GRID_COL_12 ?>">
				<div class="<?php echo SR_UI_GRID_CONTAINER ?> room_index_form_heading">
					<div class="inner">
						<h4><?php echo JText::_($displayData['roomType']->is_private ? 'SR_ROOM' : 'SR_BED') . ' ' . ($i + 1) ?>: <span
								class="tariff_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>">0</span>

							<a href="javascript:void(0)"
							   class="toggle_breakdown"
							   data-target="<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>">
								<?php echo JText::_('SR_VIEW_TARIFF_BREAKDOWN') ?>
							</a>
						</h4>
						<span style="display: none" class="breakdown" id="breakdown_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>">

						</span>
					</div>
				</div>
				<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
					<div class="<?php echo SR_UI_GRID_COL_12 ?>">
						<div class="<?php echo SR_UI_GRID_CONTAINER ?> occupancy-selection">
							<div class="inner">
								<?php if ($displayData['roomType']->params['show_adult_option'] == 1) : ?>
									<select
										data-raid="<?php echo $displayData['assetId'] ?>"
										data-roomtypeid="<?php echo $roomTypeId ?>"
										data-tariffid="<?php echo $displayData['tariffId'] ?>"
										data-adjoininglayer="<?php echo $displayData['adjoiningLayer'] ?>"
										data-roomindex="<?php echo $i ?>"
										data-max="<?php echo isset($displayData['tariff']->p_max) && $displayData['tariff']->p_max > 0 ? $displayData['tariff']->p_max : $displayData['roomType']->occupancy_max ?>"
										data-min="<?php echo isset($displayData['tariff']->p_min) && $displayData['tariff']->p_min > 0 ? $displayData['tariff']->p_min : 0 ?>"
										name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][adults_number]"
										required
										data-identity="<?php echo $identity ?>"
										class="<?php echo SR_UI_GRID_COL_6 ?> adults_number occupancy_max_constraint occupancy_max_constraint_<?php echo $i ?>_<?php echo $displayData['tariffId'] ?>_<?php echo $roomTypeId ?> occupancy_adult_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?> trigger_tariff_calculating">
										<?php echo $htmlAdultSelection ?>
									</select>
								<?php else : ?>
									<input type="hidden"
										   data-raid="<?php echo $displayData['assetId'] ?>"
										   data-roomtypeid="<?php echo $roomTypeId ?>"
										   data-tariffid="<?php echo $displayData['tariffId'] ?>"
										   data-adjoininglayer="<?php echo $displayData['adjoiningLayer'] ?>"
										   data-roomindex="<?php echo $i ?>"
										   data-max="<?php echo isset($displayData['tariff']->p_max) && $displayData['tariff']->p_max > 0 ? $displayData['tariff']->p_max : $displayData['roomType']->occupancy_max ?>"
										   data-min="<?php echo isset($displayData['tariff']->p_min) && $displayData['tariff']->p_min > 0 ? $displayData['tariff']->p_min : 0 ?>"
										   name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][adults_number]"
										   class="<?php echo SR_UI_GRID_COL_6 ?> adults_number occupancy_max_constraint occupancy_max_constraint_<?php echo $i ?>_<?php echo $displayData['tariffId'] ?>_<?php echo $roomTypeId ?> occupancy_adult_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?> trigger_tariff_calculating"
										   value="1"
										   data-identity="<?php echo $identity ?>"
										/>
								<?php endif ?>
								<?php if ($displayData['roomType']->params['show_child_option'] == 1 && $displayData['roomType']->occupancy_child > 0) : ?>
									<select
										data-raid="<?php echo $displayData['assetId'] ?>"
										data-roomtypeid="<?php echo $roomTypeId ?>"
										data-roomindex="<?php echo $i ?>"
										data-max="<?php echo isset($displayData['tariff']->p_max) && $displayData['tariff']->p_max > 0 ? $displayData['tariff']->p_max : $displayData['roomType']->occupancy_max ?>"
										data-min="<?php echo isset($displayData['tariff']->p_min) && $displayData['tariff']->p_min > 0 ? $displayData['tariff']->p_min : 0 ?>"
										data-tariffid="<?php echo $displayData['tariffId'] ?>"
										data-adjoininglayer="<?php echo $displayData['adjoiningLayer'] ?>"
										data-identity="<?php echo $identity ?>"
										name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][children_number]"
										class="<?php echo SR_UI_GRID_COL_6 ?> children_number occupancy_max_constraint occupancy_max_constraint_<?php echo $i ?>_<?php echo $displayData['tariffId'] ?>_<?php echo $roomTypeId ?> reservation-form-child-quantity trigger_tariff_calculating occupancy_child_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>">
										<?php echo $htmlChildSelection ?>
									</select>
								<?php endif ?>
								<div class="alert alert-warning" id="error_<?php echo $i ?>_<?php echo $displayData['tariffId'] ?>_<?php echo $roomTypeId ?>" style="display: none">
									<?php echo JText::sprintf('SR_ROOM_OCCUPANCY_CONSTRAINT_NOT_SATISFIED', $displayData['tariff']->p_min, $displayData['tariff']->p_max) ?>
								</div>
								<div
									class="child-age-details <?php echo(empty($htmlChildrenAges) ? 'nodisplay' : '') ?>">
									<p><?php echo JText::_('SR_AGE_OF_CHILD_AT_CHECKOUT') ?></p>
									<ul class="unstyled list-unstyled"><?php echo $htmlChildrenAges ?></ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
					<div class="<?php echo SR_UI_GRID_COL_12 ?>">
						<div class="inner">
							<?php if ($displayData['roomType']->params['show_guest_name_field'] == 1) : ?>
								<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
									<div class="<?php echo SR_UI_GRID_COL_12 ?>">
										<input name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][guest_fullname]"
											<?php echo $displayData['roomType']->params['guest_name_optional'] == 0 ? 'required' : '' ?>
											   type="text"
											   class="<?php echo 'bs3' == SR_UI ? 'form-control' : '' ?> <?php echo SR_UI_GRID_COL_12 ?>"
											   value="<?php echo(isset($currentRoomIndex['guest_fullname']) ? $currentRoomIndex['guest_fullname'] : '') ?>"
											   placeholder="<?php echo JText::_('SR_GUEST_NAME') ?>"/>
									</div>
								</div>
							<?php endif ?>

							<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
								<div class="<?php echo SR_UI_GRID_COL_12 ?>">
									<?php echo $htmlSmokingOption ?>
								</div>
							</div>

							<?php
							foreach ($displayData['extras'] as $extra) :
								$extraInputCommonName = 'jform[room_types][' . $roomTypeId . '][' . $displayData['tariffId'] . '][' . $i . '][extras][' . $extra->id . ']';
								$checked = '';
								$disabledCheckbox = '';
								$disabledSelect = 'disabled="disabled"';
								$alreadySelected = false;
								if (isset($currentRoomIndex['extras'])) :
									$alreadySelected = array_key_exists($extra->id, (array)$currentRoomIndex['extras']);
								endif;

								if ($extra->mandatory == 1 || $alreadySelected) :
									$checked = 'checked="checked"';
								endif;

								if ($extra->mandatory == 1) :
									$disabledCheckbox = 'disabled="disabled"';
									$disabledSelect = 'disabled="disabled"';
								endif;

								if ($alreadySelected && $extra->mandatory == 0) :
									$disabledSelect = '';
								endif;
								?>
								<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
									<div class="<?php echo SR_UI_GRID_COL_12 ?> extras_row_roomtypeform extras_row_roomtypeform_<?php echo $identity ?>">

										<input <?php echo $checked ?> <?php echo $disabledCheckbox ?> type="checkbox"
											data-target="extra_<?php echo $roomTypeId ?>_<?php echo $displayData['tariffId'] ?>_<?php echo $i ?>_<?php echo $extra->id ?>"
											data-extraid="<?php echo $extra->id ?>"
										/>
										<?php if ($extra->mandatory == 1) : ?>
											<input type="hidden" name="<?php echo $extraInputCommonName ?>[quantity]"
												   value="1"/>
										<?php endif ?>

										<select class="<?php echo SR_UI_GRID_COL_2 ?> extra_quantity extra_<?php echo $roomTypeId ?>_<?php echo $displayData['tariffId'] ?>_<?php echo $i ?>_<?php echo $extra->id ?> trigger_tariff_calculating"
										        data-raid="<?php echo $displayData['assetId'] ?>"
										        data-roomtypeid="<?php echo $roomTypeId ?>"
										        data-tariffid="<?php echo $displayData['tariffId'] ?>"
										        data-adjoininglayer="<?php echo $displayData['adjoiningLayer'] ?>"
										        data-roomindex="<?php echo $i ?>"
										        data-max="<?php echo isset($displayData['tariff']->p_max) && $displayData['tariff']->p_max > 0 ? $displayData['tariff']->p_max : $displayData['roomType']->occupancy_max ?>"
										        data-min="<?php echo isset($displayData['tariff']->p_min) && $displayData['tariff']->p_min > 0 ? $displayData['tariff']->p_min : 0 ?>"
										        data-identity="<?php echo $identity ?>"
										        name="<?php echo $extraInputCommonName ?>[quantity]"
											<?php echo $disabledSelect ?>>
											<?php
											for ($quantitySelection = 1; $quantitySelection <= $extra->max_quantity; $quantitySelection++) :
												$checked = '';
												if (isset($currentRoomIndex['extras'][$extra->id]['quantity'])) :
													$checked = ($currentRoomIndex['extras'][$extra->id]['quantity'] == $quantitySelection) ? 'selected' : '';
												endif;
												?>
												<option <?php echo $checked ?> value="<?php echo $quantitySelection ?>"><?php echo $quantitySelection ?></option>
												<?php
											endfor;
											?>
										</select>
										<span>
											<?php echo $extra->name ?>
											<a href="javascript:void(0)"
											   class="toggle_extra_details"
											   data-target="extra_details_<?php echo $displayData['tariffId'] ?>_<?php echo $i ?>_<?php echo $extra->id ?>">
												<?php echo JText::_('SR_EXTRA_MORE_DETAILS') ?>
											</a>
										</span>
										<span class="extra_details" id="extra_details_<?php echo $displayData['tariffId'] ?>_<?php echo $i ?>_<?php echo $extra->id ?>" style="display: none">
											<?php if ($extra->charge_type == 3 || $extra->charge_type == 5 || $extra->charge_type == 6) : ?>
												<span>
												<?php echo JText::_('SR_EXTRA_PRICE_ADULT') . ': ' . $extra->currencyAdult->format() .' (' . JText::_(SRExtra::$chargeTypes[$extra->charge_type]) .')' ?>
											</span>
												<span>
												<?php echo JText::_('SR_EXTRA_PRICE_CHILD') . ': ' . $extra->currencyChild->format() .' (' . JText::_(SRExtra::$chargeTypes[$extra->charge_type]) .')' ?>
											</span>
											<?php elseif ($extra->charge_type == 7) : ?>
												<span>
												<?php echo JText::sprintf('SR_EXTRA_PRICE_DAILY_RATE', $extra->name, ($extra->price * 100)) .  ' (' . JText::_(SRExtra::$chargeTypes[$extra->charge_type]) .')' ?>
											</span>
											<?php else : ?>
												<span>
												<?php echo JText::_('SR_EXTRA_PRICE') . ': ' . $extra->currency->format() .' (' . JText::_(SRExtra::$chargeTypes[$extra->charge_type]) .')' ?>
											</span>
											<?php endif; ?>

											<span>
												<?php echo $extra->description ?>
											</span>
										</span>
									</div>
								</div>
								<?php
							endforeach;
							?>

							<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
								<div class="<?php echo SR_UI_GRID_COL_12 ?>">
									<button data-step="room" type="submit" class="btn <?php echo SR_UI_GRID_COL_12 ?> btn-success btn-block">
										<i class="fa fa-arrow-right"></i>
										<?php echo JText::_('SR_NEXT') ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
endfor;