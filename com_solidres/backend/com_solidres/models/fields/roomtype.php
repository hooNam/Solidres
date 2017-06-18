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
 * Supports an HTML select list of room types
 *
 * @package       Solidres
 * @subpackage    RoomType
 * @since         1.6
 */
class JFormFieldRoomType extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'RoomType';

	protected function getOptions()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_solidres/models', 'SolidresModel');
		$options = array(JHtml::_('select.option', '', ''));

		$model = JModelLegacy::getInstance('RoomTypes', 'SolidresModel', array('ignore_request' => true));
		$model->setState('list.select', 'r.id AS value, r.name AS text, asset.name AS reservationasset');
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		$model->setState('filter.state', 1);
		$model->setState('list.ordering', 'r.name');

		return array_merge($options, (array) $model->getItems());
	}
}