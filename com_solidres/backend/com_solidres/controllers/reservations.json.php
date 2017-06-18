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
 * Reservation list controller class (JSON format).
 *
 * @package     Solidres
 * @subpackage	Reservation
 * @since		0.1.0
 */
class SolidresControllerReservations extends JControllerLegacy
{
    public function countUnread()
    {
        $model = JModelLegacy::getInstance('Reservations', 'SolidresModel', array('ignore_request' => true));
        $app = JFactory::getApplication();
	    if ($app->isSite() && SRPlugin::isEnabled('hub'))
	    {
			JTable::addIncludePath(SRPlugin::getAdminPath('user') . '/tables');
			$currentUser = JFactory::getUser();
			$tableCustomer = JTable::getInstance('Customer', 'SolidresTable');
			$tableCustomer->load(array('user_id' => $currentUser->get('id')));
			$model->setState('filter.partner_id', $tableCustomer->id);
	    }

	    $unread = $model->countUnread();

        echo json_encode(array('count' => $unread));
    }
}