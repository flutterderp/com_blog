<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Blog component
 *
 * @since  1.5
 */
class BlogViewForm extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $return_page;

	protected $state;

	/**
	 * Should we show a captcha form for the submission of the article?
	 *
	 * @var   bool
	 * @since 3.7.0
	 */
	protected $captchaEnabled = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		if (empty($this->item->id))
		{
			$catid = $this->state->params->get('catid');

			if ($this->state->params->get('enable_category') == 1 && $catid)
			{
				$authorised = $user->authorise('core.create', 'com_blog.category.' . $catid);
			}
			else
			{
				$authorised = $user->authorise('core.create', 'com_blog') || count($user->getAuthorisedCategories('com_blog', 'core.create'));
			}
		}
		else
		{
			$authorised = $this->item->params->get('access-edit');
		}

		if ($authorised !== true)
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		$this->item->tags = new JHelperTags;

		if (!empty($this->item->id))
		{
			$this->item->tags->getItemTags('com_blog.article', $this->item->id);

			$this->item->images = json_decode($this->item->images);
			$this->item->urls = json_decode($this->item->urls);

			$tmp = new stdClass;
			$tmp->images = $this->item->images;
			$tmp->urls = $this->item->urls;
			$this->form->bind($tmp);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

		$this->params = $params;

		// Override global params with article specific params
		$this->params->merge($this->item->params);
		$this->user   = $user;

		// Propose current language as default when creating new article
		if (empty($this->item->id) && JLanguageMultilang::isEnabled())
		{
			$lang = JFactory::getLanguage()->getTag();
			$this->form->setFieldAttribute('language', 'default', $lang);
		}

		$captchaSet = $params->get('captcha', JFactory::getApplication()->get('captcha', '0'));

		foreach (JPluginHelper::getPlugin('captcha') as $plugin)
		{
			if ($captchaSet === $plugin->name)
			{
				$this->captchaEnabled = true;
				break;
			}
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
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
			$this->params->def('page_heading', JText::_('COM_BLOG_FORM_EDIT_ARTICLE'));
		}

		$title = $this->params->def('page_title', JText::_('COM_BLOG_FORM_EDIT_ARTICLE'));

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

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
	}
}
