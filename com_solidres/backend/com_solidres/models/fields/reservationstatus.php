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


class JFormFieldReservationStatus extends JFormFieldList
{

	protected $type = 'reservationstatus';

	protected function getOptions()
	{

		$options = array();

		$options[] = JHtml::_('select.option', '', JText::_('SR_FILTER_RESERVATION_STATUS_SELECT'));
		$options[] = JHtml::_('select.option', '0', JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL'));
		$options[] = JHtml::_('select.option', '1', JText::_('SR_RESERVATION_STATE_CHECKED_IN'));
		$options[] = JHtml::_('select.option', '2', JText::_('SR_RESERVATION_STATE_CHECKED_OUT'));
		$options[] = JHtml::_('select.option', '3', JText::_('SR_RESERVATION_STATE_CLOSED'));
		$options[] = JHtml::_('select.option', '4', JText::_('SR_RESERVATION_STATE_CANCELED'));
		$options[] = JHtml::_('select.option', '5', JText::_('SR_RESERVATION_STATE_CONFIRMED'));
		$options[] = JHtml::_('select.option', '-2', JText::_('JTRASHED'));

		return $options;
	}
}