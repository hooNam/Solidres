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
 * RoomType table
 *
 * @package     Solidres
 * @subpackage	RoomType
 * @since		0.1.0
 */
class SolidresTableRoomType extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_room_types', 'id', $db);

		$this->setColumnAlias('published', 'state');
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

		// Delete all rooms belong to it
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
		$query->clear();
		$query->select('*')->from($this->_db->quoteName('#__sr_rooms'))->where('room_type_id = '. $this->_db->quote($pk));
		$rooms = $this->_db->setQuery($query)->loadObjectList();
		$roomModel = JModelLegacy::getInstance('Room', 'SolidresModel');

		foreach ($rooms as $room)
		{
			$roomModel->delete($room->id);
		}

		// Delete all coupons relation
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_room_type_coupon_xref'))->where('room_type_id = '. $this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete all extras relation
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_room_type_extra_xref'))->where('room_type_id = '. $this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete all custom fields
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_room_type_fields'))->where('room_type_id = '. $this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete all media
		$query->clear();
		$query->delete($this->_db->quoteName('#__sr_media_roomtype_xref'))->where('room_type_id = '. $this->_db->quote($pk));
		$this->_db->setQuery($query)->execute();

		// Delete all tariffs
		$query->clear();
		$query->select('id')->from($this->_db->quoteName('#__sr_tariffs'))->where('room_type_id = '. $this->_db->quote($pk));
		$tariffs = $this->_db->setQuery($query)->loadAssocList();
		$modelTariff = JModelLegacy::getInstance('Tariff', 'SolidresModel');
		foreach ($tariffs as $tariff)
		{
			$modelTariff->delete($tariff['id']);
		}

		// Delete related facilities
		if (SRPlugin::isEnabled('hub'))
		{
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_facility_room_type_xref'))->where('room_type_id = '. $this->_db->quote($pk));
			$this->_db->setQuery($query)->execute();
		}

		// Delete itself
		return parent::delete($pk);
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