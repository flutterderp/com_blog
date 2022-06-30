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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
JLoader::register('BlogModelArticle', JPATH_ADMINISTRATOR . '/components/com_blog/models/article.php');

/**
 * Blog Component Article Model
 *
 * @since  1.5
 */
class BlogModelForm extends BlogModelArticle
{
	/**
	 * Model typeAlias string. Used for version history.
	 *
	 * @var        string
	 */
	public $typeAlias = 'com_blog.article';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		if ($params && $params->get('enable_category') == 1 && $params->get('catid'))
		{
			$catId = $params->get('catid');
		}
		else
		{
			$catId = 0;
		}

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('article.id', $pk);

		$this->setState('article.catid', $app->input->getInt('catid', $catId));

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		$this->setState('layout', $app->input->getString('layout'));
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $itemId  The id of the article.
	 *
	 * @return  mixed  Blog item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('article.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());

			return false;
		}

		$properties = $table->getProperties(1);
		$value = ArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new Registry($value->attribs);

		// Compute selected asset permissions.
		$user   = Factory::getUser();
		$userId = $user->get('id');
		$asset  = 'com_blog.article.' . $value->id;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			$value->params->set('access-edit', true);
		}

		// Now check if edit.own is available.
		elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
		{
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by)
			{
				$value->params->set('access-edit', true);
			}
		}

		// Check edit state permission.
		if ($itemId)
		{
			// Existing item
			$value->params->set('access-change', $user->authorise('core.edit.state', $asset));
		}
		else
		{
			// New item.
			$catId = (int) $this->getState('article.catid');

			if ($catId)
			{
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_blog.category.' . $catId));
				$value->catid = $catId;
			}
			else
			{
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_blog'));
			}
		}

		$value->articletext = $value->introtext;

		if (!empty($value->fulltext))
		{
			$value->articletext .= '<hr id="system-readmore" />' . $value->fulltext;
		}

		// Convert the metadata field to an array.
		$registry = new Registry($value->metadata);
		$value->metadata = $registry->toArray();

		if ($itemId)
		{
			$value->tags = new TagsHelper;
			$value->tags->getTagIds($value->id, 'com_blog.article');
			$value->metadata['tags'] = $value->tags;
		}

		return $value;
	}

	/**
	 * Get the return URL.
	 *
	 * @return  string	The return URL.
	 *
	 * @since   1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function save($data)
	{
		// Associations are not edited in frontend ATM so we have to inherit them
		if (Associations::isEnabled() && !empty($data['id'])
			&& $associations = Associations::getAssociations('com_blog', '#__blog', 'com_blog.item', $data['id']))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			$data['associations'] = $associations;
		}

		return parent::save($data);
	}

	/**
	 * Allows preprocessing of the Form object.
	 *
	 * @param   Form   $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function preprocessForm(Form $form, $data, $group = 'blog')
	{
		$params = $this->getState()->get('params');

		if ($params && $params->get('enable_category') == 1 && $params->get('catid'))
		{
			$form->setFieldAttribute('catid', 'default', $params->get('catid'));
			$form->setFieldAttribute('catid', 'readonly', 'true');
		}

		return parent::preprocessForm($form, $data, $group);
	}
}
