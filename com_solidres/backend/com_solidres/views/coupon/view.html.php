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
 * View to edit a coupon.
 *
 * @package     Solidres
 * @subpackage	Coupon
 * @since		0.1.0
 */
class SolidresViewCoupon extends JViewLegacy
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
		
		SRHtml::_('jquery.datepicker');
		JHtml::_('behavior.tabstate');

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
		$isNew		= ($id == 0);
		$canDo		= SolidresHelper::getActions('', $id);
		
		if($isNew)
        {
			JToolBarHelper::title(JText::_('SR_ADD_NEW_COUPON_FIELD'), 'generic.png');
		}
        else
        {
			JToolBarHelper::title(JText::sprintf('SR_EDIT_COUPON_FIELD', $this->form->getValue('coupon_name')), 'generic.png');
		}
		
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
		JHtml::_('behavior.tooltip');
		SRHtml::_('jquery.validate');
		
		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
        {
			JToolBarHelper::apply('coupon.apply', 'JToolbar_Apply');
			JToolBarHelper::save('coupon.save', 'JToolbar_Save');
			JToolBarHelper::addNew('coupon.save2new', 'JToolbar_Save_and_new');
		}
		
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
        {
			JToolBarHelper::custom('coupon.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}
		
		if (empty($id))
        {
			JToolBarHelper::cancel('coupon.cancel', 'JToolbar_Cancel');
		} else {
			JToolBarHelper::cancel('coupon.cancel', 'JToolbar_Close');
		}

		SRToolBarHelper::mediaManager();
		JToolBarHelper::divider();
	}
}
