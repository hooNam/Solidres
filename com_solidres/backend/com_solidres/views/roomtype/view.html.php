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
 * View to edit a RoomType.
 *
 * @package     Solidres
 * @subpackage	RoomType
 * @since		0.1.0
 */
class SolidresViewRoomType extends JViewLegacy
{
	protected $form;
	protected $customerGroups;
	protected $enabledComplexTariff;

	public function display($tpl = null)
	{
		$this->form	= $this->get('Form');
		$this->nullDate = JFactory::getDbo()->getNullDate();

		$doc = JFactory::getDocument();

		$params                     = JComponentHelper::getParams('com_solidres');
		$this->currency_id          = $params->get('default_currency_id');
		$this->enabledComplexTariff = SRPlugin::isEnabled('complextariff');
		$this->solidresMedia        = SRFactory::get('solidres.media.media');

		JHtml::_('jquery.framework');
		SRHtml::_('jquery.datepicker');
		JHtml::_('script', 'jui/cms.js', false, true);
		JHtml::_('behavior.tabstate');

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);

		$roomList = $this->form->getValue('roomList');
		$rowIdRoom = isset($roomList)  ? count($roomList) : 0;

		JText::script('SR_FIELD_ROOM_CAN_NOT_DELETE_ROOM');
		$doc->addScriptDeclaration("
			Solidres.jQuery(function($) {
			    $('#toolbar').srRoomType({rowidx : 0, rowIdRoom: $rowIdRoom});
			});
		");

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		JPluginHelper::importPlugin('solidres');
		$dispatcher	= JEventDispatcher::getInstance();
		$dispatcher->trigger('onSolidresRoomTypeViewLoad', array (&$this->form));

		$this->addToolbar();
        
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		include JPATH_COMPONENT.'/helpers/toolbar.php';
		$user		= JFactory::getUser();
		$id = $this->form->getValue('id');
		$isNew		= ($id == 0);
		$checkedOut	= !($this->form->getValue('checked_out') == 0 || $this->form->getValue('checked_out') == $user->get('id'));
		$canDo		= SolidresHelper::getActions('', $id);
		
		if($isNew)
		{
			JToolBarHelper::title(JText::_('SR_ADD_NEW_ROOM_TYPE'), 'generic.png');
		}
		else
		{
			JToolBarHelper::title(JText::sprintf('SR_EDIT_ROOM_TYPE', $this->form->getValue('name')), 'generic.png');
		}
		
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			JToolBarHelper::apply('roomtype.apply', 'JToolbar_Apply');
			JToolBarHelper::save('roomtype.save', 'JToolbar_Save');
			JToolBarHelper::addNew('roomtype.save2new', 'JToolbar_Save_and_new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom('roomtype.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}
		
		if (empty($id))
		{
			JToolBarHelper::cancel('roomtype.cancel', 'JToolbar_Cancel');
		}
		else
		{
			JToolBarHelper::cancel('roomtype.cancel', 'JToolbar_Close');
		}
		
		SRToolBarHelper::mediaManager();
		JToolBarHelper::divider();
	}
}
