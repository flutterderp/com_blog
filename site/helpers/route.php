<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;

/**
 * Blog Component Route Helper.
 *
 * @since  1.5
 */
abstract class BlogHelperRoute
{
	/**
	 * Get the article route.
	 *
	 * @param   integer  $id        The route of the content item.
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getArticleRoute($id, $catid = 0, $language = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_blog&view=article&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}

	/**
	 * Get the category route.
	 *
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute($catid, $language = 0, $layout = null)
	{
		if ($catid instanceof CategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			return '';
		}

		$link = 'index.php?option=com_blog&view=category&id=' . $id;

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}

	/**
	 * Get the form route.
	 *
	 * @param   integer  $id  The form ID.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getFormRoute($id)
	{
		return 'index.php?option=com_blog&task=article.edit&a_id=' . (int) $id;
	}
}
