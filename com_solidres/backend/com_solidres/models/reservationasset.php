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
 * Reservation Asset model.
 *
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.1.0
 */
class SolidresModelReservationAsset extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = null;

	/**
	 * @var		string	The event to trigger after deleting the data.
	 * @since	1.6
	 */
	protected $event_after_delete = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_after_save = null;

	/**
	 * @var		string	The event to trigger after deleting the data.
	 * @since	1.6
	 */
	protected $event_before_delete = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_before_save = null;

	/**
	 * @var		string	The event to trigger after changing the published state of the data.
	 * @since	1.6
	 */
	protected $event_change_state = null;

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->event_after_delete 	= 'onReservationAssetAfterDelete';
		$this->event_after_save 	= 'onReservationAssetAfterSave';
		$this->event_before_delete 	= 'onReservationAssetBeforeDelete';
		$this->event_before_save 	= 'onReservationAssetBeforeSave';
		$this->event_change_state 	= 'onReservationAssetChangeState';
		$this->text_prefix 			= strtoupper($this->option);

		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
	}

	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('reservationasset.id', $pk);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();

		if (JFactory::getApplication()->isAdmin())
		{
			return $user->authorise('core.delete', 'com_solidres.reservationasset.'.(int) $record->id);
		}
		else
		{
			return SRUtilities::isAssetPartner($user->get('id'), $record->id);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		if (JFactory::getApplication()->isAdmin())
		{
			return $user->authorise('core.edit.state', 'com_solidres.reservationasset.'.(int) $record->id);
		}
		else
		{
			return SRUtilities::isAssetPartner($user->get('id'), $record->id);
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	string	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'ReservationAsset', $prefix = 'SolidresTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_solidres.reservationasset',
								'reservationasset',
                                array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
        {
			return false;
		}

		$id = (int) JFactory::getApplication()->input->getInt('id', $form->getValue('id', 0));

		if ($id > 0)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
			$menuTable = JTable::getInstance('Menu', 'MenusTable');

			if ($menuTable->load(array(
				'component_id' => JComponentHelper::getComponent('com_solidres')->id,
				'link'         => 'index.php?option=com_solidres&view=reservationasset&id=' . $id,
				'type'         => 'component',
				'client_id'    => 0,
			))
			)
			{
				$form->removeField('add_to_menu');
				$form->removeField('add_to_menutype');
				$form->removeField('menu_title');
				$form->removeField('menu_alias');
				$form->setValue('menu_id', null, $menuTable->id);
			}
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_solidres.edit.reservationasset.data', array());

		if (empty($data))
        {
			$data = $this->getItem();
		}

        // Get the dispatcher and load the users plugins.
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('solidres');

        // Trigger the data preparation event.
		$results = $dispatcher->trigger('onReservationAssetPrepareData', array('com_solidres.reservationasset', $data));

        // Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true))
        {
			$this->setError($dispatcher->getError());
		}

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'extension')
	{
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);
		JPluginHelper::importPlugin('solidres');
		JPluginHelper::importPlugin('solidrespayment');

		// Get the dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onReservationAssetPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception))
			{
				throw new Exception($error);
			}
		}
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	int	$pk The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * 
	 * @since	0.1.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('reservationasset.id');

		$item = parent::getItem($pk);
		$dispatcher	= JEventDispatcher::getInstance();

		if ($item->id)
		{
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata, 'JSON');
			$item->metadata = $registry->toArray();

			// Get the dispatcher and load the extension plugins.
			JPluginHelper::importPlugin('extension');
			JPluginHelper::importPlugin('solidres');
			JPluginHelper::importPlugin('solidrespayment');

			$solidresConfig = JComponentHelper::getParams('com_solidres');
			$enableAdjoiningTariffs = $solidresConfig->get('enable_adjoining_tariffs', 1);
			$adjoiningTariffShowDesc = $solidresConfig->get('adjoining_tariffs_show_desc', 0);

			$roomtypesModel = JModelLegacy::getInstance('RoomTypes', 'SolidresModel', array('ignore_request' => true));
			$roomtypeModel = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));
			$extrasModel = JModelLegacy::getInstance('Extras', 'SolidresModel', array('ignore_request' => true));
			$mediaListModel = JModelLegacy::getInstance('MediaList', 'SolidresModel', array('ignore_request' => true));
			$tariffsModel = JModelLegacy::getInstance('Tariffs', 'SolidresModel', array('ignore_request' => true));
			$tariffModel = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));

			// Get country name
			$countryTable = JTable::getInstance('Country', 'SolidresTable');
			$countryTable->load($item->country_id);
			$item->country_name = $countryTable->name;

			// Get state name
			$stateTable = JTable::getInstance('State', 'SolidresTable');
			$stateTable->load($item->geo_state_id);
			$item->geostate_name = $stateTable->name;
			$item->geostate_code_2 = $stateTable->code_2;
			
			// Get currency name
			$currencyTable = JTable::getInstance('Currency', 'SolidresTable');
			$currencyTable->load($item->currency_id);
			$item->currency_name = $currencyTable->currency_name;
			$item->currency_code = $currencyTable->currency_code;
			$item->currency_sign = $currencyTable->sign;

			$roomtypesModel->setState('filter.reservation_asset_id', $item->id);
			$roomtypesModel->setState('filter.state', '1');
			$item->roomTypes = $roomtypesModel->getItems();

			if (JFactory::getApplication()->isAdmin() || $this->getState('hub_dashboard', false))
			{
				$extrasModel->setState('filter.reservation_asset_id', $item->id);
				$item->extras = $extrasModel->getItems();
			}

			$item->partner_name = '';
			if (SRPlugin::isEnabled('user'))
			{
				JModelLegacy::addIncludePath(SRPlugin::getAdminPath('user') . '/models', 'SolidresModel');
				$customerModel      = JModelLegacy::getInstance('Customer', 'SolidresModel', array('ignore_request' => true));
				$partner            = $customerModel->getItem($item->partner_id);
				$item->partner_name = $partner->firstname . " " . $partner->middlename . " " . $partner->lastname;
			}

			$mediaListModel->setState('filter.reservation_asset_id', $item->id);
			$mediaListModel->setState('filter.room_type_id', NULL);
			$item->media = $mediaListModel->getItems();

			//  ** For front end tasks ** //
			$srRoomType = SRFactory::get('solidres.roomtype.roomtype');
			JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

			$checkin = $this->getState('checkin');
			$checkout = $this->getState('checkout');

			// Hard code the number of selected adult
			$adult = 1;
			$child = 0;

			// Total available rooms for a whole asset
			$item->totalAvailableRoom = 0;
			// Total max occupancy for a whole asset = total available room * occupancy max of each room
			$item->totalOccupancyMax = 0;
			$item->totalOccupancyAdult = 0;
			$item->totalOccupancyChildren = 0;

			// Get the current selected tariffs if available
			$tariffs = $this->getState('tariffs');
			$stayLength = (int) SRUtilities::calculateDateDiff($checkin, $checkout);
			if ($item->booking_type == 1)
			{
				$stayLength ++;
			}

			// Get imposed taxes
			$imposedTaxTypes = array();
			$item->taxes = array();
			if (!empty($item->tax_id))
			{
				$taxModel = JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
				$imposedTaxTypes[] = $taxModel->getItem($item->tax_id);
			}

			if (count($imposedTaxTypes) > 0)
			{
				$item->taxes = $imposedTaxTypes;
			}

			// Get customer information
			$user = JFactory::getUser();
			$customerGroupId = NULL; // Non-registered/Public/Non-loggedin customer

			if (SRPlugin::isEnabled('user') && $user->id > 0)
			{
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $user->id));
				$customerGroupId = $customerTable->customer_group_id;
			}

			JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
			$solidresCurrency = new SRCurrency(0, $item->currency_id);
			$showPriceWithTax = $this->getState('show_price_with_tax', 0);

			$roomsOccupancyOptions = $this->getState('room_opt', array());
			$item->roomsOccupancyOptionsAdults = 0;
			$item->roomsOccupancyOptionsChildren = 0;
			$item->roomsOccupancyOptionsCount = count($roomsOccupancyOptions);
			foreach ($roomsOccupancyOptions as $roomOccupancyOptions)
			{
				$item->roomsOccupancyOptionsAdults += $roomOccupancyOptions['adults'];
				$item->roomsOccupancyOptionsChildren += $roomOccupancyOptions['children'];
			}

			for ($i = 0, $n = count($item->roomTypes); $i < $n; $i++)
			{
				$roomTypeId = $item->roomTypes[$i]->id;
				$mediaListModel->setState('filter.reservation_asset_id', NULL);
				$mediaListModel->setState('filter.room_type_id', $roomTypeId);
				$item->roomTypes[$i]->media = $mediaListModel->getItems();

				// For each room type, we load all relevant tariffs for front end user selection
				// When complex tariff plugin is not enabled, load standard tariff
				$standardTariffs = NULL;
				$item->roomTypes[$i]->tariffs = array();
				if (!SRPlugin::isEnabled('complexTariff'))
				{
					$tariffsModel->setState('filter.date_constraint', NULL);
					$tariffsModel->setState('filter.room_type_id', $roomTypeId);
					$tariffsModel->setState('filter.customer_group_id', NULL);
					$tariffsModel->setState('filter.default_tariff', 1);
					$tariffsModel->setState('filter.state', 1);
					$standardTariff = $tariffsModel->getItems();
					$item->roomTypes[$i]->standardTariff = NULL;
					if (isset($standardTariff[0]->id))
					{
						$item->roomTypes[$i]->tariffs[] = $tariffModel->getItem($standardTariff[0]->id);
					}
				}
				else // When complex tariff plugin is enabled
				{
					$complexTariffs = NULL;
					$tariffsModel->setState('filter.room_type_id', $roomTypeId);
					$tariffsModel->setState('filter.customer_group_id', $customerGroupId);
					$tariffsModel->setState('filter.default_tariff', false);
					$tariffsModel->setState('filter.state', 1);

					// Only load complex tariffs that matched the checkin->checkout range.
					// Check in and check out must always use format "Y-m-d"
					if (!empty($checkin) && !empty($checkout))
					{
						$tariffsModel->setState('filter.valid_from', date('Y-m-d', strtotime($checkin)));
						$tariffsModel->setState('filter.valid_to', date('Y-m-d', strtotime($checkout)));
						$tariffsModel->setState('filter.stay_length', $stayLength);
					}

					$complexTariffs = $tariffsModel->getItems();
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

						// If this tariff does not match with number of people requirement, remove it
						if (!empty($roomsOccupancyOptions))
						{
							$isValidPeopleRange = true;
							$peopleRangeMatchCount = count($roomsOccupancyOptions);

							foreach ($roomsOccupancyOptions as $roomOccupancyOptions)
							{
								$totalPeopleRequested = $roomOccupancyOptions['adults'] + $roomOccupancyOptions['children'];

								if ($complexTariff->p_min > 0 && $complexTariff->p_max > 0)
								{
									$isValidPeopleRange = $totalPeopleRequested >= $complexTariff->p_min && $totalPeopleRequested <= $complexTariff->p_max;
								}
								elseif ( empty($complexTariff->p_min) && $complexTariff->p_max > 0)
								{
									$isValidPeopleRange = $totalPeopleRequested <= $complexTariff->p_max;
								}
								elseif ($complexTariff->p_min > 0 && empty($complexTariff->p_max))
								{
									$isValidPeopleRange = $totalPeopleRequested >= $complexTariff->p_min;
								}

								if (!$isValidPeopleRange)
								{
									$peopleRangeMatchCount--;
								}
							}

							if ($peopleRangeMatchCount == 0)
							{
								continue;
							}
						}

						// Check for valid interval
						if ($complexTariff->d_interval > 0 && $complexTariff->d_min > 0 && $complexTariff->d_max > 0)
						{
							$intervalThreshold = (int) floor($complexTariff->d_max / $complexTariff->d_interval);
							$intervalSteps = array();
							for ($intervalCount = 0; $intervalCount <= $intervalThreshold; $intervalCount++)
							{
								$intervalSteps[] = $intervalCount * $complexTariff->d_interval;
							}

							if ($item->booking_type == 1)
							{
								array_unshift($intervalSteps, 1);
							}

							if (!in_array($stayLength, $intervalSteps))
							{
								continue;
							}
						}

						$item->roomTypes[$i]->tariffs[] = $tariffModel->getItem($complexTariff->id);
					}
				}

				if (!empty($checkin) && !empty($checkout))
				{
					$app = JFactory::getApplication();
					$context = 'com_solidres.reservation.process';
					$coupon  = $app->getUserState($context.'.coupon');
					$roomTypeObj = $roomtypeModel->getItem($roomTypeId);

					// Check for number of available rooms first, if no rooms found, we should skip this room type
					$listAvailableRoom = $srRoomType->getListAvailableRoom($roomTypeId, $checkin, $checkout, $item->booking_type);
					$item->roomTypes[$i]->totalAvailableRoom = is_array($listAvailableRoom) ? count($listAvailableRoom) : 0 ;

					// Check for limit booking, if all rooms are locked, we can remove this room type without checking further
					// This is for performance purpose
					if ($item->roomTypes[$i]->totalAvailableRoom == 0)
					{
						unset($item->roomTypes[$i]);
						continue;
					}

					// Build the config values
					$tariffConfig = array(
						'booking_type' => $item->booking_type,
						'adjoining_tariffs_mode' => $solidresConfig->get('adjoining_tariffs_mode', 0),
						'child_room_cost_calc' => $solidresConfig->get('child_room_cost_calc', 1),
						'adjoining_tariffs_show_desc' => $adjoiningTariffShowDesc
					);
					if (isset($roomTypeObj->params['enable_single_supplement'])
					    &&
					    $roomTypeObj->params['enable_single_supplement'] == 1)
					{
						$tariffConfig['enable_single_supplement'] = true;
						$tariffConfig['single_supplement_value'] = $roomTypeObj->params['single_supplement_value'];
						$tariffConfig['single_supplement_is_percent'] = $roomTypeObj->params['single_supplement_is_percent'];
					}
					else
					{
						$tariffConfig['enable_single_supplement'] = false;
					}

					// Get discount
					$discounts = array();
					$isDiscountPreTax = $solidresConfig->get('discount_pre_tax', 0);
					if (SRPlugin::isEnabled('discount'))
					{
						JModelLegacy::addIncludePath(SRPlugin::getAdminPath('discount').'/models', 'SolidresModel');
						$discountModel = JModelLegacy::getInstance('Discounts', 'SolidresModel', array('ignore_request' => true));
						$discountModel->setState('filter.reservation_asset_id', $item->id);
						$discountModel->setState('filter.valid_from', $checkin);
						$discountModel->setState('filter.valid_to', $checkout);
						$discountModel->setState('filter.state', 1);
						$discounts = $discountModel->getItems();
					}

					// Holds all available tariffs (filtered) that takes checkin/checkout into calculation to be showed in front end
					$availableTariffs = array();
					$item->roomTypes[$i]->availableTariffs = array();
					if (SRPlugin::isEnabled('complexTariff'))
					{
						if (!empty($item->roomTypes[$i]->tariffs))
						{
							foreach ($item->roomTypes[$i]->tariffs as $filteredComplexTariff)
							{
								$availableTariffs[] = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, false, true, $checkin, $checkout, $solidresCurrency, $coupon, $adult, $child, array(), $stayLength, $filteredComplexTariff->id, $discounts, $isDiscountPreTax, $tariffConfig);
							}
						}
						/*else
						{*/
							if ($enableAdjoiningTariffs) // Does not work for package type
							{
								$isApplicableAdjoiningTariffs = SRUtilities::isApplicableForAdjoiningTariffs($roomTypeId, $checkin, $checkout);

								$tariffAdjoiningLayer = 0;
								$isApplicableAdjoiningTariffs2 = array();
								while (count($isApplicableAdjoiningTariffs) == 2)
								{
									$isApplicableAdjoiningTariffs2 = array_merge($isApplicableAdjoiningTariffs, $isApplicableAdjoiningTariffs2);
									$tariffConfig['adjoining_layer'] = $tariffAdjoiningLayer;
									$availableTariffs[] = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, false, true, $checkin, $checkout, $solidresCurrency, $coupon, $adult, $child, array(), $stayLength, NULL, $discounts, $isDiscountPreTax, $tariffConfig);
									$isApplicableAdjoiningTariffs = SRUtilities::isApplicableForAdjoiningTariffs($roomTypeId, $checkin, $checkout, $isApplicableAdjoiningTariffs2);
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
						$availableTariffs[] = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, true, false, $checkin, $checkout, $solidresCurrency, $coupon, 0, 0, array(), $stayLength, $item->roomTypes[$i]->tariffs[0]->id, $discounts, $isDiscountPreTax, $tariffConfig);
					}

					foreach ($availableTariffs as $availableTariff)
					{
						$id = $availableTariff['id'];
						if ($showPriceWithTax)
						{
							$item->roomTypes[$i]->availableTariffs[$id]['val'] = $availableTariff['total_price_tax_incl_discounted_formatted'];
							$item->roomTypes[$i]->availableTariffs[$id]['val_original'] = $availableTariff['total_price_tax_incl_formatted'];
						}
						else
						{
							$item->roomTypes[$i]->availableTariffs[$id]['val'] = $availableTariff['total_price_tax_excl_discounted_formatted'];
							$item->roomTypes[$i]->availableTariffs[$id]['val_original'] = $availableTariff['total_price_tax_excl_formatted'];
						}
						$item->roomTypes[$i]->availableTariffs[$id]['tariffTaxIncl'] = $availableTariff['total_price_tax_incl_discounted_formatted'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffTaxInclOriginal'] = $availableTariff['total_price_tax_incl_formatted'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffTaxExcl'] = $availableTariff['total_price_tax_excl_discounted_formatted'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffTaxExclOriginal'] = $availableTariff['total_price_tax_excl_formatted'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffIsAppliedCoupon'] = $availableTariff['is_applied_coupon'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffType'] = $availableTariff['type'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffBreakDown'] = $availableTariff['tariff_break_down'];
						// Useful for looping with Hub
						$item->roomTypes[$i]->availableTariffs[$id]['tariffTitle'] = $availableTariff['title'];
						$item->roomTypes[$i]->availableTariffs[$id]['tariffDescription'] = $availableTariff['description'];
						// For adjoining cases
						$item->roomTypes[$i]->availableTariffs[$id]['tariffAdjoiningLayer'] = $availableTariff['adjoining_layer'];
					}

					if ($item->roomTypes[$i]->occupancy_max > 0)
					{
						$item->totalOccupancyMax += $item->roomTypes[$i]->occupancy_max * $item->roomTypes[$i]->totalAvailableRoom;
					}
					else
					{
						$item->totalOccupancyMax += ($item->roomTypes[$i]->occupancy_adult + $item->roomTypes[$i]->occupancy_child) * $item->roomTypes[$i]->totalAvailableRoom;
					}

					$tariffsForFilter = array();
					if (is_array($item->roomTypes[$i]->availableTariffs))
					{
						foreach ($item->roomTypes[$i]->availableTariffs as $tariffId => $tariffInfo)
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
					foreach ($item->roomTypes[$i]->availableTariffs as $tariffId => $tariffInfo)
					{
						$uniqueTariffIds = array_keys($tariffsForFilter);
						if (!in_array($tariffId, $uniqueTariffIds))
						{
							unset($item->roomTypes[$i]->availableTariffs[$tariffId]);
						}
					}


					// Take overlapping mode into consideration
					$overlappingTariffsMode = $solidresConfig->get('overlapping_tariffs_mode', 0);
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
							SRUtilities::removeArrayElementsExcept($item->roomTypes[$i]->availableTariffs, $lowestTariffId);
							break;
						case 2: // Highest
							$highestTariffId = end($tariffsForFilterOverlappingKeys);
							SRUtilities::removeArrayElementsExcept($item->roomTypes[$i]->availableTariffs, $highestTariffId);
							break;
					}


					if (SRPlugin::isEnabled('hub'))
					{
						$origin = $this->getState('origin');
						if ($origin == 'hubsearch')
						{
							if (empty($tariffsForFilter))
							{
								unset($item->roomTypes[$i]);
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
								unset($item->roomTypes[$i]);
								continue;
							}
							else // This room type is matched but we have to check if all tariffs are matched or just some matched?
							{
								if (!empty($qualifiedTariffs) && count($qualifiedTariffs) != count($item->roomTypes[$i]->availableTariffs))
								{
									foreach ($item->roomTypes[$i]->availableTariffs as $k => $v)
									{
										if (!isset($qualifiedTariffs[$k]))
										{
											unset($item->roomTypes[$i]->availableTariffs[$k]);
										}
									}
								}
							}
						}
					} // End logic of Hub's filtering

					// If this room type has no available tariffs, it is equal to no availability therefore don't count
					// this room type's rooms
					if (!empty($item->roomTypes[$i]->availableTariffs))
					{
						$item->totalAvailableRoom += $item->roomTypes[$i]->totalAvailableRoom;
					}
					else
					{
						if ( isset($item->params['show_unavailable_roomtype']) && $item->params['show_unavailable_roomtype'] == 0 )
						{
							unset($item->roomTypes[$i]);
							continue;
						}
					}
				}

				// Get custom fields
				$results = $dispatcher->trigger('onRoomTypePrepareData', array('com_solidres.roomtype', $item->roomTypes[$i]));

				if (count($results) && in_array(false, $results, true))
				{
					$this->setError($dispatcher->getError());
					$item->roomTypes[$i] = false;
				}
			} // End room type loop

			JFactory::getApplication()->setUserState('com_solidres.reservation.process.current_selected_tariffs', $tariffs);

			// Compute view access permissions.
			if ($access = $this->getState('filter.access'))
			{
				// If the access filter has been set, we already know this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				$user = JFactory::getUser();
				$groups = $user->getAuthorisedViewLevels();

				$item->params['access-view'] = in_array($item->access, $groups);
			}
		}

        // Trigger the data preparation event.
        $results = $dispatcher->trigger('onReservationAssetPrepareData', array('com_solidres.reservationasset', $item));

		if (count($results) && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
			$item = false;
		}

		return $item;
	}
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias = JApplicationHelper::stringURLSafe($table->alias);

		if (empty ($table->alias))
        {
			$table->alias = JApplicationHelper::stringURLSafe($table->name);
		}

        if (empty ($table->geo_state_id))
        {
            $table->geo_state_id = NULL;
        }

		if (empty ($table->partner_id))
		{
			$table->partner_id = null;
			// Automatically assign partner_id if this asset is created in front end via hub dashboard
			if (SRPlugin::isEnabled('user') && SRPlugin::isEnabled('hub') && JFactory::getApplication()->isSite())
			{
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => JFactory::getUser()->get('id')));
				$table->partner_id = $customerTable->id;
			}
		}

		if (empty ($table->category_id))
		{
			$table->category_id = NULL;
		}

		if (empty($table->id))
        {
			// Set ordering to the last item if not set
			if (empty($table->ordering))
            {
				$db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->clear();
                $query->select('MAX(ordering)')->from( $db->quoteName('#__sr_reservation_assets'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}

		// If tax_id is empty, then set it to null
		if ($table->tax_id == 0)
		{
			$table->tax_id = NULL;
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'category_id = '.(int) $table->category_id;
		return $condition;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		
		// Initialise variables;
		$dispatcher = JEventDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('extension');
		// Import the solidres plugin group. TODO: consolidate these plugin groups
		JPluginHelper::importPlugin('solidres');
		JPluginHelper::importPlugin('solidrespayment');

		// Load the row if saving an existing record.
		if ($pk > 0)
        {
			$table->load($pk);
			$isNew = false;
		}

		if ((int) $data['category_id'] == 0)
		{
			if (empty($data['category_name']))
			{
				$this->setError(JText::_('SR_ERROR_CATEGORY_NAME_IS_EMPTY'));

				return false;
			}

			JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

			$categoryTable              = array();
			$categoryTable['title']     = $data['category_name'];
			$categoryTable['parent_id'] = 1;
			$categoryTable['published'] = 1;
			$categoryTable['language']  = '*';
			$categoryTable['extension'] = 'com_solidres';

			$data['category_id'] = CategoriesHelper::createCategory($categoryTable);
		}

		// Bind the data.
		if (!$table->bind($data))
        {
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
        {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($data, $table, $isNew));
		if (in_array(false, $result, true))
        {
			$this->setError($this->getError());
			return false;
		}

		// Store the data.
		if (!($result = $table->store(true)))
        {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($data, $table, $result,  $isNew));

		$pkName = $table->getKeyName();
		if (isset($table->$pkName))
        {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		if (!empty($data['add_to_menu']) && empty($data['menu_id']))
		{
			JFactory::getLanguage()->load('com_menus');
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models', 'MenusModel');
			$menuModel = JModelLegacy::getInstance('Item', 'MenusModel', array('ignore_request' => true));
			$save      = $menuModel->save(array(
				'id'           => 0,
				'parent_id'    => 1,
				'published'    => 1,
				'home'         => 0,
				'menutype'     => $data['add_to_menutype'],
				'title'        => $data['menu_title'],
				'alias'        => $data['menu_alias'],
				'type'         => 'component',
				'component_id' => JComponentHelper::getComponent('com_solidres')->id,
				'request'      => array('id' => $pk),
				'link'         => 'index.php?option=com_solidres&view=reservationasset&id=' . $pk,
				'language'     => '*',
			));
			$app       = JFactory::getApplication();

			if ($save)
			{
				$app->enqueueMessage(JText::sprintf('SR_CREATED_NEW_MENU_SUCCESS_PLURAL', $data['menu_title']));
			}
			else
			{
				$app->enqueueMessage($menuModel->getError(), 'warning');
			}
		}

		return true;
	}
}