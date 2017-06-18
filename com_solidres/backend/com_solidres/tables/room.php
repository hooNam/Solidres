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
 * Room table
 *
 * @package     Solidres
 * @subpackage	Room
 * @since		0.1.0
 */
class SolidresTableRoom extends JTable
{
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_rooms', 'id', $db);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param	mixed	An optional primary key value to delete.  If not set the
	 *					instance property value is used.
	 * @return	boolean	True on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		$query = $this->_db->getQuery(true);

		// Take care of relationship with Reservation
		$query->update('#__sr_reservation_room_xref')
			  ->set('room_id = NULL')
			  ->where('room_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of relationship with Extra in Reservation
		$query->clear();
		$query->update('#__sr_reservation_room_extra_xref')
		 	  ->set('room_id = NULL')
			  ->where('room_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		if (SRPlugin::isEnabled('limitbooking'))
		{
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_limit_booking_details'))->where('room_id = ' . (int) $pk);
			$this->_db->setQuery($query)->execute();
		}

		if (SRPlugin::isEnabled('housekeeping'))
		{
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_housekeeping'))->where('room_id = ' . (int) $pk);
			$this->_db->setQuery($query)->execute();
		}

		// Delete it
		return parent::delete($pk);
	}
}

