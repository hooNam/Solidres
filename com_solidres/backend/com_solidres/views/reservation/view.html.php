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
 * View to edit a Reservation.
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresViewReservation extends JViewLegacy
{
	protected $state;
	protected $form;
	protected $invoiceTable;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->state = $model->getState();
		$this->form	= $model->getForm();
		$lang = JFactory::getLanguage();
		$lang->load('com_solidres', JPATH_SITE . '/components/com_solidres');
		$solidresConfig = JComponentHelper::getParams('com_solidres');
		$this->dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
		$this->customer_id = $this->form->getValue('customer_id', 0);
		$this->customerIdentification = '';
		if ($this->customer_id > 0 && SRPlugin::isEnabled('user'))
		{
			JModelLegacy::addIncludePath(SRPlugin::getAdminPath('user') . '/models', 'SolidresModel');
			$customerModel = JModelLegacy::getInstance('Customer', 'SolidresModel');
			$customer = $customerModel->getItem($this->customer_id);
			$this->customerIdentification = $customer->name . ' ( ' . $customer->id . ' - ' . (empty($customer->customer_group_name) ? JText::_('SR_GENERAL_CUSTOMER_GROUP') : $customer->customer_group_name) . ' )';
		}

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if (!in_array($this->form->getValue('payment_method_id'), array('paylater', 'bankwire')))
		{
			$lang->load('plg_solidrespayment_'.$this->form->getValue('payment_method_id'), JPATH_PLUGINS . '/solidrespayment/' . $this->form->getValue('payment_method_id'), null, 1);
		}

		JFactory::getDocument()->addScriptDeclaration('
			Solidres.child_max_age_limit = '.$solidresConfig->get('child_max_age_limit', 17).';
			Solidres.jQuery(function($) {
				$("a#payment-data-delete-btn").on(\'click\', function(e){
				    if (confirm("' . JText::_( 'SR_DELETE_RESERVATION_PAYMENT_DATA_CONFIRM' ) . '") != true) {
				        e.preventDefault();
				    }
				});
			});
		');

		JText::script("SR_RESERVATION_NOTE_NOTIFY_CUSTOMER");
		JText::script("SR_RESERVATION_NOTE_DISPLAY_IN_FRONTEND");
		JText::script('SR_PROCESSING');
		JText::script('SR_NEXT');
		JText::script('SR_CHILD');
		JText::script('SR_CHILD_AGE_SELECTION_JS');
		JText::script('SR_CHILD_AGE_SELECTION_1_JS');

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);

		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$this->lengthOfStay = (int)SRUtilities::calculateDateDiff($this->form->getValue('checkin'), $this->form->getValue('checkout'));
		if(SRPlugin::isEnabled('invoice'))
		{
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('solidres');
			$this->invoiceTable = $dispatcher->trigger('onSolidresLoadReservation', array($this->form->getValue('id')));
		}

		JPluginHelper::importPlugin('solidres');
		$dispatcher	= JEventDispatcher::getInstance();
		$dispatcher->trigger('onSolidresReservationViewLoad', array (&$this->form));

		SRHtml::_('jquery.datepicker');
		$this->addToolbar();

		$model->recordAccess();

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
		$id = $this->form->getValue('id');
		$isNew = ($id == 0);
		JToolBarHelper::title($isNew ? JText::_('SR_ADD_NEW_RESERVATION') : JText::_('SR_EDIT_RESERVATION') . ' ' . $this->form->getValue('code'), 'generic.png');
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

		JLoader::register('SRToolBarHelper', JPATH_COMPONENT.'/helpers/toolbar.php');

		if ($this->_layout != 'edit2')
		{
			SRToolBarHelper::customLink(JRoute::_('index.php?option=com_solidres&task=reservationbase.amend&id='. $id), 'JTOOLBAR_AMEND', 'icon-edit');
		}

		if ($this->_layout != 'edit' && !empty($id))
		{
			SRToolBarHelper::customLink(JRoute::_('index.php?option=com_solidres&task=reservationbase.edit&id='. $id), 'JTOOLBAR_VIEW', 'icon-eye');
		}

		if (empty($id))
		{
			JToolBarHelper::cancel('reservationbase.cancel', 'JToolbar_Cancel');
		}
		else
		{
			JToolBarHelper::cancel('reservationbase.cancel', 'JToolbar_Close');
		}
	}
}
