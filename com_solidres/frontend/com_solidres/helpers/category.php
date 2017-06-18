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

class SolidresCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table']     = '#__sr_reservation_assets';
		$options['extension'] = 'com_solidres';

		parent::__construct($options);
	}
}