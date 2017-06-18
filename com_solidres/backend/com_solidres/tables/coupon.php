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
 * Coupon table
 *
 * @package     Solidres
 * @subpackage	Coupon
 * @since		0.1.0
 */
class SolidresTableCoupon extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_coupons', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		$array Named array
	 * @param   string 		$ignore
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param	mixed	An optional primary key value to delete.  If not set the
	 *					instance property value is used.
	 * @return	boolean	True on success.
	 * @since	0.3.0
	 * @link	http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		$query = $this->_db->getQuery(true);

		// Take care of left over in Reservation table
		$query->update($this->_db->quoteName('#__sr_reservations'))
			  ->set('coupon_id = NULL')
			  ->where('coupon_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of left over in any relationships with Room Type
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_room_type_coupon_xref'))->where('coupon_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete it
		return parent::delete($pk);
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 */
	public function check()
	{
		$query = $this->_db->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__sr_coupons')
			->where('coupon_code = '. $this->_db->quote($this->coupon_code))
			->where('id <> ' . (int) $this->id );

		$count = $this->_db->setQuery($query)->loadResult();

		if ($count > 0)
		{
			$this->setError(JText::_('SR_DUPLICATE_COUPON_CODE'));
			return false;
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		JFactory::getDate($this->valid_from)->toSql();
		$this->valid_from = JFactory::getDate($this->valid_from)->toSql();
		$this->valid_to = JFactory::getDate($this->valid_to)->toSql();
		$this->valid_from_checkin = JFactory::getDate($this->valid_from_checkin)->toSql();
		$this->valid_to_checkin = JFactory::getDate($this->valid_to_checkin)->toSql();
		return parent::store($updateNulls);
	}
}

