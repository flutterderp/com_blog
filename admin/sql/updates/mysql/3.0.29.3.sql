ALTER TABLE `#__blog`
MODIFY COLUMN `secondary_categories` mediumtext AFTER `fulltext`,
MODIFY COLUMN `xreference` varchar(50) COMMENT 'A reference to enable linkages to external data sets.' AFTER `language`;
