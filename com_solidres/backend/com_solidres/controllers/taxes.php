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
 * Tax list controller class.
 *
 * @package     Solidres
 * @subpackage	Tax
 * @since		0.1.0
 */
class SolidresControllerTaxes extends JControllerAdmin
{
	public function &getModel($name = 'Tax', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function find()
	{
		$input = JFactory::getApplication()->input;
		$assetId = $input->get('id', 0, 'int');
		$countryId = $input->get('country_id', 0, 'int');
		$taxes = SolidresHelper::getTaxOptions($assetId, $countryId);
		$html = '';
		foreach ($taxes as $tax)
		{
			$html .= '<option value="'.$tax->value.'">'.$tax->text.'</option>';
		}
		echo $html;
		JFactory::getApplication()->close();
	}
}