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

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of ReservationAssets
 *
 * @package       Solidres
 * @subpackage    RoomType
 * @since         1.6
 */
class JFormFieldReservationAsset extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ReservationAsset';

	protected function getOptions()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_solidres/models', 'SolidresModel');
		$options = array(JHtml::_('select.option', '', JText::_('SR_FILTER_RESERVATION_ASSET_SELECT')));

		$raModel = JModelLegacy::getInstance('ReservationAssets', 'SolidresModel', array('ignore_request' => true));
		$raModel->setState('list.select', 'a.id AS value, a.name AS text');
		$raModel->setState('list.start', 0);
		$raModel->setState('list.limit', 0);
		$raModel->setState('filter.state', 1);
		$raModel->setState('list.ordering', 'a.name');
		$raModel->setState('hub.ignore', true);

		if (JFactory::getApplication()->isSite())
		{
			$user = JFactory::getUser();
			JTable::addIncludePath(SRPlugin::getAdminPath('user') . '/tables');
			JModelLegacy::addIncludePath(SRPlugin::getAdminPath('user') . '/models', 'SolidresModel');
			$customerTable = JTable::getInstance('Customer', 'SolidresTable');
			$customerTable->load(array('user_id' => $user->get('id')));
			$raModel->setState('filter.partner_id', $customerTable->id);
		}
		$options = array_merge($options, (array) $raModel->getItems());

		return $options;
	}
}