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

class SRLayoutHelper
{
	protected static $includePaths = array();
	protected static $instance;

	public static function getInstance()
	{
		if (!is_object(static::$instance))
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	public static function addIncludePath($paths = array())
	{
		settype($paths, 'array');
		foreach ($paths as $includePath)
		{
			if (!in_array($includePath, self::$includePaths))
			{
				array_unshift(self::$includePaths, $includePath);
			}
		}
	}

	public static function resetPath()
	{
		self::addIncludePath(
			array(
				JPATH_BASE . '/components/com_solidres/layouts',
				JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/com_solidres'
			)
		);

		return self::$includePaths;
	}

	public static function render($layoutId, $displayData = array(), $reset = true)
	{
		if ($reset)
		{
			self::resetPath();
		}
		$rawPath = str_replace('.', '/', $layoutId) . '.php';
		$path    = JPath::find(self::$includePaths, $rawPath);
		if (!empty($path))
		{
			ob_start();
			include $path;

			return ob_get_clean();
		}

		return false;
	}

}
