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
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.1.0
 */
class SolidresControllerReservationAsset extends JControllerLegacy
{
	private $context;

	protected $reservationDetails;

	public function __construct($config = array())
	{
		$config['model_path'] = JPATH_COMPONENT_ADMINISTRATOR . '/models';
		parent::__construct($config);
		$this->app = JFactory::getApplication();

		// $raid is preferred because it does not conflict with core Joomla multilingual feature
		$this->reservationAssetId = $this->input->getUint('raid');

		if (empty($this->reservationAssetId))
		{
			$this->reservationAssetId = $this->input->getUint('id');
		}

		$this->context = 'com_solidres.reservation.process';

		// Get the default currency
		$reservationAssetModel = $this->getModel('ReservationAsset', 'SolidresModel', array('ignore_request' => true));
		$asset = $reservationAssetModel->getItem($this->reservationAssetId);
		$currencyTable = JTable::getInstance('Currency', 'SolidresTable');
		$currencyTable->load($asset->currency_id);
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

	public function checkavailability()
	{
		$id = $this->input->getUint('id', 0, 'int');
		$checkIn =  $this->input->get('checkin', '', 'string');
		$checkOut = $this->input->get('checkout', '', 'string');

		if (empty($checkIn) && empty($checkOut))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_solidres&view=reservationasset&id='.$id, false));
			$this->redirect();
		}

		$config = JFactory::getConfig();
		$timezone = new DateTimeZone($config->get('offset'));
		$checkIn =  JDate::getInstance($checkIn, $timezone)->format('Y-m-d', true);
		$checkOut = JDate::getInstance($checkOut, $timezone)->format('Y-m-d', true);

		$model = $this->getModel();
		$app = JFactory::getApplication();
		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$solidresReservation = SRFactory::get('solidres.reservation.reservation');
		$conditions = array();
		$conditions['min_days_book_in_advance'] = $solidresConfig->get('min_days_book_in_advance', 0);
		$conditions['max_days_book_in_advance'] = $solidresConfig->get('max_days_book_in_advance', 0);
		$conditions['min_length_of_stay'] = $solidresConfig->get('min_length_of_stay', 1);
		$conditions['booking_type'] = $this->app->getUserState($this->context.'.booking_type');
		$showPriceWithTax = $solidresConfig->get('show_price_with_tax', 0);

		$itemId = $this->input->getUInt('Itemid', 0);
		$roomsOccupancyOptions = $this->input->get('room_opt', array(), 'array');
		$app->setUserState($this->context.'.checkin', $checkIn);
		$app->setUserState($this->context.'.checkout', $checkOut);
		$app->setUserState($this->context.'.room_opt', $roomsOccupancyOptions);

		try
		{
			$solidresReservation->isCheckInCheckOutValid($checkIn, $checkOut, $conditions);
		}
		catch (Exception $e)
		{
			switch ($e->getCode())
			{
				default:
				case 50001:
					$msg = JText::_($e->getMessage());
					break;
				case 50002:
					$msg = JText::sprintf($e->getMessage(), $conditions['min_length_of_stay']);
					break;
				case 50003:
					$msg = JText::sprintf($e->getMessage(), $conditions['min_days_book_in_advance']);
					break;
				case 50004:
					$msg = JText::sprintf($e->getMessage(), $conditions['max_days_book_in_advance']);
					break;
			}

			$this->setRedirect(JRoute::_('index.php?option=com_solidres&view=reservationasset&id='.$id.'#system-message', false), $msg);
			$this->redirect();
		}

		// Get the current selected tariffs if available
		$tariffs = $this->app->getUserState($this->context.'.current_selected_tariffs');

		// Set the current active menu item id
		$this->app->setUserState($this->context . '.activeItemId', $itemId > 0 ? $itemId : NULL);

		$model->setState('id', $id);
		$model->setState('checkin',	$checkIn);
		$model->setState('checkout', $checkOut);
		$model->setState('country_id', $this->input->get('country_id', 0, 'int'));
		$model->setState('geo_state_id', $this->input->get('geo_state_id', 0, 'int'));
		$model->setState('show_price_with_tax', $showPriceWithTax);
		$model->setState('tariffs', $tariffs);
		$model->setState('room_opt', $roomsOccupancyOptions);

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = 'ReservationAsset';
		$viewLayout = 'default';

		$this->hit($id);

		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
		$view->setModel($model, true);
		$view->document = $document;
		$view->display();
	}

	/**
	 * Increase the hit counter
	 *
	 * @param $pk
	 *
	 * @return void
	 */
	public function hit($pk)
	{
		$table = JTable::getInstance('ReservationAsset', 'SolidresTable');
		$table->hit($pk);
	}

	/**
	 * Get the html output according to the room type quantity selection
	 *
	 * This output contains room specific form like adults and children's quantity (including children's ages) as well
	 * as some other information like room preferences like smoking and room's extra items
	 *
	 * @return string
	 */
	public function getRoomTypeForm()
	{
		$params = JComponentHelper::getParams('com_solidres');
		$showTaxIncl = $params->get('show_price_with_tax', 0);
		$roomTypeId = $this->input->get('rtid', 0, 'int');
		$raId = $this->input->get('raid', 0, 'int');
		$tariffId = $this->input->get('tariffid', 0, 'int');
		$adjoiningLayer = $this->input->get('adjoininglayer', 0, 'int');
		$quantity = $this->input->get('quantity', 0, 'int');
		$modelRoomType = $this->getModel('RoomType');
		$modelTariff = $this->getModel('Tariff');
		$modelExtras = $this->getModel('Extras', 'SolidresModel', array('ignore_request' => true));
		$modelExtras->setState('filter.room_type_id', $roomTypeId);
		$modelExtras->setState('filter.state', 1);
		$modelExtras->setState('filter.show_price_with_tax', $showTaxIncl);
		$extras = $modelExtras->getItems();
		$roomType = $modelRoomType->getItem($roomTypeId);
		$tariff = $modelTariff->getItem($tariffId);
		$this->reservationDetails = $this->app->getUserState($this->context);
		$childMaxAge = $params->get('child_max_age_limit', 17);

		$form = new JLayoutFile('asset.roomtypeform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''));

		$displayData = array(
			'assetId' => $raId,
			'roomTypeId' => $roomTypeId,
			'tariffId' => $tariffId,
			'quantity' => $quantity,
			'roomType' => $roomType,
			'reservationDetails' => $this->reservationDetails,
			'extras' => $extras,
			'childMaxAge' => $childMaxAge,
			'tariff' => $tariff,
			'adjoiningLayer' => $adjoiningLayer
		);

		echo $form->render($displayData);
		$this->app->close();
	}

	/**
	 * Get the availability calendar
	 *
	 * The number of months to be displayed in configured in component's options
	 *
	 * @return string
	 */
	public function getAvailabilityCalendar()
	{
		JLoader::register('SRCalendar', SRPATH_LIBRARY . '/utilities/calendar.php');
		$roomTypeId = $this->input->get('id', 0, 'int');
		$params = JComponentHelper::getParams('com_solidres');
		$weekStartDay = $params->get('week_start_day', 1) == 1 ? 'monday' : 'sunday' ;

		$calendar = new SRCalendar(array('start_day' => $weekStartDay));
		$html = '';
		$html .= '<span class="legend-busy"></span> ' . JText::_('SR_AVAILABILITY_CALENDAR_BUSY');
		//$html .= '<span class="legend-provisional"></span> ' . JText::_('SR_AVAILABILITY_CALENDAR_PROVISIONAL');
		$period = $params->get('availability_calendar_month_number', 6);
		for ($i = 0; $i < $period; $i ++)
		{
			if ($i % 3 == 0 && $i == 0)
			{
				$html .= '<div class="' . SR_UI_GRID_CONTAINER . '">';
			}
			else if ($i % 3 == 0)
			{
				$html .= '</div><div class="' . SR_UI_GRID_CONTAINER . '">';
			}

			$year = date('Y', strtotime('first day of this month +' . $i . ' month'));
			$month = date('n', strtotime('first day of this month +' . $i . ' month'));
			$html .= '<div class="'. SR_UI_GRID_COL_4 .'">' . $calendar->generate($year, $month, $roomTypeId ) . '</div>';
		}

		echo $html;

		$this->app->close();
	}

	public function getCheckInOutForm()
	{
		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$systemConfig = JFactory::getConfig();
		$tariffId = $this->input->getUInt('tariff_id', 0);
		$roomtypeId = $this->input->getUInt('roomtype_id', 0);
		$assetId = $this->input->getUInt('id', 0);
		$itemId = $this->input->getUInt('Itemid', 0);
		$modelTariff = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));
		$tariff = $modelTariff->getItem($tariffId);
		$this->reservationDetails = $this->app->getUserState($this->context);
		$tzoffset = $systemConfig->get('offset');
		$timezone = new DateTimeZone($tzoffset);
		$checkin = isset($this->reservationDetails->checkin) ? $this->reservationDetails->checkin : NULL;
		$checkout = isset($this->reservationDetails->checkout) ? $this->reservationDetails->checkout : NULL;
		$datePickerMonthNum = $solidresConfig->get('datepicker_month_number', 3);
		$weekStartDay = $solidresConfig->get('week_start_day', 1);
		$currentSelectedTariffs = $this->app->getUserState($this->context.'.current_selected_tariffs');
		$currentSelectedTariffs[$roomtypeId][] = $tariffId;
		$this->app->setUserState($this->context.'.current_selected_tariffs', $currentSelectedTariffs);
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
		$jsDateFormat = SRUtilities::convertDateFormatPattern($dateFormat);
		$bookingType = $this->app->getUserState($this->context.'.booking_type');

		$form = new JLayoutFile('asset.checkinoutform' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''));
		$displayData = array(
			'tariff' => $tariff,
			'assetId' => $assetId,
			'roomTypeId' => $roomtypeId,
			'checkin' => $checkin,
			'checkout' => $checkout,
			'minDaysBookInAdvance' => $solidresConfig->get('min_days_book_in_advance', 0),
			'maxDaysBookInAdvance' => $solidresConfig->get('max_days_book_in_advance', 0),
			'minLengthOfStay' => $solidresConfig->get('min_length_of_stay', 1),
			'timezone' => $timezone,
			'itemId' => $itemId,
			'datePickerMonthNum' => $datePickerMonthNum,
			'weekStartDay' => $weekStartDay,
			'dateFormat' => $dateFormat, // default format d-m-y
			'jsDateFormat' => $jsDateFormat,
			'bookingType' => $bookingType
		);

		echo $form->render($displayData);
		$this->app->close();
	}

	public function getCheckInOutFormChangeDates()
	{
		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$systemConfig = JFactory::getConfig();
		$tariffId = $this->input->getUInt('tariff_id', 0);
		$roomtypeId = $this->input->getUInt('roomtype_id', 0);
		$assetId = $this->input->getUInt('id', 0);
		$itemId = $this->input->getUInt('Itemid', 0);
		$return = $this->input->getString('return', '');
		$reservationId = $this->input->getUInt('reservation_id', 0);
		$modelTariff = JModelLegacy::getInstance('Tariff', 'SolidresModel', array('ignore_request' => true));
		$tariff = $modelTariff->getItem($tariffId);
		$this->reservationDetails = $this->app->getUserState($this->context);
		$tzoffset = $systemConfig->get('offset');
		$timezone = new DateTimeZone($tzoffset);
		/*$checkin = isset($this->reservationDetails->checkin) ? $this->reservationDetails->checkin : NULL;
		$checkout = isset($this->reservationDetails->checkout) ? $this->reservationDetails->checkout : NULL;*/
		$checkin = $this->input->getString('checkin', '');
		$checkout = $this->input->getString('checkout', '');

		$datePickerMonthNum = $solidresConfig->get('datepicker_month_number', 3);
		$weekStartDay = $solidresConfig->get('week_start_day', 1);
		$currentSelectedTariffs = $this->app->getUserState($this->context.'.current_selected_tariffs');
		$currentSelectedTariffs[$roomtypeId][] = $tariffId;
		$this->app->setUserState($this->context.'.current_selected_tariffs', $currentSelectedTariffs);
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
		$jsDateFormat = SRUtilities::convertDateFormatPattern($dateFormat);

		$form = new JLayoutFile('asset.changedates');
		$displayData = array(
			'tariff' => $tariff,
			'assetId' => $assetId,
			'checkin' => $checkin,
			'checkout' => $checkout,
			'minDaysBookInAdvance' => $solidresConfig->get('min_days_book_in_advance', 0),
			'maxDaysBookInAdvance' => $solidresConfig->get('max_days_book_in_advance', 0),
			'minLengthOfStay' => $solidresConfig->get('min_length_of_stay', 1),
			'timezone' => $timezone,
			'itemId' => $itemId,
			'reservationId' => $reservationId,
			'datePickerMonthNum' => $datePickerMonthNum,
			'weekStartDay' => $weekStartDay,
			'dateFormat' => $dateFormat, // default format d-m-y
			'jsDateFormat' => $jsDateFormat,
			'return' => $return
		);

		echo $form->render($displayData);
		$this->app->close();
	}


	public function startOver()
	{
		$id = $this->input->getUint('id');
		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$enableAutoScroll = $solidresConfig->get('enable_auto_scroll', 1);

		$this->app->setUserState($this->context . '.room', NULL);
		$this->app->setUserState($this->context . '.extra', NULL);
		$this->app->setUserState($this->context . '.guest', NULL);
		/*$this->app->setUserState($this->context . '.payment', NULL);*/
		$this->app->setUserState($this->context . '.discount', NULL);
		$this->app->setUserState($this->context . '.deposit', NULL);
		$this->app->setUserState($this->context . '.coupon', NULL);
		$this->app->setUserState($this->context . '.token', NULL);
		$this->app->setUserState($this->context . '.cost', NULL);
		$this->app->setUserState($this->context . '.checkin', NULL);
		$this->app->setUserState($this->context . '.checkout', NULL);
		$this->app->setUserState($this->context . '.room_type_prices_mapping', NULL);
		$this->app->setUserState($this->context . '.selected_room_types', NULL);
		$this->app->setUserState($this->context . '.reservation_asset_id', NULL);
		$this->app->setUserState($this->context . '.current_selected_tariffs', NULL);
		$this->app->setUserState($this->context . '.room_opt', NULL);

		$this->setRedirect(JRoute::_('index.php?option=com_solidres&view=reservationasset&id='.$id. ( $enableAutoScroll ? '#form' : '' ), false));
	}
}