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


class JFormFieldPaymentStatus extends JFormFieldList
{

	protected $type = 'paymentstatus';

	protected function getOptions()
	{

		$options = array();

		$options[] = JHtml::_('select.option', '', JText::_('SR_RESERVATION_PAYMENT_STATUS_SELECT'));
		$options[] = JHtml::_('select.option', '0', JText::_('SR_RESERVATION_PAYMENT_STATUS_UNPAID'));
		$options[] = JHtml::_('select.option', '1', JText::_('SR_RESERVATION_PAYMENT_STATUS_COMPLETED'));
		$options[] = JHtml::_('select.option', '2', JText::_('SR_RESERVATION_PAYMENT_STATUS_CANCELLED'));
		$options[] = JHtml::_('select.option', '3', JText::_('SR_RESERVATION_PAYMENT_STATUS_PENDING'));

		return $options;
	}
}