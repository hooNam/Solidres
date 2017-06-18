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
 * Reservation Asset table
 *
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.1.0
 */
class SolidresTableReservationAsset extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_reservation_assets', 'id', $db);

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

		if (isset($array['metadata']) && is_array($array['metadata']))
        {
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}
		return parent::bind($array, $ignore);
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
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');

		// Check to see if it contains any Room Types, if yes then notify user to delete all of its Room Type first
		$query = $this->_db->getQuery(true);
		$query->select('name')->from($this->_db->quoteName('#__sr_reservation_assets'))->where('id = '.$pk);
		$this->_db->setQuery($query);
		$assetName = $this->_db->loadResult();
		
		$query->clear();
		$query->select('COUNT(id)')->from($this->_db->quoteName('#__sr_room_types'))->where('reservation_asset_id = '.$pk);
		$this->_db->setQuery($query);
		$result = (int) $this->_db->loadResult();
		if($result > 0)
		{
			$e = new JException(JText::sprintf('SR_ERROR_RESERVATION_CONTAIN_ROOM_TYPE', $assetName));
			$this->setError($e);
			return false;
		}
		
		// Take care of Reservation
		$query->clear();
		$query->update($this->_db->quoteName('#__sr_reservations'))
			  ->set($this->_db->quoteName('reservation_asset_id') . ' = NULL')
			  ->where($this->_db->quoteName('reservation_asset_id') .' = '.(int) $pk );
		$this->_db->setQuery($query)->execute();

		// Take care of media, if it has any, remove all of them
		$query->clear();
		$query->delete('')->from($this->_db->quoteName('#__sr_media_reservation_assets_xref'))->where('reservation_asset_id = '.$pk);
		$this->_db->setQuery($query)->execute();

		// Take care of Extra
		$extrasModel = JModelLegacy::getInstance('Extras', 'SolidresModel', array('ignore_request' => true));
		$extraModel = JModelLegacy::getInstance('Extra', 'SolidresModel', array('ignore_request' => true));
		$extrasModel->setState('filter.reservation_asset_id', $pk);
		$extras = $extrasModel->getItems();

		foreach ($extras as $extra)
		{
			$extraModel->delete($extra->id);
		}

		// Take care of Coupon
		$couponsModel = JModelLegacy::getInstance('Coupons', 'SolidresModel', array('ignore_request' => true));
		$couponModel = JModelLegacy::getInstance('Coupon', 'SolidresModel', array('ignore_request' => true));
		$couponsModel->setState('filter.reservation_asset_id', $pk);
		$coupons = $couponsModel->getItems();

		foreach ($coupons as $coupon)
		{
			$couponModel->delete($coupon->id);
		}

		// Take care of Custom Fields
		$query->clear();
		$query->delete('')->from($this->_db->quoteName('#__sr_reservation_asset_fields'))->where('reservation_asset_id = '.$pk);
		$this->_db->setQuery($query)->execute();

		if (SRPlugin::isEnabled('hub'))
		{
			// Take care of Themes
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_reservation_asset_theme_xref'))->where('reservation_asset_id = '.$pk);
			$this->_db->setQuery($query)->execute();

			// Take care of Facilities
			$query->clear();
			$query->delete($this->_db->quoteName('#__sr_facility_reservation_asset_xref'))->where('reservation_asset_id = '.$pk);
			$this->_db->setQuery($query)->execute();
		}

		// Take care of Limit Booking
		if (SRPlugin::isEnabled('limitbooking'))
		{
			$limitBookingsModel = JModelLegacy::getInstance('LimitBookings', 'SolidresModel', array('ignore_request' => true));
			$limitBookingModel = JModelLegacy::getInstance('LimitBooking', 'SolidresModel', array('ignore_request' => true));
			$limitBookingsModel->setState('filter.reservation_asset_id', $pk);
			$limitBookings = $limitBookingsModel->getItems();

			foreach ($limitBookings as $limitBooking)
			{
				$limitBookingModel->delete($limitBooking->id);
			}
		}

		// Delete itself, finally
		return parent::delete($pk);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return	string
	 * @since	1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_solidres.reservationasset.'.(int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return	string
	 * @since	1.6
	 */
	protected function _getAssetTitle()
	{
		return $this->name;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// Initialise variables.
		$assetId = null;
		$db = $this->getDbo();

		// This is a reservation asset under a category.
		if ($this->category_id) {
			// Build the query to get the asset id for the parent category.
			$query	= $db->getQuery(true);
			$query->select('asset_id')->from($db->quoteName('#__categories'))->where('id = '.(int) $this->category_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult()) {
				$assetId = (int) $result;
			}
		}
		// This is an uncategorized article that needs to parent with the extension.
		elseif ($assetId === null) {
			// Build the query to get the asset id for the parent category.
			$query	= $db->getQuery(true);
			$query->select('id')->from($db->quoteName('#__assets'))->where('name = '.$db->quote('com_solidres'));

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult()) {
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId) {
			return $assetId;
		} else {
			return parent::_getAssetParentId($table, $id);
		}
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
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->modified_date = $date->toSql();
		$this->name          = str_replace('"', "'", $this->name);

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

		// Only 1 asset can be set as default
		if ($this->default == 1)
		{
			$query = $this->_db->getQuery(true)
			                   ->update($this->_db->quoteName($this->_tbl))
			                   ->set($this->_db->quoteName('default') . ' = 0');
			if ($this->id)
			{
				$query->where('id <> ' . $this->id);
			}
			$this->_db->setQuery($query)->execute();
		}

		$k = $this->_tbl_keys;

		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeStore', array($updateNulls, $k));

		$currentAssetId = 0;

		if (!empty($this->asset_id))
		{
			$currentAssetId = $this->asset_id;
		}

		// The asset id field is managed privately by this class.
		if ($this->_trackAssets)
		{
			unset($this->asset_id);
		}

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$result = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, $updateNulls);
		}
		else
		{
			$result = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys[0]);
		}

		// If the table is not set to track assets return true.
		if ($this->_trackAssets)
		{
			if ($this->_locked)
			{
				$this->_unlock();
			}

			/*
			 * Asset Tracking
			 */
			$parentId = $this->_getAssetParentId();
			$name     = $this->_getAssetName();
			$title    = $this->_getAssetTitle();

			$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->asset_id = $asset->id;

			// Custom fix to fix the NULL storage bug
			unset($asset->alias);

			// Check for an error.
			$error = $asset->getError();

			if ($error)
			{
				$this->setError($error);

				return false;
			}
			else
			{
				// Specify how a new or moved node asset is inserted into the tree.
				if (empty($this->asset_id) || $asset->parent_id != $parentId)
				{
					$asset->setLocation($parentId, 'last-child');
				}

				// Prepare the asset to be stored.
				$asset->parent_id = $parentId;
				$asset->name      = $name;
				$asset->title     = $title;

				if ($this->_rules instanceof JAccessRules)
				{
					$asset->rules = (string) $this->_rules;
				}

				if (!$asset->check() || !$asset->store($updateNulls))
				{
					$this->setError($asset->getError());

					return false;
				}
				else
				{
					// Create an asset_id or heal one that is corrupted.
					if (empty($this->asset_id) || ($currentAssetId != $this->asset_id && !empty($this->asset_id)))
					{
						// Update the asset_id field in this table.
						$this->asset_id = (int) $asset->id;

						$query = $this->_db->getQuery(true)
							->update($this->_db->quoteName($this->_tbl))
							->set('asset_id = ' . (int) $this->asset_id);
						$this->appendPrimaryKeys($query);
						$this->_db->setQuery($query)->execute();
					}
				}
			}
		}

		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterStore', array(&$result));

		return $result;
	}
}

