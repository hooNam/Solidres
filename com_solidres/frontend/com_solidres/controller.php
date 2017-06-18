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
 * Solidres Component Controller
 *
 * @package     Solidres
 * @since 		0.1.0
 */
class SolidresController extends SRControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable If true, the view output will be cached
	 * @param	boolean			$urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JControllerLegacy		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;
		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);
		$safeurlparams = array(
			'catid'            => 'INT',
			'id'               => 'INT',
			'cid'              => 'ARRAY',
			'year'             => 'INT',
			'month'            => 'INT',
			'limit'            => 'INT',
			'limitstart'       => 'INT',
			'showall'          => 'INT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD',
			'location'         => 'STRING',
			'categories'       => 'STRING',
			'mode'             => 'STRING',
			'Itemid'           => 'UINT'
		);
		$viewName = $this->input->get('view');
		$user = JFactory::getUser();
		JPluginHelper::importPlugin('solidres');
		JEventDispatcher::getInstance()->trigger('onSolidresBeforeDisplay', array($viewName, &$cachable, &$safeurlparams));
		$return = JUri::getInstance()->toString();

		switch ($viewName)
		{
			case 'articles':
				if ($user->get('guest') == 1)
				{
					// Redirect to login page.
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($return), false));

					return;
				}

				if (!$user->authorise('core.create', 'com_content'))
				{
					return;
				}

				if ($this->input->get('layout') === 'modal')
				{
					JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
					$this->addViewPath(JPATH_ADMINISTRATOR . '/components/com_solidres/views');
					JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_solidres/models/forms');
					JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/models');

					$model = JModelLegacy::getInstance('Articles', 'SolidresModel', array('ignore_request' => true));
					$model->setState('filter.author_id', $user->get('id'));
					$model->setState('filter.author_id.include', true);

					$document = JFactory::getDocument();
					$viewType = $document->getType();
					$viewName = 'Articles';
					$viewLayout = 'modal';

					$view = $this->getView($viewName, $viewType, '', array('base_path' => JPATH_ADMINISTRATOR . '/components/com_solidres', 'layout' => $viewLayout));
					$view->setModel($model, true);
					$view->document = $document;
					$view->display();
				}
				break;
			default:
				parent::display($cachable, $safeurlparams);
				break;
		}

		return $this;
	}
}