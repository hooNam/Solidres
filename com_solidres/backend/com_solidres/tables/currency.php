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
 * Currency table
 *
 * @package     Solidres
 * @subpackage	Currency
 * @since		0.1.0
 */
class SolidresTableCurrency extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_currencies', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	public function check()
	{
		if ($this->exchange_rate == 0)
		{
			$this->setError(JText::_('SR_CURRENCY_RATE_CAN_NOT_0'));
			return false;
		}

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param	mixed	$pk An optional primary key value to delete.  If not set the
	 *					instance property value is used.
	 * @return	boolean	True on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		// Check for relationship with price table
		$query = $this->_db->getQuery(true);
		$query->select('count(*)')
			->from($this->_db->quoteName('#__sr_tariffs'))
			->where($this->_db->quoteName('currency_id' ) . '=' . (int) $this->id);
		$this->_db->setQuery($query);

		if ($this->_db->loadResult() >= 1)
		{
			$this->setError(JText::_('SR_ERROR_CURRENCY_HAS_RELATIONSHIP_WITH_PRICE_TABLE'));
			return false;
		}

		// Check for relationshop with Asset
		$query->clear();
		$query->select('count(*)')
			->from($this->_db->quoteName('#__sr_reservation_assets'))
			->where($this->_db->quoteName('currency_id' ) . '=' . (int) $this->id);
		$this->_db->setQuery($query);

		if ($this->_db->loadResult() >= 1)
		{
			$this->setError(JText::_('SR_ERROR_CURRENCY_HAS_RELATIONSHIP_WITH_RESERVATION_ASSET_TABLE'));
			return false;
		}

		// Set all foreign key to NULL in table reservation
		$query->clear();
		$query->update($this->_db->quoteName('#__sr_reservations'))
			->set($this->_db->quoteName('currency_id') . ' = NULL' )
			->where($this->_db->quoteName('currency_id') .' = ' . (int) $this->id);
		$this->_db->setQuery($query)->execute();

		return parent::delete($pk);
	}
}

