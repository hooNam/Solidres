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
 * Reservation table
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresTableReservation extends JTable
{
    function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_reservations', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overload the store method
	 *
	 * @param	boolean	$updateNulls Toggle whether null values should be updated.
	 * @return	boolean	True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->id)
		{
			$this->modified_date	= $date->toSql();
			$this->modified_by		= $user->get('id');
		}
		else
		{
			if (!intval($this->created_date))
			{
				$this->created_date = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		if (empty($this->code))
		{
			$this->code = SRFactory::get('solidres.reservation.reservation')->getCode($this->created_date);
		}

		// Prepare some NULL value
		if (empty($this->coupon_id))
		{
			$this->coupon_id = NULL;
		}

		if (empty($this->customer_id))
		{
			$this->customer_id = NULL;
		}

		if (empty($this->accessed_date))
		{
			$this->accessed_date = '0000-00-00 00:00:00';
		}

		$this->checkin 	= JFactory::getDate($this->checkin)->toSql();
		$this->checkout = JFactory::getDate($this->checkout)->toSql();
				
		// Attempt to store the user data.
		return parent::store($updateNulls);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param	mixed	$pk An optional primary key value to delete.  If not set the
	 *					instance property value is used.
	 * @return	boolean	True on success.
	 * @since	0.1.0
	 * @link	http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		$query = $this->_db->getQuery(true);

		// Take care of relationship with Room in Reservation
		$query->select('id')->from($this->_db->quoteName('#__sr_reservation_room_xref'))->where('reservation_id = '.$this->_db->quote($pk));
		$reservationRoomIds = $this->_db->setQuery($query)->loadColumn();

		foreach ($reservationRoomIds as $reservationRoomId)
		{
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_reservation_room_details'))->where('reservation_room_id = '.$this->_db->quote($reservationRoomId));
			$this->_db->setQuery($query)->execute();
		}

		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_reservation_room_xref'))->where('reservation_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of relationship with Room and Extra in Reservation
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_reservation_room_extra_xref'))->where('reservation_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of Reservation Notes
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_reservation_notes'))->where('reservation_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of Reservation's Per booking extra items
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_reservation_extra_xref'))->where('reservation_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of related Invoices
		if(SRPlugin::isEnabled('invoice'))
		{
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_invoices'))->where('reservation_id = '.$this->_db->quote($pk));
			$this->_db->setQuery($query)->execute();
		}

		if (SRPlugin::isEnabled('feedback'))
		{
			JTable::addIncludePath(SR_PLUGIN_FEEDBACK_ADMINISTRATOR.'/tables');
			$tableFeedback = JTable::getInstance( 'Feedback', 'SolidresTable' );
			if ( $tableFeedback->load( array( 'reservation_id' => $pk ) ) )
			{
				$fId = (int) $tableFeedback->get( 'id' );
				$tableFeedback->delete( $fId );
			}
		}

		if (SRPlugin::isEnabled('customfield'))
		{
			SRCustomFieldHelper::cleanValues(array('context' => 'com_solidres.customer.' . $pk));
		}
		
		// Delete itself
		return parent::delete($pk);
	}

	public function recordAccess($pk)
	{
		$accessedDate = JFactory::getDate()->toSql();
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set($this->_db->quoteName($this->getColumnAlias('accessed_date')) . ' = ' . $this->_db->quote($accessedDate))
			->where($this->_db->quoteName('id') . ' = ' . (int) $pk );
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Set table values in the object.
		$this->accessed_date = $accessedDate;

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @link    https://docs.joomla.org/JTable/load
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		$result= parent::load($keys, $reset);

		if ($result && SRPlugin::isEnabled('channelmanager'))
		{
			JLoader::register('plgSolidresChannelManager', SRPATH_LIBRARY . '/channelmanager/channelmanager.php');

			if (isset(plgSolidresChannelManager::$channelKeyMapping[$this->origin]))
			{
				$this->origin = plgSolidresChannelManager::$channelKeyMapping[$this->origin];
			}
		}

		return $result;
	}
}

