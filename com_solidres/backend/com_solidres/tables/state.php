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
 * State table
 *
 * @package     Solidres
 * @subpackage	State
 * @since		0.1.0
 */
class SolidresTableState extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_geo_states', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}
}

