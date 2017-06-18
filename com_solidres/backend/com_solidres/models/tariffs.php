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
 * @subpackage	Tariff
 * @since		0.1.0
 */
class SolidresModelTariffs extends JModelList
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
		$id .= ':' . $this->getState('filter.room_type_id');
		$id .= ':' . $this->getState('filter.bookday');
		$id .= ':' . $this->getState('filter.date_constraint');
		$id .= ':' . $this->getState('filter.default_tariff');
		$id .= ':' . $this->getState('filter.valid_from');
		$id .= ':' . $this->getState('filter.valid_to');

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
		$nullDate = $dbo->getNullDate();

        $query->select( $this->getState('list.select', 't.*, cgroup.name as customer_group_name' ));

        $query->from($dbo->quoteName('#__sr_tariffs').' AS t');

		$roomTypeId = $this->getState('filter.room_type_id', NULL);

		if (isset($roomTypeId))
		{
			$query->where('t.room_type_id = '.(int) $roomTypeId);
		}

		$query->join('left', $dbo->quoteName('#__sr_customer_groups') .' as cgroup ON cgroup.id = t.customer_group_id');

		// Filter by customer group id
		// -1 means no checking, load them all
		// NULL means load tariffs for Public customer group
		// any other value > 0 means load tariffs belong to specific groups
		$customer_group_id = $this->getState('filter.customer_group_id');
		if ($customer_group_id != -1)
		{
			$query->where('t.customer_group_id '.($customer_group_id === NULL ? 'IS NULL' : '= ' .(int) $customer_group_id));
		}

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('t.state = '.(int) $published);
		}
		else if ($published === '')
		{
			$query->where('(t.state IN (0, 1))');
		}

        if ($date_constraint = $this->getState('filter.date_constraint'))
        {
			$query->where('t.valid_from <= '.$dbo->quote($this->getState('filter.bookday'))) ;
			$query->where('t.valid_to >= '.$dbo->quote($this->getState('filter.bookday')));
        }

		/* Get the default (fixed) tariff. Default tariff has no date constraint and
		   is available for all customer groups */
		$defaultTariff = $this->getState('filter.default_tariff', false);
		if ($defaultTariff)
		{
			$query->where('t.valid_from = '.$dbo->quote(substr($nullDate, 0, 10)) ) ;
			$query->where('t.valid_to = '.$dbo->quote(substr($nullDate, 0, 10)) );
		}
		else
		{
			$query->where('t.valid_from != '.$dbo->quote(substr($nullDate, 0, 10)) ) ;
			$query->where('t.valid_to != '.$dbo->quote(substr($nullDate, 0, 10)) );
		}

		$stayLength = $this->getState('filter.stay_length', 0);
		if ($stayLength > 0)
		{
			$query->where('
			CASE
				WHEN t.d_min > 0 AND t.d_max > 0 THEN t.d_min <= '.(int)$stayLength .' AND t.d_max >= '.(int)$stayLength .'
				WHEN t.d_min = 0 AND t.d_max > 0 THEN t.d_max >= '.(int)$stayLength .'
				WHEN t.d_min > 0 AND t.d_max = 0 THEN t.d_min <= '.(int)$stayLength .'
				WHEN t.d_min = 0 AND t.d_max = 0 THEN 1
				ELSE 1
			END
			');
		}

		$validFrom = $this->getState('filter.valid_from', NULL);
		$validTo = $this->getState('filter.valid_to', NULL);

		if (isset($validFrom) && $validTo)
		{
			$query->where('t.valid_from <= '. $dbo->quote($validFrom) .' AND t.valid_to >=' . $dbo->quote($validTo));
		}

	    $isPartialMatch = $this->getState('filter.partial_match', 0);
	    $checkin = $this->getState('filter.checkin', '');
	    $checkout = $this->getState('filter.checkout', '');
	    if ($isPartialMatch && !empty($checkin) && !empty($checkout))
	    {
		    $query->where('t.id NOT IN
		        (SELECT id FROM '. $dbo->quoteName('#__sr_tariffs'). ' as t1
		        WHERE t1.valid_from <= '. $dbo->quote($checkin) .' AND t1.valid_to >= '.$dbo->quote($checkout).' AND t1.room_type_id = '.$dbo->quote($roomTypeId).')');
	    }

	    if($this->getState('list.ordering', 'valid_from') == 'valid_from')
	    {
		    $query->order($dbo->escape($this->getState('list.ordering', 'valid_from')).' '.$dbo->escape($this->getState('list.direction', 'ASC')));
	    }
	    else
	    {
		    // Add the list ordering clause.
		    $query->order($dbo->escape($this->getState('list.ordering', 'valid_from')).' '.$dbo->escape($this->getState('list.direction', 'ASC')));
	    }

        return $query;
    }
}
