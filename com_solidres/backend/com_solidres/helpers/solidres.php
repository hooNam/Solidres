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
 * Solidres component helper.
 *
 * @package     Solidres
 * @since       0.6.0
 */
class SolidresHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	The name of the active view.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('SR_SUBMENU_ASSETS_CATEGORY'),
			'index.php?option=com_categories&extension=com_solidres',
			$vName == 'categories'
		);

		if ($vName == 'categories')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_solidres')),
				'solidres-categories');
		}

		JHtmlSidebar::addEntry(
			JText::_('SR_SUBMENU_ASSETS_LIST'),
			'index.php?option=com_solidres&view=reservationassets',
			$vName == 'reservationassets'
		);

		JHtmlSidebar::addEntry(
			JText::_('SR_SUBMENU_ROOM_TYPE_LIST'),
			'index.php?option=com_solidres&view=roomtypes',
			$vName == 'roomtypes'
		);

		JHtmlSidebar::addEntry(
			JText::_('SR_SUBMENU_RESERVATIONS_LIST'),
			'index.php?option=com_solidres&view=reservations',
			$vName == 'reservations'
		);
	}
}
