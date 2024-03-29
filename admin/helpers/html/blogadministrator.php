<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

JLoader::register('BlogHelper', JPATH_ADMINISTRATOR . '/components/com_blog/helpers/blog.php');

/**
 * Blog HTML helper
 *
 * @since  3.0
 */
abstract class JHtmlBlogAdministrator
{
	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $articleid  The article item id
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception
	 */
	public static function association($articleid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = Associations::getAssociations('com_blog', '#__blog', 'com_blog.item', $articleid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('c.*')
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__blog as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->where('c.id != ' . $articleid)
				->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$url     = Route::_('index.php?option=com_blog&task=article.edit&id=' . (int) $item->id);

					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br />' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover label label-association label-' . $item->lang_sef;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			HTMLHelper::_('bootstrap.popover');

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Show the feature/unfeature links
	 *
	 * @param   integer  $value      The state value
	 * @param   integer  $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public static function featured($value = 0, $i = 0, $canChange = true)
	{
		HTMLHelper::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'articles.featured', 'COM_BLOG_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'articles.unfeatured', 'COM_BLOG_FEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. HTMLHelper::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
