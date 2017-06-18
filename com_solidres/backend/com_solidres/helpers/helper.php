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

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');
JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

/**
 * Solidres Helper class
 *
 * @since		0.1.0
 */
class SolidresHelper {
	
	public static $extention = 'com_solidres';

	/**
	 * Gets a list of the actions that can be performed
	 *
	 * @param	int	$categoryId 			The category ID.
	 * @param   int $reservation_asset_id 	The reservation asset_id
	 * @return	JObject
	 */
	public static function getActions($categoryId = 0, $reservation_asset_id = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($reservation_asset_id) && empty($categoryId))
        {
			$assetName = 'com_solidres';
		}
		else if (empty($reservation_asset_id) && !empty($categoryId))
        {
			$assetName = 'com_solidres.category.'.(int) $categoryId;
		}
		else if (!empty($reservation_asset_id) && empty($categoryId))
        {
			$assetName = 'com_solidres.reservationasset.'.(int) $reservation_asset_id;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
        {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	
	public static function getCouponOptions()
	{
		$options 	= array();
		$model      = JModelLegacy::getInstance('Coupons', 'SolidresModel', array('ignore_request' => true));
		$model->setState('filter.state', 1);
        $results 	= $model->getItems();
		$options[] 	= JHTML::_('select.option', '', JText::_('- Select a coupon -') );
		
		if (!empty($results))
        {
			foreach($results as $item)
            {
				$options[] = JHTML::_('select.option', $item->id, $item->coupon_name);
			}
		}
		
		return $options;
	}
	
	/**
	 * Get list currency option
     * 
     * @return string
	 */
	public static function getCurrencyOptions()
	{
		$options 	= array();
        $model      = JModelLegacy::getInstance('Currencies', 'SolidresModel', array('ignore_request' => true));

        $model->setState('filter.state', 1);
        $model->setState('list.ordering', 'u.currency_name');
		$results 	= $model->getItems();
		
		if (!empty($results))
        {
			foreach($results as $item)
            {
				$options[] = JHTML::_('select.option', $item->id, $item->currency_name);
			}
		}
		
		return $options;
	}

    /**
	 * Get list currency option
	 *
     * @return string
	 */
	public static function getCustomerGroupOptions($showAll = true)
	{
		$options = array();
		$results = array();
		if (SRPlugin::isEnabled('user'))
		{
			$model = JModelLegacy::getInstance('CustomerGroups', 'SolidresModel', array('ignore_request' => true));
			$model->setState('list.start', 0);
			$model->setState('list.limit', 0);
			$model->setState('filter.state', 1);
			$model->setState('list.ordering', 'a.name');
			$results = $model->getItems();
		}

		if ($showAll)
		{
			$options[] = JHTML::_('select.option', -1, JText::_('SR_FILTER_ALL'));
		}

		$options[] = JHTML::_('select.option', 'NULL', JText::_('SR_GENERAL_CUSTOMER_GROUP'));

		if (!empty($results))
        {
			foreach($results as $item)
            {
				$options[] = JHTML::_('select.option', $item->id, $item->name);
			}
		}

		return $options;
	}

	/**
	 * Get asset <option> to build <select>
	 * 
	 * @return array $options An array of <option>
	 */
	public static function getReservationAssetOptions()
	{
		$options = array();
		$raModel = JModelLegacy::getInstance('ReservationAssets', 'SolidresModel', array('ignore_request' => true));
		$raModel->setState('list.select', 'a.id, a.name');
		$raModel->setState('list.start', 0);
		$raModel->setState('list.limit', 0);
		$raModel->setState('filter.state', 1);
		$raModel->setState('list.ordering', 'a.name');
		$raModel->setState('hub.ignore', true);

		$isSite = JFactory::getApplication()->isSite();
		if ($isSite)
		{
			$user = JFactory::getUser();
			JTable::addIncludePath(SRPlugin::getAdminPath('user') . '/tables');
			JModelLegacy::addIncludePath(SRPlugin::getAdminPath('user') . '/models', 'SolidresModel');
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$customerTable->load(array('user_id' => $user->get('id')));
			$raModel->setState('filter.partner_id', $customerTable->id);
		}

		$results = $raModel->getItems();

		$options[] = JHTML::_('select.option', '', '&nbsp;' );

		if (!empty($results))
        {
			foreach($results as $item)
            {
				$options[] = JHTML::_('select.option', $item->id, $item->name );
			}
		}
		return $options;
	}

	/**
	 * Get country select <option>
	 * 
	 * @return array $option An array of country <option>
	 */
	public static function getCountryOptions()
	{
		$options 		= array();
		$countriesModel = JModelLegacy::getInstance('Countries', 'SolidresModel', array('ignore_request' => true));

		$countriesModel->setState('list.start', 0);
		$countriesModel->setState('list.limit', 0);
		$countriesModel->setState('filter.state', 1);
		$countriesModel->setState('list.ordering', 'r.name');
		$results 		= $countriesModel->getItems();

		$options[] = JHTML::_('select.option', '', JText::_('SR_FIELD_COUNTRY_SELECT'));
		
		if (!empty($results))
        {
			foreach($results as $item)
            {
				$options[] = JHTML::_('select.option', $item->id, $item->name );
			}
		}
		
		return $options;
	}

	public static function getTaxOptions($assetId = 0, $countryId = 0)
	{
		$options = array();
		$model = JModelLegacy::getInstance('Taxes', 'SolidresModel', array('ignore_request' => true));

		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.state', 1);
		$model->setState('list.ordering', 'r.name');
		if ($assetId > 0)
		{
			$model->setState('filter.reservation_asset_id', $assetId);
		}

		if ($countryId > 0)
		{
			$model->setState('filter.country_id', $countryId);
		}

		$results = $model->getItems();

		$options[] = JHTML::_('select.option', '', '&nbsp;' );

		if (!empty($results))
		{
			foreach($results as $item)
			{
				$options[] = JHTML::_('select.option', $item->id, $item->name . ' (' . $item->rate * 100 .'%)' );
			}
		}

		return $options;
	}

	/**
	 * Get all state of a specific country
	 * 
	 * @param 	int 	$country_id The country ID
	 * @return 	array 	An array of country <option> 
	 */
	public static function getGeoStateOptions($country_id)
	{
		$options 		= array();
		$geoStatesModel = JModelLegacy::getInstance('States', 'SolidresModel', array('ignore_request' => true));
		$geoStatesModel->setState('list.start', 0);
		$geoStatesModel->setState('list.limit', 0);
		$geoStatesModel->setState('filter.state', 1);
		$geoStatesModel->setState('list.ordering', 'name');

		if ($country_id > 1)
		{
			$geoStatesModel->setState('filter.country_id', $country_id);
		}

		$results 		= $geoStatesModel->getItems();

		if (!empty($results))
        {
            $options[] = JHTML::_('select.option', NULL, JText::_('SR_SELECT') );
			foreach($results as $item)
            {
				$options[] = JHTML::_('select.option', $item->id, $item->name );
			}
		}
		return $options;
	}

	/**
	 * Get all state of a specific country
	 *
	 * @param 	int 	$reservationAssetId The reservation asset ID
	 * @param 	string 	$format
	 *
	 * @return 	array 	An array of room types <option>
	 */
	public static function getRoomTypeOptions($reservationAssetId, $format = 'html') {
		$options = array();
		$user    = JFactory::getUser();

		if (JFactory::getApplication()->isSite() && ! SRUtilities::isAssetPartner( $user->get( 'id' ), $reservationAssetId ))
		{
			return $options;
		}

		$model = JModelLegacy::getInstance('RoomTypes', 'SolidresModel', array('ignore_request' => true));
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.state', 1);
		$model->setState('list.ordering', 'name');

		if ($reservationAssetId > 1)
		{
			$model->setState('filter.reservation_asset_id', $reservationAssetId);
		}

		$results = $model->getItems();

		if (!empty($results))
		{
			if ($format == 'html')
			{
				foreach($results as $item)
				{
					$options[] = JHTML::_('select.option', $item->id, $item->name );
				}
				return $options;
			}
		}
		return $results;
	}

	public static function getGalleryOptions()
	{
		$dbo = JFactory::getDbo();
		$options = array();
		$query = $dbo->getQuery(true);
		$query->select('*')
			->from($dbo->quoteName('#__extensions'))
			->where($dbo->quoteName('folder') .' = '.$dbo->quote('solidres'))
			->where($dbo->quoteName('enabled') .' = 1')
			->where($dbo->quoteName('element') .' LIKE '.$dbo->quote('%gallery%') . ' OR ' .$dbo->quoteName('element') .' LIKE '.$dbo->quote('%slideshow%'));

		$dbo->setQuery($query);

		$results = $dbo->loadObjectList();

		if (!empty($results))
		{
			$options[] = JHTML::_('select.option', NULL, JText::_('SR_SELECT_DEFAULT_GALLERY') );
			foreach($results as $item)
			{
				$options[] = JHTML::_('select.option', $item->element, $item->element );
			}
		}
		return $options;
	}

	public static function getPaymentPluginOptions($listOnly = true)
	{
		$dbo = JFactory::getDbo();
		$options = array();
		$query = $dbo->getQuery(true);
		$query->select('*')
			->from($dbo->quoteName('#__extensions'))
			->where($dbo->quoteName('folder') .' = '.$dbo->quote('solidrespayment'))
			->where($dbo->quoteName('enabled') .' = 1');

		$dbo->setQuery($query);

		$results = $dbo->loadObjectList();

		if ($listOnly)
		{
			return $results;
		}

		if (!empty($results))
		{
			$options[] = JHTML::_('select.option', NULL, JText::_('SR_SELECT_DEFAULT_GALLERY') );
			foreach($results as $item)
			{
				$options[] = JHTML::_('select.option', $item->element, $item->element );
			}
		}
		return $options;
	}
}