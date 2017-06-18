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
 * View to edit a state.
 *
 * @package     Solidres
 * @subpackage	State
 * @since		0.1.0
 */
class SolidresViewState extends JViewLegacy
{
	protected $state;
	protected $form;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->state = $model->getState();
		$this->form	= $model->getForm();

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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		include JPATH_COMPONENT.'/helpers/toolbar.php';
		$id = $this->form->getValue('id');
		$isNew = ($id == 0);
		$canDo = SolidresHelper::getActions('', $id);
		
		if($isNew)
		{
			JToolBarHelper::title(JText::_('SR_ADD_NEW_STATE'), 'generic.png');
		}
		else
		{
			JToolBarHelper::title(JText::_('SR_EDIT_STATE'), 'generic.png');
		}
		
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
		
		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('state.apply', 'JToolbar_Apply');
			JToolBarHelper::save('state.save', 'JToolbar_Save');
			JToolBarHelper::addNew('state.save2new', 'JToolbar_Save_and_new');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom('state.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}
		if (empty($id))
		{
			JToolBarHelper::cancel('state.cancel', 'JToolbar_Cancel');
		}
		else
		{
			JToolBarHelper::cancel('state.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
	}
}
