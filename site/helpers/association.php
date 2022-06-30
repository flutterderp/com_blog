<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;

JLoader::register('BlogHelper', JPATH_ADMINISTRATOR . '/components/com_blog/helpers/blog.php');
JLoader::register('BlogHelperRoute', JPATH_SITE . '/components/com_blog/helpers/route.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Blog Component Association Helper
 *
 * @since  3.0
 */
abstract class BlogHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id      Id of the item
	 * @param   string   $view    Name of the view
	 * @param   string   $layout  View layout
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null, $layout = null)
	{
		$jinput    = Factory::getApplication()->input;
		$view      = $view === null ? $jinput->get('view') : $view;
		$component = $jinput->getCmd('option');
		$id        = empty($id) ? $jinput->getInt('id') : $id;

		if ($layout === null && $jinput->get('view') == $view && $component == 'com_blog')
		{
			$layout = $jinput->get('layout', '', 'string');
		}

		if ($view === 'article')
		{
			if ($id)
			{
				$user      = Factory::getUser();
				$groups    = implode(',', $user->getAuthorisedViewLevels());
				$db        = Factory::getDbo();
				$advClause = array();

				// Filter by user groups
				$advClause[] = 'c2.access IN (' . $groups . ')';

				// Filter by current language
				$advClause[] = 'c2.language != ' . $db->quote(Factory::getLanguage()->getTag());

				if (!$user->authorise('core.edit.state', 'com_blog') && !$user->authorise('core.edit', 'com_blog'))
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date = Factory::getDate();

					$nowDate = $db->quote($date->toSql());

					$advClause[] = '(c2.publish_up = ' . $nullDate . ' OR c2.publish_up <= ' . $nowDate . ')';
					$advClause[] = '(c2.publish_down = ' . $nullDate . ' OR c2.publish_down >= ' . $nowDate . ')';

					// Filter by published
					$advClause[] = 'c2.state = 1';
				}

				$associations = Associations::getAssociations('com_blog', '#__blog', 'com_blog.item', $id, 'id', 'alias', 'catid', $advClause);

				$return = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = BlogHelperRoute::getArticleRoute($item->id, (int) $item->catid, $item->language, $layout);
				}

				return $return;
			}
		}

		if ($view === 'category' || $view === 'categories')
		{
			return self::getCategoryAssociations($id, 'com_blog', $layout);
		}

		return array();
	}

	/**
	 * Method to display in frontend the associations for a given article
	 *
	 * @param   integer  $id  Id of the article
	 *
	 * @return  array  An array containing the association URL and the related language object
	 *
	 * @since  3.7.0
	 */
	public static function displayAssociations($id)
	{
		$return = array();

		if ($associations = self::getAssociations($id, 'article'))
		{
			$levels    = Factory::getUser()->getAuthorisedViewLevels();
			$languages = LanguageHelper::getLanguages();

			foreach ($languages as $language)
			{
				// Do not display language when no association
				if (empty($associations[$language->lang_code]))
				{
					continue;
				}

				// Do not display language without frontend UI
				if (!array_key_exists($language->lang_code, LanguageHelper::getInstalledLanguages(0)))
				{
					continue;
				}

				// Do not display language without specific home menu
				if (!array_key_exists($language->lang_code, Multilanguage::getSiteHomePages()))
				{
					continue;
				}

				// Do not display language without authorized access level
				if (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					continue;
				}

				$return[$language->lang_code] = array('item' => $associations[$language->lang_code], 'language' => $language);
			}
		}

		return $return;
	}
}
