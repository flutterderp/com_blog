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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

/**
 * Frontpage View class
 *
 * @since  1.5
 */
class BlogViewFeatured extends JViewLegacy
{
	protected $state = null;

	protected $item = null;

	protected $items = null;

	protected $pagination = null;

	protected $lead_items = array();

	protected $intro_items = array();

	protected $link_items = array();

	/** @deprecated  4.0 */
	protected $columns = 1;

	/**
	 * An instance of JDatabaseDriver.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.6.3
	 */
	protected $db;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$user = Factory::getUser();

		$state      = $this->get('State');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Flag indicates to not add limitstart=0 to URL
		$pagination->hideEmptyLimitstart = true;

		$offset = $this->state->get('list.offset', 0);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$params = &$state->params;

		// PREPARE THE DATA

		// Get the metrics for the structural page layout.
		$numLeading = (int) $params->def('num_leading_articles', 1);
		$numIntro   = (int) $params->def('num_intro_articles', 4);

		PluginHelper::importPlugin('content');

		// Compute the article slugs and prepare introtext (runs content plugins).
		foreach ($items as &$item)
		{
			$item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$item->catslug     = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			// No link for ROOT category
			if ($item->parent_alias === 'root')
			{
				$item->parent_slug = null;
			}

			$item->event = new stdClass;

			// Old plugins: Ensure that text property is available
			if (!isset($item->text))
			{
				$item->text = $item->introtext;
			}

			$app->triggerEvent('onContentPrepare', array ('com_blog.featured', &$item, &$item->params, $offset));

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results = $app->triggerEvent('onContentAfterTitle', array('com_blog.featured', &$item, &$item->params, $offset));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $app->triggerEvent('onContentBeforeDisplay', array('com_blog.featured', &$item, &$item->params, $offset));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $app->triggerEvent('onContentAfterDisplay', array('com_blog.featured', &$item, &$item->params, $offset));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		// Preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interogate the arrays.
		$max = count($items);

		// The first group is the leading articles.
		$limit = $numLeading;

		for ($i = 0; $i < $limit && $i < $max; $i++)
		{
			$this->lead_items[$i] = &$items[$i];
		}

		// The second group is the intro articles.
		$limit = $numLeading + $numIntro;

		// Order articles across, then down (or single column mode)
		for ($i = $numLeading; $i < $limit && $i < $max; $i++)
		{
			$this->intro_items[$i] = &$items[$i];
		}

		$this->columns = max(1, $params->def('num_columns', 1));
		$order = $params->def('multi_column_order', 1);

		if ($order == 0 && $this->columns > 1)
		{
			// Call order down helper
			$this->intro_items = BlogHelperQuery::orderDownColumns($this->intro_items, $this->columns);
		}

		// The remainder are the links.
		for ($i = $numLeading + $numIntro; $i < $max; $i++)
		{
			$this->link_items[$i] = &$items[$i];
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

		$this->params     = &$params;
		$this->items      = &$items;
		$this->pagination = &$pagination;
		$this->user       = &$user;
		$this->db         = Factory::getDbo();

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Add feed links
		if ($this->params->get('show_feed_link', 1))
		{
			$link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
