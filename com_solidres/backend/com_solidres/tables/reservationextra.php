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
 * Reservation extra table
 *
 * @package     Solidres
 * @subpackage	ReservationExtra
 * @since		0.5.0
 */
class SolidresTableReservationExtra extends JTable
{
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_reservation_extra_xref', 'id', $db);
	}
}

