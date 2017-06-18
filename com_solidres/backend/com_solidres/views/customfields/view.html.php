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

if (!SRPlugin::isEnabled('customfield'))
{
	class SolidresViewCustomfields extends JViewLegacy
	{
		function display($tpl = null)
		{
			$this->addToolbar();

			parent::display($tpl);
		}
		protected function addToolbar()
		{
			JToolBarHelper::title(JText::_('SR_SUBMENU_CUSTOM_FIELDS'), 'generic.png');
			include JPATH_COMPONENT.'/helpers/toolbar.php';
			SRToolBarHelper::customLink('index.php?option=com_solidres', 'JToolbar_Close', 'fa fa-arrow-left');
		}
	}
}
else
{
	require_once SR_PLUGIN_CUSTOMFIELD_ADMINISTRATOR . '/views/customfields/view.html.php';
}