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

JLoader::register('SolidresHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/helper.php');
JLoader::register('SolidresControllerReservationBase', JPATH_COMPONENT_ADMINISTRATOR . '/controllers/reservationbase.json.php');

/**
 * Controller to handle one-page reservation form
 *
 * @package       Solidres
 * @subpackage    Reservation
 * @since         0.1.0
 */
class SolidresControllerReservation extends SolidresControllerReservationBase
{

	public function removeCoupon()
	{
		$app     = JFactory::getApplication();
		$context = 'com_solidres.reservation.process';
		$status  = false;

		$currentAppliedCoupon = $app->getUserState($context . '.coupon');

		if ($currentAppliedCoupon['coupon_id'] == $app->input->get('id', 0, 'int'))
		{
			$app->setUserState($context . '.coupon', null);
			$status = true;
		}

		$response = array('status' => $status, 'message' => '');

		echo json_encode($response);

		die(1);
	}

	public function requestBooking()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		try
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables');
			$assetTable = JTable::getInstance('ReservationAsset', 'SolidresTable');
			$assetId    = $this->input->getInt('assetId');

			if ($assetTable->load($assetId))
			{
				$name    = $this->input->getString('fullname');
				$phone   = $this->input->getString('phone');
				$email   = $this->input->getString('email');
				$message = $this->input->getString('message');
				$params  = new Joomla\Registry\Registry($assetTable->params);
				if ($params->get('use_captcha'))
				{
					JPluginHelper::importPlugin('captcha', 'recaptcha');
					$dispatcher = JEventDispatcher::getInstance();
					$results    = $dispatcher->trigger('onCheckAnswer');

					if (in_array(false, $results, true))
					{
						throw new Exception('Invalid captcha');
					}
				}
				$recipients = array();

				if ($assetTable->get('email') && filter_var($assetTable->get('email'), FILTER_VALIDATE_EMAIL))
				{
					$recipients[] = $assetTable->get('email');
				}

				$additional = explode(',', $params->get('additional_notification_emails'));

				if (count($additional))
				{
					foreach ($additional as $mail)
					{
						if (filter_var($mail, FILTER_VALIDATE_EMAIL))
						{
							$recipients[] = $mail;
						}
					}
				}

				if (empty($recipients))
				{
					throw new Exception('Recipients not found.');
				}

				$mailer = JFactory::getMailer();
				$mailer->setSender(array(
					$app->get('mailfrom'),
					$app->get('fromname')
				));

				$mailer->addRecipient($recipients);
				$mailer->isHtml(false);
				$mailer->setSubject(JText::plural('SR_INQUIRY_FORM_SEND_MAIL_SUBJECT_PLURAL', strtoupper($name), strtoupper($assetTable->name)));
				$body = $params->get('email_content_format');

				if (empty($body))
				{
					$body = 'Hi,
									You have a new booking inquiry for ' . ucfirst($assetTable->name) . ' via ' . $app->get('sitename') . ':
									Name: ' . $name . '
									Email: ' . $email . '
									Phone: ' . $phone . '
									Message: ' . $message . '
									Cheers,';
				}
				else
				{
					$body = str_replace(
						array('{site_name}', '{asset_name}', '{name}', '{phone}', '{email}', '{message}'),
						array($app->get('sitename'), ucfirst($assetTable->name), $name, $phone, $email, $message),
						$body
					);
				}

				$mailer->setBody($body);

				if ($mailer->send())
				{
					$response = array(
						'status'  => 'success',
						'message' => JText::_('SR_INQUIRY_FORM_SEND_MAIL_SUCCESS_MESSAGE')
					);
				}

			}
		}
		catch (Exception $e)
		{
			$response = array(
				'status'  => 'error',
				'message' => $e->getMessage()
			);

		}

		echo json_encode($response);

		$app->close();
	}
}