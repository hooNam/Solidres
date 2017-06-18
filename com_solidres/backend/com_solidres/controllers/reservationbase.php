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

/**
 * Reservation base controller class. This class is used in backend and front end.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresControllerReservationBase extends JControllerForm
{
	protected $reservationData = array();

	protected $selectedRoomTypes = array();

	protected $reservationAssetId = array();

	protected $bookingConditionsArticleId = 0;

	protected $privacyPolicyArticleId = 0;

	protected $solidresConfig;

	protected $solidresPaymentPlugins;

	public function __construct($config = array())
	{
		$this->view_item = 'reservation';
		$this->view_list = 'reservations';
		parent::__construct($config);

		$this->app = JFactory::getApplication();
		$this->context = 'com_solidres.reservation.process';
		$this->solidresConfig = JComponentHelper::getParams('com_solidres');
		$this->reservationData['checkin'] = $this->app->getUserState($this->context . '.checkin');
		$this->reservationData['checkout'] = $this->app->getUserState($this->context . '.checkout');
		$this->solidresPaymentPlugins = SolidresHelper::getPaymentPluginOptions(true);

		if ($this->app->isAdmin())
		{
			$lang = JFactory::getLanguage();
			$lang->load('com_solidres', JPATH_SITE . '/components/com_solidres');
		}

		// Load payment plugins language
		$lang = JFactory::getLanguage();
		foreach ($this->solidresPaymentPlugins as $paymentPlugin)
		{
			$paymentPluginId = $paymentPlugin->element;					
			$lang->load('plg_solidrespayment_' . $paymentPluginId, JPATH_PLUGINS . '/solidrespayment/' . $paymentPluginId);
		}
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name The model name. Optional.
	 * @param	string	$prefix The class prefix. Optional.
	 * @param	array	$config Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'Reservation', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array $data An array of input data.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowAdd($data = array())
	{
		$user		= JFactory::getUser();
		$allow		= null;

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		}
		else
		{
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param	array $data An array of input data.
	 * @param	string $key The name of the key for the primary key.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user		= JFactory::getUser();
		return parent::allowEdit($data, $key);
	}

	public function getAvailableRooms()
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables', 'SolidresTable');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$reservationId = $this->input->get('id', 0, 'uint');
		$assetId = $this->input->get('assetid', 0, 'uint');
		$assetModel = JModelLegacy::getInstance('ReservationAsset', 'SolidresModel', array('ignore_request' => true));
		$currencyTable = JTable::getInstance('Currency', 'SolidresTable');
		$asset = $assetModel->getItem($assetId);
		$currencyTable->load($asset->currency_id);
		$solidresReservation = SRFactory::get('solidres.reservation.reservation');
		$solidresRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$checkin = $this->input->get('checkin', '', 'string');
		$checkout = $this->input->get('checkout', '', 'string');
		$state = $this->input->get('state', 0, 'uint');
		$paymentStatus = $this->input->get('payment_status', 0, 'uint');
		$customerJoomlaUserId = $this->input->get('customer_id', 0, 'uint');
		$hubDashboard = $this->input->get('hub_dashboard', 0, 'int');
		$stayLength = (int) SRUtilities::calculateDateDiff($checkin, $checkout);
		$params = JComponentHelper::getParams('com_solidres');
		$enableAdjoiningTariffs = $params->get('enable_adjoining_tariffs', 1);
		$childMaxAge = $params->get('child_max_age_limit', 17);

		if ($asset->booking_type == 1)
		{
			$stayLength ++;
		}
		$showTaxIncl = $this->solidresConfig->get('show_price_with_tax', 0);
		$currentReservationData = NULL;

		$this->app->setUserState($this->context . '.id', $reservationId);
		$this->app->setUserState($this->context . '.checkin', $checkin);
		$this->app->setUserState($this->context . '.checkout', $checkout);
		$this->app->setUserState($this->context . '.state', $state);
		$this->app->setUserState($this->context . '.payment_status', $paymentStatus);
		$this->app->setUserState($this->context . '.hub_dashboard', $hubDashboard);
		$this->app->setUserState($this->context . '.customer_joomla_user_id', $customerJoomlaUserId);
		//$this->app->setUserState($this->context . '.room', array('raid' => $assetId));

		if (!empty($assetId))
		{
			// Get the current reservation data if available
			if ($reservationId > 0)
			{
				$modelReservation = JModelLegacy::getInstance('Reservation', 'SolidresModel', array('ignore_request' => true));
				$currentReservationData = $modelReservation->getItem($reservationId);

				// We need to rebuild the data structure a little bit to make it easier for array looping here
				// The original data structure for "reserved_room_details" array is numeric based (from 0, 1,...)
				// But we need the key of this array to be room's id
				$currentReservationData->reserved_room_details_cloned = array();
				if (is_array($currentReservationData->reserved_room_details))
				{
					$currentReservationData->reserved_room_details_cloned = $currentReservationData->reserved_room_details;
					$currentReservationData->reserved_room_details = array();
					foreach ($currentReservationData->reserved_room_details_cloned as $reserved_room_detail_cloned)
					{
						$currentReservationData->reserved_room_details[$reserved_room_detail_cloned->room_id] = (array) clone $reserved_room_detail_cloned;
						// If guest also booked extra items for this room, we have to include it as well
						if (isset($reserved_room_detail_cloned->extras))
						{
							unset($currentReservationData->reserved_room_details[$reserved_room_detail_cloned->room_id]['extras']);
							foreach ($reserved_room_detail_cloned->extras as $key => $reservedRoomExtra)
							{
								if ($reservedRoomExtra->room_id == $reserved_room_detail_cloned->room_id)
								{
									$currentReservationData->reserved_room_details[$reserved_room_detail_cloned->room_id]['extras'][$reservedRoomExtra->extra_id]['quantity'] = $reservedRoomExtra->extra_quantity;
								}
							}
						}
					}
					unset($currentReservationData->reserved_room_details_cloned);
				}
			}

			// Get the default currency
			$this->reservationData['currency_id'] = $currencyTable->id;
			$this->reservationData['currency_code'] = $currencyTable->currency_code;

			$this->app->setUserState($this->context.'.currency_id', $currencyTable->id);
			$this->app->setUserState($this->context.'.currency_code', $currencyTable->currency_code);
			$this->app->setUserState($this->context.'.deposit_required', $asset->deposit_required);
			$this->app->setUserState($this->context.'.deposit_is_percentage', $asset->deposit_is_percentage);
			$this->app->setUserState($this->context.'.deposit_amount', $asset->deposit_amount);
			$this->app->setUserState($this->context.'.deposit_by_stay_length', $asset->deposit_by_stay_length);
			$this->app->setUserState($this->context.'.deposit_include_extra_cost', $asset->deposit_include_extra_cost);
			$this->app->setUserState($this->context.'.tax_id', $asset->tax_id);
			$this->app->setUserState($this->context.'.booking_type', $asset->booking_type);
			$this->app->setUserState($this->context.'.asset_params', $asset->params);
			$this->app->setUserState($this->context.'.origin', JText::_('SR_RESERVATION_ORIGIN_DIRECT'));

			$model = JModelLegacy::getInstance('RoomTypes', 'SolidresModel', array('ignore_request' => true));
			$modelRoomType = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));
			$model->setState('filter.reservation_asset_id', $assetId);
			$model->setState('filter.state', 1);
			$roomTypeArray = $model->getItems();
			foreach ($roomTypeArray as $roomTypeItem)
			{
				$roomTypes[] = $modelRoomType->getItem($roomTypeItem->id);
			}
			
			// Query all available tariffs for this room type
			$modelTariffs = JModelLegacy::getInstance('Tariffs', 'SolidresModel', array('ignore_request' => true));
			$modelTariff = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));
			$dbo = JFactory::getDbo();
			$query = $dbo->getQuery(true);

			// Get imposed taxes
			$imposedTaxTypes = array();
			if (!empty($asset->tax_id))
			{
				$taxModel = JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
				$imposedTaxTypes[] = $taxModel->getItem($asset->tax_id);
			}

			JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
			$solidresCurrency = new SRCurrency(0, $asset->currency_id);

			if (!empty($roomTypes))
			{
				foreach ($roomTypes as $roomType)
				{
					$query->clear();
					$query->select('id, label');
					$query->from($dbo->quoteName('#__sr_rooms'))->where('room_type_id = '.$dbo->quote($roomType->id));
					$rooms = $dbo->setQuery($query)->loadObjectList();

					if (!SRPlugin::isEnabled('complexTariff'))
					{
						$modelTariffs->setState('filter.date_constraint', NULL);
						$modelTariffs->setState('filter.room_type_id', $roomType->id);
						$modelTariffs->setState('filter.customer_group_id', NULL);
						$modelTariffs->setState('filter.default_tariff', 1);
						$modelTariffs->setState('filter.state', 1);
						$standardTariff = $modelTariffs->getItems();
						if (isset($standardTariff[0]->id))
						{
							$roomType->tariffs[] = $modelTariff->getItem($standardTariff[0]->id);
						}
					}
					else
					{
						$modelTariffs->setState('filter.room_type_id', $roomType->id);
						$modelTariffs->setState('filter.customer_group_id', -1);
						$modelTariffs->setState('filter.default_tariff', false);
						$modelTariffs->setState('filter.state', 1);

						// Only load complex tariffs that matched the checkin->checkout range.
						// Check in and check out must always use format "Y-m-d"
						if (!empty($checkin) && !empty($checkout))
						{
							$modelTariffs->setState('filter.valid_from', date('Y-m-d', strtotime($checkin)));
							$modelTariffs->setState('filter.valid_to', date('Y-m-d', strtotime($checkout)));
							$modelTariffs->setState('filter.stay_length', $stayLength);
						}

						$complexTariffs = $modelTariffs->getItems();
						foreach ($complexTariffs as $complexTariff)
						{
							// If limit checkin field is set, we have to make sure that it is matched
							if (!empty($complexTariff->limit_checkin))
							{
								if (!empty($checkin) && !empty($checkout))
								{
									$limitCheckinArray = json_decode($complexTariff->limit_checkin, true);
									$checkinDate = new DateTime($checkin);
									$dayInfo = getdate($checkinDate->format('U'));

									// If the current check in date does not match the allowed check in dates, we ignore this tariff
									if (!in_array($dayInfo['wday'], $limitCheckinArray))
									{
										continue;
									}
								}
							}
							$roomType->tariffs[] = $modelTariff->getItem($complexTariff->id);
						}
					}

					if (!empty($checkin) && !empty($checkout))
					{
						$srRoomType = SRFactory::get('solidres.roomtype.roomtype');
						$app = JFactory::getApplication();
						$context = 'com_solidres.reservation.process';
						$coupon  = $app->getUserState($context.'.coupon');
						$customerGroupId = NULL;
						// Hard code the number of selected adult
						$adult = 1;
						$child = 0;
						//$roomTypeObj = $roomtypeModel->getItem($roomTypeId);

						// Check for number of available rooms first, if no rooms found, we should skip this room type
						$listAvailableRoom = $srRoomType->getListAvailableRoom($roomType->id, $checkin, $checkout, $asset->booking_type);
						$roomType->totalAvailableRoom = is_array($listAvailableRoom) ? count($listAvailableRoom) : 0 ;

						// Check for limit booking, if all rooms are locked, we can remove this room type without checking further
						// This is for performance purpose
						/*if ($roomType->totalAvailableRoom == 0)
						{
							unset($roomType);
							continue;
						}*/

						//$item->totalAvailableRoom += $roomType->totalAvailableRoom;

						// Build the config values
						$tariffConfig = array(
							'booking_type' => $asset->booking_type,
							'adjoining_tariffs_mode' => $params->get('adjoining_tariffs_mode', 0),
							'child_room_cost_calc' => $params->get('child_room_cost_calc', 1)
						);
						if (isset($roomType->params['enable_single_supplement'])
						    &&
						    $roomType->params['enable_single_supplement'] == 1)
						{
							$tariffConfig['enable_single_supplement'] = true;
							$tariffConfig['single_supplement_value'] = $roomType->params['single_supplement_value'];
							$tariffConfig['single_supplement_is_percent'] = $roomType->params['single_supplement_is_percent'];
						}
						else
						{
							$tariffConfig['enable_single_supplement'] = false;
						}

						// Get discount
						$discounts = array();
						$isDiscountPreTax = $params->get('discount_pre_tax', 0);
						if (SRPlugin::isEnabled('discount'))
						{
							$discountModel = JModelLegacy::getInstance('Discounts', 'SolidresModel', array('ignore_request' => true));
							$discountModel->setState('filter.reservation_asset_id', $assetId);
							$discountModel->setState('filter.valid_from', $checkin);
							$discountModel->setState('filter.valid_to', $checkout);
							$discountModel->setState('filter.state', 1);
							$discounts = $discountModel->getItems();
						}

						// Holds all available tariffs (filtered) that takes checkin/checkout into calculation to be showed in front end
						$availableTariffs = array();
						$roomType->availableTariffs = array();
						if (SRPlugin::isEnabled('complexTariff'))
						{
							if (!empty($roomType->tariffs))
							{
								foreach ($roomType->tariffs as $filteredComplexTariff)
								{
									$availableTariffs[] = $srRoomType->getPrice($roomType->id, $customerGroupId, $imposedTaxTypes, false, true, $checkin, $checkout, $solidresCurrency, $coupon, $adult, $child, array(), $stayLength, $filteredComplexTariff->id, $discounts, $isDiscountPreTax, $tariffConfig);
								}
							}
							/*else
							{*/
							if ($enableAdjoiningTariffs)
							{
								$isApplicableAdjoiningTariffs = SRUtilities::isApplicableForAdjoiningTariffs($roomType->id, $checkin, $checkout);

								$tariffAdjoiningLayer = 0;
								$isApplicableAdjoiningTariffs2 = array();
								while (count($isApplicableAdjoiningTariffs) == 2)
								{
									$isApplicableAdjoiningTariffs2 = array_merge($isApplicableAdjoiningTariffs, $isApplicableAdjoiningTariffs2);
									$tariffConfig['adjoining_layer'] = $tariffAdjoiningLayer;
									$availableTariffs[] = $srRoomType->getPrice($roomType->id, $customerGroupId, $imposedTaxTypes, false, true, $checkin, $checkout, $solidresCurrency, $coupon, $adult, $child, array(), $stayLength, NULL, $discounts, $isDiscountPreTax, $tariffConfig);
									$isApplicableAdjoiningTariffs = SRUtilities::isApplicableForAdjoiningTariffs($roomType->id, $checkin, $checkout, $isApplicableAdjoiningTariffs2);
									if (empty($isApplicableAdjoiningTariffs))
									{
										break;
									}
									$tariffAdjoiningLayer ++;
								}
							}
							/*}*/
						}
						else
						{
							$availableTariffs[] = $srRoomType->getPrice($roomType->id, $customerGroupId, $imposedTaxTypes, true, false, $checkin, $checkout, $solidresCurrency, $coupon, 0, 0, array(), $stayLength, $roomType->tariffs[0]->id, $discounts, $isDiscountPreTax, $tariffConfig);
						}

						foreach ($availableTariffs as $availableTariff)
						{
							$id = $availableTariff['id'];
							if ($showTaxIncl)
							{
								$roomType->availableTariffs[$id]['val'] = $availableTariff['total_price_tax_incl_formatted'];
							}
							else
							{
								$roomType->availableTariffs[$id]['val'] = $availableTariff['total_price_tax_excl_formatted'];
							}
							$roomType->availableTariffs[$id]['tariffTaxIncl'] = $availableTariff['total_price_tax_incl_formatted'];
							$roomType->availableTariffs[$id]['tariffTaxExcl'] = $availableTariff['total_price_tax_excl_formatted'];
							$roomType->availableTariffs[$id]['tariffIsAppliedCoupon'] = $availableTariff['is_applied_coupon'];
							$roomType->availableTariffs[$id]['tariffType'] = $availableTariff['type']; // Per room per night or Per person per night
							$roomType->availableTariffs[$id]['tariffBreakDown'] = $availableTariff['tariff_break_down'];
							// Useful for looping with Hub
							$roomType->availableTariffs[$id]['tariffTitle'] = $availableTariff['title'];
							$roomType->availableTariffs[$id]['tariffDescription'] = $availableTariff['description'];
							// For adjoining cases
							$roomType->availableTariffs[$id]['tariffAdjoiningLayer'] = $availableTariff['adjoining_layer'];
						}

						/*if ($roomType->occupancy_max > 0)
						{
							$item->totalOccupancyMax += $roomType->occupancy_max * $roomType->totalAvailableRoom;
						}
						else
						{
							$item->totalOccupancyMax += ($roomType->occupancy_adult + $roomType->occupancy_child) * $roomType->totalAvailableRoom;
						}*/

						$tariffsForFilter = array();
						if (is_array($roomType->availableTariffs))
						{
							foreach ($roomType->availableTariffs as $tariffId => $tariffInfo)
							{
								if (is_null($tariffInfo['val']))
								{
									continue;
								}
								$tariffsForFilter[$tariffId] = $tariffInfo['val']->getValue();
							}
						}

						// Remove tariffs that has the same price
						$tariffsForFilter = array_unique($tariffsForFilter);
						foreach ($roomType->availableTariffs as $tariffId => $tariffInfo)
						{
							$uniqueTariffIds = array_keys($tariffsForFilter);
							if (!in_array($tariffId, $uniqueTariffIds))
							{
								unset($roomType->availableTariffs[$tariffId]);
							}
						}


						// Take overlapping mode into consideration
						$overlappingTariffsMode = $params->get('overlapping_tariffs_mode', 0);
						$tariffsForFilterOverlapping = $tariffsForFilter;
						asort($tariffsForFilterOverlapping); // from lowest to highest
						$tariffsForFilterOverlappingKeys = array_keys($tariffsForFilterOverlapping);
						$lowestTariffId = NULL;
						$highestTariffId = NULL;
						switch ($overlappingTariffsMode)
						{
							case 0:
								break;
							case 1: // Lowest
								$lowestTariffId = current($tariffsForFilterOverlappingKeys);
								SRUtilities::removeArrayElementsExcept($roomType->availableTariffs, $lowestTariffId);
								break;
							case 2: // Highest
								$highestTariffId = end($tariffsForFilterOverlappingKeys);
								SRUtilities::removeArrayElementsExcept($roomType->availableTariffs, $highestTariffId);
								break;
						}


						/*if (SRPlugin::isEnabled('hub'))
						{
							$origin = $this->getState('origin');
							if ($origin == 'hubsearch')
							{
								if (empty($tariffsForFilter))
								{
									unset($roomType);
									continue;
								}
							}

							if (!empty($tariffsForFilter))
							{
								$filterConditions = array(
									'tariffs_for_filter' => $tariffsForFilter
								);

								$filteringResults = $dispatcher->trigger('onReservationAssetFilterRoomType', array(
									'com_solidres.reservationasset',
									$item,
									$this->getState(),
									$filterConditions
								));

								$qualifiedTariffs = array();
								$roomTypeMatched = true;

								foreach ($filteringResults as $result)
								{
									if (!is_array($result))
									{
										continue;
									}

									$qualifiedTariffs = $result;

									if (count($qualifiedTariffs) <= 0) // No qualified tariffs
									{
										$roomTypeMatched = false;
										continue;
									}
								}

								if (!$roomTypeMatched)
								{
									unset($roomType);
									continue;
								}
								else // This room type is matched but we have to check if all tariffs are matched or just some matched?
								{
									if (!empty($qualifiedTariffs) && count($qualifiedTariffs) != count($roomType->availableTariffs))
									{
										foreach ($roomType->availableTariffs as $k => $v)
										{
											if (!isset($qualifiedTariffs[$k]))
											{
												unset($roomType->availableTariffs[$k]);
											}
										}
									}
								}
							}
						}*/ // End logic of Hub's filtering
					}

					if (!empty($rooms))
					{
						// Get list reserved rooms
						$reservedRoomsForThisReservation = $solidresRoomType->getListReservedRoom($roomType->id, $reservationId);
						$reservedRoomIds = array();
						foreach ($reservedRoomsForThisReservation as $roomObj)
						{
							$reservedRoomIds[] = $roomObj->id;
						}

						foreach ($rooms as $room)
						{
							$isAvailable = $solidresReservation->isRoomAvailable($room->id, $checkin, $checkout, $asset->booking_type);
							$room->isAvailable = true;
							$room->isReservedForThisReservation = false;
							if (!$isAvailable)
							{
								$room->isAvailable = false;
							}

							if (in_array($room->id, $reservedRoomIds))
							{
								$room->isReservedForThisReservation = true;
							}
						}
					}
					$roomType->rooms = $rooms;

					// Query for room type's extra items
					$modelExtras = JModelLegacy::getInstance('Extras', 'SolidresModel', array('ignore_request' => true));
					$modelExtras->setState('filter.room_type_id', $roomType->id);
					$modelExtras->setState('filter.state', 1);
					$modelExtras->setState('filter.show_price_with_tax', $showTaxIncl);
					$roomType->extras = $modelExtras->getItems();
				}
			}
		}

		// This is a workaroud for this Joomla's bug  https://github.com/joomla/joomla-cms/issues/3451
		// When it is fixed, update this logic
		if (file_exists(JPATH_BASE . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/layouts/com_solidres/asset/rooms.php' ))
		{
			$layout = new JLayoutFile('asset.rooms' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''));
		}
		else
		{
			$layout = new JLayoutFile('asset.rooms' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''), JPATH_SITE . '/components/com_solidres/layouts');
		}

		$displayData = array(
			'room_types' => $roomTypes,
			'raid' => $assetId,
			'current_reservation_data' => $currentReservationData,
			'childMaxAge' => $childMaxAge,
			'currency' => $solidresCurrency
		);

		echo $layout->render($displayData);
		$this->app->close();
	}

	/**
	 * Decide which will be the next screen
	 *
	 * @return void
	 */
	public function progress()
	{
		$next	= $this->input->get('next_step', '', 'string');
		if (!empty($next))
		{
			switch($next)
			{
				case 'guestinfo':
					$this->getHtmlGuestInfo();
					break;
				case 'confirmation':
					$this->getHtmlConfirmation();
					break;
				default:
					$response = array('status' => 1, 'message' => '', 'next' => '');
					echo json_encode($response);
					die(1);
					break;
			}
		}
	}

	/**
	 * Return html to display guest info form in one-page reservation, data is retrieved from user session
	 *
	 * @return string $html The HTML output
	 */
	public function getHtmlGuestInfo()
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables', 'SolidresTable');
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models', 'SolidresModel');
		$this->countries = SolidresHelper::getCountryOptions();
		$reservationId = $this->app->getUserState($this->context . '.id');
		$hubDashboard = $this->app->getUserState($this->context . '.hub_dashboard', 0);
		$currentReservationData = NULL;
		$guestFields = array(
			'customer_firstname',
			'customer_middlename',
			'customer_lastname',
			'customer_vat_number',
			'customer_company',
			'customer_phonenumber',
			'customer_mobilephone',
			'customer_address1',
			'customer_address2',
			'customer_city',
			'customer_zipcode',
			'customer_country_id',
			'customer_geo_state_id',
		);
		$showTaxIncl = $this->solidresConfig->get('show_price_with_tax', 0);
		$customerTitles = array(
			'' => '',
			JText::_("SR_CUSTOMER_TITLE_MR") => JText::_("SR_CUSTOMER_TITLE_MR"),
			JText::_("SR_CUSTOMER_TITLE_MRS") => JText::_("SR_CUSTOMER_TITLE_MRS"),
			JText::_("SR_CUSTOMER_TITLE_MS") => JText::_("SR_CUSTOMER_TITLE_MS")
		);
		$isNew = true;

		if ($reservationId > 0) // we are editing an existing reservation
		{
			$isNew = false;
			$guestFields[] = 'customer_title';
			$guestFields[] = 'customer_email';
			$guestFields[] = 'customer_vat_number';
			$guestFields[] = 'payment_method_id';
			$modelReservation = JModelLegacy::getInstance('Reservation', 'SolidresModel', array('ignore_request' => true));
			$currentReservationData = $modelReservation->getItem($reservationId);
			foreach ($guestFields as $guestField)
			{
				if (!isset($this->reservationDetails->guest[$guestField]))
				{
					$this->reservationDetails->guest[$guestField] = $currentReservationData->{$guestField};
				}
			}

			if (SRPlugin::isEnabled('customfield'))
			{
				//Sync via custom field but still keep detail from reservation
				if ($details = SRCustomFieldHelper::findValues(array('context' => 'com_solidres.customer.' . $reservationId)))
				{
					foreach ($details as $detail)
					{
						$value   = trim($detail->value);
						$storage = json_decode($detail->storage);
						if (is_object($storage))
						{
							$name                                   = $storage->field_name;
							$this->reservationDetails->guest[$name] = $value;
						}
					}
				}
			}

			$raId = $currentReservationData->reservation_asset_id;

			$dbo = JFactory::getDbo();
			$query = $dbo->getQuery(true);
			$query->clear();
			$query->select('extra_id, extra_quantity')->from($dbo->quoteName('#__sr_reservation_extra_xref'))
				->where('reservation_id = ' . (int) $reservationId);

			$currentReservedExtras = $dbo->setQuery($query)->loadObjectList();

			foreach ($currentReservedExtras as $reservedExtra)
			{
				$this->reservationDetails->guest['extras'][$reservedExtra->extra_id]['quantity'] = $reservedExtra->extra_quantity;
			}
		}
		else // making brand new reservation
		{
			$this->reservationDetails = $this->app->getUserState($this->context);
			$raId = $this->reservationDetails->room['raid'];
		}

		$modelExtras = JModelLegacy::getInstance('Extras', 'SolidresModel', array('ignore_request' => true));
		$modelExtras->setState('filter.reservation_asset_id', $raId);
		$modelExtras->setState('filter.charge_type', array(1,2,3)); // Only get extra item with charge type = Per Booking
		$modelExtras->setState('filter.state', 1);
		$modelExtras->setState('filter.show_price_with_tax', $showTaxIncl);
		$extras = $modelExtras->getItems();
		
		// Try to get the customer information if he/she logged in
		$selectedCountryId = 0;
		if (SRPlugin::isEnabled('user'))
		{
			JTable::addIncludePath(SRPlugin::getAdminPath('user') . '/tables');
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$user          = JFactory::getUser();
			$customerJoomlaUserId = 0;
			if ($this->app->isSite() && !$hubDashboard)
			{
				$customerTable->load( array( 'user_id' => $user->get( 'id' ) ) );
			}
			else if ($this->app->isAdmin() || $hubDashboard)
			{
				$customerJoomlaUserId = $this->app->getUserState($this->context . '.customer_joomla_user_id', 0);
				if ($customerJoomlaUserId > 0)
				{
					$customerTable->load( array( 'user_id' => $customerJoomlaUserId ) );
				}
			}

			$isCustomerChanged = false;
			if (!$isNew && $customerJoomlaUserId > 0)
			{
				if ($currentReservationData->customer_id != $customerTable->id)
				{
					$isCustomerChanged = true;
				}
			}

			if ( ! empty( $customerTable->id ) ) 
			{
				foreach ( $guestFields as $guestField ) 
				{
					if ( ! isset( $this->reservationDetails->guest[ $guestField ] ) || $isCustomerChanged )
					{
						$customerTablePropertyName = substr( $guestField, 9 );
						if (isset($customerTable->{$customerTablePropertyName}))
						{
							$this->reservationDetails->guest[ $guestField ] = $customerTable->{$customerTablePropertyName};
						}
					}
				}
				if (($this->app->isAdmin() || $hubDashboard) && $customerJoomlaUserId > 0)
				{
					$customerJoomlaUser = JFactory::getUser($customerJoomlaUserId);
					if (! isset( $this->reservationDetails->guest["customer_email"] ) || $isCustomerChanged)
					{
						$this->reservationDetails->guest["customer_email"] = $customerJoomlaUser->get( 'email' );
					}
				}
				else // For front end normal guest booking
				{
					if (! isset( $this->reservationDetails->guest["customer_email"] ))
					{
						$this->reservationDetails->guest["customer_email"] = $user->get( 'email' );
					}
				}
			}

			if ( isset( $this->reservationDetails->guest["customer_country_id"] )
			     &&
			     $this->reservationDetails->guest["customer_country_id"] > 0 )
			{
				$selectedCountryId = $this->reservationDetails->guest["customer_country_id"];
			}
			else
			{
				if ($customerTable->country_id > 0)
				{
					$selectedCountryId = $customerTable->country_id;
				}
				else
				{
					$selectedCountryId = plgUserSolidres::autoLoadCountry();
				}

				$this->reservationDetails->guest['customer_country_id'] = $selectedCountryId;
			}
		}

		$options = array();
		$options[] = JHTML::_('select.option', NULL, JText::_('SR_SELECT') );
		$this->geoStates = $selectedCountryId > 0 ? SolidresHelper::getGeoStateOptions($selectedCountryId) : $options;

		if (!isset($this->reservationDetails->asset_params))
		{
			$this->reservationDetails->asset_params = $this->app->getUserState($this->context.'.asset_params');
		}

		// Rebind some missing variable
		$this->reservationDetails->hub_dashboard = $hubDashboard;

		$displayData = array(
			'customerTitles'         => $customerTitles,
			'reservationDetails'     => $this->reservationDetails,
			'extras'                 => $extras,
			'assetId'                => $raId,
			'countries'              => $this->countries,
			'geoStates'              => $this->geoStates,
			'solidresPaymentPlugins' => $this->solidresPaymentPlugins,
			'isNew'                  => $isNew
		);

		$app = JFactory::getApplication();

		if (SRPlugin::isEnabled('customfield'))
		{
			$layout = SRLayoutHelper::getInstance();
			$layout->addIncludePath(
				array(
					SRPlugin::getSitePath('customfield') . '/layouts',
					JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/layouts/com_solidres'
				)
			);
			echo $layout->render('asset.guestform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''), $displayData, false);
		}
		else
		{
			// This is a workaround for this Joomla's bug  https://github.com/joomla/joomla-cms/issues/3451
			// When it is fixed, update this logic
			if (file_exists(JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/layouts/com_solidres/asset/guestform.php'))
			{
				$layout = new JLayoutFile('asset.guestform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''));
			}
			else
			{
				$layout = new JLayoutFile('asset.guestform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''), JPATH_SITE . '/components/com_solidres/layouts');
			}
			echo $layout->render($displayData);
		}

		$this->app->close();
	}

	/**
	 * Return html to display confirmation form in one-page reservation, data is retrieved from user session
	 *
	 * @return string $html The HTML output
	 */
	public function getHtmlConfirmation()
	{
		JLoader::register('ContentHelperRoute', JPATH_SITE.'/components/com_content/helpers/route.php');
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$this->reservationDetails = $this->app->getUserState($this->context);

		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$model = $this->getModel();
		$modelName = $model->getName();
		$checkin = $this->reservationDetails->checkin;
		$checkout = $this->reservationDetails->checkout;
		$raId = $this->reservationDetails->room['raid'];
		$bookingType = $this->reservationDetails->booking_type;

		$currency = new SRCurrency(0, $this->reservationDetails->currency_id);
		$stayLength = SRUtilities::calculateDateDiff($checkin, $checkout);
		$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
		$jsDateFormat = SRUtilities::convertDateFormatPattern($dateFormat);
		$tzoffset = JFactory::getConfig()->get('offset');
		$timezone = new DateTimeZone($tzoffset);
		$isDiscountPreTax = $solidresConfig->get('discount_pre_tax', 0);

		$model->setState($modelName.'.roomTypes', $this->reservationDetails->room['room_types']);
		$model->setState($modelName.'.checkin',  $checkin);
		$model->setState($modelName.'.checkout', $checkout);
		$model->setState($modelName.'.reservationAssetId',  $raId);
		$model->setState($modelName.'.booking_type', $bookingType);
		$model->setState($modelName.'.is_editing', isset($this->reservationDetails->id) && $this->reservationDetails->id > 0 ? 1 : 0 );

		$task = 'reservation'. ($this->app->isSite() ? '' : 'base') .'.save';

		// Query for room types data and their associated costs
		$roomTypes = $model->getRoomType();

		// Calculate extra item with charge type per daily rate
		JPluginHelper::importPlugin('solidres');
		$dispatcher	= JEventDispatcher::getInstance();
		$dispatcher->trigger('onSolidresBeforeDisplayConfirmationForm', array(&$roomTypes, &$this->reservationDetails));
		$totalRoomTypeExtraCostTaxIncl = $this->reservationDetails->room['total_extra_price_tax_incl_per_room'] + $this->reservationDetails->guest['total_extra_price_tax_incl_per_booking'];
		$totalRoomTypeExtraCostTaxExcl = $this->reservationDetails->room['total_extra_price_tax_excl_per_room'] + $this->reservationDetails->guest['total_extra_price_tax_excl_per_booking'];

		// Rebind the session data because it has been changed in the previous line
		$this->reservationDetails = $this->app->getUserState($this->context);
		$cost = $this->app->getUserState($this->context.'.cost');

		// This is a workaroud for this Joomla's bug  https://github.com/joomla/joomla-cms/issues/3451
		// When it is fixed, update this logic
		if (file_exists(JPATH_BASE . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/layouts/com_solidres/asset/confirmationform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : '') . '.php' ))
		{
			$layout = new JLayoutFile('asset.confirmationform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''));
		}
		else
		{
			$layout = new JLayoutFile('asset.confirmationform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''), JPATH_SITE . '/components/com_solidres/layouts');
		}

		$displayData = array(
			'roomTypes' => $roomTypes,
			'reservationDetails' => $this->reservationDetails,
			'totalRoomTypeExtraCostTaxIncl' => $totalRoomTypeExtraCostTaxIncl,
			'totalRoomTypeExtraCostTaxExcl' => $totalRoomTypeExtraCostTaxExcl,
			'task' => $task,
			'assetId' => $raId,
			'cost' => $cost,
			'stay_length' => $stayLength,
			'currency' => $currency,
			'context' => $this->context,
			'dateFormat' => $dateFormat, // default format d-m-y
			'jsDateFormat' => $jsDateFormat,
			'timezone' => $timezone,
			'isDiscountPreTax' => $isDiscountPreTax,
			'booking_type' => $this->reservationDetails->booking_type
		);

		echo $layout->render($displayData);
		$this->app->close();
	}

	/**
	 * Build a correct data structure for the saving
	 *
	 * @since 0.3.0
	 */
	protected function prepareSavingData()
	{
		if (is_array($this->app->getUserState($this->context.'.room')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.room'));
		}

		if (is_array($this->app->getUserState($this->context.'.guest')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.guest'));
		}

		if (is_array($this->app->getUserState($this->context.'.cost')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.cost'));
		}

		if (is_array($this->app->getUserState($this->context.'.discount')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.discount'));
		}

		if (is_array($this->app->getUserState($this->context.'.coupon')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.coupon'));
		}

		if (is_array($this->app->getUserState($this->context.'.deposit')))
		{
			$this->reservationData = array_merge($this->reservationData, $this->app->getUserState($this->context.'.deposit'));
		}

		$this->reservationData['total_extra_price'] = $this->reservationData['total_extra_price_per_room'] + $this->reservationData['total_extra_price_per_booking'];
		$this->reservationData['total_extra_price_tax_incl'] = $this->reservationData['total_extra_price_tax_incl_per_room'] + $this->reservationData['total_extra_price_tax_incl_per_booking'];
		$this->reservationData['total_extra_price_tax_excl'] = $this->reservationData['total_extra_price_tax_excl_per_room'] + $this->reservationData['total_extra_price_tax_excl_per_booking'];

		$raTable = JTable::getInstance('ReservationAsset', 'SolidresTable');
		$raTable->load($this->reservationData['raid']);
		$this->reservationData['reservation_asset_name'] = $raTable->name;
		$this->reservationData['reservation_asset_id'] = $this->reservationData['raid'];
		$this->reservationData['currency_id'] = $this->app->getUserState($this->context.'.currency_id');
		$this->reservationData['currency_code'] = $this->app->getUserState($this->context.'.currency_code');
		$this->reservationData['booking_type'] = $this->app->getUserState($this->context.'.booking_type');
		$this->reservationData['origin'] = $this->app->getUserState($this->context.'.origin');

		if ($this->app->isSite())
		{
			$this->reservationData['state'] = $this->solidresConfig->get('default_reservation_state', 0);
		}
		else // In the backend, let admin choose which reservation state is needed
		{
			$this->reservationData['state'] = $this->app->getUserState($this->context.'.state');
			$this->reservationData['payment_status'] = $this->app->getUserState($this->context.'.payment_status');
		}

		$this->reservationData['discount_pre_tax'] = $this->solidresConfig->get('discount_pre_tax', 0);
		$this->reservationData['id'] = $this->app->getUserState($this->context.'.id');
		$this->reservationData['customer_ip'] = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		JPluginHelper::importPlugin('solidrespayment');
		$model = $this->getModel();
		$resTable = JTable::getInstance('Reservation', 'SolidresTable');
		$reservationDetails = $this->app->getUserState($this->context);
		$isGuestMakingReservation = $this->app->isSite() && !$reservationDetails->hub_dashboard;
		$sendOutgoingEmails = true;
		if (!$isGuestMakingReservation)
		{
			// Get override cost
			$amendData = $this->input->post->get('jform', array(), 'array');

			if (!isset($amendData['sendoutgoingemails']))
			{
				$sendOutgoingEmails = false;
			}

			// Get current cost
			$roomTypePricesMapping = $this->app->getUserState($this->context . '.room_type_prices_mapping');
			$cost = $this->app->getUserState($this->context . '.cost');
			$reservationRooms = $this->app->getUserState($this->context . '.room');
			$reservationGuest = $this->app->getUserState($this->context . '.guest');
			$deposit = $this->app->getUserState($this->context . '.deposit');

			$totalPriceTaxExcl = 0;
			$totalImposedTaxAmount = 0;
			$totalRoomTypeExtraCostTaxExcl = 0;
			$totalRoomTypeExtraCostTaxIncl = 0;
			$totalPerBookingExtraCostTaxIncl = 0;
			$totalPerBookingExtraCostTaxExcl = 0;
			foreach ($amendData['override_cost']['room_types'] as $roomTypeId => $tariffs)
			{
				foreach ($tariffs as $tariffId => $rooms)
				{
					foreach ($rooms as $roomId => $room)
					{
						$totalPriceTaxExcl += $room['total_price_tax_excl'];

						$totalImposedTaxAmount += $room['tax_amount'];
						$roomTotalPriceTaxIncl = $room['total_price_tax_excl'] + $room['tax_amount'];

						$roomTypePricesMapping[$roomTypeId][$tariffId][$roomId]['total_price'] = $roomTotalPriceTaxIncl;
						$roomTypePricesMapping[$roomTypeId][$tariffId][$roomId]['total_price_tax_incl'] = $roomTotalPriceTaxIncl;
						$roomTypePricesMapping[$roomTypeId][$tariffId][$roomId]['total_price_tax_excl'] = $room['total_price_tax_excl'];

						// Override extra cost
						if (is_array($room['extras']))
						{
							foreach ($room['extras'] as $overriddenExtraKey => $overriddenExtraCost)
							{
								$reservationRooms['room_types'][$roomTypeId][$tariffId][$roomId]['extras'][$overriddenExtraKey]['total_extra_cost_tax_incl'] = $overriddenExtraCost['price'] + $overriddenExtraCost['tax_amount'];
								$reservationRooms['room_types'][$roomTypeId][$tariffId][$roomId]['extras'][$overriddenExtraKey]['total_extra_cost_tax_excl'] = $overriddenExtraCost['price'];
								$totalRoomTypeExtraCostTaxIncl += $reservationRooms['room_types'][$roomTypeId][$tariffId][$roomId]['extras'][$overriddenExtraKey]['total_extra_cost_tax_incl'];
								$totalRoomTypeExtraCostTaxExcl += $reservationRooms['room_types'][$roomTypeId][$tariffId][$roomId]['extras'][$overriddenExtraKey]['total_extra_cost_tax_excl'];

							}
						}

					}
				}
			}

			// Override extra per booking if available
			if (is_array($amendData['override_cost']['extras_per_booking']))
			{
				foreach ($amendData['override_cost']['extras_per_booking'] as $overriddenExtraBookingKey => $overriddenExtraBookingCost)
				{
					$reservationGuest['extras'][$overriddenExtraBookingKey]['total_extra_cost_tax_incl'] = $overriddenExtraBookingCost['price'] + $overriddenExtraBookingCost['tax_amount'];
					$reservationGuest['extras'][$overriddenExtraBookingKey]['total_extra_cost_tax_excl'] = $overriddenExtraBookingCost['price'];
					$totalPerBookingExtraCostTaxIncl += $reservationGuest['extras'][$overriddenExtraBookingKey]['total_extra_cost_tax_incl'];
					$totalPerBookingExtraCostTaxExcl += $reservationGuest['extras'][$overriddenExtraBookingKey]['total_extra_cost_tax_excl'];
				}
			}

			$totalPriceTaxIncl = $totalPriceTaxExcl + $totalImposedTaxAmount;
			$reservationRooms['total_extra_price_per_room'] = $totalRoomTypeExtraCostTaxIncl;
			$reservationRooms['total_extra_price_tax_incl_per_room'] = $totalRoomTypeExtraCostTaxIncl;
			$reservationRooms['total_extra_price_tax_excl_per_room'] = $totalRoomTypeExtraCostTaxExcl;

			$reservationGuest['total_extra_price_per_booking'] = $totalPerBookingExtraCostTaxIncl;
			$reservationGuest['total_extra_price_tax_incl_per_booking'] = $totalPerBookingExtraCostTaxIncl;
			$reservationGuest['total_extra_price_tax_excl_per_booking'] = $totalPerBookingExtraCostTaxExcl;

			$cost['total_price'] = $totalPriceTaxIncl;
			$cost['total_price_tax_incl'] = $totalPriceTaxIncl;
			$cost['total_price_tax_excl'] = $totalPriceTaxExcl;
			$cost['tax_amount'] = $totalImposedTaxAmount;
			$deposit['deposit_amount'] = $amendData['override_cost']['deposit_amount'];

			// Update existing prices with overridden prices
			$this->app->setUserState($this->context . '.cost', $cost);
			$this->app->setUserState($this->context . '.room_type_prices_mapping', $roomTypePricesMapping);
			$this->app->setUserState($this->context . '.room', $reservationRooms);
			$this->app->setUserState($this->context . '.guest', $reservationGuest);
			$this->app->setUserState($this->context . '.deposit', $deposit);
		}

		// Get the data from user state and build a correct array that is ready to be stored
		$this->prepareSavingData();
		$isNew = true;
		if (isset($this->reservationData['id']) && $this->reservationData['id'] > 0)
		{
			$isNew = false;
		}

		if(!$model->save($this->reservationData))
		{
		}
		else
		{
			// Prepare some data for final layout
			$savedReservationId = $model->getState($model->getName().'.id');
			$resTable->load($savedReservationId);
			$this->app->setUserState($this->context.'.savedReservationId', $savedReservationId);
			$this->app->setUserState($this->context.'.code', $resTable->code);
			$this->app->setUserState($this->context.'.payment_method_id', $resTable->payment_method_id);
			$this->app->setUserState($this->context.'.customer_firstname', $this->reservationData['customer_firstname']);
			$this->app->setUserState($this->context.'.customeremail', $this->reservationData['customer_email']);
			$this->app->setUserState($this->context.'.reservation_asset_name', $this->reservationData['reservation_asset_name']);

			$processOnlinePayment = isset($reservationDetails->guest['processonlinepayment']) ?
				$reservationDetails->guest['processonlinepayment'] : 0;
			if ($resTable->payment_method_id != 'paylater' && $resTable->payment_method_id != 'bankwire' && $processOnlinePayment)
			{
				// Work fine with payment gateway that does not require redirection, for example stripe, authorize.net
				JPluginHelper::importPlugin('solidrespayment', $resTable->payment_method_id);
				$responses = $this->app->triggerEvent('OnSolidresPaymentNew', array( $resTable ));
			}

			if ($sendOutgoingEmails)
			{
				$this->sendEmail();
			}

			$this->app->setUserState($this->context, null);
			$msg = $isNew ? JText::_('SR_YOUR_RESERVATION_HAS_BEEN_ADDED') : JText::_('SR_YOUR_RESERVATION_HAS_BEEN_AMENDED');

			// Redirect to the list screen.
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit&id=' . $savedReservationId
					. $this->getRedirectToListAppend(), false
				), $msg
			);
		}
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 *
	 * @since   12.2
	 */
	public function add()
	{
		$this->input->set('layout', 'edit2');

		parent::add();
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   12.2
	 */
	public function amend($key = null, $urlVar = null)
	{
		$this->input->set('layout', 'edit2');

		parent::edit($key, $urlVar);
	}

	/**
	 * Send email when reservation is completed
	 *
	 * @since  0.1.0
	 *
	 * @param int $reservationId The reservation to get the reservation info for emails (Optional)
	 *
	 * @return boolean True if email sending completed successfully. False otherwise
	 */
	protected function sendEmail($reservationId = null)
	{
		$solidresReservation = SRFactory::get('solidres.reservation.reservation');
		return $solidresReservation->sendEmail($reservationId);
	}

	public function deletePaymentData()
	{
		JSession::checkToken('GET') or jexit(JText::_('JINVALID_TOKEN'));
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

		$id = $this->input->getUInt('id', 0);
		$reservationTable = JTable::getInstance('Reservation', 'SolidresTable');
		$reservationTable->load($id);
		$isSite = JFactory::getApplication()->isSite();

		if ($isSite)
		{
			$joomlaUserId = JFactory::getUser()->get('id');
			$isAssetPartner = SRUtilities::isAssetPartner($joomlaUserId, $reservationTable->reservation_asset_id);

			if (!$isAssetPartner)
			{
				$msg = JText::_( 'SR_RESERVATION_PAYMENT_DATA_REMOVED_FAILED_NO_PERMISSION' );
				$this->setRedirect( JRoute::_( 'index.php', false ), $msg );
				return;
			}
		}

		// Empty the payment data
		$reservationTable->payment_data = '';
		$result = $reservationTable->store();

		$msg = JText::_( 'SR_RESERVATION_PAYMENT_DATA_REMOVED_SUCCESSFULLY' );
		if (!$result)
		{
			$msg = JText::_( 'SR_RESERVATION_PAYMENT_DATA_REMOVED_FAILED' );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_solidres&view=reservation' . ($isSite ? 'form' : '') . '&layout=edit&id=' . $id, false ), $msg );
	}
}