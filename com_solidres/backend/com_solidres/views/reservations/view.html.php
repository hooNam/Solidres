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
 * Reservation view class
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresViewReservations extends JViewLegacy
{
	protected $state;
	protected $items;
	protected $pagination;
	protected $reservationStatusList;
	protected $reservationPaymentStatusList;
	
    public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$solidresConfig   = JComponentHelper::getParams('com_solidres');
		$this->dateFormat = $solidresConfig->get('date_format', 'd-m-Y');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);

		$this->addToolbar();
		
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

		JToolBarHelper::title(JText::_('SR_MANAGE_RESERVATION'), 'generic.png');

		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('reservationbase.add','JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::custom('reservationbase.edit', 'eye', '', 'JTOOLBAR_VIEW', true);
			JToolBarHelper::custom('reservationbase.amend', 'edit', '', 'JTOOLBAR_AMEND', true);
			JToolBarHelper::custom('reservations.export', 'download', '','SR_RESERVATION_EXPORT', true);
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($state->get('filter.state') != 2)
			{
				//JToolBarHelper::divider();
				//JToolBarHelper::custom('reservations.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				//JToolBarHelper::custom('reservations.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}	
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'reservations.delete','JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('reservations.trash','JTOOLBAR_TRASH');
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_solidres');
		}
	}
}
