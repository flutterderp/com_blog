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

/**
 * Methods supporting a list of article records.
 *
 * @since  1.6
 */
class BlogModelArticles extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JControllerLegacy
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
				'modified', 'a.modified',
				'created_by', 'a.created_by',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'published', 'a.published',
				'author_id',
				'category_id',
				'level',
				'tag',
				'rating_count', 'rating',
			);

			if (Associations::isEnabled())
			{
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config);
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
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Adjust the context to support forced languages.
		if ($forcedLanguage)
		{
			$this->context .= '.' . $forcedLanguage;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$formSubmited = $app->input->post->get('form_submited');

		$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$tag        = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');

		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);

			$authorId = $app->input->post->get('author_id');
			$this->setState('filter.author_id', $authorId);

			$categoryId = $app->input->post->get('category_id');
			$this->setState('filter.category_id', $categoryId);

			$tag = $app->input->post->get('tag');
			$this->setState('filter.tag', $tag);
		}

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . serialize($this->getState('filter.access'));
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
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
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid' .
				', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.ordering, a.featured, a.language, a.hits' .
				', a.publish_up, a.publish_down, a.note'
			)
		);
		$query->from('#__blog AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

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

		// Join over the associations.
		if (Associations::isEnabled())
		{
			$subQuery = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('asso1.id') . ') > 1')
				->from($db->quoteName('#__associations', 'asso1'))
				->join('INNER', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso1.key') . ' = ' . $db->quoteName('asso2.key'))
				->where(
					array(
						$db->quoteName('asso1.id') . ' = ' . $db->quoteName('a.id'),
						$db->quoteName('asso1.context') . ' = ' . $db->quote('com_content.item'),
					)
				);

			$query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
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

		// Filter by categories and by level
		$categoryId = $this->getState('filter.category_id', array());
		$level = $this->getState('filter.level');

		if (!is_array($categoryId))
		{
			$categoryId = $categoryId ? array($categoryId) : array();
		}

		// Case: Using both categories filter and by level filter
		if (count($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryTable = Table::getInstance('Category', 'JTable');
			$subCatItemsWhere = array();

			foreach ($categoryId as $filter_catid)
			{
				$categoryTable->load($filter_catid);
				$subCatItemsWhere[] = '(' .
					($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
					'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
					'c.rgt <= ' . (int) $categoryTable->rgt . ')';
			}

			$query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
		}

		// Case: Using only the by level filter
		elseif ($level)
		{
			$query->where('c.level <= ' . (int) $level);
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

		$tag  = $this->getState('filter.tag');

		// Run simplified query when filtering by one tag.
		if (\is_array($tag) && \count($tag) === 1)
		{
			$tag = $tag[0];
		}

		if ($tag && \is_array($tag))
		{
			$tag = ArrayHelper::toInteger($tag);

			$subQuery = $db->getQuery(true)
				->select('DISTINCT ' . $db->quoteName('content_item_id'))
				->from($db->quoteName('#__contentitem_tag_map'))
				->where(
					array(
						$db->quoteName('tag_id') . ' IN (' . implode(',', $tag) . ')',
						$db->quoteName('type_alias') . ' = ' . $db->quote('com_blog.article'),
					)
				);
			$query->join(
				'INNER',
				'(' . $subQuery . ') AS ' . $db->quoteName('tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
			);
		}
		elseif ($tag = (int) $tag)
		{
			$query->join(
				'INNER',
				$db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
			)
				->where(
					array(
						$db->quoteName('tagmap.tag_id') . ' = ' . $tag,
						$db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_blog.article'),
					)
				);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		if ($orderCol == 'a.ordering' || $orderCol == 'category_title')
		{
			$orderCol = $db->quoteName('c.title') . ' ' . $orderDirn . ', ' . $db->quoteName('a.ordering');
		}

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Build a list of authors
	 *
	 * @return  stdClass
	 *
	 * @since   1.6
	 *
	 * @deprecated  4.0  To be removed with Hathor
	 */
	public function getAuthors()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', '#__blog AS c ON c.created_by = u.id')
			->group('u.id, u.name')
			->order('u.name');

		// Setup the query
		$db->setQuery($query);

		// Return the result
		return $db->loadObjectList();
	}
}
