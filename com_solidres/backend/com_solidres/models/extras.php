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
 * Extras model
 *
 * @package     Solidres
 * @subpackage	Extra
 * @since		0.1.0
 */
class SolidresModelExtras extends JModelList
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
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'state', 'a.state',
				'created_date', 'a.created_date',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'price', 'a.price',
				'reservation_asset_id', 'a.reservation_asset_id',
			);
		}

		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);

		$reservationAssetId = $app->getUserStateFromRequest($this->context.'.filter.reservation_asset_id', 'filter_reservation_asset_id', '');
		$this->setState('filter.reservation_asset_id', $reservationAssetId);
		
		// List state information.
		parent::populateState('a.name', 'asc');
	}

	public function getItems()
	{
		$items = parent::getItems();

		if (!empty($items))
		{
			// TODO replace this manual call with autoloading later
			JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');

			$showTaxIncl = $this->getState('filter.show_price_with_tax', 0);
			$assetTable = JTable::getInstance('ReservationAsset', 'SolidresTable');
			$taxTable = JTable::getInstance('Tax', 'SolidresTable');
			// Caching to prevent unnecessary multiple loading
			$assetTable->load($items[0]->reservation_asset_id);
			$solidresCurrency = new SRCurrency(0, $assetTable->currency_id);

			foreach ($items as $item)
			{
				if ($assetTable->id != $item->reservation_asset_id)
				{
					$assetTable->load($item->reservation_asset_id);
					$solidresCurrency = new SRCurrency(0, $assetTable->currency_id);
				}

				$taxTable->reset();
				if (isset($item->tax_id))
				{
					$taxTable->load($item->tax_id);
				}

				$taxAmount = 0;
				$taxAdultAmount = 0;
				$taxChildAmount = 0;
				if (!empty($taxTable->rate))
				{
					$taxAmount = $item->price * $taxTable->rate;
					$taxAdultAmount = $item->price_adult * $taxTable->rate;
					$taxChildAmount = $item->price_child * $taxTable->rate;
				}

				// For charge type != per person
				$item->currencyTaxIncl = clone $solidresCurrency;
				$item->currencyTaxExcl = clone $solidresCurrency;
				$item->currencyTaxIncl->setValue($item->price + $taxAmount);
				$item->currencyTaxExcl->setValue($item->price);
				$item->price_tax_incl = $item->price + $taxAmount;
				$item->price_tax_excl = $item->price;

				// For adult
				$item->currencyAdultTaxIncl = clone $solidresCurrency;
				$item->currencyAdultTaxExcl = clone $solidresCurrency;
				$item->currencyAdultTaxIncl->setValue($item->price_adult + $taxAdultAmount);
				$item->currencyAdultTaxExcl->setValue($item->price_adult);
				$item->price_adult_tax_incl = $item->price_adult + $taxAdultAmount;
				$item->price_adult_tax_excl = $item->price_adult;

				// For child
				$item->currencyChildTaxIncl = clone $solidresCurrency;
				$item->currencyChildTaxExcl = clone $solidresCurrency;
				$item->currencyChildTaxIncl->setValue($item->price_child + $taxChildAmount);
				$item->currencyChildTaxExcl->setValue($item->price_child);
				$item->price_child_tax_incl = $item->price_child + $taxChildAmount;
				$item->price_child_tax_excl = $item->price_child;

				if ($showTaxIncl)
				{
					$item->currency = $item->currencyTaxIncl;
					$item->currencyAdult = $item->currencyAdultTaxIncl;
					$item->currencyChild = $item->currencyChildTaxIncl;
				}
				else
				{
					$item->currency = $item->currencyTaxExcl;
					$item->currencyAdult = $item->currencyAdultTaxExcl;
					$item->currencyChild = $item->currencyChildTaxExcl;
				}
			}
		}
		
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
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select($this->getState('list.select','a.*'));
		$query->from($db->quoteName('#__sr_extras').' AS a');

		$query->select('r.name AS reservationasset');
		$query->join('INNER', $db->quoteName('#__sr_reservation_assets').'as r On a.reservation_asset_id = r.id');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
        {
			$query->where('a.state = '.(int) $published);
		}
        else if ($published === '')
        {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by reservation asset.
		$reservationAssetId = $this->getState('filter.reservation_asset_id');
		if (is_numeric($reservationAssetId))
        {
			$query->where('a.reservation_asset_id = '.(int) $reservationAssetId);
		}

		// If loading from front end, make sure we only load items belong to current user
		$isFrontEnd = JFactory::getApplication()->isSite();
		$partnerId = $this->getState('filter.partner_id', 0);
		if ($isFrontEnd && $partnerId > 0)
		{
			$query->join('INNER', $db->quoteName('#__sr_reservation_assets'). ' AS ra
			ON ra.id = a.reservation_asset_id
			AND ra.partner_id = ' . (int) $partnerId);
		}

		// Filter by room type
		$roomTypeId = $this->getState('filter.room_type_id');
		if (is_numeric($roomTypeId))
		{
			$query->innerJoin($db->quoteName('#__sr_room_type_extra_xref') .'  as rxt ON a.id = rxt.extra_id AND rxt.room_type_id = ' . $db->quote($roomTypeId));
		}

		// Filter by charge type, support filter by multiple charge types
		$chargeType = $this->getState('filter.charge_type');
		$chargeType = (array) $chargeType;
		if (!empty($chargeType))
		{
			$query->where('a.charge_type IN (' . implode(',', $chargeType) .')');
		}

		// Filter by mandatory
		$mandatory = $this->getState('filter.mandatory');
		if (is_numeric($mandatory))
		{
			$query->where('a.mandatory = '. (int) $mandatory);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
        {
			if (stripos($search, 'id:') === 0)
            {
				$query->where('a.id = '.(int) substr($search, 3));
			}
            else
            {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('a.name LIKE '.$search);
			}
		}

		if($this->getState('list.ordering', 'a.ordering') == 'a.ordering')
        {
			$query->order($db->escape($this->getState('list.ordering', 'a.ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
        else
        {
			// Add the list ordering clause.
			$query->order($db->escape($this->getState('list.ordering', 'a.name')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		}
		return $query;
	}
}