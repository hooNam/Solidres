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

JLoader::register('SolidresHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/helper.php');
JLoader::register('SolidresControllerReservationBase', JPATH_COMPONENT_ADMINISTRATOR.'/controllers/reservationbase.php');
JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

/**
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresControllerReservation extends SolidresControllerReservationBase
{
	public function __construct($config = array())
	{
		$this->view_item = 'reservation';
		$this->view_list = 'reservations';
		parent::__construct($config);
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
		$model = $this->getModel();
		$resTable = JTable::getInstance('Reservation', 'SolidresTable');
		$hubDashboard = $this->app->getUserState($this->context.'.hub_dashboard');
		$isGuestMakingReservation = $this->app->isSite() && !$hubDashboard;
		$assetId = $this->input->getUInt('id', 0);
		$sendOutgoingEmails = true;

		if (!$isGuestMakingReservation && SRUtilities::isAssetPartner(JFactory::getUser()->get('id'), $assetId))
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

		if(!$model->save($this->reservationData))
		{
			// Fail, turn back and correct
			$msg = JText::_(' SR_RESERVATION_SAVE_ERROR');
			$returnUrl = 'index.php?option=com_solidres&Itemid='.$this->app->getUserState($this->context . '.activeItemId').
			            '&task=reservationasset.checkavailability&id='.$this->reservationData['reservation_asset_id'].
			            '&checkin='.$this->reservationData['checkin'].
			            '&checkout='.$this->reservationData['checkout'];

			$roomsOccupancyOptions = $this->app->getUserState($this->context.'.room_opt');
			if (count($roomsOccupancyOptions) > 0)
			{
				for ($r = 1, $rCount = count($roomsOccupancyOptions); $r <= $rCount; $r++)
				{
					$returnUrl .=
						"&room_opt[$r][adults]={$roomsOccupancyOptions[$r]['adults']}".
						"&room_opt[$r][children]={$roomsOccupancyOptions[$r]['children']}";
				}
			}

			$returnUrl = JRoute::_($returnUrl, false);
			$this->setRedirect($returnUrl, $msg);
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

			if ($hubDashboard == 0)
			{
				if ($resTable->payment_method_id != 'paylater' && $resTable->payment_method_id != 'bankwire')
				{
					// Run payment plugin here
					JPluginHelper::importPlugin('solidrespayment', $resTable->payment_method_id);
					$responses = $this->app->triggerEvent('OnSolidresPaymentNew', array( $resTable ));
					$document = JFactory::getDocument();
					$viewType = $document->getType();
					$viewName = 'Reservation';
					$viewLayout = 'payment';

					$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
					if (!empty($responses))
					{
						foreach ($responses as $response)
						{
							if ($response === false) continue;
							$view->paymentForm = $response;
						}
					}

					if (!empty($view->paymentForm))
					{
						$view->display();
					}
					else
					{
						$link = JRoute::_('index.php?option=com_solidres&task=reservation.finalize&reservation_id='.$savedReservationId, false);
						$this->setRedirect($link);
					}
				}
				else
				{
					$link = JRoute::_('index.php?option=com_solidres&task=reservation.finalize&reservation_id='.$savedReservationId, false);
					$this->setRedirect($link);
				}
			}
			else
			{
				$processOnlinePayment = isset($reservationGuest['processonlinepayment']) ?
											$reservationGuest['processonlinepayment'] : 0;
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

				// Redirect to the list screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);
			}
		}
	}

	/**
	 * Finalize the reservation process
	 *
	 * @since  0.3.0
	 *
	 * @return void
	 */
	public function finalize()
	{
		JPluginHelper::importPlugin('solidrespayment');
		$reservationId = $this->input->get('reservation_id', 0, 'int');
		$results = $this->app->triggerEvent('OnReservationFinalize', array($this->context, &$reservationId));

		$savedReservationId = $this->app->getUserState($this->context.'.savedReservationId');

		$activeItemId = $this->app->getUserState($this->context . '.activeItemId');

		if ($savedReservationId == $reservationId)
		{
			$msg = $this->sendEmail();

			if (!is_string($msg))
			{
				$msg = NULL;
			}

			// Done, we do not need these data, wipe them !!!
			$this->app->setUserState($this->context . '.room', NULL);
			$this->app->setUserState($this->context . '.extra', NULL);
			$this->app->setUserState($this->context . '.guest', NULL);
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

			$link = JRoute::_('index.php?option=com_solidres&view=reservation&layout=final&Itemid='.$activeItemId, false);
			$this->setRedirect($link, $msg);
		}
	}

	public function paymentcallback()
	{
		$callbackData = $this->input->getArray($_REQUEST);
		JPluginHelper::importPlugin('solidrespayment', $callbackData['payment_method_id']);

		$responses = $this->app->triggerEvent('OnSolidresPaymentCallback', array(
			$callbackData['payment_method_id'],
			$callbackData
		));
	}
}