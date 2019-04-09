<?php
defined('_JEXEC') or die;

class com_blogInstallerScript
{
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __construct(JAdapterInstance $adapter)
	{

	}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter)
	{

	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($route, JAdapterInstance $adapter)
	{
		// Possible routes: install, update, discover_install
		if($route == 'install')
		{
			jimport('joomla.filesystem.folder');
			JFolder::create(JPATH_SITE . '/images/blog/gallery');

			// Create categories for our component
			$base_path = JPATH_ADMINISTRATOR . '/components/com_categories';
			require_once($base_path . '/models/category.php');
			$config    = array('table_path' => $base_path . '/tables');
			$cat_model = new CategoriesModelCategory($config);
			$cat_data  = array('id' => 0, 'parent_id' => 1, 'level' => 1, 'path' => 'uncategorised', 'extension' => 'com_blog'
			, 'title' => 'Uncategorised', 'alias' => 'uncategorised', 'description' => '', 'published' => 1, 'language' => '*');
			$status = $cat_model->save($cat_data);

			if(!$status)
			{
				JError::raiseWarning(500, JText::_('Unable to create default content category!'));
			}

			/**
			 * Set component defaults
			 *
			 * @Todo pull the JSON from our file if possible
			 **/
			try
			{
				$db       = JFactory::getDbo();
				$sql      = $db->getQuery(true);
				// $d_params = file_get_contents(JPATH_ADMINISTRATOR . '/com_blog/config.defaults.min.json');
				$d_params = '{"sef_advanced":"1", "sef_ids":"0", "vlog_mode":"0", "show_title":"1", "link_titles":"1", "show_intro":"1", "info_block_position":"0", "show_category":"1", "link_category":"0", "show_parent_category":"0", "link_parent_category":"0", "show_author":"1", "link_author":"0", "show_create_date":"1", "show_modify_date":"0", "show_publish_date":"0", "show_item_navigation":"0", "show_vote":"0", "show_readmore":"1", "show_readmore_title":"0", "readmore_limit":"100", "show_tags":"1", "show_icons":"0", "show_print_icon":"0", "show_email_icon":"0", "show_hits":"0", "show_noauth":"0", "urls_position":"0", "show_publishing_options":"1", "show_article_options":"1", "save_history":"1", "history_limit":"10", "show_urls_images_frontend":"0", "show_urls_images_backend":"1", "targeta":"Parent", "targetb":"Parent", "targetc":"Parent", "float_intro":"none", "float_fulltext":"none", "show_category_heading_title_text":"1", "show_category_title":"1", "show_description":"1", "show_description_image":"0", "maxLevel":"0", "show_empty_categories":"0", "show_no_articles":"1", "show_subcat_desc":"0", "show_cat_num_articles":"0", "show_cat_tags":"1", "show_base_description":"1", "maxLevelcat":"-1", "show_empty_categories_cat":"0", "show_subcat_desc_cat":"0", "show_cat_num_articles_cat":"0", "num_leading_articles":"5", "num_intro_articles":"0", "num_columns":"1", "num_links":"0", "multi_column_order":"0", "show_subcategory_content":"-1", "show_pagination_limit":"0", "filter_field":"hide", "show_headings":"0", "list_show_date":"0", "list_show_hits":"0", "list_show_author":"0", "orderby_pri":"none", "orderby_sec":"rdate", "order_date":"published", "show_pagination":"2", "show_pagination_results":"1", "show_featured":"show", "show_feed_link":"1", "feed_summary":"0", "feed_show_readmore":"0"}';
				$sql
					->update($db->quoteName('#__extensions'))
					->set('params = ' . $db->quote($d_params))
					->where('name = ' . $db->quote('com_blog'));
				$db->setQuery($sql);
				$db->execute();
				$sql->clear();
			}
			catch(Exception $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

		}
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
	{

	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
	{

	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter)
	{

	}
}
