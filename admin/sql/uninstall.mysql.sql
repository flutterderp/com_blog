DROP TABLE IF EXISTS `#__blog`;
DROP TABLE IF EXISTS `#__blog_frontpage`;
DROP TABLE IF EXISTS `#__blog_rating`;

DELETE FROM `#__action_log_config` WHERE `type_alias` LIKE 'com_blog%';
DELETE FROM `#__action_logs_extensions` WHERE `extension` = 'com_blog';
DELETE FROM `#__content_types` WHERE `type_alias` LIKE 'com_blog%';
-- DELETE FROM `#__content_types` WHERE `type_alias` = 'com_blog.article';
-- DELETE FROM `#__content_types` WHERE `type_alias` = 'com_blog.category';
