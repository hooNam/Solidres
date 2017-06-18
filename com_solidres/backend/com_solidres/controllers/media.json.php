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

require_once JPATH_LIBRARIES.'/solidres/media/helper.php';

/**
 * Media JSON controller class.
 *
 * @package     Solidres
 * @subpackage	Media
 * @since		0.1.0
 */
class SolidresControllerMedia extends JControllerForm
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
		$allow		= null;

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		} else {
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

	/**
	 * Method to upload a file from client side, storing and making thumbnail for images
	 *
	 * TODO add token check for file uploading
	 *
	 * @return JSON
	 */
	public function upload()
	{
		// Check for request forgeries
		if (!JSession::checkToken('request'))
		{
			die('{"jsonrpc" : "2.0", "error" : {"code": 104, "message": "'.JText::_('JINVALID_TOKEN').'"}, "id" : "id"}');
		}

		$user = JFactory::getUser();
		$srMedia = SRFactory::get('solidres.media.media');
		$date = JFactory::getDate();
		$model = $this->getModel('media');
		$err = NULL;
		$targetDir = SRPATH_MEDIA_IMAGE_SYSTEM;
		$targetThumbDir = SRPATH_MEDIA_IMAGE_SYSTEM.'/thumbnails';
		$solidresParams = JComponentHelper::getParams('com_solidres');

		static $log;

		if ($log == null)
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'media.php';
			$log = JLog::addLogger($options);
		}

		JLog::add('Start uploading', JLog::DEBUG );

		if (!$user->authorise('core.create', 'com_solidres'))
		{
			JError::raiseWarning(403, JText::_('SR_ERROR_CREATE_NOT_PERMITTED'));
			return;
		}

		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		JLog::add('Original file name '. $fileName, JLog::DEBUG );

		// Clean the fileName for security reasons
		$_FILES['file']['name'] = JFile::makeSafe($_FILES['file']['name']);
		$fileName = $_FILES['file']['name'];

		JLog::add('Cleaned file name '. $_FILES['file']['name'], JLog::DEBUG );

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		// Check the target file against our rules to see if it is allow to be uploaded
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		// Do not check the chunk since it is not valid
		if (strpos($contentType, "multipart") !== false && $chunks == 0)
		{
			if (!SRMediaHelper::canUpload($_FILES['file'], $err))
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 104, "message": "'.JText::_($err).'"}, "id" : "id"}');
				//return;
			}
		}

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		// ONLY PERFORM THESE LAST OPERATIONS WHEN THE FILE IS TOTALLY UPLOADED (NOT PARTLY UPLOADED)
		if ($chunks == 0 || ($chunk == $chunks - 1) )
		{
			$uploadedFilePath = $targetDir .'/'. $fileName;

			// Prepare some data for db storing
			$data = array(
				'type'  		=> 'IMAGE', // TODO: do we need to store this 'type'
				'value' 		=> $fileName,
				'name'  		=> $fileName,
				'created_date' 	=> $date->toSql(),
				'created_by'	=> $user->get('id'),
				'mime_type'		=> $srMedia->getMime($uploadedFilePath),
				'size'			=> filesize($uploadedFilePath)
			);

			// Attempt to save the data.
			if (!$model->save($data))
			{
				JLog::add('Can not save this file to db: '. $fileName, JLog::DEBUG );
				die('{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "'.JText::_('SR_ERROR_CAN_NOT_SAVE_DB').'"}, "id" : "id"}');
			}

			$thumbSizes = $solidresParams->get('thumb_sizes', "");
			$thumbSizes = preg_split("/\r\n|\n|\r/", $thumbSizes);
			// Validate sizes
			for ($tid = 0, $tCount = count($thumbSizes); $tid < $tCount; $tid++)
			{
				if (empty($thumbSizes[$tid]) || ctype_space($thumbSizes[$tid]))
				{
					unset($thumbSizes[$tid]);
				}
				else
				{
					trim($thumbSizes[$tid]);
				}
			}
			$legacyThumbSizes = array('300x250', '75x75');
			$legacyThumbPaths = array($targetThumbDir.'/1', $targetThumbDir.'/2');

			// If media is image, create thumbnail for it
			if (SRMediaHelper::isImage($uploadedFilePath))
			{
				$joomlaImage = new JImage();
				try {
					$joomlaImage->loadFile($uploadedFilePath);

					// Legacy thumbnails
					if ($thumbs = $joomlaImage->generateThumbs($legacyThumbSizes, 5))
					{
						// Parent image properties
						$imgProperties = $joomlaImage::getImageFileProperties($uploadedFilePath);

						foreach ($thumbs as $thumbIdx => $thumb)
						{
							$thumbFileName = $fileName;
							$thumbFileName = $legacyThumbPaths[$thumbIdx] . '/' . $thumbFileName;
							$thumb->toFile($thumbFileName, $imgProperties->type);
						}
					}

					// Create custom thumbnails
					if (!empty($thumbSizes))
					{
						$joomlaImage->createThumbs($thumbSizes, 5, $targetThumbDir);
					}
				}
				catch (Exception $e) {
					JLog::add('Exception when loading file: '. $fileName . '. The full error is ' . $e->getMessage() , JLog::DEBUG );
				}
			}
		}

		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}') ;
	}

	public function delete()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$mediaIds = $this->input->post->get('media', array(), 'array');
		$dbo = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$model = $this->getModel();

		$response = array();

		if (count($mediaIds))
		{
			foreach ($mediaIds as $mediaId)
			{
				$query->clear();
				$query->select('name')->from($dbo->quoteName('#__sr_media'))->where('id = '.$mediaId);
				$dbo->setQuery($query);
				$mediaName = $dbo->loadResult();

				if ($mediaName !== JFile::makeSafe($mediaName))
				{
					$filename = htmlspecialchars($mediaName, ENT_COMPAT, 'UTF-8');
					JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME', $filename));
					continue;
				}

				$fullPath = SRPATH_MEDIA_IMAGE_SYSTEM.'/'.$mediaName;
				$thumbPath1 = SRPATH_MEDIA_IMAGE_SYSTEM.'/thumbnails/1/'.$mediaName;
				$thumbPath2 = SRPATH_MEDIA_IMAGE_SYSTEM.'/thumbnails/2/'.$mediaName;
				$mediaNameParts = explode('.', $mediaName);

				$result = $model->delete($mediaId);
				if ($result)
				{
					JFile::delete(array($fullPath, $thumbPath1, $thumbPath2));
					$solidresParams = JComponentHelper::getParams('com_solidres');
					$thumbSizes = $solidresParams->get('thumb_sizes', "300x250\r\n75x75");
					$thumbSizes = preg_split("/\r\n|\n|\r/", $thumbSizes);
					foreach ($thumbSizes as $thumbSize)
					{
						JFile::delete(SRPATH_MEDIA_IMAGE_SYSTEM.'/thumbnails/'.$mediaNameParts[0].'_'.trim($thumbSize).'.'.$mediaNameParts[1]);
					}

					$response[] = $mediaId;
				}
			}
		}

		echo json_encode($response);

		die(1);
	}

	public function ajaxProgressMedia()
	{
		$token = $this->input->get('token', '', 'alnum');

		if ($token)
		{
			$this->input->set($token, 1);
		}

		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		if (!JFactory::getUser()->authorise('core.create', 'com_solidres') && !JFactory::getUser()->authorise('core.edit', 'com_solidres'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);

			return false;
		}

		$targetId  = (int) $this->input->getInt('targetId');
		$target    = strtolower($this->input->getString('target'));
		$mediaKeys = $this->input->get('mediaKeys', array(), 'array');
		$response  = array('status' => false, 'media_keys' => $mediaKeys);

		if ($targetId > 0 && count($mediaKeys))
		{
			$targetTable = '#__sr_media_' . $target . '_xref';
			$targetKey   = $target == 'roomtype' ? 'room_type_id' : 'reservation_asset_id';
			$db          = JFactory::getDbo();
			$query       = $db->getQuery(true)
				->delete($db->qn($targetTable))
				->where($db->qn($targetKey) . ' = ' . $targetId);
			$db->setQuery($query)
				->execute();

			$query->clear()
				->insert($db->qn($targetTable))
				->columns(array('media_id', $targetKey, 'weight'));

			foreach ($mediaKeys as $k => $v)
			{
				$query->values((int) $v . ',' . $targetId . ',' . (int) $k);
			}

			$db->setQuery($query);

			if ($db->execute())
			{
				$response['status'] = true;
			}
		}

		echo json_encode($response);

		JFactory::getApplication()->close();
	}

	public function ajaxRemoveMedia()
	{
		$token = $this->input->get('token', '', 'alnum');

		if ($token)
		{
			$this->input->set($token, 1);
		}

		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		if (!JFactory::getUser()->authorise('core.delete', 'com_solidres'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);

			return false;
		}

		$target   = strtolower($this->input->getString('target'));
		$targetId = (int) $this->input->getInt('targetId');
		$mediaId  = (int) $this->input->getInt('mediaId');
		$response = array('status' => false);

		if ($targetId > 0 && $mediaId > 0)
		{
			$targetTable = '#__sr_media_' . $target . '_xref';
			$targetKey   = $target == 'roomtype' ? 'room_type_id' : 'reservation_asset_id';
			$db          = JFactory::getDbo();
			$query       = $db->getQuery(true)
				->delete($db->qn($targetTable))
				->where($db->qn($targetKey) . ' = ' . $targetId . ' AND ' . $db->qn('media_id') . ' = ' . $mediaId);
			$db->setQuery($query);

			if ($db->execute())
			{
				$response['status'] = true;
			}
		}
		else
		{
			$response['message'] = 'Cannot remove media ID ' . $mediaId;
		}

		echo json_encode($response);

		JFactory::getApplication()->close();
	}
}