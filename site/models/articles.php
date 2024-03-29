<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('BlogHelperAssociation', JPATH_SITE . '/components/com_blog/helpers/association.php');

/**
 * This models supports retrieving lists of articles.
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
	 * @see     JController
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
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'images', 'a.images',
				'urls', 'a.urls',
				'filter_tag',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		$app = Factory::getApplication();

		// List state information
		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$value = $app->input->get('filter_tag', 0, 'uint');
		$this->setState('filter.tag', $value);

		$orderCol = $app->input->get('filter_order', 'a.ordering');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);
		$user = Factory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_blog')) && (!$user->authorise('core.edit', 'com_blog')))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());

		// Process show_noauth parameter
		if ((!$params->get('show_noauth')) || (!ComponentHelper::getParams('com_blog')->get('show_noauth')))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}

		$this->setState('layout', $app->input->getString('layout'));
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
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . serialize($this->getState('filter.article_id'));
		$id .= ':' . $this->getState('filter.article_id.include');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . $this->getState('filter.category_id.include');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . serialize($this->getState('filter.author_alias'));
		$id .= ':' . $this->getState('filter.author_alias.include');
		$id .= ':' . $this->getState('filter.date_filtering');
		$id .= ':' . $this->getState('filter.date_field');
		$id .= ':' . $this->getState('filter.start_date_range');
		$id .= ':' . $this->getState('filter.end_date_range');
		$id .= ':' . $this->getState('filter.relative_date');
		$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of articles subject to the model state.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$user = Factory::getUser();

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.introtext, a.fulltext, ' .
				'a.extra1, a.extra2, a.extra3, ' .
				'a.checked_out, a.checked_out_time, ' .
				'a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// Published/archived article in archive category is treats as archive article
				// If category is not published then force 0
				'CASE WHEN c.published = 2 AND a.state > 0 THEN 2 WHEN c.published != 1 THEN 0 ELSE a.state END as state,' .
				// Use created if modified is 0
				'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
				'a.modified_by, uam.name as modified_by_name,' .
				// Use created if publish_up is 0
				'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
				'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
				'a.hits, a.xreference, a.featured, a.language, ' . ' ' . $query->length('a.fulltext') . ' AS readmore, a.ordering'
			)
		);

		$query->from('#__blog AS a');

		$params      = $this->getState('params');
		$orderby_sec = $params->get('orderby_sec');

		// Join over the frontpage articles if required.
		if ($this->getState('filter.frontpage'))
		{
			if ($orderby_sec === 'front')
			{
				$query->select('fp.ordering');
				$query->join('INNER', '#__blog_frontpage AS fp ON fp.content_id = a.id');
			}
			else
			{
				$query->where('a.featured = 1');
			}
		}
		elseif ($orderby_sec === 'front' || $this->getState('list.ordering') === 'fp.ordering')
		{
			$query->select('fp.ordering');
			$query->join('LEFT', '#__blog_frontpage AS fp ON fp.content_id = a.id');
		}

		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			->select('c.published, c.published AS parents_published, c.lft')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
			->select('ua.email AS author_email')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
			->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Join over the categories to get parent category titles
		$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
			->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

		if (PluginHelper::isEnabled('content', 'vote'))
		{
			// Join on voting table
			$query->select('COALESCE(NULLIF(ROUND(v.rating_sum  / v.rating_count, 0), 0), 0) AS rating,
							COALESCE(NULLIF(v.rating_count, 0), 0) as rating_count')
				->join('LEFT', '#__blog_rating AS v ON a.id = v.content_id');
		}

		// Filter by access level.
		if ($this->getState('filter.access', true))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')')
				->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published) && $published == 2)
		{
			/**
			 * If category is archived then article has to be published or archived.
			 * Or category is published then article has to be archived.
			 */
			$query->where('((c.published = 2 AND a.state > 0) OR (c.published = 1 AND a.state = 2))');
		}
		elseif (is_numeric($published))
		{
			// Category has to be published
			$query->where('c.published = 1 AND a.state = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			$published = ArrayHelper::toInteger($published);
			$published = implode(',', $published);

			// Category has to be published
			$query->where('c.published = 1 AND a.state IN (' . $published . ')');
		}

		// Filter by featured state
		$featured = $this->getState('filter.featured');

		switch ($featured)
		{
			case 'hide':
				$query->where('a.featured = 0');
				break;

			case 'only':
				$query->where('a.featured = 1');
				break;

			case 'show':
			default:
				// Normally we do not discriminate between featured/unfeatured items.
				break;
		}

		// Filter by a single or group of articles.
		$articleId = $this->getState('filter.article_id');

		if (is_numeric($articleId))
		{
			$type = $this->getState('filter.article_id.include', true) ? '= ' : '<> ';
			$query->where('a.id ' . $type . (int) $articleId);
		}
		elseif (is_array($articleId))
		{
			$articleId = ArrayHelper::toInteger($articleId);
			$articleId = implode(',', $articleId);
			$type      = $this->getState('filter.article_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id ' . $type . ' (' . $articleId . ')');
		}

		// Filter by a single or group of categories
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals       = '(a.catid ' . $type . (int) $categoryId . ' OR FIND_IN_SET('.(int) $categoryId.', a.secondary_categories))';

			if ($includeSubcategories)
			{
				$levels = (int) $this->getState('filter.max_category_levels', '1');

				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true)
					->select('sub.id')
					->from('#__categories as sub')
					->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
					->where('this.id = ' . (int) $categoryId);

				if ($levels >= 0)
				{
					$subQuery->where('sub.level <= this.level + ' . $levels);
				}

				// Add the subquery to the main query
				$query->where('(' . $categoryEquals . ' OR a.catid IN (' . (string) $subQuery . '))');
			}
			else
			{
				$query->where($categoryEquals);
			}
		}
		elseif (is_array($categoryId) && (count($categoryId) > 0))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);

			if (!empty($categoryId))
			{
				$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.catid ' . $type . ' (' . $categoryId . ')');
			}
		}

		// Filter by author
		$authorId    = $this->getState('filter.author_id');
		$authorWhere = '';

		if (is_numeric($authorId))
		{
			$type        = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by ' . $type . (int) $authorId;
		}
		elseif (is_array($authorId))
		{
			$authorId = array_filter($authorId, 'is_numeric');

			if ($authorId)
			{
				$authorId    = implode(',', $authorId);
				$type        = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by ' . $type . ' (' . $authorId . ')';
			}
		}

		// Filter by author alias
		$authorAlias      = $this->getState('filter.author_alias');
		$authorAliasWhere = '';

		if (is_string($authorAlias))
		{
			$type             = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias ' . $type . $db->quote($authorAlias);
		}
		elseif (is_array($authorAlias))
		{
			$first = current($authorAlias);

			if (!empty($first))
			{
				foreach ($authorAlias as $key => $alias)
				{
					$authorAlias[$key] = $db->quote($alias);
				}

				$authorAlias = implode(',', $authorAlias);

				if ($authorAlias)
				{
					$type             = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias ' . $type . ' (' . $authorAlias .
						')';
				}
			}
		}

		if (!empty($authorWhere) && !empty($authorAliasWhere))
		{
			$query->where('(' . $authorWhere . ' OR ' . $authorAliasWhere . ')');
		}
		elseif (empty($authorWhere) && empty($authorAliasWhere))
		{
			// If both are empty we don't want to add to the query
		}
		else
		{
			// One of these is empty, the other is not so we just add both
			$query->where($authorWhere . $authorAliasWhere);
		}

		// Define null and now dates
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_blog')) && (!$user->authorise('core.edit', 'com_blog')))
		{
			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}

		// Filter by Date Range or Relative Date
		$dateFiltering = $this->getState('filter.date_filtering', 'off');
		$dateField     = $this->getState('filter.date_field', 'a.created');

		switch ($dateFiltering)
		{
			case 'range':
				$startDateRange = $db->quote($this->getState('filter.start_date_range', $nullDate));
				$endDateRange   = $db->quote($this->getState('filter.end_date_range', $nullDate));
				$query->where(
					'(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField .
					' <= ' . $endDateRange . ')'
				);
				break;

			case 'relative':
				$relativeDate = (int) $this->getState('filter.relative_date', 0);
				$query->where(
					$dateField . ' >= ' . $query->dateAdd($nowDate, -1 * $relativeDate, 'DAY')
				);
				break;

			case 'off':
			default:
				break;
		}

		// Process the filter for list views with user-entered filters
		if (is_object($params) && ($params->get('filter_field') !== 'hide') && ($filter = $this->getState('list.filter')))
		{
			// Clean filter variable
			$filter      = StringHelper::strtolower($filter);
			$monthFilter = $filter;
			$hitsFilter  = (int) $filter;
			$filter      = $db->quote('%' . $db->escape($filter, true) . '%', false);

			switch ($params->get('filter_field'))
			{
				case 'author':
					$query->where(
						'LOWER( CASE WHEN a.created_by_alias > ' . $db->quote(' ') .
						' THEN a.created_by_alias ELSE ua.name END ) LIKE ' . $filter . ' '
					);
					break;

				case 'hits':
					$query->where('a.hits >= ' . $hitsFilter . ' ');
					break;

				case 'month':
					if ($monthFilter != '')
					{
						$query->where(
							$db->quote(date("Y-m-d", strtotime($monthFilter)) . ' 00:00:00') . ' <= CASE WHEN a.publish_up = ' .
							$db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END'
						);

						$query->where(
							$db->quote(date("Y-m-t", strtotime($monthFilter)) . ' 23:59:59') . ' >= CASE WHEN a.publish_up = ' .
							$db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END'
						);
					}
					break;

				case 'title':
				default:
					// Default to 'title' if parameter is not valid
					$query->where('LOWER( a.title ) LIKE ' . $filter);
					break;
			}
		}

		// Filter by language
		if ($this->getState('filter.language'))
		{
			$query->where('a.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
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

				$query->innerJoin('(' . (string) $subQuery . ') AS tagmap ON tagmap.content_item_id = a.id');
			}
		}
		elseif ($tagId)
		{
			$query->innerJoin(
				$db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON tagmap.tag_id = ' . (int) $tagId
				. ' AND tagmap.content_item_id = a.id'
				. ' AND tagmap.type_alias = ' . $db->quote('com_blog.article')
			);
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$items  = parent::getItems();
		$user   = Factory::getUser();
		$userId = $user->get('id');
		$guest  = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
		$input  = Factory::getApplication()->input;

		// Get the global params
		$globalParams = ComponentHelper::getParams('com_blog', true);

		// Convert the parameter fields into objects.
		foreach ($items as &$item)
		{
			$articleParams = new Registry($item->attribs);

			// Unpack readmore and layout params
			$item->alternative_readmore = $articleParams->get('alternative_readmore');
			$item->layout               = $articleParams->get('layout');

			$item->params = clone $this->getState('params');

			/**
			 * For blogs, article params override menu item params only if menu param = 'use_article'
			 * Otherwise, menu item params control the layout
			 * If menu item is 'use_article' and there is no article param, use global
			 */
			if (($input->getString('layout') === 'blog') || ($input->getString('view') === 'featured')
				|| ($this->getState('params')->get('layout_type') === 'blog'))
			{
				// Create an array of just the params set to 'use_article'
				$menuParamsArray = $this->getState('params')->toArray();
				$articleArray    = array();

				foreach ($menuParamsArray as $key => $value)
				{
					if ($value === 'use_article')
					{
						// If the article has a value, use it
						if ($articleParams->get($key) != '')
						{
							// Get the value from the article
							$articleArray[$key] = $articleParams->get($key);
						}
						else
						{
							// Otherwise, use the global value
							$articleArray[$key] = $globalParams->get($key);
						}
					}
				}

				// Merge the selected article params
				if (count($articleArray) > 0)
				{
					$articleParams = new Registry($articleArray);
					$item->params->merge($articleParams);
				}
			}
			else
			{
				// For non-blog layouts, merge all of the article params
				$item->params->merge($articleParams);
			}

			// Get display date
			switch ($item->params->get('list_show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}

			/**
			 * Compute the asset access permissions.
			 * Technically guest could edit an article, but lets not check that to improve performance a little.
			 */
			if (!$guest)
			{
				$asset = 'com_blog.article.' . $item->id;

				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$item->params->set('access-edit', true);
				}

				// Now check if edit.own is available.
				elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by)
					{
						$item->params->set('access-edit', true);
					}
				}
			}

			$access = $this->getState('filter.access');

			if ($access)
			{
				// If the access filter has been set, we already have only the articles this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null)
				{
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else
				{
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}

			// Some contexts may not use tags data at all, so we allow callers to disable loading tag data
			if ($this->getState('load_tags', $item->params->get('show_tags', '1')))
			{
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_blog.article', $item->id);
			}

			if ($item->params->get('show_associations'))
			{
				$item->associations = BlogHelperAssociation::displayAssociations($item->id);
			}
		}

		return $items;
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   3.0.1
	 */
	public function getStart()
	{
		return $this->getState('list.start');
	}

	/**
	 * Count Items by Month
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   3.9.0
	 */
	public function countItemsByMonth()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query
			->select('DATE(' .
				$query->concatenate(
					array(
						$query->year($query->quoteName('publish_up')),
						$query->quote('-'),
						$query->month($query->quoteName('publish_up')),
						$query->quote('-01')
					)
				) . ') as d'
			)
			->select('COUNT(*) as c')
			->from('(' . $this->getListQuery() . ') as b')
			->group($query->quoteName('d'))
			->order($query->quoteName('d') . ' desc');

		return $db->setQuery($query)->loadObjectList();
	}
}
