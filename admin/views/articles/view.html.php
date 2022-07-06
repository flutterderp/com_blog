<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

define('CONDITION_TRASHED', -2);

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

/**
 * View class for a list of articles.
 *
 * @since  1.6
 */
class BlogViewArticles extends JViewLegacy
{
	/**
	 * The item authors
	 *
	 * @var  stdClass
	 *
	 * @deprecated  4.0  To be removed with Hathor
	 */
	protected $authors;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * All transition, which can be executed of one if the items
	 *
	 * @var  array
	 */
	protected $transitions = [];

	/**
	 * Is this view an Empty State
	 *
	 * @var  boolean
	 * @since 4.0.0
	 */
	private $isEmptyState = false;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal' && Version::MAJOR_VERSION < 4)
		{
			BlogHelper::addSubmenu('articles');
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		// $this->authors       = $this->get('Authors');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->vote          = PluginHelper::isEnabled('content', 'vote');
		$this->hits          = ComponentHelper::getParams('com_blog')->get('record_hits', 1);
		$this->transitions   = null;

		if (Version::MAJOR_VERSION === 4)
		{
			if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
			{
				/** @todo try and port getEmptyStateQuery stuff into our component */

				$this->setLayout('emptystate');
			}

			if (ComponentHelper::getParams('com_blog')->get('workflow_enabled'))
			{
				PluginHelper::importPlugin('workflow');

				$this->transitions = $this->get('Transitions');
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Levels filter - Used in Hathor.
		// @deprecated  4.0 To be removed with Hathor
		$this->f_levels = array(
			HTMLHelper::_('select.option', '1', Text::_('J1')),
			HTMLHelper::_('select.option', '2', Text::_('J2')),
			HTMLHelper::_('select.option', '3', Text::_('J3')),
			HTMLHelper::_('select.option', '4', Text::_('J4')),
			HTMLHelper::_('select.option', '5', Text::_('J5')),
			HTMLHelper::_('select.option', '6', Text::_('J6')),
			HTMLHelper::_('select.option', '7', Text::_('J7')),
			HTMLHelper::_('select.option', '8', Text::_('J8')),
			HTMLHelper::_('select.option', '9', Text::_('J9')),
			HTMLHelper::_('select.option', '10', Text::_('J10')),
		);

		$tpl = (Version::MAJOR_VERSION === 4) ? 'jfour' : 'jthree';

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			if(Version::MAJOR_VERSION < 4)
			{
				$this->sidebar = JHtmlSidebar::render();
			}

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = BlogHelper::getActions('com_blog', 'category', $this->state->get('filter.category_id'));
		$user  = Version::MAJOR_VERSION === 4 ? Factory::getApplication()->getIdentity() : Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_BLOG_ARTICLES_TITLE'), 'stack article');

		if (Version::MAJOR_VERSION === 4)
		{
			if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_blog', 'core.create')) > 0)
			{
				$toolbar->addNew('article.add');
			}

			if (!$this->isEmptyState && ($canDo->get('core.edit.state') || \count($this->transitions)))
			{
				$dropdown = $toolbar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('icon-ellipsis-h')
					->buttonClass('btn btn-action')
					->listCheck(true);

				$childBar = $dropdown->getChildToolbar();

				/* if (\count($this->transitions))
				{
					$childBar->separatorButton('transition-headline')
						->text('COM_GUESTCOMMENTS_RUN_TRANSITIONS')
						->buttonClass('text-center py-2 h3');

					$cmd = "Joomla.submitbutton('articles.runTransition');";
					$messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
					$alert = 'Joomla.renderMessages(' . $messages . ')';
					$cmd   = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

					foreach ($this->transitions as $transition)
					{
						$childBar->standardButton('transition')
							->text($transition['text'])
							->buttonClass('transition-' . (int) $transition['value'])
							->icon('icon-project-diagram')
							->onclick('document.adminForm.transition_id.value=' . (int) $transition['value'] . ';' . $cmd);
					}

					$childBar->separatorButton('transition-separator');
				} */

				if ($canDo->get('core.edit.state'))
				{
					$childBar->publish('articles.publish')->listCheck(true);

					$childBar->unpublish('articles.unpublish')->listCheck(true);

					$childBar->standardButton('featured')
						->text('JFEATURE')
						->task('articles.featured')
						->listCheck(true);

					$childBar->standardButton('unfeatured')
						->text('JUNFEATURE')
						->task('articles.unfeatured')
						->listCheck(true);

					$childBar->archive('articles.archive')->listCheck(true);

					$childBar->checkin('articles.checkin')->listCheck(true);

					if ($this->state->get('filter.state') != CONDITION_TRASHED)
					{
						$childBar->trash('articles.trash')->listCheck(true);
					}
				}

				// Add a batch button
				if ($user->authorise('core.create', 'com_blog')
					&& $user->authorise('core.edit', 'com_blog')
					&& $user->authorise('core.execute.transition', 'com_blog'))
				{
					$childBar->popupButton('batch')
						->text('JTOOLBAR_BATCH')
						->selector('collapseModal')
						->listCheck(true);
				}
			}

			if (/* !$this->isEmptyState && */ $this->state->get('filter.state') == CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('articles.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}

			if ($user->authorise('core.admin', 'com_blog') || $user->authorise('core.options', 'com_blog'))
			{
				$toolbar->preferences('com_blog');
			}

			$toolbar->help('JHELP_CONTENT_ARTICLE_MANAGER', true);
		}
		else
		{
			if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_blog', 'core.create')) > 0)
			{
				ToolbarHelper::addNew('article.add');
			}

			if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
			{
				ToolbarHelper::editList('article.edit');
			}

			if ($canDo->get('core.edit.state'))
			{
				ToolbarHelper::publish('articles.publish', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::unpublish('articles.unpublish', 'JTOOLBAR_UNPUBLISH', true);
				ToolbarHelper::custom('articles.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
				ToolbarHelper::custom('articles.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
				ToolbarHelper::archiveList('articles.archive');
				ToolbarHelper::checkin('articles.checkin');
			}

			// Add a batch button
			if ($user->authorise('core.create', 'com_blog')
				&& $user->authorise('core.edit', 'com_blog')
				&& $user->authorise('core.edit.state', 'com_blog'))
			{
				$title = Text::_('JTOOLBAR_BATCH');

				// Instantiate a new JLayoutFile instance and render the batch button
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				$toolbar->appendButton('Custom', $dhtml, 'batch');
			}

			if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
			{
				ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'articles.delete', 'JTOOLBAR_EMPTY_TRASH');
			}
			elseif ($canDo->get('core.edit.state'))
			{
				ToolbarHelper::trash('articles.trash');
			}

			if ($canDo->get('core.admin') || $canDo->get('core.options'))
			{
				ToolbarHelper::preferences('com_blog');
			}

			ToolbarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER');
		}

	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'        => Text::_('JSTATUS'),
			'a.title'        => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'access_level'   => Text::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => Text::_('JAUTHOR'),
			'language'       => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => Text::_('JDATE'),
			'a.id'           => Text::_('JGRID_HEADING_ID'),
			'a.featured'     => Text::_('JFEATURED'),
			'a.catid'        => Text::_('JCATEGORY')
		);
	}
}
