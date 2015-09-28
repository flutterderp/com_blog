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
		if($route == 'install')
		{
			// jimport('joomla.filesystem.folder');
			// JFolder::create( JPATH_SITE . '/images/stories' );
			
			// Create categories for our component
			$base_path	= JPATH_ADMINISTRATOR . '/components/com_categories';
			require_once( $base_path . '/models/category.php' );
			$config			= array( 'table_path' => $base_path . '/tables');
			$cat_model	= new CategoriesModelCategory( $config );
			$cat_data		= array( 'id' => 0, 'parent_id' => 1, 'level' => 1, 'path' => 'uncategorised', 'extension' => 'com_blog'
			, 'title' => 'Uncategorised', 'alias' => 'uncategorised', 'description' => '', 'published' => 1, 'language' => '*');
			$status			= $cat_model->save( $cat_data );
			
			if( !$status ) 
			{
				JError::raiseWarning( 500, JText::_( 'Unable to create default content category!' ) );
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