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

JLoader::register('SolidresHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/helper.php');

/**
 * State list controller class.
 *
 * @package     Solidres
 * @subpackage	State
 * @since		0.1.0
 */
class SolidresControllerRoomTypes extends JControllerAdmin
{
	public function &getModel($name = 'RoomTypes', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function find()
	{
		$reservationAssetId = JFactory::getApplication()->input->get('id', 0, 'int');
		$output = JFactory::getApplication()->input->get('output', 'html', 'string');
		$roomTypes = SolidresHelper::getRoomTypeOptions($reservationAssetId, $output);
		if ($output == 'html')
		{
			$html = '';
			foreach ($roomTypes as $roomType)
			{
				$html .= '<option value="'.$roomType->value.'">'.$roomType->text.'</option>';
			}
			echo $html;
		}
		else
		{
			$results = array();
			foreach ($roomTypes as $roomType)
			{
				$results[] = array('id' => $roomType->id, 'name' => $roomType->name);
			}
			echo json_encode($results);
		}

		JFactory::getApplication()->close();
	}
}