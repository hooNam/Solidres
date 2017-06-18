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

require_once JPATH_ADMINISTRATOR.'/components/com_solidres/helpers/helper.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldThumbSizes extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'thumbsizes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		$solidresParams = JComponentHelper::getParams('com_solidres');
		$availableSizes = $solidresParams->get('thumb_sizes', '');
		if (!empty($availableSizes))
		{
			$availableSizes = preg_split("/\r\n|\n|\r/", $availableSizes);
		}
		else
		{
			$availableSizes = array('300x250', '75x75');
		}

		foreach($availableSizes as $size)
		{
			$options[] = JHTML::_('select.option', $size, $size);
		}

		return array_merge(parent::getOptions(), $options);
	}
}


