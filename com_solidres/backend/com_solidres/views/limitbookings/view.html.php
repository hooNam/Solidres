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
 * Limit Bookings view class
 *
 * @package     Solidres
 * @subpackage	LimitBooking
 * @since		0.6.0
 */
class SolidresViewLimitBookings extends JViewLegacy
{
	protected $state;
	protected $items;
	protected $pagination;

	public function display($tpl = null)
	{
		if (SRPlugin::isEnabled('limitbooking'))
		{
			$this->state         = $this->get('State');
			$this->items         = $this->get('Items');
			$this->pagination    = $this->get('Pagination');
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');

			if (count($errors = $this->get('Errors')))
			{
				JError::raiseError(500, implode("\n", $errors));

				return false;
			}

			JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);
			JFactory::getLanguage()->load('plg_solidres_limitbooking', JPATH_ADMINISTRATOR, null, 1);

			$this->addToolbar();
		}

		parent::display($tpl);
    }

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= SolidresHelper::getActions();

		JToolBarHelper::title(JText::_('SR_MANAGE_LIMITBOOKINGS'), 'generic.png');
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('limitbooking.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('limitbooking.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state'))
		{
			if ($state->get('filter.state') != 2)
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('limitbookings.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('limitbookings.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			if ($state->get('filter.state') != -1)
			{
				JToolBarHelper::divider();
				if ($state->get('filter.state') != 2)
				{
					//JToolBarHelper::archiveList('limitbookings.archive','JTOOLBAR_ARCHIVE');
				}
				else if ($state->get('filter.state') == 2)
				{
					JToolBarHelper::unarchiveList('limitbookings.publish', 'JTOOLBAR_UNARCHIVE');
				}
			}
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'limitbookings.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('limitbookings.trash', 'JTOOLBAR_TRASH');
		}
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_solidres');
		}
		JToolBarHelper::divider();
	}
}