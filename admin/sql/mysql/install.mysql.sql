--
-- Table structure for table `#__blog`
--

CREATE TABLE IF NOT EXISTS `#__blog` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `secondary_categories` mediumtext,
  `gallery` text NOT NULL COMMENT 'A Subform-driven pseudo image gallery.',
  `video_uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'A YouTube/Vimeo URI.',
  `toggle_sources_type` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `sources` text NOT NULL,
  `sources_blob` text NOT NULL,
  `extra1` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.',
  `extra2` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.',
  `extra3` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.',
  `state` tinyint NOT NULL DEFAULT 0,
  `catid` int unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  `checked_out` int unsigned,
  `checked_out_time` datetime NULL DEFAULT NULL,
  `publish_up` datetime NULL DEFAULT NULL,
  `publish_down` datetime NULL DEFAULT NULL,
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `version` int unsigned NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  `metakey` text,
  `metadesc` text NOT NULL,
  `access` int unsigned NOT NULL DEFAULT 0,
  `hits` int unsigned NOT NULL DEFAULT 0,
  `metadata` text NOT NULL,
  `featured` tinyint unsigned NOT NULL DEFAULT 0 COMMENT 'Set if article is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `xreference` varchar(50) COMMENT 'A reference to enable linkages to external data sets.',
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_alias` (`alias`(191)),
  KEY `idx_secondary_categories` (`secondary_categories`(128))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__blog_frontpage`
--

CREATE TABLE IF NOT EXISTS `#__blog_frontpage` (
  `content_id` int NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `featured_up` datetime,
  `featured_down` datetime,
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__blog_rating`
--

CREATE TABLE IF NOT EXISTS `#__blog_rating` (
  `content_id` int NOT NULL DEFAULT 0,
  `rating_sum` int unsigned NOT NULL DEFAULT 0,
  `rating_count` int unsigned NOT NULL DEFAULT 0,
  `lastip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Article', 'com_blog.article', '{\"special\":{\"dbtable\":\"#__blog\",\"key\":\"id\",\"type\":\"ArticleTable\",\"prefix\":\"Joomla\\\\Component\\\\Blog\\\\Administrator\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"state\",\"core_alias\":\"alias\",\"core_created_time\":\"created\",\"core_modified_time\":\"modified\",\"core_body\":\"introtext\", \"core_hits\":\"hits\",\"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"access\", \"core_params\":\"attribs\", \"core_featured\":\"featured\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"images\", \"core_urls\":\"urls\", \"core_version\":\"version\", \"core_ordering\":\"ordering\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"catid\", \"core_xreference\":\"xreference\", \"asset_id\":\"asset_id\", \"note\":\"note\"}, \"special\":{\"fulltext\":\"fulltext\"}}', 'BlogHelperRoute::getArticleRoute', '{\"formFile\":\"administrator\\/components\\/com_blog\\/models\\/forms\\/article.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\"],\"ignoreChanges\":[\"modified_by\", \"modified\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"ordering\"],\"convertToInt\":[\"publish_up\", \"publish_down\", \"featured\", \"ordering\"],\"displayLookup\":[{\"sourceColumn\":\"catid\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"created_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_by\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"} ]}'),
('Article Category', 'com_blog.category', '{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"Category\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"Joomla\\\\CMS\\\\Table\\\\\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"core_xreference\":\"null\", \"asset_id\":\"asset_id\"}, \"special\":{\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}', 'BlogHelperRoute::getCategoryRoute', '{\"formFile\":\"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml\", \"hideFields\":[\"asset_id\",\"checked_out\",\"checked_out_time\",\"version\",\"lft\",\"rgt\",\"level\",\"path\",\"extension\"], \"ignoreChanges\":[\"modified_user_id\", \"modified_time\", \"checked_out\", \"checked_out_time\", \"version\", \"hits\", \"path\"],\"convertToInt\":[\"publish_up\", \"publish_down\"], \"displayLookup\":[{\"sourceColumn\":\"created_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"access\",\"targetTable\":\"#__viewlevels\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"},{\"sourceColumn\":\"modified_user_id\",\"targetTable\":\"#__users\",\"targetColumn\":\"id\",\"displayColumn\":\"name\"},{\"sourceColumn\":\"parent_id\",\"targetTable\":\"#__categories\",\"targetColumn\":\"id\",\"displayColumn\":\"title\"}]}');

INSERT INTO `#__action_logs_extensions` (`extension`) VALUES ('com_blog');

--
-- Table structure for table `#__action_log_config`
--

INSERT INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`) VALUES
('article', 'com_blog.article', 'id' ,'title' , '#__blog', 'PLG_ACTIONLOG_JOOMLA'),
('article', 'com_blog.form', 'id', 'title' , '#__blog', 'PLG_ACTIONLOG_JOOMLA');

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

--   -- Add workflow transitions
--   INSERT INTO `#__workflow_transitions` (`id`, `published`, `ordering`, `workflow_id`, `title`, `description`, `from_stage_id`, `to_stage_id`, `options`) VALUES
--   (null, 1, (@t_order + 1), @w_id, 'UNPUBLISH', '', -1, 1, '{"publishing":"0"}'),
--   (null, 1, (@t_order + 2), @w_id, 'PUBLISH', '', -1, 1, '{"publishing":"1"}'),
--   (null, 1, (@t_order + 3), @w_id, 'TRASH', '', -1, 1, '{"publishing":"-2"}'),
--   (null, 1, (@t_order + 4), @w_id, 'ARCHIVE', '', -1, 1, '{"publishing":"2"}'),
--   (null, 1, (@t_order + 5), @w_id, 'FEATURE', '', -1, 1, '{"featuring":"1"}'),
--   (null, 1, (@t_order + 6), @w_id, 'UNFEATURE', '', -1, 1, '{"featuring":"0"}'),
--   (null, 1, (@t_order + 7), @w_id, 'PUBLISH_AND_FEATURE', '', -1, 1, '{"publishing":"1","featuring":"1"}');
-- END

-- CALL addTransitions();

-- DROP PROCEDURE IF EXISTS addTransitions;
