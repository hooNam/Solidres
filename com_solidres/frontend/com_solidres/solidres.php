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

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/helper.php';
require_once JPATH_COMPONENT_ADMINISTRATOR .'/helpers/layout.php';
$controller = SRControllerLegacy::getInstance('Solidres');
$controller->execute(JFactory::getApplication()->input->get('task', '', 'cmd'));
$controller->redirect();