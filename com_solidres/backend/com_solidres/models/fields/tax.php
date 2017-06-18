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

JFormHelper::loadFieldClass('list');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/helper.php';

/**
 * Supports an HTML select list of taxes
 *
 * @package
 * @subpackage
 * @since		1.6
 */
class JFormFieldTax extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Tax';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		$assetId = (int) $this->form->getValue('reservation_asset_id');
		$options = SolidresHelper::getTaxOptions($assetId);

		return  array_merge(parent::getOptions(), $options);
	}
}