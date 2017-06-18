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
 * Tariff Details table
 *
 * @package     Solidres
 * @subpackage	Tariff
 * @since		0.1.0
 */
class SolidresTableTariffDetails extends JTable
{
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_tariff_details', 'id', $db);
	}
}

