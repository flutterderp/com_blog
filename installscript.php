<?php
/**
 * Install script for com_blog
 *
 * @link https://manual.joomla.org/docs/building-extensions/install-update/installation/install-process/#example-script-file
 */

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\Database\ParameterType;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Exception\FilesystemException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->set(
			InstallerScriptInterface::class,
			new class(
				$container->get(AdministratorApplication::class),
				$container->get(DatabaseInterface::class)
			) implements InstallerScriptInterface {
				private AdministratorApplication $app;
				private DatabaseInterface $db;
				private string $minimumJoomla = '5.3.0';
				private string $minimumPhp    = '8.1.0';

				protected string $prev_release = '';

				public function __construct(AdministratorApplication $app, DatabaseInterface $db)
				{
					$this->app          = $app;
					$this->db           = $db;
					$this->prev_release = $this->getOldVersion();
				}

				protected function getOldVersion()
				{
					$query = $this->db->getQuery(true);
					$query
						->select('manifest_cache, name, type')
						->from($this->db->quoteName('#__extensions'))
						->where('name = ' . $this->db->quote('com_blog'));

					try
					{
						$this->db->setQuery($query);

						if ($result = $this->db->loadObject())
						{
							$registry = new Joomla\Registry\Registry($result->manifest_cache);
							$manifest = $registry->toArray();
							$version  = $manifest['version'];
						}
						else
						{
							$version = '';
						}
					}
					catch(Exception $e)
					{
						$this->app->enqueueMessage($e->getMessage(), 'error');

						$version = '';
					}

					return $version;
				}

				/*
				 * Method to install the component
				 *
				 * @return void
				 */
				public function install(InstallerAdapter $adapter): bool
				{
					$this->app->enqueueMessage('Installing component…', 'info');

					return true;
				}

				public function update(InstallerAdapter $adapter): bool
				{
					$this->app->enqueueMessage('Updating component…', 'info');

					return true;
				}

				public function uninstall(InstallerAdapter $adapter): bool
				{
					$this->app->enqueueMessage('Uninstalling component…', 'info');

					return true;
				}

				public function preflight(string $type, InstallerAdapter $adapter): bool
				{
					if (version_compare(PHP_VERSION, $this->minimumPhp, '<'))
					{
						$this->app->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');

						return false;
					}

					if (version_compare(JVERSION, $this->minimumJoomla, '<'))
					{
						$this->app->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');

						return false;
					}

					/**
					 * We might need this to remove some of the workflow entries on uninstall…
					 */
					// if ($type == 'uninstall') { }

					return true;
				}

				public function postflight(string $type, InstallerAdapter $adapter): bool
				{
					// $app = $this->app;
					$this->app->enqueueMessage('Running postflight…', 'warning');

					if ($type == 'install')
					{
						Folder::create(JPATH_SITE . '/images/blog/gallery');

						// Create categories for our component
						$cat_model = AdminModel::getInstance('Category', 'CategoriesModel');
						$cat_data  = array('id' => 0, 'parent_id' => 1, 'level' => 1, 'path' => 'uncategorised', 'extension' => 'com_blog'
						, 'title' => 'Uncategorised', 'alias' => 'uncategorised', 'description' => '', 'published' => 1, 'language' => '*');
						$status    = $cat_model->save($cat_data);

						if (!$status)
						{
							$this->app->enqueueMessage('Unable to create default content category.', 'warning');
						}

						/**
						 * Set component defaults
						 *
						 * @Todo pull the JSON from our file if possible
						 **/
						try
						{
							$query = $this->db->getQuery(true);

							// $default_params = file_get_contents(JPATH_ADMINISTRATOR . '/com_blog/config.defaults.min.json');
							$default_params = '{"show_image_gallery_frontend":"1", "vlog_mode":"0", "show_title":"1", "link_titles":"1", "show_intro":"1", "info_block_position":"0", "info_block_show_title":"0", "show_category":"1", "link_category":"0", "show_parent_category":"0", "link_parent_category":"0", "show_associations":"0", "flags":"0", "show_author":"1", "link_author":"0", "show_create_date":"0", "show_modify_date":"0", "show_publish_date":"1", "show_item_navigation":"0", "show_vote":"0", "show_readmore":"1", "show_readmore_title":"0", "readmore_limit":"100", "show_tags":"1", "show_hits":"0", "show_noauth":"0", "urls_position":"0", "show_publishing_options":"1", "show_article_options":"1", "show_configure_edit_options":"", "show_permissions":"", "show_associations_edit":"", "save_history":"0", "history_limit":"10", "show_urls_images_frontend":"0", "show_urls_images_backend":"1", "targeta":"Parent", "targetb":"Parent", "targetc":"Parent", "float_intro":"none", "float_fulltext":"none", "show_category_title":"1", "show_description":"1", "show_description_image":"0", "maxLevel":"0", "show_empty_categories":"0", "show_no_articles":"1", "show_category_heading_title_text":"1", "show_subcat_desc":"0", "show_cat_num_articles":"0", "show_cat_tags":"1", "show_base_description":"1", "maxLevelcat":"-1", "show_empty_categories_cat":"0", "show_subcat_desc_cat":"0", "show_cat_num_articles_cat":"0", "blog_default_parameters":"", "num_leading_articles":"5", "content_class_leading":"", "num_intro_articles":"0", "content_class":"", "num_columns":"1", "multi_column_order":"0", "num_links":"0", "show_subcategory_content":"-1", "link_intro_image":"0", "show_pagination_limit":"0", "filter_field":"hide", "show_headings":"0", "list_show_date":"0", "date_format":"", "list_show_hits":"0", "list_show_author":"0", "display_num":"10", "list_show_votes":"", "list_show_ratings":"", "orderby_pri":"none", "orderby_sec":"rdate", "order_date":"published", "show_pagination":"2", "show_pagination_results":"1", "show_featured":"show", "integration_newsfeed":"1", "show_feed_link":"1", "feed_summary":"0", "feed_show_readmore":"0", "sef_ids":"1", "custom_fields_enable":"1", "workflow_enabled":"0"}';
							$extension_name = 'com_blog';

							$query
								->update($this->db->quoteName('#__extensions'))
								->set('params = :params')
								->where('name = :extName')
								->bind(':extName', $extension_name, ParameterType::STRING)
								->bind(':params', $default_params, ParameterType::STRING);
							$this->db->setQuery($query);
							$this->db->execute();

							// Add workflow transitions and a basic stage
							$query->clear();
							$query
								->select('id')
								->from($this->db->quoteName('#__workflows'))
								->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_blog.article'))
								->setLimit(1);
							$this->db->setQuery($query);
							$w_id = (int) $this->db->loadResult();

							$query->clear();
							$query->select('MAX(ordering)')->from($this->db->quoteName('#__workflow_stages'));
							$this->db->setQuery($query);
							$s_order = (int) $this->db->loadResult();

							$query->clear();
							$query->select('MAX(ordering)')->from($this->db->quoteName('#__workflow_transitions'));
							$this->db->setQuery($query);
							$t_order = (int) $this->db->loadResult();

							// Add a basic workflow stage
							$stage_columns = ['ordering', 'workflow_id', 'published', 'title', 'description', 'default'];
							$stage_values  = [($s_order + 1) . ', ' . $w_id . ', 1, "COM_WORKFLOW_BASIC_STAGE", "", 1'];

							$query->clear();
							$query
								->insert($this->db->quoteName('#__workflow_stages'))
								->columns($this->db->quoteName($stage_columns))
								->values($stage_values);
							$this->db->setQuery($query);
							$this->db->execute();

							// Add workflow transitions
							$transition_columns  = ['id', 'published', 'ordering', 'workflow_id', 'title', 'description', 'from_stage_id', 'to_stage_id', 'options'];
							$transition_values   = [];
							$transition_values[] = 'NULL, 1, ' . ($t_order + 1) . ', ' . $w_id . ', "UNPUBLISH", "", -1, 1, "{\"publishing\":\"0\"}"';
							$transition_values[] = 'NULL, 1, ' . ($t_order + 2) . ', ' . $w_id . ', "PUBLISH", "", -1, 1, "{\"publishing\":\"1\"}"';
							$transition_values[] = 'NULL, 1, ' . ($t_order + 3) . ', ' . $w_id . ', "TRASH", "", -1, 1, "{\"publishing\":\"-2\"}"';
							$transition_values[] = 'NULL, 1, ' . ($t_order + 4) . ', ' . $w_id . ', "ARCHIVE", "", -1, 1, "{\"publishing\":\"2\"}"';
							$transition_values[] = 'NULL, 1, ' . ($t_order + 5) . ', ' . $w_id . ', "FEATURE", "", -1, 1, "{\"featuring\":\"1\"}"';
							$transition_values[] = 'NULL, 1, ' . ($t_order + 6) . ', ' . $w_id . ', "UNFEATURE", "", -1, 1, "{\"featuring\":\"0\"}"';
							$transition_values[] = 'NULL, 1, ' . ($t_order + 7) . ', ' . $w_id . ', "PUBLISH_AND_FEATURE", "", -1, 1, "{\"publishing\":\"1\",\"featuring\":\"1\"}"';

							$query->clear();
							$query
								->insert($this->db->quoteName('#__workflow_transitions'))
								->columns($this->db->quoteName($transition_columns))
								->values($transition_values);
							$this->db->setQuery($query);
							$this->db->execute();
							// End add workflow transitions and a basic stage
						}
						catch(Exception $e)
						{
							$this->app->enqueueMessage($e->getMessage(), 'error');
						}
					}

					if ($type == 'update')
					{
						$this->app->enqueueMessage('Previous version: ' . $this->prev_release, 'info');

						$query = $this->db->getQuery(true);

						try
						{
							$query->clear();
							$query
								->select('id')
								->from($this->db->quoteName('#__workflows'))
								->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_blog.article'))
								->setLimit(1);
							$this->db->setQuery($query);
							$w_id = (int) $this->db->loadResult();

							if (version_compare($this->prev_release, '5.0.0', '<'))
							{
								// Add workflow transitions and a basic stage
								$query->clear();
								$query->select('MAX(ordering)')->from($this->db->quoteName('#__workflow_stages'));
								$this->db->setQuery($query);
								$s_order = (int) $this->db->loadResult();

								$query->clear();
								$query->select('MAX(ordering)')->from($this->db->quoteName('#__workflow_transitions'));
								$this->db->setQuery($query);
								$t_order = (int) $this->db->loadResult();

								// Add a basic workflow stage
								$stage_columns = ['ordering', 'workflow_id', 'published', 'title', 'description', 'default'];
								$stage_values  = [($s_order + 1) . ', ' . $w_id . ', 1, "COM_WORKFLOW_BASIC_STAGE", "", 1'];

								$query->clear();
								$query
									->insert($this->db->quoteName('#__workflow_stages'))
									->columns($this->db->quoteName($stage_columns))
									->values($stage_values);
								$this->db->setQuery($query);
								$this->db->execute();

								// Add workflow transitions
								$transition_columns  = ['id', 'published', 'ordering', 'workflow_id', 'title', 'description', 'from_stage_id', 'to_stage_id', 'options'];
								$transition_values   = [];
								$transition_values[] = 'NULL, 1, ' . ($t_order + 1) . ', ' . $w_id . ', "UNPUBLISH", "", -1, 1, "{\"publishing\":\"0\"}"';
								$transition_values[] = 'NULL, 1, ' . ($t_order + 2) . ', ' . $w_id . ', "PUBLISH", "", -1, 1, "{\"publishing\":\"1\"}"';
								$transition_values[] = 'NULL, 1, ' . ($t_order + 3) . ', ' . $w_id . ', "TRASH", "", -1, 1, "{\"publishing\":\"-2\"}"';
								$transition_values[] = 'NULL, 1, ' . ($t_order + 4) . ', ' . $w_id . ', "ARCHIVE", "", -1, 1, "{\"publishing\":\"2\"}"';
								$transition_values[] = 'NULL, 1, ' . ($t_order + 5) . ', ' . $w_id . ', "FEATURE", "", -1, 1, "{\"featuring\":\"1\"}"';
								$transition_values[] = 'NULL, 1, ' . ($t_order + 6) . ', ' . $w_id . ', "UNFEATURE", "", -1, 1, "{\"featuring\":\"0\"}"';
								$transition_values[] = 'NULL, 1, ' . ($t_order + 7) . ', ' . $w_id . ', "PUBLISH_AND_FEATURE", "", -1, 1, "{\"publishing\":\"1\",\"featuring\":\"1\"}"';

								$query->clear();
								$query
									->insert($this->db->quoteName('#__workflow_transitions'))
									->columns($this->db->quoteName($transition_columns))
									->values($transition_values);
								$this->db->setQuery($query);
								$this->db->execute();
								// End add workflow transitions and a basic stage
							}

							// Add associations for existing articles
							$subone = $this->db->getQuery(true);
							$subtwo = $this->db->getQuery(true);

							$subtwo
								->select('wa.item_id')
								->from($this->db->quoteName('#__workflow_associations', 'wa'))
								// ->innerJoin($this->db->quoteName('#__blog', 'tmp'))
								->where('wa.item_id = c.id')
								->where('wa.extension = ' . $this->db->quote('com_blog.article'));

							$subone
								->select('c.id AS item_id, s.id AS s_id, ' . $this->db->quote('com_blog.article'))
								->from($this->db->quoteName('#__blog', 'c'))
								->innerJoin($this->db->quoteName('#__workflows', 'w') . ' ON w.id = ' . (int) $w_id)
								->innerJoin($this->db->quoteName('#__workflow_stages', 's') . ' ON s.workflow_id = w.id')
								->where('NOT EXISTS (' . $subtwo . ')');
							$this->db->setQuery($subone);

							$assoc_items = $this->db->loadRowList();
							$assoc_data  = [];

							if (\is_countable($assoc_items))
							{
								array_walk($assoc_items, function($v, $k) use (&$assoc_data) { $assoc_data[] = $v[0] . ', ' . $v[1] . ', "' . $v[2] . '"'; });
								$assoc_columns = ['item_id', 'stage_id', 'extension'];
								$assoc_values  = $assoc_data;

								if (\count($assoc_values) > 0)
								{
									$query->clear();
									$query
										->insert($this->db->quoteName('#__workflow_associations'))
										->columns($this->db->quoteName($assoc_columns))
										->values($assoc_values);
									$this->db->setQuery($query);
									$this->db->execute();
								}
							}
						}
						catch(Exception $e)
						{
							$this->app->enqueueMessage($e->getMessage(), 'error');
							$this->app->enqueueMessage($query->dump(), 'error');
						}
					}

					return true;
				}
			}
		);
	}
};
