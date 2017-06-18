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
 * Tax table
 *
 * @package     Solidres
 * @subpackage	Tax
 * @since		0.1.0
 */
class SolidresTableTax extends JTable
{
	function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__sr_taxes', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		$query = $this->_db->getQuery(true);

		$query->update($this->_db->quoteName('#__sr_reservation_assets'))
			->set($this->_db->quoteName('tax_id') . ' = NULL')
			->where($this->_db->quoteName('tax_id') .' = ' . $pk);

		$this->_db->setQuery($query)->execute();

		if (SRPlugin::isEnabled('experience'))
		{
			$query->clear();
			$query->update($this->_db->quoteName('#__sr_experiences'))
			      ->set($this->_db->quoteName('tax_id') . ' = NULL')
			      ->where($this->_db->quoteName('tax_id') .' = ' . $pk);

			$this->_db->setQuery($query)->execute();
		}

		// Delete itself, finally
		return parent::delete($pk);
	}
}

