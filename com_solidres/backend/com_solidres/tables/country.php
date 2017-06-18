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
 * Country table
 *
 * @package     Solidres
 * @subpackage	Country
 * @since		0.1.0
 */
class SolidresTableCountry extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_countries', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->modified_date = $date->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_by	= $user->get('id');
		}
		else
		{
			if (!(int) $this->created_date)
			{
				$this->created_date = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}
}

