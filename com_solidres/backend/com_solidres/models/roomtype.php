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
 * RoomType model.
 *
 * @package     Solidres
 * @subpackage	RoomType
 * @since		0.1.0
 */
class SolidresModelRoomType extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = null;

	/**
	 * @var		string	The event to trigger after deleting the data.
	 * @since	1.6
	 */
	protected $event_after_delete = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_after_save = null;

	/**
	 * @var		string	The event to trigger after deleting the data.
	 * @since	1.6
	 */
	protected $event_before_delete = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_before_save = null;

	/**
	 * @var		string	The event to trigger after changing the published state of the data.
	 * @since	1.6
	 */
	protected $event_change_state = null;

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->event_after_delete 	= 'onRoomTypeAfterDelete';
		$this->event_after_save 	= 'onRoomTypeAfterSave';
		$this->event_before_delete 	= 'onRoomTypeBeforeDelete';
		$this->event_before_save 	= 'onRoomTypeBeforeSave';
		$this->event_change_state 	= 'onRoomTypeChangeState';
		$this->text_prefix 			= strtoupper($this->option);

		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
	}

	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('roomtype.id', $pk);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		if ($app->isAdmin() || $app->input->get('api_request'))
		{
			return $user->authorise('core.delete', 'com_solidres');
		}
		else
		{
			return SRUtilities::isAssetPartner($user->get('id'), $record->reservation_asset_id);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		if ($app->isAdmin() || $app->input->get('api_request'))
		{
			return $user->authorise('core.edit.state', 'com_solidres');
		}
		else
		{
			return SRUtilities::isAssetPartner($user->get('id'), $record->reservation_asset_id);
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'RoomType', $prefix = 'SolidresTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_solidres.roomtype', 'roomtype', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('roomtype.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('reservation_asset_id', 'action', 'core.edit');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('reservation_asset_id', 'action', 'core.create');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_solidres.edit.roomtype.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$data->standard_tariff_title = isset($data->default_tariff->title) ? $data->default_tariff->title : '' ;
		$data->standard_tariff_description = isset($data->default_tariff->description) ? $data->default_tariff->description: '' ;

		// Get the dispatcher and load the users plugins.
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('extension');
		JPluginHelper::importPlugin('solidres');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onRoomTypePrepareData', array('com_solidres.roomtype', $data));

		// Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'extension')
	{
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);
		JPluginHelper::importPlugin('solidres');

		// Get the dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onRoomTypePrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception))
			{
				throw new Exception($error);
			}
		}
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('roomtype.id');

		$item = parent::getItem($pk);
		$tableRA = JTable::getInstance('ReservationAsset', 'SolidresTable');
		$currencyModel = JModelLegacy::getInstance('Currency', 'SolidresModel', array('ignore_request' => true));

		if ($item->id)
		{
			$dbo = JFactory::getDbo();
			$query = $dbo->getQuery(true);
			$media = JModelLegacy::getInstance('MediaList', 'SolidresModel', array('ignore_request' => true));
			$nullDate = substr($dbo->getNullDate(), 0, 10);

			// Load the standard tariff
			$query->select('p.*, c.currency_code, c.currency_name');
			$query->from($dbo->quoteName('#__sr_tariffs').' as p');
			$query->join('left', $dbo->quoteName('#__sr_currencies').' as c ON c.id = p.currency_id');
			$query->where('room_type_id = '.(empty($item->id) ? 0 : (int) $item->id));
			$query->where('valid_from = '. $dbo->quote($nullDate));
			$query->where('valid_to = '. $dbo->quote($nullDate));

			$item->default_tariff = $dbo->setQuery($query)->loadObject();

			if (isset($item->default_tariff))
			{
				$query->clear();
				$query->select('id, tariff_id, price, w_day, guest_type, from_age, to_age');
				$query->from($dbo->quoteName('#__sr_tariff_details'));
				$query->where('tariff_id = ' . (int) $item->default_tariff->id);
				$query->order('w_day ASC');
				$item->default_tariff->details = $dbo->setQuery($query)->loadObjectList();
			}

			$query->clear();
			$query->select('a.id, a.label');
			$query->from($dbo->quoteName('#__sr_rooms').' a');
			$query->where('room_type_id = '.(empty($item->id) ? 0 : (int) $item->id));
			$dbo->setQuery($query);
			$item->roomList = $dbo->loadObjectList();

			// Load media
			$media->setState('filter.reservation_asset_id', NULL);
			$media->setState('filter.room_type_id', (int) $item->id);
			$item->media = $media->getItems();
		}

        // Load currency
		$tableRA->load($item->reservation_asset_id);
		$currency = $currencyModel->getItem($tableRA->currency_id);

        $item->currency = $currency;

		// Load tax id
		$item->tax_id = $tableRA->tax_id;

		return $item;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias = JApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplicationHelper::stringURLSafe($table->name);
		}

		if (empty($table->params))
		{
			$table->params = '';
		}

		if (empty($table->id))
		{
			$table->created_date = $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('MAX(ordering)')->from($db->quoteName('#__sr_room_types'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else
		{
			$table->modified_date	= $date->toSql();
			$table->modified_by		= $user->get('id');
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'reservation_asset_id = '.(int) $table->reservation_asset_id;
		return $condition;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('extension');
		JPluginHelper::importPlugin('solidres');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($data, $table, $isNew));
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($data, $table, $isNew));

		$pkName = $table->getKeyName();
		if (isset($table->$pkName))
		{
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		JPluginHelper::importPlugin('solidres');

		return parent::delete($pks);
	}
}