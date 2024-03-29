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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * Articles list controller class.
 *
 * @since  1.6
 */
class BlogControllerArticles extends JControllerAdmin
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
		parent::__construct($config);

		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}

		$this->registerTask('unfeatured', 'featured');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		$this->checkToken();

		$user   = Factory::getUser();
		$ids    = (array) $this->input->get('cid', array(), 'int');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			// Remove zero value resulting from input filter
			if ($id === 0)
			{
				unset($ids[$i]);

				continue;
			}

			if (!$user->authorise('core.edit.state', 'com_blog.article.' . (int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				throw new Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 403);
			}
		}

		if (empty($ids))
		{
			$message = null;

			throw new Exception(Text::_('JERROR_NO_ITEMS_SELECTED'), 500);
		}
		else
		{
			// Get the model.
			/** @var BlogModelArticle $model */
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				throw new Exception($model->getError(), 500);
			}

			if ($value == 1)
			{
				$message = Text::plural('COM_BLOG_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				$message = Text::plural('COM_BLOG_N_ITEMS_UNFEATURED', count($ids));
			}
		}

		$view = $this->input->get('view', '');

		if ($view == 'featured')
		{
			$this->setRedirect(Route::_('index.php?option=com_blog&view=featured', false), $message);
		}
		else
		{
			$this->setRedirect(Route::_('index.php?option=com_blog&view=articles', false), $message);
		}
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Article', $prefix = 'BlogModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
