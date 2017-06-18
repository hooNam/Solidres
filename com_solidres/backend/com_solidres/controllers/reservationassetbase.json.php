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
 * Reservation Asset controller class.
 *
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.1.0
 */
class SolidresControllerReservationAssetBase extends JControllerLegacy
{
	private $context;

	protected $reservationDetails;

	public function __construct($config = array())
	{
		$config['model_path'] = JPATH_COMPONENT_ADMINISTRATOR . '/models';
		$this->context = 'com_solidres.reservation.process';
		$this->app = JFactory::getApplication();
		parent::__construct($config);
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
	public function &getModel($name = 'ReservationAsset', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Recalculate the tariff accoriding to guest's room selection (adult number, child number, child's ages)
	 *
	 * This is used for tariff per person per night, when guest enter adults and children quantity as well as children's
	 * ages, we re-calculate the tariff.
	 *
	 * @return json
	 */
	public function calculateTariff()
	{
		JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_solidres/tables');
		$adultNumber = $this->app->input->get('adult_number', 0, 'int');
		$childNumber = $this->app->input->get('child_number', 0, 'int');
		$roomTypeId = $this->app->input->get('room_type_id', 0, 'int');
		$roomIndex = $this->app->input->get('room_index', 0, 'int');
		$extrasSelected = $this->app->input->get('extras', array(), 'array');
		// When reservation is made in backend, there is no room index, instead of that we use room id
		if ($roomIndex == 'undefined')
		{
			$roomIndex = $this->app->input->get('room_id', 0, 'int');
		}
		$raId = $this->app->input->get('raid', 0, 'int');
		$tariffId = $this->app->input->get('tariff_id', 0, 'int');
		$adjoiningLayer = $this->app->input->get('adjoining_layer', 0, 'int');
		$currencyId = $this->app->getUserState($this->context . '.currency_id');
		$taxId = $this->app->getUserState($this->context . '.tax_id');
		$solidresCurrency = new SRCurrency(0, $currencyId);
		$checkIn = $this->app->getUserState($this->context.'.checkin');
		$checkOut = $this->app->getUserState($this->context.'.checkout');
		$bookingType = $this->app->getUserState($this->context.'.booking_type');
		$coupon  = $this->app->getUserState($this->context.'.coupon');
		$srRoomType = SRFactory::get('solidres.roomtype.roomtype');

		$dayMapping = array('0' => JText::_('SUN'), '1' => JText::_('MON'), '2' => JText::_('TUE'), '3' => JText::_('WED'), '4' => JText::_('THU'), '5' => JText::_('FRI'), '6' => JText::_('SAT') );
		$solidresParams = JComponentHelper::getParams('com_solidres');
		$showTaxIncl = $solidresParams->get('show_price_with_tax', 0);
		$isDiscountPreTax = $solidresParams->get('discount_pre_tax', 0);
		$tariffBreakDownNetOrGross = $showTaxIncl== 1 ? 'net' : 'gross';

		if ($this->app->isAdmin())
		{
			JFactory::getLanguage()->load('com_solidres', JPATH_SITE.'/components/com_solidres');
		}

		// Get imposed taxes
		$imposedTaxTypes = array();
		if (!empty($taxId))
		{
			$taxModel = JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
			$imposedTaxTypes[] = $taxModel->getItem($taxId);
		}

		// Get discount
		$discounts = array();
		if (SRPlugin::isEnabled('discount'))
		{
			$discountModel = JModelLegacy::getInstance('Discounts', 'SolidresModel', array('ignore_request' => true));
			$discountModel->setState('filter.reservation_asset_id', $raId);
			$discountModel->setState('filter.valid_from', $checkIn);
			$discountModel->setState('filter.valid_to', $checkOut);
			$discountModel->setState('filter.state', 1);
			$discountModel->setState('filter.type', array(0,2,3));
			$discounts = $discountModel->getItems();
		}

		// Get customer information
		$user = JFactory::getUser();
		$customerGroupId = NULL;
		if (SRPlugin::isEnabled('user'))
		{
			JTable::addIncludePath(SRPlugin::getAdminPath('user').'/tables');
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
			$checkinToCheck  = JFactory::getDate(date('Y-M-d', strtotime($checkIn)), $tzoffset)->toUnix();
			$couponIsValid = $srCoupon->isValid($coupon['coupon_code'], $raId, $currentDate, $checkinToCheck, $customerGroupId);
		}

		$stayLength = (int) SRUtilities::calculateDateDiff($checkIn, $checkOut);
		if ($bookingType == 1)
		{
			$stayLength ++;
		}

		// Build the config values
		$tariffConfig = array(
			'booking_type' => $bookingType,
			'adjoining_tariffs_mode' => $solidresParams->get('adjoining_tariffs_mode', 0),
			'child_room_cost_calc' => $solidresParams->get('child_room_cost_calc', 1),
			'adjoining_layer' => $adjoiningLayer
		);

		// Calculate single supplement
		$roomTypeModel = JModelLegacy::getInstance('RoomType', 'SolidresModel', array('ignore_request' => true));
		$roomType = $roomTypeModel->getItem($roomTypeId);
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

		// Get children ages
		$childAges = array();
		for ($i = 0; $i < $childNumber; $i++)
		{
			$childAges[] = $this->app->input->get('child_age_'.$roomTypeId.'_'.$tariffId.'_'.$roomIndex.'_'.$i, '0', 'int');
		}

		// Get selected extra items
		$extras = array();
		if (!empty($extrasSelected))
		{
			foreach ($extrasSelected as $extraId)
			{
				$extras[$extraId]['quantity'] = $this->app->input->get('extra_'.$roomTypeId.'_'.$tariffId.'_'.$roomIndex.'_'.$extraId, '1', 'int');
			}
		}

		$totalExtraCostTaxIncl = 0;
		$totalExtraCostTaxExcl = 0;
		$totalExtraCost = 0;
		if (!empty($extras))
		{
			$extraModel = JModelLegacy::getInstance('Extra', 'SolidresModel', array('ignore_request' => true));

			foreach ($extras as $extraId => &$extraDetails)
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
				$extraDetails['adults_number'] = $adultNumber;
				$extraDetails['children_number'] = $childNumber;
				$extraDetails['stay_length'] = $stayLength;
				$extraDetails['booking_type'] = $bookingType;

				if (7 == $extraDetails['charge_type'])
				{
					continue;
				}
				$solidresExtra = new SRExtra($extraDetails);
				$costs = $solidresExtra->calculateExtraCost();

				$totalExtraCostTaxIncl += $costs['total_extra_cost_tax_incl'];
				$totalExtraCostTaxExcl += $costs['total_extra_cost_tax_excl'];
			}

			if ($showTaxIncl)
			{
				$totalExtraCost = $totalExtraCostTaxIncl;
			}
			else
			{
				$totalExtraCost = $totalExtraCostTaxExcl;
			}

			$totalExtraCostFormat = clone $solidresCurrency;
			$totalExtraCostFormat->setValue($totalExtraCost);
		}

		// Search for complex tariff first, if no complex tariff found, we will search for Standard Tariff
		if (SRPlugin::isEnabled('complextariff'))
		{
			$tariff = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, false, true, $checkIn, $checkOut, $solidresCurrency, $couponIsValid ? $coupon : NULL, $adultNumber, $childNumber, $childAges, $stayLength, (isset($tariffId) && $tariffId > 0 ? $tariffId : NULL ), $discounts, $isDiscountPreTax, $tariffConfig);
		}
		else
		{
			$tariff = $srRoomType->getPrice($roomTypeId, $customerGroupId, $imposedTaxTypes, true, false, $checkIn, $checkOut, $solidresCurrency, $couponIsValid ? $coupon : NULL, $adultNumber, 0, array(), $stayLength, $tariffId, $discounts, $isDiscountPreTax, $tariffConfig);
		}

		if ($showTaxIncl)
		{
			$shownTariff = $tariff['total_price_tax_incl_discounted_formatted'];
			if ($totalExtraCostTaxIncl > 0)
			{
				$shownTariff->setValue($shownTariff->getValue() + $totalExtraCostTaxIncl);
			}
			$shownTariffBeforeDiscounted = $tariff['total_price_tax_incl_formatted'];
		}
		else
		{
			$shownTariff = $tariff['total_price_tax_excl_discounted_formatted'];
			if ($totalExtraCostTaxExcl > 0)
			{
				$shownTariff->setValue($shownTariff->getValue() + $totalExtraCostTaxExcl);
			}
			$shownTariffBeforeDiscounted = $tariff['total_price_tax_excl_formatted'];
		}

		// Prepare tariff break down, since JSON is not able to handle PHP object correctly, we should prepare a simple array
		$tariffBreakDown = array();
		$tariffBreakDownHtml = '';
		if ($tariff['type'] == 0)
		{
			$tariffBreakDown = array();
			$tariffBreakDownHtml = '';
			$tempKeyWeekDay = NULL;
			$totalBreakDown = count($tariff['tariff_break_down']);
			for ($key = 0; $key <= $totalBreakDown; $key ++)
			{
				if ($key % 6 == 0 && $key == 0) :
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key % 6 == 0 && $key != $totalBreakDown) :
					$tariffBreakDownHtml .= '</div><div class="'.SR_UI_GRID_CONTAINER.' '.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key == $totalBreakDown) :
					$tariffBreakDownHtml .= '</div>';
				endif;

				if ($key < $totalBreakDown)
				{
					$priceOfDayDetails = $tariff['tariff_break_down'][$key];
					$tempKeyWeekDay = key($priceOfDayDetails);
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_COL_2.'"><p class="breakdown-wday">'.
					                        $dayMapping[$tempKeyWeekDay].
					                        '</p><span class="'.$tariffBreakDownNetOrGross.'">'.
					                        $priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross]->format().
					                        '</span></div>';
					$tariffBreakDown[][$tempKeyWeekDay] = array('wday' => $tempKeyWeekDay, 'priceOfDay' => $priceOfDayDetails[$tempKeyWeekDay]['gross']->format());
				}
			}

			$tariffBreakDownHtml .= '<table class="table table-bordered">';
			$tariffBreakDownHtml .= '<tr>';
			$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_COST') . '</td><td class="sr-align-right"> ';
			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$shownTariffBeforeDiscounted->setValue($shownTariffBeforeDiscounted->getValue() - $tariff['total_single_supplement'] );
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			else
			{
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			$tariffBreakDownHtml .= '</td>';
			$tariffBreakDownHtml .= '</tr>';

			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_SINGLE_SUPPLEMENT_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_single_supplement_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($totalExtraCost > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_EXTRA_AMOUNT') . '</td><td class="sr-align-right">' . $totalExtraCostFormat->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($tariff['total_discount'] > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_discount_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_COST') . '</td><td class="sr-align-right">' . $tariff['total_price_tax_'.($showTaxIncl == 1 ? 'incl' : 'excl' ).'_discounted_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			$tariffBreakDownHtml .= '</table>';
		}
		else if ($tariff['type'] == 1)
		{
			$tariffBreakDown = array();
			$tariffBreakDownHtml = '';
			$tempKeyWeekDay = NULL;
			$totalBreakDown = count($tariff['tariff_break_down']);
			for ($key = 0; $key <= $totalBreakDown; $key ++)
			{
				if ($key % 6 == 0 && $key == 0) :
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key % 6 == 0 && $key != $totalBreakDown) :
					$tariffBreakDownHtml .= '</div><div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key == $totalBreakDown) :
					$tariffBreakDownHtml .= '</div>';
				endif;

				if ($key < $totalBreakDown)
				{
					$priceOfDayDetails = $tariff['tariff_break_down'][$key];
					$tempKeyWeekDay = key($priceOfDayDetails);
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_COL_2.'"><p class="breakdown-wday">'
					                        .$dayMapping[$tempKeyWeekDay].
					                        '</p>
											<p class="breakdown-adult">' . JText::_('SR_ADULT'). '</p>
											<span class="'.$tariffBreakDownNetOrGross.'">'.$priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross . '_adults']->format().'</span>
											<p class="breakdown-child">' . JText::_('SR_CHILD'). '</p>
											<span class="'.$tariffBreakDownNetOrGross.'">'.$priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross . '_children']->format().'</span></div>';
					$tariffBreakDown[][$tempKeyWeekDay] = array('wday' => $tempKeyWeekDay, 'priceOfDay' => $priceOfDayDetails[$tempKeyWeekDay]['gross']->format());
				}
			}

			$tariffBreakDownHtml .= '<table class="table table-bordered">';
			$tariffBreakDownHtml .= '<tr>';
			$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_COST') . '</td><td class="sr-align-right"> ';
			if ($tariff['total_single_supplement'] != 0)
			{
				$shownTariffBeforeDiscounted->setValue($shownTariffBeforeDiscounted->getValue() - $tariff['total_single_supplement'] );
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			else
			{
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			$tariffBreakDownHtml .= '</td>';
			$tariffBreakDownHtml .= '</tr>';

			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_SINGLE_SUPPLEMENT_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_single_supplement_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($totalExtraCost > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_EXTRA_AMOUNT') . '</td><td class="sr-align-right">' . $totalExtraCostFormat->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($tariff['total_discount'] > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_discount_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_COST') . '</td><td class="sr-align-right">' . $tariff['total_price_tax_'.($showTaxIncl == 1 ? 'incl' : 'excl' ).'_discounted_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}
			$tariffBreakDownHtml .= '</table>';
		}
		else if ($tariff['type'] == 2)
		{
			$tariffBreakDown = array();
			$tariffBreakDownHtml = '';
			$tempKeyWeekDay = NULL;
			$totalBreakDown = count($tariff['tariff_break_down']);
			for ($key = 0; $key <= $totalBreakDown; $key ++)
			{
				if ($key % 6 == 0 && $key == 0) :
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key % 6 == 0 && $key != $totalBreakDown) :
					$tariffBreakDownHtml .= '</div><div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key == $totalBreakDown) :
					$tariffBreakDownHtml .= '</div>';
				endif;

				if ($key < $totalBreakDown)
				{
					$priceOfDayDetails = $tariff['tariff_break_down'][$key];
					$tempKeyWeekDay = key($priceOfDayDetails);
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_COL_2.'">
											<span class="'.$tariffBreakDownNetOrGross.'">'.
					                        $priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross]->format().
					                        '</span></div>';
					$tariffBreakDown[][$tempKeyWeekDay] = array('wday' => $tempKeyWeekDay, 'priceOfDay' => $priceOfDayDetails[$tempKeyWeekDay]['gross']->format());
				}
			}

			$tariffBreakDownHtml .= '<table class="table table-bordered">';
			$tariffBreakDownHtml .= '<tr>';
			$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_COST') . '</td><td class="sr-align-right"> ';
			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$shownTariffBeforeDiscounted->setValue($shownTariffBeforeDiscounted->getValue() - $tariff['total_single_supplement'] );
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			else
			{
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			$tariffBreakDownHtml .= '</td>';
			$tariffBreakDownHtml .= '</tr>';

			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_SINGLE_SUPPLEMENT_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_single_supplement_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($totalExtraCost > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_EXTRA_AMOUNT') . '</td><td class="sr-align-right">' . $totalExtraCostFormat->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($tariff['total_discount'] > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_discount_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_COST') . '</td><td class="sr-align-right">' . $tariff['total_price_tax_'.($showTaxIncl == 1 ? 'incl' : 'excl' ).'_discounted_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}
			$tariffBreakDownHtml .= '</table>';
		}
		else if ($tariff['type'] == 3)
		{
			$tariffBreakDown = array();
			$tariffBreakDownHtml = '';
			$tempKeyWeekDay = NULL;
			$totalBreakDown = count($tariff['tariff_break_down']);
			for ($key = 0; $key <= $totalBreakDown; $key ++)
			{
				if ($key % 6 == 0 && $key == 0) :
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key % 6 == 0 && $key != $totalBreakDown) :
					$tariffBreakDownHtml .= '</div><div class="'.SR_UI_GRID_CONTAINER.' breakdown-row">';
				elseif ($key == $totalBreakDown) :
					$tariffBreakDownHtml .= '</div>';
				endif;

				if ($key < $totalBreakDown)
				{
					$priceOfDayDetails = $tariff['tariff_break_down'][$key];
					$tempKeyWeekDay = key($priceOfDayDetails);
					$tariffBreakDownHtml .= '<div class="'.SR_UI_GRID_COL_2.'">
											<p class="breakdown-adult">' . JText::_('SR_ADULT'). '</p>
											<span class="'.$tariffBreakDownNetOrGross.'">'.$priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross . '_adults']->format().'</span>
											<p class="breakdown-child">' . JText::_('SR_CHILD'). '</p>
											<span class="'.$tariffBreakDownNetOrGross.'">'.$priceOfDayDetails[$tempKeyWeekDay][$tariffBreakDownNetOrGross . '_children']->format().'</span></div>';
					$tariffBreakDown[][$tempKeyWeekDay] = array('wday' => $tempKeyWeekDay, 'priceOfDay' => $priceOfDayDetails[$tempKeyWeekDay]['gross']->format());
				}

			}

			$tariffBreakDownHtml .= '<table class="table table-bordered">';
			$tariffBreakDownHtml .= '<tr>';
			$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_COST') . '</td><td class="sr-align-right"> ';
			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$shownTariffBeforeDiscounted->setValue($shownTariffBeforeDiscounted->getValue() - $tariff['total_single_supplement'] );
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			else
			{
				$tariffBreakDownHtml .= $shownTariffBeforeDiscounted->format() ;
			}
			$tariffBreakDownHtml .= '</td>';
			$tariffBreakDownHtml .= '</tr>';

			if ($tariff['total_single_supplement'] != 0) // We allow negative value for single supplement
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_SINGLE_SUPPLEMENT_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_single_supplement_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($totalExtraCost > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_EXTRA_AMOUNT') . '</td><td class="sr-align-right">' . $totalExtraCostFormat->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}

			if ($tariff['total_discount'] > 0)
			{
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_AMOUNT') . '</td><td class="sr-align-right">' . $tariff['total_discount_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
				$tariffBreakDownHtml .= '<tr>';
				$tariffBreakDownHtml .= '<td>' . JText::_('SR_ROOM_X_DISCOUNTED_COST') . '</td><td class="sr-align-right">' . $tariff['total_price_tax_'.($showTaxIncl == 1 ? 'incl' : 'excl' ).'_discounted_formatted']->format() . '</td>';
				$tariffBreakDownHtml .= '</tr>';
			}
			$tariffBreakDownHtml .= '</table>';
		}

		echo json_encode(array(
			'room_index' => $roomIndex,
			'room_index_tariff' => array(
				'id' => !empty($shownTariff) ? $shownTariff->getId() : NULL ,
				'activeId' => !empty($shownTariff) ? $shownTariff->getActiveId() : NULL ,
				'code' => !empty($shownTariff) ? $shownTariff->getCode() : NULL ,
				'sign' => !empty($shownTariff) ? $shownTariff->getSign() : NULL ,
				'name' => !empty($shownTariff) ? $shownTariff->getName() : NULL ,
				'rate' => !empty($shownTariff) ? $shownTariff->getRate() : NULL ,
				'value' => !empty($shownTariff) ? $shownTariff->getValue() : NULL,
				'formatted' => !empty($shownTariff) ? $shownTariff->format() : NULL
			),
			'room_index_tariff_breakdown' => $tariffBreakDown,
			'room_index_tariff_breakdown_html' => $tariffBreakDownHtml
		));

		$this->app->close();
	}
}