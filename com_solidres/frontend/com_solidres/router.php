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

class SRRouter extends JComponentRouterBase
{
	protected $hub;

	public function __construct($app = null, $menu = null)
	{
		parent::__construct($app, $menu);

		if ($this->hub = JPluginHelper::isEnabled('solidres', 'hub'))
		{
			JPluginHelper::importPlugin('solidres', 'hub');
		}
	}

	public function build(&$query)
	{
		$segments = array();
		$menus    = JFactory::getApplication()->getMenu();
		$db       = JFactory::getDbo();
		$sql      = $db->getQuery(true);
		$hubQuery = $query;

		if (isset($query['Itemid']))
		{
			$menuItem = $menus->getItem($query['Itemid']);
		}
		else
		{
			$menuItem = $menus->getActive();
		}

		if ($menuItem
			&& $menuItem->query['option'] != 'com_solidres'
			&& isset($query['Itemid'])
		)
		{
			$menuItem = null;
			unset($query['Itemid']);
		}

		$view = isset($query['view']) ? strtolower($query['view']) : null;
		$slug = isset($query['id']) ? (int) $query['id'] : null;

		if (!$view && isset($query['task']) && strpos($query['task'], '.') !== false)
		{
			$task = explode('.', $query['task'], 2);

			if ($task[0] == 'reservationasset')
			{
				$view = $task[0];
			}
		}

		if ($menuItem)
		{
			if (isset($menuItem->query['view']) && $menuItem->query['view'] == $view)
			{
				unset($query['view']);
			}

			if (isset($menuItem->query['id']) && $menuItem->query['id'] == $slug)
			{
				unset($query['id']);

				return $segments;
			}
		}

		if ($slug && in_array($view, array('reservationasset', 'subscriptionform', 'experience')))
		{
			if (strpos($slug, ':') === false)
			{
				switch ($view)
				{
					case 'reservationasset':
						$sql->select('CONCAT(a.alias, ":", a.id) AS slug')
							->from($db->qn('#__sr_reservation_assets', 'a'))
							->where('a.id = ' . (int) $query['id']);
						$db->setQuery($sql);
						$slug = $db->loadResult();
						break;

					case 'subscriptionform':
						$sql->select('a.id, a.title')
							->from($db->qn('#__sr_subscription_levels', 'a'))
							->where('a.id = ' . (int) $query['id']);
						$db->setQuery($sql);
						$row  = $db->loadObject();
						$slug = JFilterOutput::stringURLSafe($row->title) . ':' . $row->id;
						break;

					case 'experience':
						$sql->select('CONCAT(a.alias, ":", a.id) AS slug')
							->from($db->qn('#__sr_experiences', 'a'))
							->where('a.id = ' . (int) $query['id']);
						$db->setQuery($sql);
						$slug = $db->loadResult();
						break;
				}
			}

			if (isset($query['view']))
			{
				$segments[] = $view;

				unset($query['view']);
			}

			$segments[] = $slug;

			unset($query['id']);
		}

		if ($view == 'experiences' && isset($query['category_id']))
		{
			$sql->clear()
				->select('a.alias')
				->from($db->qn('#__sr_experience_categories', 'a'))
				->where('a.id = ' . (int) $query['category_id']);
			$db->setQuery($sql);

			if ($alias = $db->loadResult())
			{
				$segments[] = 'category:' . $alias;
				unset($query['category_id']);
			}
		}

		if ($this->hub)
		{
			JEventDispatcher::getInstance()->trigger('onSolidresBuildRoute', array($hubQuery, &$segments));
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	public function parse(&$segments)
	{
		$vars  = array();
		$count = count($segments);

		for ($i = 0; $i < $count; $i++)
		{
			$segments[$i] = str_replace('-', ':', $segments[$i]);
		}

		if ($count > 0)
		{
			if (strpos($segments[0], ':') !== false)
			{
				$array = explode(':', $segments[0]);
				$id    = (int) $array[count($array) - 1];
				array_pop($array);
				$alias = join('-', $array);
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id, a.alias')
					->from($db->qn('#__sr_reservation_assets', 'a'))
					->where('a.id = ' . (int) $id);
				$db->setQuery($query);
				$asset = $db->loadObject();

				if ($asset && $asset->alias == $alias)
				{
					$vars['view'] = 'reservationasset';
					$vars['id']   = $id;
				}
			}

			if (preg_match('/^(category\:)/', $segments[0]))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id')
					->from($db->qn('#__sr_experience_categories', 'a'))
					->where('a.alias = ' . $db->q(str_replace(array('category:', ':'), array('', '-'), $segments[0])));
				$db->setQuery($query);
				$vars['view']        = 'experiences';
				$vars['category_id'] = (int) $db->loadResult();
			}
			elseif (isset($segments[1]))
			{
				$vars['view'] = $segments[0];
				preg_match('/([0-9]+)$/', $segments[1], $matches);

				if (!empty($matches[0]))
				{
					$vars['id'] = (int) $matches[1];
				}
				else
				{
					$vars['id'] = (int) $segments[1];
				}
			}
		}

		if ($this->hub)
		{
			JEventDispatcher::getInstance()->trigger('onSolidresParseRoute', array(&$vars, $segments));
		}

		return $vars;
	}
}

function solidresBuildRoute(&$query)
{
	$router = new SRRouter;

	return $router->build($query);
}


function solidresParseRoute($segments)
{
	$router = new SRRouter;

	return $router->parse($segments);
}
