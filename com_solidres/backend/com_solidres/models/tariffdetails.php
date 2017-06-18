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
 * Tariff model
 *
 * @package     Solidres
 * @subpackage	TariffDetails
 * @since		0.1.0
 */
class SolidresModelTariffDetails extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	$config An optional associative array of configuration settings.
     * @see		JController
     * @since	1.6
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * Override the default function since we need to generate different store id for
     * different data set depended on room type id
     *
     * @see     \components\com_solidres\models\reservation.php (181 ~ 186)
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   11.1
     */
    protected function getStoreId($id = '')
    {
        // Add the list state to the store id.
		$id .= ':' . $this->getState('filter.tariff_id');
		$id .= ':' . $this->getState('filter.guest_type');

        return md5($this->context . ':' . $id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $query->select( $this->getState('list.select', 't.*' ));
        $query->from($dbo->quoteName('#__sr_tariff_details').' AS t');
		$tariffId = $this->getState('filter.tariff_id', NULL);
		$guestType = $this->getState('filter.guest_type', NULL);
	    $tariffMode = $this->getState('filter.tariff_mode', 0);

		if (isset($tariffId))
		{
			$query->where('t.tariff_id = '.(int) $tariffId);
		}

		if (isset($guestType))
		{
			$query->where('t.guest_type = '.$dbo->quote($guestType));
		}

		if ($tariffMode == 0)
		{
			$query->order('w_day ASC');
		}
	    else if ($tariffMode == 1)
	    {
		    $query->order('date ASC');
	    }

        return $query;
    }

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();
		$forCalc = $this->getState('filter.for_calc', 0);
		$tariffMode = $this->getState('filter.tariff_mode', 0);

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			if ($forCalc == 1 && $tariffMode == 1)
			{
				$items = $this->_getListWithKey($query, $this->getStart(), $this->getState('list.limit'), 'date');
			}
			else
			{
				$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	protected function _getListWithKey($query, $limitstart = 0, $limit = 0, $key = '')
	{
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList($key);

		return $result;
	}
}
