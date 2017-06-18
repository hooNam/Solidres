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
 * Extra table
 *
 * @package     Solidres
 * @subpackage	Extra
 * @since		0.1.0
 */
class SolidresTableExtra extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_extras', 'id', $db);

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
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		$query = $this->_db->getQuery(true);

		// Take care of relationshop with Reservation and Room
		$query->update($this->_db->quoteName('#__sr_reservation_room_extra_xref'))
			  ->set('extra_id = NULL')
			  ->where('extra_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of relationship with Room Type
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_room_type_extra_xref'))->where('extra_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Take care of relationship with Reservation Extra (for Extra that apply per reservation)
		$query->clear();
		$query->update($this->_db->quoteName('#__sr_reservation_extra_xref'))
			->set('extra_id = NULL')
			->where('extra_id = '.$this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete itself
		return parent::delete($pk);
	}

	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->modified_date = $date->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_by	= $user->get('id');
		}
		else
		{
			if (!(int) $this->created_date)
			{
				$this->created_date = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}
}

