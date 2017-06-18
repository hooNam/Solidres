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
 * Reservation list controller class.
 *
 * @package     Solidres
 * @subpackage	Reservation	
 * @since		0.1.0
 */
class SolidresControllerReservations extends JControllerAdmin
{
	public function getModel($name = 'Reservation', $prefix = 'SolidresModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Export selected reservation to CSV format
	 *
	 * @return void
	 */
	public function export()
	{
		$ids = $this->input->get('cid', array(), 'array');
		$results = array();
		$dbo = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		foreach ($ids as $id)
		{
			$query->clear();
			$query->select('*')->from($dbo->quoteName('#__sr_reservations'))->where('id = ' . $dbo->quote($id));
			$results[] = $dbo->setQuery($query)->loadAssoc();
		}

		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=solidres_reservation_export.csv");
		header("Content-Transfer-Encoding: binary");

		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys(reset($results)));
		foreach ($results as $row)
		{
			fputcsv($df, $row);
		}
		fclose($df);
		echo ob_get_clean();
		JFactory::getApplication()->close();
	}
}