<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
			Text::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_blog&view=articles',
			$vName == 'articles'
		);
		JHtmlSidebar::addEntry(
			Text::_('COM_BLOG_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_blog',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_BLOG_SUBMENU_FEATURED'),
			'index.php?option=com_blog&view=featured',
			$vName == 'featured'
		);

		if (ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_blog')->get('custom_fields_enable', '1'))
		{
			JHtmlSidebar::addEntry(
				Text::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_blog.article',
				$vName == 'fields.fields'
			);
			JHtmlSidebar::addEntry(
				Text::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_blog.article',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use ComponentHelper::filterText() instead.
	 */
	public static function filterText($text)
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use ComponentHelper::filterText() instead', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		return ComponentHelper::filterText($text);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$config = (object) array(
			'related_tbl'   => 'blog',
			'state_col'     => 'state',
			'group_col'     => 'catid',
			'relation_type' => 'category_or_group',
		);

		return parent::countRelations($items, $config);
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$parts   = explode('.', $extension);
		$section = count($parts) > 1 ? $parts[1] : null;

		$config = (object) array(
			'related_tbl'   => ($section === 'category' ? 'categories' : 'blog'),
			'state_col'     => ($section === 'category' ? 'published' : 'state'),
			'group_col'     => 'tag_id',
			'extension'     => $extension,
			'relation_type' => 'tag_assigments',
		);

		return parent::countRelations($items, $config);
	}

	/**
	 * Returns a valid section for articles. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 *
	 * @return  string|null  The new section
	 *
	 * @since   3.7.0
	 */
	public static function validateSection($section)
	{
		if (Factory::getApplication()->isClient('site'))
		{
			// On the front end we need to map some sections
			switch ($section)
			{
				// Editing an article
				case 'form':

				// Category list view
				case 'featured':
				case 'category':
					$section = 'article';
			}
		}

		if ($section != 'article')
		{
			// We don't know other sections
			return null;
		}

		return $section;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		Factory::getLanguage()->load('com_blog', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_blog.article'    => Text::_('COM_BLOG'),
			'com_blog.categories' => Text::_('JCATEGORY')
		);

		return $contexts;
	}
}
