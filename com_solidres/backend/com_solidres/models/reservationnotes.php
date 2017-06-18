<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

/**
 * ReservationNotes model
 *
 * @package     Solidres
 * @subpackage	ReservationNote
 * @since		0.3.0
 */
class SolidresModelReservationNotes extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.*, u.username as username')
		);
		$query->from($db->quoteName('#__sr_reservation_notes').' AS a');

		$query->join('LEFT', '#__users as u ON a.created_by = u.id');

		// Filter by reservation.
		$reservationId = $this->getState('filter.reservation_id');
		if (is_numeric($reservationId))
        {
			$query->where('a.reservation_id = '.(int) $reservationId);
		}

		// Filter by visibility in front end
		$visibleInFrontend = $this->getState('filter.visible_in_frontend');
		if (is_numeric($visibleInFrontend))
		{
			$query->where('a.visible_in_frontend = 1');
		}

		$query->order('a.created_date ASC');

		return $query;
	}
}