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
 * Reservation controller class.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.4.0
 */
class SolidresControllerReservationBase extends JControllerForm
{
	protected $numberOfNights;

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->app = JFactory::getApplication();
		$this->context = 'com_solidres.reservation.process';
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
		$allow	= null;

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		} else {
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
		return parent::allowEdit($data, $key);
	}

	/**
	 * General save method for inline editing feature
	 *
	 * @param null $key
	 * @param null $urlVar
	 *
	 * @return bool|void
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$filterMask = 'int';
		$pk = $input->get('pk', 0);
		$name = $input->get('name', 0, 'string');
		$canContinue = true;
		if (in_array($name, array('total_paid')))
		{
			$filterMask = 'double';
		}
		else if (in_array($name, array('payment_method_txn_id', 'origin')))
		{
			$filterMask = 'string';
		}
		$value = $input->get('value', 0, $filterMask);

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_solidres/tables');
		$table = JTable::getInstance('Reservation', 'SolidresTable');
		$currencyFields = array(
			'total_price', 'total_price_tax_incl', 'total_price_tax_excl', 'total_extra_price', 'total_extra_price_tax_incl',
			'total_extra_price_tax_excl', 'total_discount', 'total_paid', 'deposit_amount'
		);

		$table->load($pk);
		$oldValue = null;
		$oldValue = $table->$name;

		// Hook in reservation status changing event
		if ($name == 'payment_status')
		{
			JPluginHelper::importPlugin('solidrespayment');

			$responses = $app->triggerEvent('OnReservationPaymentStatusBeforeChange', array( $table, $value ));

			if (in_array(false, $responses, true))
			{
				$canContinue = false;
			}
		}

		if ($canContinue)
		{
			$table->$name = $value;

			// When payment status is changed to cancelled, update the reservation status to cancelled as well
			if ($name == 'payment_status' && $value == 2)
			{
				$table->state = 4;
			}

			$result = $table->store();
			$newValue = $table->$name;
		}

		if (in_array($name, $currencyFields))
		{
			JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
			$baseCurrency = new SRCurrency($value, $table->currency_id);
			$newValue = $baseCurrency->format();
		}

		if ($name == 'state')
		{
			JPluginHelper::importPlugin('extension');
			JPluginHelper::importPlugin('solidres');
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('solidrespayment');
			$dispatcher->trigger('onReservationChangeState', array('com_solidres.changestate', array($pk), $value, $oldValue));
			if ($value == 1)
			{
				$invoice = $dispatcher->trigger('onSolidresGenerateInvoice', array($pk));
			}
		}

		echo json_encode(array('success' => $result, 'newValue' => $newValue));
	}

	public function loadAvailableRooms()
	{
		$checkIn = $this->input->get('checkin');
		$checkOut = $this->input->get('checkout');
		$roomTypeId = $this->input->get('room_type_id');

		$solidresRoomType = SRFactory::get('solidres.roomtype.roomtype');
		$bookingType = $solidresRoomType->getBookingType($roomTypeId);

		$results = array();

		$availableRooms = $solidresRoomType->getListAvailableRoom($roomTypeId, $checkIn, $checkOut, $bookingType);

		foreach ($availableRooms as $k => $room)
		{
			$results[$k]['value'] = $room->id;
			$results[$k]['text'] = $room->label;
		}

		echo json_encode($results);
	}

	/**
	 * Prepare the reservation data, store them into user session so that it can be saved into the db later
	 *
	 * @params string $type Type of data to process
	 *
	 * @return void
	 */
	public function process()
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$data = $this->input->post->get('jform', array(), 'array');
		$step = $this->input->get('step', '', 'string');
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.'/models');

		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$reservationData = $this->app->getUserState($this->context);
		$this->stayLength = SRUtilities::calculateDateDiff($reservationData->checkin, $reservationData->checkout);

		if (SRPlugin::isEnabled('user') && ($userId = JFactory::getUser()->get('id')))
		{
			JTable::addIncludePath(SRPlugin::getAdminPath('user').'/tables');
			$userTable = JTable::getInstance('Customer', 'SolidresTable');
			if($userTable->load($userId))
			{
				$guest = $this->app->getUserState($this->context.'.guest', array());
				foreach($userTable->getProperties() as $name)
				{
					if(!isset($guest[$name]))
					{
						$guest[$name] = $userTable->get($name);
					}
				}
				$this->app->setUserState($this->context.'.guest', $guest);
			}
		}
		switch ($step)
		{
			case 'room':
				$this->processRoom($data);
				break;
			case 'guestinfo':
				$this->processGuestInfo($data);
				break;
			default:
				break;
		}
	}

	/**
	 * Process submitted room information and store some data into session for further steps
	 *
	 * @param $data array The submitted data
	 *
	 * @return json
	 */
	public function processRoom($data)
	{
		// Get the extra price to display in the confirmmation screen
		$extraModel = $this->getModel('Extra', 	'SolidresModel') ;
		$totalRoomTypeExtraCostTaxExcl = 0;
		$totalRoomTypeExtraCostTaxIncl = 0;
		$totalAdults = 0;
		$totalChildren = 0;

		foreach ($data['room_types'] as $roomTypeId => &$bookedTariffs)
		{
			foreach ($bookedTariffs as $tariffId => &$rooms)
			{
				foreach ($rooms as &$room)
				{
					$totalAdults += isset($room['adults_number']) ? $room['adults_number'] : 0;
					$totalChildren += !empty($room['children_number']) ? $room['children_number'] : 0;

					if (isset($room['extras']))
					{
						foreach ($room['extras'] as $extraId => &$extraDetails)
						{
							$extra = $extraModel->getItem($extraId);
							$extraDetails['price'] = $extra->price;
							$extraDetails['price_tax_incl'] = $extra->price_tax_incl;
							$extraDetails['price_tax_excl'] = $extra->price_tax_excl;
							$extraDetails['price_adult'] = $extra->price_adult;
							$extraDetails['price_adult_tax_incl'] = $extra->price_adult_tax_incl;
							$extraDetails['price_adult_tax_excl'] = $extra->price_adult_tax_excl;
							$extraDetails['price_child'] = $extra->price_child;
							$extraDetails['price_child_tax_incl'] = $extra->price_child_tax_incl;
							$extraDetails['price_child_tax_excl'] = $extra->price_child_tax_excl;
							$extraDetails['name'] = $extra->name;
							$extraDetails['charge_type'] = $extra->charge_type;
							$extraDetails['adults_number'] = isset($room['adults_number']) ? $room['adults_number'] : 0 ;
							$extraDetails['children_number'] = isset($room['children_number']) ? $room['children_number'] : 0;
							$extraDetails['stay_length'] = $this->stayLength;
							$extraDetails['booking_type'] = $this->app->getUserState($this->context.'.booking_type');

							if (7 == $extraDetails['charge_type'])
							{
								continue;
							}
							$solidresExtra = new SRExtra($extraDetails);
							$costs = $solidresExtra->calculateExtraCost();

							$totalRoomTypeExtraCostTaxIncl += $costs['total_extra_cost_tax_incl'];
							$totalRoomTypeExtraCostTaxExcl += $costs['total_extra_cost_tax_excl'];

							$extraDetails['total_extra_cost_tax_incl'] = $costs['total_extra_cost_tax_incl'];
							$extraDetails['total_extra_cost_tax_excl'] = $costs['total_extra_cost_tax_excl'];
						}
					}
				}
			}
		}

		// manually unset those referenced instances
		unset($rooms);
		unset($room);
		unset($extraDetails);

		$data['total_extra_price_per_room'] = $totalRoomTypeExtraCostTaxIncl;
		$data['total_extra_price_tax_incl_per_room'] = $totalRoomTypeExtraCostTaxIncl;
		$data['total_extra_price_tax_excl_per_room'] = $totalRoomTypeExtraCostTaxExcl;

		$this->app->setUserState($this->context . '.room', $data);
		$this->app->setUserState($this->context . '.total_adults', $totalAdults);
		$this->app->setUserState($this->context . '.total_children', $totalChildren);
		$this->app->setUserState($this->context . '.booking_conditions', isset( $data['bookingconditions'] ) ? $data['bookingconditions'] : '');
		$this->app->setUserState($this->context . '.privacy_policy', isset( $data['privacypolicy'] ) ? $data['privacypolicy'] : '');

		// Store all selected tariffs
		$this->app->setUserState($this->context.'.current_selected_tariffs', isset( $data['selected_tariffs'] ) ? $data['selected_tariffs'] : '');

		// If error happened, output correct error message in json format so that we can handle in the front end
		$response = array('status' => 1, 'message' => '', 'next_step' => $data['next_step']);

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Process submitted guest information: guest personal information and their payment method
	 *
	 * @param $data
	 *
	 * @return json
	 */
	public function processGuestInfo($data)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables', 'SolidresTable');
		$isHubDashboard = $this->app->getUserState($this->context . '.hub_dashboard');

		if (isset($data['customer_country_id']))
		{
			$countryModel = $this->getModel('Country', 'SolidresModel');
			$country = $countryModel->getItem($data['customer_country_id']);
			$data['country_name'] 	= $country->name;
		}

		$totalPerBookingExtraCostTaxIncl = 0;
		$totalPerBookingExtraCostTaxExcl = 0;

		// Query country and geo state name
		if (isset($data['customer_geo_state_id']))
		{
			$geostateModel = $this->getModel('State', 'SolidresModel');
			$geoState = $geostateModel->getItem($data['customer_geo_state_id']);
			$data['geo_state_name'] = $geoState->name;
		}

		// Process customer group
		$customerId = null;
		if (SRPlugin::isEnabled('user'))
		{
			$customerJoomlaUserId = $this->app->getUserState($this->context . '.customer_joomla_user_id', 0);
			$user = JFactory::getUser();
			if ($customerJoomlaUserId == 0 && $user->get('id') > 0 && $this->app->isSite() && !$isHubDashboard)
			{
				$customerJoomlaUserId = $user->get('id');
			}

			if ($customerJoomlaUserId > 0)
			{
				JTable::addIncludePath(SRPlugin::getAdminPath('user').'/tables');
				$customerTable = JTable::getInstance('Customer', 'SolidresTable');
				$customerTable->load(array('user_id' => $customerJoomlaUserId));
				$customerId = $customerTable->id;
			}
		}

		$data['customer_id'] = $customerId;

		// Process extra (Per booking)
		if (isset($data['extras']))
		{
			$extraModel = $this->getModel('Extra', 	'SolidresModel') ;

			foreach ($data['extras'] as $extraId => &$extraDetails)
			{
				$extra = $extraModel->getItem($extraId);
				$extraDetails['price'] = $extra->price;
				$extraDetails['price_tax_incl'] = $extra->price_tax_incl;
				$extraDetails['price_tax_excl'] = $extra->price_tax_excl;
				$extraDetails['price_adult'] = $extra->price_adult;
				$extraDetails['price_adult_tax_incl'] = $extra->price_adult_tax_incl;
				$extraDetails['price_adult_tax_excl'] = $extra->price_adult_tax_excl;
				$extraDetails['price_child'] = $extra->price_child;
				$extraDetails['price_child_tax_incl'] = $extra->price_child_tax_incl;
				$extraDetails['price_child_tax_excl'] = $extra->price_child_tax_excl;
				$extraDetails['name'] = $extra->name;
				$extraDetails['charge_type'] = $extra->charge_type;
				$extraDetails['adults_number'] = $this->app->getUserState($this->context.'.total_adults');
				$extraDetails['children_number'] = $this->app->getUserState($this->context.'.total_children');
				$extraDetails['stay_length'] = $this->stayLength;
				$extraDetails['booking_type'] = $this->app->getUserState($this->context.'.booking_type');

				$solidresExtra = new SRExtra($extraDetails);
				$costs = $solidresExtra->calculateExtraCost();

				$totalPerBookingExtraCostTaxIncl += $costs['total_extra_cost_tax_incl'];
				$totalPerBookingExtraCostTaxExcl += $costs['total_extra_cost_tax_excl'];

				$extraDetails['total_extra_cost_tax_incl'] = $costs['total_extra_cost_tax_incl'];
				$extraDetails['total_extra_cost_tax_excl'] = $costs['total_extra_cost_tax_excl'];
			}
		}

		$data['total_extra_price_per_booking'] = $totalPerBookingExtraCostTaxIncl;
		$data['total_extra_price_tax_incl_per_booking'] = $totalPerBookingExtraCostTaxIncl;
		$data['total_extra_price_tax_excl_per_booking'] = $totalPerBookingExtraCostTaxExcl;

		// Bind them to session
		$this->app->setUserState($this->context.'.guest', $data);

		// If error happened, output correct error message in json format so that we can handle in the front end
		$response = array('status' => 1, 'message' => '', 'next_step' => $data['next_step']);

		echo json_encode($response);

		$this->app->close();
	}
}
