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
 * Reservation model.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresModelReservation extends JModelAdmin
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
	 * @param	array $config An optional associative array of configuration settings.
	 *
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->event_after_delete 	= 'onReservationAfterDelete';
		$this->event_after_save 	= 'onReservationAfterSave';
		$this->event_before_delete 	= 'onReservationBeforeDelete';
		$this->event_before_save 	= 'onReservationBeforeSave';
		$this->event_change_state 	= 'onReservationChangeState';
		$this->text_prefix 			= strtoupper($this->option);
		$this->context = 'com_solidres.reservation.process';

		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
	}

	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('reservation.id', $pk);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	$record A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();

		if (JFactory::getApplication()->isAdmin())
		{
			return $user->authorise('core.delete', 'com_solidres.reservation.'.(int) $record->id);
		}
		else
		{
			return SRUtilities::isAssetPartner($user->get('id'), $record->reservation_asset_id);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	$record A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		if (JFactory::getApplication()->isAdmin())
		{
			return $user->authorise('core.edit.state', 'com_solidres.reservation.'.(int) $record->id);
		}
		else
		{
			return SRUtilities::isAssetPartner($user->get('id'), $record->reservation_asset_id);
		}
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	string	$type The table type to instantiate
	 * @param	string	$prefix A prefix for the table class name. Optional.
	 * @param	array	$config Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Reservation', $prefix = 'SolidresTable', $config = array())
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
		$form = $this->loadForm('com_solidres.reservation', 'reservation', array('control' => 'jform', 'load_data' => $loadData));
        
		if (empty($form))
        {
			return false;
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
		$data = JFactory::getApplication()->getUserState('com_solidres.edit.reservation.data', array());

		if (empty($data))
        {
			$data = $this->getItem();
		}

		return $data;
	}

    /**
	 * Method to get a single record.
	 *
	 * @param	integer	$pk The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('reservation.id');
		$item = parent::getItem($pk);
		if ($item->id)
        {
			$modelCoupon = JModelLegacy::getInstance('Coupon', 'SolidresModel', array('ignore_request' => true));
			$notesModel = JModelLegacy::getInstance('ReservationNotes', 'SolidresModel', array('ignore_request' => true));
			$item->coupon_code = empty($item->coupon_id) ? '' : $modelCoupon->getItem($item->coupon_id)->coupon_code;
			$query = $this->_db->getQuery(true);

            if(!empty($item->customer_id))
            {
                $query->select('CONCAT(u.name, " (", c.customer_code, " - ", cg.name, ")" )');
                $query->from($this->_db->quoteName('#__sr_customers').'as c');
                $query->join('LEFT', $this->_db->quoteName('#__sr_customer_groups').' as cg ON cg.id = c.customer_group_id');
                $query->join('LEFT', $this->_db->quoteName('#__users').' as u ON u.id = c.user_id');
				$query->where('c.id = '. (int) $item->customer_id);
                $item->customer_code = $this->_db->setQuery($query)->loadResult();
            }

			if(!empty($item->customer_country_id))
			{
				$query->clear();
				$query->select('ct.name as countryname');
				$query->from($this->_db->quoteName('#__sr_countries').'as ct');
				$query->where('ct.id = '. (int) $item->customer_country_id);
				$item->customer_country_name = $this->_db->setQuery($query)->loadResult();
			}

			if(!empty($item->customer_geo_state_id))
			{
				$query->clear();
				$query->select('gst.name as geostatename');
				$query->from($this->_db->quoteName('#__sr_geo_states').'as gst');
				$query->where('gst.id = '. (int) $item->customer_geo_state_id);
				$item->customer_geostate_name = $this->_db->setQuery($query)->loadResult();
			}

            $query = $this->_db->getQuery(true);
            $query->select('x.*, rtype.id as room_type_id, rtype.name as room_type_name, room.label as room_label')
				  ->from($this->_db->quoteName('#__sr_reservation_room_xref'). 'as x')
				  ->join('INNER', $this->_db->quoteName('#__sr_rooms').' as room ON room.id = x.room_id')
				  ->join('INNER', $this->_db->quoteName('#__sr_room_types').' as rtype ON rtype.id = room.room_type_id')
				  ->where('reservation_id = '.$this->_db->quote($item->id));

            $item->reserved_room_details = $this->_db->setQuery($query)->loadObjectList();

			foreach($item->reserved_room_details as $reserved_room_detail)
			{
				$query->clear();
				$query->select('x.*, extra.id as extra_id, extra.name as extra_name')->from($this->_db->quoteName('#__sr_reservation_room_extra_xref').' as x')
					  ->join('INNER', $this->_db->quoteName('#__sr_extras').' as extra ON extra.id = x.extra_id')
					  ->where('reservation_id = '.$this->_db->quote($item->id))
					  ->where('room_id = '. (int) $reserved_room_detail->room_id);

				$result = $this->_db->setQuery($query)->loadObjectList();

				if (!empty($result))
				{
					$reserved_room_detail->extras =  $result;
				}

				$query->clear();
				$query->select('*')
					  ->from($this->_db->quoteName('#__sr_reservation_room_details'))
					  ->where($this->_db->quoteName('reservation_room_id') .' = '.$reserved_room_detail->id);

				$result = $this->_db->setQuery($query)->loadObjectList();

				$reserved_room_detail->other_info = array();
				if (!empty($result))
				{
					$reserved_room_detail->other_info =  $result;
				}
			}

			$item->notes = NULL;
			$notesModel->setState('filter.reservation_id', $item->id);
	        $isHubDashboard = $this->getState('hub_dashboard', 0);
	        if (JFactory::getApplication()->isSite() && $isHubDashboard != 1)
	        {
		        $notesModel->setState('filter.visible_in_frontend' , 1);
	        }
			$notes = $notesModel->getItems();

			if (!empty($notes))
			{
				$item->notes = $notes;
			}

			$query->clear();
			$query->select('*')
				->from($this->_db->quoteName('#__sr_reservation_extra_xref'))
				->where($this->_db->quoteName('reservation_id') .' = ' . $this->_db->quote($item->id) );
			$result = $this->_db->setQuery($query)->loadObjectList();

			if (!empty($result))
			{
				$item->extras = $result;
			}
		}
        
		return $item;
	}

	/**
	 * Get room type information to be display in the reservation confirmation screen
	 *
	 * This is intended to be used in the front end
	 *
	 * @return array $ret An array contain room type information
	 */
	public function getRoomType()
	{
		// Construct a simple array of room type ID and its price
		$roomTypePricesMapping = array();
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
		JLoader::register('SRDiscount', JPATH_PLUGINS . '/solidres/discount/libraries/discount/discount.php');
		$app = JFactory::getApplication();
		$srRoomType = SRFactory::get('solidres.roomtype.roomtype');

		$currencyId = $app->getUserState($this->context . '.currency_id');
		$taxId = $app->getUserState($this->context . '.tax_id');
		$solidresCurrency = new SRCurrency(0, $currencyId);

		$modelName = $this->getName();
		$roomTypes = $this->getState($modelName .'.roomTypes');
		$checkin = $this->getState($modelName .'.checkin');
		$checkout = $this->getState($modelName .'.checkout');
		$bookingType = $this->getState($modelName .'.booking_type', 0);
		$reservationAssetId = $this->getState($modelName.'.reservationAssetId');
		$coupon = $app->getUserState($this->context . '.coupon');
		$solidresParams = JComponentHelper::getParams('com_solidres');
		$isDiscountPreTax = $solidresParams->get('discount_pre_tax', 0);
		$isEditing = $this->getState($modelName .'.is_editing', 0);
		$isDepositRequired = $app->getUserState($this->context . '.deposit_required');
		$depositByStayLength = $app->getUserState($this->context . '.deposit_by_stay_length');

		// Get imposed taxes
		$imposedTaxTypes = array();
		if (!empty($taxId))
		{
			$taxModel	= JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
			$imposedTaxTypes[] = $taxModel->getItem($taxId);
		}

		// Get discount
		$discounts = array();
		if (SRPlugin::isEnabled('discount'))
		{
			JModelLegacy::addIncludePath(SRPlugin::getAdminPath('discount').'/models', 'SolidresModel');
			$discountModel = JModelLegacy::getInstance('Discounts', 'SolidresModel', array('ignore_request' => true));
			$discountModel->setState('filter.reservation_asset_id', $reservationAssetId);
			$discountModel->setState('filter.valid_from', $checkin);
			$discountModel->setState('filter.valid_to', $checkout);
			$discountModel->setState('filter.state', 1);
			$discountModel->setState('filter.type', array(0,2,3));
			$discounts = $discountModel->getItems();
		}

		// Get customer information
		$user            = JFactory::getUser();
		$customerGroupId = null;  // Non-registered/Public/Non-loggedin customer
		if (SRPlugin::isEnabled('user'))
		{
			JTable::addIncludePath(SRPlugin::getAdminPath('user') . '/tables');
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$customerTable->load(array('user_id' => $user->id));
			$customerGroupId = $customerTable->customer_group_id;
		}

		$couponIsValid = false;
		if (isset($coupon) && is_array($coupon))
		{
			$srCoupon = SRFactory::get('solidres.coupon.coupon');
			$jconfig = JFactory::getConfig();
			$tzoffset = $jconfig->get('offset');
			$currentDate = JFactory::getDate(date('Y-M-d'), $tzoffset)->toUnix();
			$checkinToCheck  = JFactory::getDate(date('Y-M-d', strtotime($checkin)), $tzoffset)->toUnix();
			$couponIsValid = $srCoupon->isValid($coupon['coupon_code'], $reservationAssetId, $currentDate, $checkinToCheck, $customerGroupId);
		}

		$stayLength = (int) SRUtilities::calculateDateDiff($checkin, $checkout);
		if ($bookingType == 1)
		{
			$stayLength ++;
		}

		// Build the config values
		$tariffConfig = array(
			'booking_type' => $bookingType,
			'enable_single_supplement' => false,
			'child_room_cost_calc' => $solidresParams->get('child_room_cost_calc', 1),
			'adjoining_tariffs_mode' => $solidresParams->get('adjoining_tariffs_mode', 0)
		);

		$roomtypeModel = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));

		$totalPriceTaxIncl = 0; // Not include discounted
		$totalPriceTaxExcl = 0; // Not include discounted
		$totalPriceTaxInclDiscounted = 0; // Include discounted
		$totalPriceTaxExclDiscounted = 0; // Include discounted
		$totalDiscount = 0;
		$totalReservedRoom = 0;
		$totalDepositByStayLength = 0;
		$totalSingleSupplement = 0;
		$ret = array();

		// Get a list of room type based on search conditions
		foreach ($roomTypes as $roomTypeId => $bookedTariffs )
		{
			//$bookedRoomTypeQuantity = count($roomTypes[$roomTypeId]);
			$bookedRoomTypeQuantity = 0;

			$r = $roomtypeModel->getItem(array(
				'id' => $roomTypeId,
				'reservation_asset_id' => $reservationAssetId
			));

			if (isset($r->params['enable_single_supplement'])
			    &&
			    $r->params['enable_single_supplement'] == 1)
			{
				$tariffConfig['enable_single_supplement'] = true;
				$tariffConfig['single_supplement_value'] = $r->params['single_supplement_value'];
				$tariffConfig['single_supplement_is_percent'] = $r->params['single_supplement_is_percent'];
			}
			else
			{
				$tariffConfig['enable_single_supplement'] = false;
			}

			foreach ($bookedTariffs as $tariffId => $roomTypeRoomDetails )
			{
				$bookedRoomTypeQuantity += count($roomTypeRoomDetails);

				$tariffConfig['adjoining_layer'] = abs($tariffId);

				$ret[$roomTypeId]['name'] = $r->name;
				$ret[$roomTypeId]['description'] = $r->description;
				$ret[$roomTypeId]['occupancy_adult'] = $r->occupancy_adult;
				$ret[$roomTypeId]['occupancy_child'] = $r->occupancy_child;

				// Some data to query the correct tariff
				foreach ($roomTypeRoomDetails as $roomIndex => $roomDetails)
				{
					if (SRPlugin::isEnabled('complextariff'))
					{
						$cost  = $srRoomType->getPrice(
							$roomTypeId,
							$customerGroupId,
							$imposedTaxTypes,
							false,
							true,
							$checkin,
							$checkout,
							$solidresCurrency,
							$couponIsValid ? $coupon : NULL,
							(isset($roomDetails['adults_number']) ? $roomDetails['adults_number'] : 0),
							(isset($roomDetails['children_number']) ? $roomDetails['children_number'] : 0),
							(isset($roomDetails['children_ages']) ? $roomDetails['children_ages'] : array()),
							$stayLength,
							(isset($tariffId) && $tariffId > 0) ? $tariffId : NULL,
							$discounts,
							$isDiscountPreTax,
							$tariffConfig
						);
					}
					else
					{
						$cost = $srRoomType->getPrice(
							$roomTypeId,
							$customerGroupId,
							$imposedTaxTypes,
							true,
							false,
							$checkin,
							$checkout,
							$solidresCurrency,
							$couponIsValid ? $coupon : NULL,
							(isset($roomDetails['adults_number']) ? $roomDetails['adults_number'] : 0),
							0,
							array(),
							$stayLength,
							$tariffId,
							$discounts,
							$isDiscountPreTax,
							$tariffConfig
						);
					}

					$ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency'] = $cost;
					$totalPriceTaxIncl += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_incl'];
					$totalPriceTaxExcl += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_excl'];
					$totalPriceTaxInclDiscounted += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_incl_discounted'];
					$totalPriceTaxExclDiscounted += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_excl_discounted'];
					$totalDiscount += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_discount'];
					$totalSingleSupplement += $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_single_supplement'];

					$roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex] = array(
						'total_price' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price'],
						'total_price_tax_incl' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_incl'],
						'total_price_tax_excl' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_excl'],
						'total_price_discounted' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_discounted'],
						'total_price_tax_incl_discounted' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_incl_discounted'],
						'total_price_tax_excl_discounted' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_price_tax_excl_discounted'],
						'total_discount' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_discount'],
						'total_discount_formatted' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_discount_formatted'],
						'tariff_break_down' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['tariff_break_down'],
						'total_single_supplement' => $ret[$roomTypeId]['rooms'][$tariffId][$roomIndex]['currency']['total_single_supplement']
					);

					if ($isDepositRequired && $depositByStayLength > 0)
					{
						for ($i = 0; $i < $depositByStayLength; $i++)
						{
							if (isset($roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex]['tariff_break_down'][$i]))
							{
								$mappedWDay = key($roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex]['tariff_break_down'][$i]);
								$totalDepositByStayLength += $roomTypePricesMapping[$roomTypeId][$tariffId][$roomIndex]['tariff_break_down'][$i][$mappedWDay]['gross']->getValue();
							}
						}
					}
				}

				// Calculate number of available rooms
				$ret[$roomTypeId]['totalAvailableRoom'] = count( $srRoomType->getListAvailableRoom($roomTypeId, $checkin, $checkout, $bookingType) );
				$ret[$roomTypeId]['quantity'] = $bookedRoomTypeQuantity;

				// Only allow quantity within quota
				if (!$isEditing)
				{
					if ($bookedRoomTypeQuantity <= $ret[$roomTypeId]['totalAvailableRoom'])
					{
						$totalReservedRoom += $bookedRoomTypeQuantity;
					}
					else
					{
						return false;
					}
				}

			} // end room type loop
		}

		// Calculate discounts on number of booked rooms, need to take before and after tax into consideration
		// Get discount
		$totalDiscountOnNumOfBookedRoom = 0;
		if (SRPlugin::isEnabled('discount'))
		{
			$discountModel = JModelLegacy::getInstance('Discounts', 'SolidresModel', array('ignore_request' => true));
			$discountModel->setState('filter.reservation_asset_id', $reservationAssetId);
			$discountModel->setState('filter.valid_from', $checkin);
			$discountModel->setState('filter.valid_to', $checkout);
			$discountModel->setState('filter.state', 1);
			$discountModel->setState('filter.type', array(1)); // only query for Discount on number of booked rooms
			$discounts2 = $discountModel->getItems();

			$reservationData = array(
				'checkin' => $checkin,
				'checkout' => $checkout,
				'discount_pre_tax' => $isDiscountPreTax,
				'stay_length' => $stayLength,
				'scope' => 'asset',
				'scope_id' => $reservationAssetId,
				'total_reserved_room' => $totalReservedRoom,
				'total_price_tax_excl' => $totalPriceTaxExcl,
				'total_price_tax_incl' => $totalPriceTaxIncl,
				'booking_type' => $bookingType
			);

			$solidresDiscount = new SRDiscount($discounts2, $reservationData);
			$solidresDiscount->calculate();
			$appliedDiscounts = $solidresDiscount->appliedDiscounts;
			$totalDiscountOnNumOfBookedRoom = $solidresDiscount->totalDiscount;
		}

		// End of discount calculation

		if ($totalDiscountOnNumOfBookedRoom > 0)
		{
			$totalDiscount += $totalDiscountOnNumOfBookedRoom;
		}

		$totalImposedTax = 0;
		foreach ($imposedTaxTypes as $taxType)
		{
			if ($isDiscountPreTax)
			{
				$imposedAmount = $taxType->rate * ($totalPriceTaxExcl - $totalDiscount);
			}
			else
			{
				$imposedAmount = $taxType->rate * ($totalPriceTaxExcl);
			}
			$totalImposedTax += $imposedAmount;
		}

		$this->setState($modelName .'.totalReservedRoom', $totalReservedRoom);

		$app->setUserState($this->context . '.cost',
			array(
				'total_price' => $totalPriceTaxIncl,
				'total_price_tax_incl' => $totalPriceTaxIncl,
				'total_price_tax_excl' => $totalPriceTaxExcl,
				'total_price_tax_incl_discounted' => $totalPriceTaxInclDiscounted - $totalDiscountOnNumOfBookedRoom,
				'total_price_tax_excl_discounted' => $totalPriceTaxExclDiscounted - $totalDiscountOnNumOfBookedRoom,
				'total_discount' => $totalDiscount,
				'tax_amount' => $totalImposedTax,
				'total_single_supplement' => $totalSingleSupplement
			)
		);

		$app->setUserState($this->context . '.room_type_prices_mapping', $roomTypePricesMapping);
		$app->setUserState($this->context . '.deposit_amount_by_stay_length', $totalDepositByStayLength);

		return $ret;
	}

	/**
	 * Save the reservation data
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;
		$app = JFactory::getApplication();
		$roomTypePricesMapping = $app->getUserState($this->context.'.room_type_prices_mapping', NULL);

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('extension');
		JPluginHelper::importPlugin('user');
		JPluginHelper::importPlugin('solidres');
		JPluginHelper::importPlugin('solidrespayment', $data['payment_method_id']);

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		//$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($data, $table, $isNew, $this));
		if (in_array(false, $result, true))
		{
			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		$this->setState($this->getName().'.room_type_prices_mapping', $roomTypePricesMapping);
		$result = $dispatcher->trigger($this->event_after_save, array($data, $table, $isNew, $this));
		if (in_array(false, $result, true))
		{
			return false;
		}

		$pkName = $table->getKeyName();
		if (isset($table->$pkName))
		{
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		if (SRPlugin::isEnabled('customfield'))
		{
			//Customer fields
			if ($fields = SRCustomFieldHelper::findFields(array('context' => 'com_solidres.customer')))
			{
				$reservationId = (int) $table->get('id');
				$dataValue     = array();
				foreach ($fields as $field)
				{
					if (isset($data[$field->field_name]))
					{
						$dataValue[] = array(
							'id'      => 0,
							'context' => 'com_solidres.customer.' . $reservationId,
							'value'   => $data[$field->field_name],
							'storage' => $field
						);
					}
				}
				if (count($dataValue))
				{
					SRCustomFieldHelper::storeValues($dataValue, $isNew);
				}
			}
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * Override to import Solidres plugin group
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		JPluginHelper::importPlugin('solidres');

		parent::delete($pks);
	}

	/**
	 * Record the last accessed date
	 *
	 * @param   integer  $pk  Optional primary key of the reservation asset
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function recordAccess($pk = 0)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('reservation.id');
		$table = JTable::getInstance('Reservation', 'SolidresTable');
		$table->load($pk);
		$table->recordAccess($pk);

		return true;
	}
}