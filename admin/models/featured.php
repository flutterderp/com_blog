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
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\UCM\UCMType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('BlogModelArticles', __DIR__ . '/articles.php');

/**
 * Methods supporting a list of featured article records.
 *
 * @since  1.6
 */
class BlogModelFeatured extends BlogModelArticles
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'fp.ordering',
				'published', 'a.published',
				'author_id',
				'category_id',
				'level',
				'tag',
				'rating_count', 'rating',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid, a.state, a.access, a.created, a.hits,' .
					'a.created_by, a.featured, a.language, a.created_by_alias, a.publish_up, a.publish_down, a.note'
			)
		);
		$query->from('#__blog AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the content table.
		$query->select('fp.ordering')
			->join('INNER', '#__blog_frontpage AS fp ON fp.content_id = a.id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.title AS category_title, c.created_user_id AS category_uid, c.level AS category_level')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the parent categories.
		$query->select('parent.title AS parent_category_title, parent.id AS parent_category_id,
								parent.created_user_id AS parent_category_uid, parent.level AS parent_category_level')
			->join('LEFT', '#__categories AS parent ON parent.id = c.parent_id');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Join on voting table
		if (PluginHelper::isEnabled('content', 'vote'))
		{
			$query->select('COALESCE(NULLIF(ROUND(v.rating_sum  / v.rating_count, 0), 0), 0) AS rating,
							COALESCE(NULLIF(v.rating_count, 0), 0) as rating_count')
				->join('LEFT', '#__blog_rating AS v ON a.id = v.content_id');
		}

		// Filter by access level.
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$query->where('a.access = ' . (int) $access);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$access = implode(',', $access);
			$query->where('a.access IN (' . $access . ')');
		}

		// Filter by access level on categories.
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
			$query->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');

		if (is_array($categoryId) && count($categoryId) === 1)
		{
			$cat_tbl = Table::getInstance('Category', 'Table');
			$cat_tbl->load($categoryId[0]);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			$categoryId = implode(',', ArrayHelper::toInteger($categoryId));
			$query->where('a.catid IN (' . $categoryId . ')');
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('c.level <= ' . ((int) $level + (int) $baselevel - 1));
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $authorId);
		}
		elseif (is_array($authorId))
		{
			$authorId = ArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			$query->where('a.created_by IN (' . $authorId . ')');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			elseif (stripos($search, 'content:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 8), true) . '%');
				$query->where('(a.introtext LIKE ' . $search . ' OR a.fulltext LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by a single or group of tags.
		$tagId = $this->getState('filter.tag');

		if (is_array($tagId) && count($tagId) === 1)
		{
			$tagId = current($tagId);
		}

		if (is_array($tagId))
		{
			$tagId = implode(',', ArrayHelper::toInteger($tagId));

			if ($tagId)
			{
				$subQuery = $db->getQuery(true)
					->select('DISTINCT content_item_id')
					->from($db->quoteName('#__contentitem_tag_map'))
					->where('tag_id IN (' . $tagId . ')')
					->where('type_alias = ' . $db->quote('com_blog.article'));

				$query->join('INNER', '(' . (string) $subQuery . ') AS tagmap ON tagmap.content_item_id = a.id');
			}
		}
		elseif ($tagId)
		{
			$query->join(
				'INNER',
				$db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON tagmap.tag_id = ' . (int) $tagId
				. ' AND tagmap.content_item_id = a.id'
				. ' AND tagmap.type_alias = ' . $db->quote('com_blog.article')
			);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);
	}
}
