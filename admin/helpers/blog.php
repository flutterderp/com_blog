<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Blog component helper.
 *
 * @since  1.6
 */
class BlogHelper extends JHelperContent
{
	public static $extension = 'com_blog';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_blog&view=articles',
			$vName == 'articles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_BLOG_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_blog',
			$vName == 'categories');
		JHtmlSidebar::addEntry(
			JText::_('COM_BLOG_SUBMENU_FEATURED'),
			'index.php?option=com_blog&view=featured',
			$vName == 'featured'
		);
	}

	/**
	 * Applies the blog tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use JComponentHelper::filterText() instead.
	*/
	public static function filterText($text)
	{
		JLog::add('BlogHelper::filterText() is deprecated. Use JComponentHelper::filterText() instead.', JLog::WARNING, 'deprecated');

		return JComponentHelper::filterText($text);
	}
}
