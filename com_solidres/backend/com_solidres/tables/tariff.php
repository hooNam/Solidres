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
 * Tariff table
 *
 * @package     Solidres
 * @subpackage	Tariff
 * @since		0.1.0
 */
class SolidresTableTariff extends JTable
{
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_tariffs', 'id', $db);
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

		// Delete all tariff's details
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_tariff_details'));
		$query->where('tariff_id  = ' . $this->_db->quote($pk) );
		$this->_db->setQuery($query)->execute();

		// Take care of relationship with Reservation
		$query->clear();
		$query->update($this->_db->quoteName('#__sr_reservation_room_xref'))
			->set('tariff_id = NULL')
			->where('tariff_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete itself
		return parent::delete($pk);
	}
}

