<?php
/**
 * Joomla! Blog Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Observer\Tags;
use Joomla\CMS\Table\Observer\ContentHistory as ContentHistoryObserver;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Version;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Blog table
 *
 * @since       1.5
 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
 */

if(Version::MAJOR_VERSION === 4)
{
	abstract class BlogBaseTable extends Table implements VersionableTableInterface, TaggableTableInterface
	{
		use TaggableTableTrait;
	}
}
else
{
	class BlogBaseTable extends Table { }
}

class Blog extends BlogBaseTable
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  A database connector object
	 *
	 * @since   1.5
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	public function __construct(&$db)
	{
		if(Version::MAJOR_VERSION === 4)
		{
			$this->typeAlias = 'com_blog.article';
		}

		parent::__construct('#__blog', 'id', $db);

		if(Version::MAJOR_VERSION < 4)
		{
			Tags::createObserver($this, array('typeAlias' => 'com_blog.article'));
			ContentHistoryObserver::createObserver($this, array('typeAlias' => 'com_blog.article'));
		}

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_blog.article.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the parent asset id for the record
	 *
	 * @param   Table    $table  A Table object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 *
	 * @since   1.6
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$assetId = null;

		// This is an article under a category.
		if ($this->catid)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__categories'))
				->where($this->_db->quoteName('id') . ' = ' . (int) $this->catid);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
	 *
	 * @see     Table::bind()
	 * @since   1.6
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	public function bind($array, $ignore = '')
	{
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext']))
		{
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos = preg_match($pattern, $array['articletext']);

			if ($tagPos == 0)
			{
				$this->introtext = $array['articletext'];
				$this->fulltext = '';
			}
			else
			{
				list ($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}
		}

		if (isset($array['gallery']) && is_array($array['gallery']))
		{
			$registry = new Registry($array['gallery']);
			$array['gallery'] = (string) $registry;
		}

		if (isset($array['sources']) && is_array($array['sources']))
		{
			$registry = new Registry($array['sources']);
			$array['sources'] = (string) $registry;
		}

		if(isset($array['secondary_categories']) && is_array($array['secondary_categories']))
		{
			$array['secondary_categories'] = implode(',', $array['secondary_categories']);
		}

		if (isset($array['attribs']) && is_array($array['attribs']))
		{
			$registry = new Registry($array['attribs']);
			$array['attribs'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new Registry($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new Rules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     Table::check()
	 * @since   1.5
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	public function check()
	{
		if (trim($this->title) == '')
		{
			$this->setError(\Text::_('COM_BLOG_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '')
		{
			$this->fulltext = '';
		}

		/**
		 * Ensure any new items have compulsory fields set. This is needed for things like
		 * frontend editing where we don't show all the fields or using some kind of API
		 */
		if (!$this->id)
		{
			// Images can be an empty json string
			if (!isset($this->images))
			{
				$this->images = '{}';
			}

			// URLs can be an empty json string
			if (!isset($this->urls))
			{
				$this->urls = '{}';
			}

			// Attributes (article params) can be an empty json string
			if (!isset($this->attribs))
			{
				$this->attribs = '{}';
			}

			// Metadata can be an empty json string
			if (!isset($this->metadata))
			{
				$this->metadata = '{}';
			}
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down < $this->publish_up && $this->publish_down > $this->_db->getNullDate())
		{
			// Swap the dates.
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey))
		{
			// Only process if not empty

			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", '<', '>');

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, '', $this->metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);

			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(', ', $clean_keys);
		}

		return true;
	}

	/**
	 * Gets the default asset values for a component.
	 *
	 * @param   string  $component  The component asset name to search for
	 *
	 * @return  Rules  The Rules object for the asset
	 *
	 * @since   3.4
	 * @deprecated  3.4 Class will be removed upon completion of transition to UCM
	 */
	protected function getDefaultAssetValues($component)
	{
		// Need to find the asset id by the name of the component.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$assetId = (int) $db->loadResult();

		return Access::getAssetRules($assetId);
	}

	/**
	 * Overrides Table::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
	 */
	public function store($updateNulls = false)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		$this->modified = $date->toSql();

		if (!$this->checked_out_time)
		{
			$this->checked_out_time = $this->_db->getNullDate();
		}

		if (!$this->publish_up)
		{
			$this->publish_up = $this->_db->getNullDate();
		}

		if (!$this->publish_down)
		{
			$this->publish_down = $this->_db->getNullDate();
		}

		if (!$this->hits)
		{
			$this->hits = 0;
		}

		if ($this->id)
		{
			// Existing item
			$this->modified_by = $user->get('id');
		}
		else
		{
			// New article. An article created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		// Verify that the alias is unique
		$table = Table::getInstance('Blog', 'Joomla\\CMS\\Table\\', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(\Text::_('JLIB_DATABASE_ERROR_ARTICLE_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Get the type alias for UCM features
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
}
