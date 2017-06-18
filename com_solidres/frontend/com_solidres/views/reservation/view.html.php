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

/**
 * Reservation view class
 *
 * @package     Solidres
 * @since		0.1.0
 */
class SolidresViewReservation extends JViewLegacy
{
	public $reservation = NULL;
    function display($tpl = null)
	{
		$this->context = 'com_solidres.reservation.process';
		$this->config = JComponentHelper::getParams('com_solidres');
		$this->showPoweredByLink = $this->config->get('show_solidres_copyright', '1');
		$this->app = JFactory::getApplication();
		$this->id = $this->app->input->getUint('id', 0);
		$this->code = $this->app->input->getString('code', '');

		if ($this->id > 0 && !empty($this->code))
		{
			JModelLegacy::addIncludePath( JPATH_COMPONENT_ADMINISTRATOR . '/models/');
			$reservatonModel = JModelLegacy::getInstance('Reservation', 'SolidresModel', array('ignore_request' => true));
			$assetModel = JModelLegacy::getInstance('ReservationAsset', 'SolidresModel', array('ignore_request' => true));
			$reservation = $reservatonModel->getItem($this->id);
			$this->asset = NULL;
			if ($reservation->code == $this->code)
			{
				$this->reservation = $reservation;
				$this->asset = $assetModel->getItem($this->reservation->reservation_asset_id);
				JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
				$this->lengthOfStay = (int)SRUtilities::calculateDateDiff(
					$this->reservation->checkin,
					$this->reservation->checkout
				);
			}
		}

		$this->layout = $this->app->input->getString('layout', '');
		if ($this->layout == 'final')
		{
			$dispatcher = JEventDispatcher::getInstance();
			$result = $dispatcher->trigger('onSolidresReservationFinalScreenDisplay', array($this->app->getUserState($this->context.'.code')));
		}

		JHtml::stylesheet('com_solidres/assets/main.css', false, true, false);

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
    }
}
