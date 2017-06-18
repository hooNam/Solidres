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

$acl = JEventDispatcher::getInstance()->trigger('onSolidresAuthentication');

if (!JFactory::getUser()->authorise('core.manage', 'com_solidres') || in_array(false, $acl))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once JPATH_COMPONENT . '/helpers/sidenavigation.php';
require_once JPATH_COMPONENT . '/helpers/helper.php';
require_once JPATH_COMPONENT . '/helpers/layout.php';

$controller = SRControllerLegacy::getInstance('Solidres');
$controller->execute(JFactory::getApplication()->input->get('task', '', 'cmd'));
$controller->redirect();