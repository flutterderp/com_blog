--
-- Table structure for table `#__blog`
--

ALTER TABLE `#__blog`
MODIFY COLUMN `id` int unsigned NOT NULL AUTO_INCREMENT FIRST,
MODIFY COLUMN `asset_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.' AFTER `id`,
MODIFY COLUMN `state` tinyint NOT NULL DEFAULT 0 AFTER `extra3`,
MODIFY COLUMN `catid` int unsigned NOT NULL DEFAULT 0 AFTER `state`,
MODIFY COLUMN `created` datetime NOT NULL AFTER `catid`,
MODIFY COLUMN `created_by` int unsigned NOT NULL DEFAULT 0 AFTER `created`,
MODIFY COLUMN `modified` datetime NOT NULL AFTER `created_by_alias`,
MODIFY COLUMN `modified_by` int unsigned NOT NULL DEFAULT 0 AFTER `modified`,
MODIFY COLUMN `checked_out` int unsigned AFTER `modified_by`,
MODIFY COLUMN `checked_out_time` datetime NULL DEFAULT NULL AFTER `checked_out`,
MODIFY COLUMN `publish_up` datetime NULL DEFAULT NULL AFTER `checked_out_time`,
MODIFY COLUMN `publish_down` datetime NULL DEFAULT NULL AFTER `publish_up`,
MODIFY COLUMN `attribs` varchar(5120) NOT NULL AFTER `urls`,
MODIFY COLUMN `version` int unsigned NOT NULL DEFAULT 1 AFTER `attribs`,
MODIFY COLUMN `ordering` int NOT NULL DEFAULT 0 AFTER `version`,
MODIFY COLUMN `metakey` text AFTER `ordering`,
MODIFY COLUMN `access` int unsigned NOT NULL DEFAULT 0 AFTER `metadesc`,
MODIFY COLUMN `hits` int unsigned NOT NULL DEFAULT 0 AFTER `access`,
MODIFY COLUMN `featured` tinyint unsigned NOT NULL DEFAULT 0 COMMENT 'Set if article is featured.' AFTER `metadata`;

-- --------------------------------------------------------

-- drop the xreference key
ALTER TABLE `#__blog`
DROP KEY `idx_xreference`;

-- --------------------------------------------------------

--
-- Table structure for table `#__blog_frontpage`
--

ALTER TABLE `#__blog_frontpage`
MODIFY COLUMN `content_id` int NOT NULL DEFAULT 0 FIRST,
MODIFY COLUMN `ordering` int NOT NULL DEFAULT 0 AFTER `content_id`,
ADD COLUMN `featured_up` datetime AFTER `ordering`,
ADD COLUMN `featured_down` datetime AFTER `featured_up`;

-- --------------------------------------------------------

--
-- Table structure for table `#__blog_rating`
--

ALTER TABLE `#__blog_rating`
MODIFY COLUMN `content_id` int NOT NULL DEFAULT 0 FIRST,
MODIFY COLUMN `rating_sum` int unsigned NOT NULL DEFAULT 0 AFTER `content_id`,
MODIFY COLUMN `rating_count` int unsigned NOT NULL DEFAULT 0 AFTER `rating_sum`;

-- --------------------------------------------------------

DELETE FROM `#__content_types` WHERE `type_alias` LIKE 'com_blog%';

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Article', 'com_blog.article', '{\"special\":{\"dbtable\":\"#__blog\",\"key\":\"id\",\"type\":\"ArticleTable\",\"prefix\":\"Joomla\\\\Component\\\\Blog\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"attribs\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"core_xreference\":\"xreference\", \"asset_id\":\"asset_id\", \"note\":\"note\"}, \"special\":{\"fulltext\":\"fulltext\"}}', 'BlogHelperRoute::getArticleRoute', '{\"formFile\":\"administrator\\/components\\/com_blog\\/models\\/forms\\/article.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"ordering\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),
('Article Category', 'com_blog.category', '{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"Category\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"core_xreference\":\"null\", \"asset_id\":\"asset_id\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}', 'BlogHelperRoute::getCategoryRoute', '{\"formFile\":\"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"],\"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}');

--
-- Dumping data for table `#__workflows`
--

INSERT INTO `#__workflows` (`published`, `title`, `description`, `extension`, `default`, `ordering`, `created`, `created_by`, `modified`, `modified_by`) VALUES
(1, 'COM_WORKFLOW_BASIC_WORKFLOW', '', 'com_blog.article', 1, 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0);

--
-- Add workflow transitions and a basic stage
--

-- CREATE PROCEDURE addTransitions()
-- BEGIN

--   SELECT `id` INTO @w_id FROM `#__workflows` WHERE `extension` = 'com_blog.article' LIMIT 1;
--   SELECT MAX(`ordering`) INTO @s_order FROM `#__workflow_stages`;
--   SELECT MAX(`ordering`) INTO @t_order FROM `#__workflow_transitions`;

--   -- Add a basic workflow stage
--   INSERT INTO `#__workflow_stages` (`ordering`, `workflow_id`, `published`, `title`, `description`, `default`) VALUES
--   ((@s_order + 1), @w_id, 1, 'COM_WORKFLOW_BASIC_STAGE', '', 1);

--   -- Add associations for existing articles
--   SELECT `id` INTO @s_id FROM `#__workflow_stages` WHERE `workflow_id` = @w_id LIMIT 1;

--   INSERT INTO `#__workflow_associations` (`item_id`, `stage_id`, `extension`)
--   SELECT c.`id` AS item_id, @s_id, 'com_blog.article' FROM `#__blog` AS c
--   WHERE NOT EXISTS (SELECT wa.`item_id` FROM `#__workflow_associations` AS wa WHERE wa.`item_id` = c.`id` AND wa.`extension` = 'com_blog.article');

--   -- Add workflow transitions
--   INSERT INTO `#__workflow_transitions` (`id`, `published`, `ordering`, `workflow_id`, `title`, `description`, `from_stage_id`, `to_stage_id`, `options`) VALUES
--   (null, 1, (@t_order + 1), @w_id, 'UNPUBLISH', '', -1, 1, '{"publishing":"0"}'),
--   (null, 1, (@t_order + 2), @w_id, 'PUBLISH', '', -1, 1, '{"publishing":"1"}'),
--   (null, 1, (@t_order + 3), @w_id, 'TRASH', '', -1, 1, '{"publishing":"-2"}'),
--   (null, 1, (@t_order + 4), @w_id, 'ARCHIVE', '', -1, 1, '{"publishing":"2"}'),
--   (null, 1, (@t_order + 5), @w_id, 'FEATURE', '', -1, 1, '{"featuring":"1"}'),
--   (null, 1, (@t_order + 6), @w_id, 'UNFEATURE', '', -1, 1, '{"featuring":"0"}'),
--   (null, 1, (@t_order + 7), @w_id, 'PUBLISH_AND_FEATURE', '', -1, 1, '{"publishing":"1","featuring":"1"}');
-- END;

-- CALL addTransitions();

-- DROP PROCEDURE IF EXISTS addTransitions;
