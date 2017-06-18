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
 * System controller class.
 *
 * @package       Solidres
 * @subpackage    System
 * @since         0.1.0
 */
class SolidresControllerSystem extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array $data An array of input data.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowAdd($data = array())
	{
		$allow = null;

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		}
		else
		{
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param	array $data An array of input data.
	 * @param	string $key The name of the key for the primary key.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return parent::allowEdit($data, $key);
	}

	public function getModel($name = 'System', $prefix = 'SolidresModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Install sample data
	 *
	 * @return void
	 */
	public function installSampleData()
	{
		$model = $this->getModel();

		$canInstall = $model->canInstallSampleData();
		if ($canInstall)
		{
			$result = $model->installSampleData();
			if (!$result)
			{
				JError::raiseNotice(500, $model->getError());
			}
			else
			{
				$msg = JText::_('SR_INSTALL_SAMPLE_DATA_SUCCESS');
				$this->setRedirect('index.php?option=com_solidres', $msg);
			}
		}
		else
		{
			$msg = JText::_('SR_INSTALL_SAMPLE_DATA_IS_ALREADY_INSTALLED');
			$this->setRedirect('index.php?option=com_solidres', $msg);
		}
	}

	public function checkVerification()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');

		$files    = unserialize(base64_decode($this->input->getString('files')));
		$language = JFactory::getLanguage();
		if (is_array($files) && count($files))
		{
			$results = array();
			foreach ($files as $package => $file)
			{
				if (JFile::exists($file) && ($contents = JFile::read($file)))
				{
					$paths = array();
					if ($package == 'com_solidres')
					{
						$paths[] = JPATH_COMPONENT_ADMINISTRATOR;
						$paths[] = JPATH_ROOT . '/components/com_solidres';
						$paths[] = JPATH_ROOT . '/libraries/solidres';
						$paths[] = JPATH_ROOT . '/media/com_solidres/assets/css';
						$paths[] = JPATH_ROOT . '/media/com_solidres/assets/js';
						$paths[] = JPATH_ROOT . '/modules/mod_sr_checkavailability';
						$paths[] = JPATH_ROOT . '/modules/mod_sr_currency';
						$paths[] = JPATH_PLUGINS . '/content/solidres';
						$paths[] = JPATH_PLUGINS . '/extension/solidres';
						$paths[] = JPATH_PLUGINS . '/system/solidres';
						$language->load($package, JPATH_COMPONENT_ADMINISTRATOR);
						$language->load($package, JPATH_ROOT . '/components/com_solidres');
					}
					else
					{
						if (preg_match('/^(mod)/', $package))
						{
							$paths[] = JPATH_ROOT . '/modules/' . $package;
							$language->load($package, JPATH_ROOT . '/modules/' . $package);
						}
						elseif (preg_match('/^(plg)/', $package))
						{
							$plugin  = explode('_', $package, 3);
							$paths[] = JPATH_PLUGINS . '/' . $plugin[1] . '/' . $plugin[2];
							$language->load($package, JPATH_PLUGINS . '/' . $plugin[1] . '/' . $plugin[2]);
						}

						$paths[] = JPATH_ROOT . '/media/' . $package;
					}
					$package           = JText::_(strtoupper($package));
					$results[$package] = array(
						'removed'  => array(),
						'modified' => array(),
						'new'      => array()
					);
					$currentFiles      = $this->getCurrentFiles($paths);
					$oldFiles          = array();
					$fileList          = explode("\n", $contents);
					foreach ($fileList as $fileName)
					{

						list($md5, $filePath) = preg_split('/\s+/', $fileName, 2);

						if (!JFile::exists(JPATH_ROOT . '/' . $filePath))
						{
							$results[$package]['removed'][] = $filePath;
						}
						elseif (($content = JFile::read(JPATH_ROOT . '/' . $filePath)) && $md5 !== md5($content))
						{
							$results[$package]['modified'][] = $filePath;
						}
						if (basename($filePath) != 'checksums')
						{
							$oldFiles[] = $filePath;
						}
					}
					$results[$package]['new'] = array_values(array_diff($currentFiles, $oldFiles));
				}
			}
		}
		echo new JResponseJson($results);
		JFactory::getApplication()->close();
	}

	protected function getCurrentFiles($paths)
	{
		$files    = array();
		$pathRoot = preg_replace('/\/+|\\+|\\\\+/', '/', JPATH_ROOT);
		foreach ($paths as $path)
		{
			if (JFolder::exists($path))
			{
				$fileList = JFolder::files($path, '.', true, true);
				if (count($fileList))
				{
					foreach ($fileList as $file)
					{
						if (basename($file) == 'checksums')
						{
							unset($file);
							continue;
						}
						$file    = preg_replace('/\/+|\\+|\\\\+/', '/', $file);
						$file    = str_replace($pathRoot, '', $file);
						$file    = preg_replace('/^\/+/', '', $file);
						$files[] = $file;
					}
				}
			}
		}

		return array_unique($files);
	}

	public function togglePluginState()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$extTable = JTable::getInstance('Extension');
		$data     = array('enabled' => 'NULL');

		if ($extTable->load((int) $this->input->getInt('extension_id')))
		{
			$enabled = !(bool) $extTable->get('enabled');
			$extTable->set('enabled', (int) $enabled);

			if ($extTable->store())
			{
				$data['enabled'] = (int) $enabled;
			}
		}

		ob_clean();

		echo json_encode($data);

		JFactory::getApplication()->close();
	}

	public function getLogFile()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$file = $this->input->getPath('file');
		$data = array();

		if (file_exists($file) && ($content = file_get_contents($file)))
		{
			$data['content'] = $content;
			$data['status']  = true;
		}
		else
		{
			$data['content'] = 'File: ' . $file . ' not found.';
			$data['status']  = false;
		}

		ob_clean();

		echo json_encode($data);

		JFactory::getApplication()->close();
	}

	public function progressThumbnails()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		JLoader::import('joomla.filesystem.folder');

		$app            = JFactory::getApplication();
		$solidresParams = JComponentHelper::getParams('com_solidres');
		$targetDir      = SRPATH_MEDIA_IMAGE_SYSTEM;
		$targetThumbDir = $targetDir . '/thumbnails';
		$backup         = $targetThumbDir . '_backup';
		$thumbSizes     = preg_split("/\r\n|\n|\r/", trim($solidresParams->get('thumb_sizes', '')));

		echo '[5%]';
		echo str_pad("", 1024, " ");

		ob_flush();
		flush();
		usleep(25000);

		try
		{
			if (empty($thumbSizes))
			{
				throw new Exception('Thumbnail sizes not found.');
			}

			$jImage = new JImage;
			$images = JFolder::files($targetDir, 'JPE?G|jpe?g|GIF|gif|PNG|png', false, true);

			if (JFolder::exists($backup))
			{
				JFolder::delete($backup);
			}

			if (!JFolder::move($targetThumbDir, $backup) || !JFolder::create($targetThumbDir))
			{
				throw new Exception('Cannot create a backup directory.');
			}

			$count        = count($images);
			$processCount = 1;

			foreach ($images as $imageFile)
			{
				$jImage->loadFile($imageFile);
				$name = basename($imageFile);
				$type = $jImage::getImageFileProperties($imageFile)->type;

				if ($thumbs = $jImage->generateThumbs(array('300x250', '75x75'), 5))
				{
					if (!JFolder::exists($targetThumbDir . '/1'))
					{
						JFolder::create($targetThumbDir . '/1');
					}

					if (!JFolder::exists($targetThumbDir . '/2'))
					{
						JFolder::create($targetThumbDir . '/2');
					}

					$thumbs[0]->toFile($targetThumbDir . '/1/' . $name, $type);
					$thumbs[1]->toFile($targetThumbDir . '/2/' . $name, $type);
				}

				$jImage->createThumbs($thumbSizes, 5, $targetThumbDir);

				$processCount++;
				$processState = ($processCount / $count) * 100 . '%';

				echo '[' . $processState . ']';
				echo str_pad("", 1024, " ");

				ob_flush();
				flush();
				usleep(25000);
			}

		}
		catch (Exception $e)
		{
			if (JFolder::exists($backup))
			{
				if (JFolder::exists($targetThumbDir))
				{
					JFolder::delete($targetThumbDir);
				}

				rename($backup, $targetThumbDir);
			}

			echo $e->getMessage();
		}

		if (JFolder::exists($backup))
		{
			JFolder::delete($backup);
		}

		echo '[100%]';
		echo str_pad("", 1024, " ");

		ob_flush();
		flush();
		usleep(25000);

		ob_end_flush();

		$app->close();
	}

	public function renameOverrideFiles()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		JLoader::import('joomla.filesystem.folder');

		$solidresModules = array(
			'mod_sr_advancedsearch',
			'mod_sr_camera',
			'mod_sr_checkavailability',
			'mod_sr_currency',
			'mod_sr_filter',
			'mod_sr_roomtypes',
			'mod_sr_locationmap',
			'mod_sr_coupons',
			'mod_sr_extras',
			'mod_sr_assets',
			'mod_sr_map',
			'mod_sr_myrecentsearches',
			'mod_sr_feedbacks',
			'mod_sr_vegas',
			'mod_sr_experience_search',
			'mod_sr_experience_list',
		);

		$frontendTemplateNames = JFolder::folders(JPATH_ROOT . '/templates/');
		$overrideCandidates    = array_merge(array('com_solidres', 'layouts/com_solidres'), $solidresModules);

		ob_clean();

		try
		{
			foreach ($frontendTemplateNames as $frontendTemplateName)
			{
				foreach ($overrideCandidates as $candidate)
				{
					$candidatePath = JPATH_ROOT . '/templates/' . $frontendTemplateName . '/html/' . $candidate;

					if (JFolder::exists($candidatePath) && !@rename($candidatePath, $candidatePath . '-SR_disabled'))
					{
						throw new Exception(JText::_('Rename failed'));
					}
				}
			}

			echo 'Success';
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		JFactory::getApplication()->close();

	}

	public function checkUpdates()
	{
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		$url = 'https://www.solidres.com/checkupdates';

		try
		{
			if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL))
			{
				throw new RuntimeException(JText::_('SR_CHECK_UPDATES_ERROR_INVALID_URL'));
			}

			if (!JFactory::getUser()->authorise('core.admin', 'com_solidres'))
			{
				throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'));
			}

			$this->postFindUpdates($url);
			$this->setMessage(JText::_('SR_CHECK_UPDATES_SUCCESSFUL'));

		}
		catch (RuntimeException $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_solidres&view=system', false));
	}

	public function postFindUpdates($url)
	{
		JTable::addIncludePath(JPATH_LIBRARIES . '/joomla/table');
		$table = JTable::getInstance('Extension', 'JTable');
		$table->load(JComponentHelper::getComponent('com_solidres')->id);
		$this->addViewPath(JPATH_ADMINISTRATOR . '/components/com_solidres/views');
		$this->addModelPath(JPATH_ADMINISTRATOR . '/components/com_solidres/models', 'SolidresModel');

		$manifest   = json_decode($table->get('manifest_cache'));
		$view       = $this->getView('System', 'html', 'SolidresView');
		$plugins    = $view->get('solidresPlugins');
		$modules    = $view->get('solidresModules');
		$templates  = $this->getModel()->getSolidresTemplates();
		$extensions = array('com_solidres' => $manifest->version);

		foreach ($plugins as $group => $items)
		{
			foreach ($items as $item)
			{
				if ($table->load(array('type' => 'plugin', 'folder' => $group, 'element' => $item)))
				{
					$manifest = json_decode($table->get('manifest_cache'));

					$extensions['plg_' . $group . '_' . $item] = $manifest->version;
				}
			}
		}

		foreach ($modules as $module)
		{
			if ($table->load(array('type' => 'module', 'enabled' => '1', 'element' => $module)))
			{
				$manifest            = json_decode($table->get('manifest_cache'));
				$extensions[$module] = $manifest->version;
			}
		}

		if (!empty($templates))
		{
			foreach ($templates as $template)
			{
				$extensions['tpl_' . $template->template] = $template->manifest->version;
			}
		}

		$data = array(
			'data' => array(
				'extensions' => $extensions
			),
		);

		static $log;

		if ($log == null)
		{
			$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'solidres_update.php';
			$log                  = JLog::addLogger($options);
		}

		try
		{
			JLog::add('Start checking for update', JLog::DEBUG);
			$response = JHttpFactory::getHttp()->post($url, $data, null, 5);
		}
		catch (UnexpectedValueException $e)
		{
			JLog::add('Could not connect to update server: ' . $url . ' ' . $e->getMessage(), JLog::DEBUG);
			return false;
		}
		catch (RuntimeException $e)
		{
			JLog::add('Could not connect to update server: ' . $url . ' ' . $e->getMessage(), JLog::DEBUG);
			return false;
		}
		catch (Exception $e)
		{
			JLog::add('Unexpected error connecting to update server: ' . $url . ' ' . $e->getMessage(), JLog::DEBUG);
			return false;
		}

		if ($response->code !== 200)
		{
			JLog::add('Could not connect to update server', JLog::DEBUG);
			return false;
		}

		$updates = json_decode(trim($response->body), true);

		// The success response contain a json of updates extension list, if it contain 'data' index, it means
		// not successful
		if (is_array($updates) && !empty($updates) && json_last_error() == JSON_ERROR_NONE && !isset($updates['data']))
		{
			JLoader::import('joomla.filesystem.folder');
			JLoader::import('joomla.filesystem.file');

			$cachePath = JPATH_ADMINISTRATOR . '/components/com_solidres/views/system/cache';

			if (!JFolder::exists($cachePath))
			{
				if (!JFolder::create($cachePath, 0755))
				{
					JLog::add('Solidres update cache folder failed to be created', JLog::DEBUG);
				}
			}

			if (version_compare(PHP_VERSION, '5.4.0', '>='))
			{
				$updateContent = json_encode($updates, JSON_PRETTY_PRINT);
			}
			else
			{
				$updateContent = json_encode($updates);
			}

			if (!JFile::write($cachePath . '/updates.json', $updateContent))
			{
				JLog::add('Solidres update cache file failed to be created', JLog::DEBUG );
			}
		}
	}

	public function databaseFix()
	{
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		if (!JFactory::getUser()->authorise('core.admin', 'com_solidres'))
		{
			throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$model = $this->getModel();

		if ($model->databaseFix())
		{
			$this->setRedirect(JRoute::_('index.php?option=com_solidres&view=system', false), 'Solidres database schemas is up to date.')
				->redirect();
		}

		$this->setRedirect(JRoute::_('index.php?option=com_solidres&view=system', false))
			->redirect();
	}
}