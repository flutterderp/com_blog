ALTER TABLE `#__blog`
MODIFY COLUMN `extra1` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.' AFTER `sources_blob`,
MODIFY COLUMN `extra2` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.' AFTER `extra1`,
MODIFY COLUMN `extra3` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.' AFTER `extra2`,
MODIFY COLUMN `attribs` text NOT NULL AFTER `urls`,
DROP KEY `idx_introtext`,
DROP KEY `idx_fulltext`,
DROP KEY `idx_sources`,
ADD KEY `idx_introtext` (`introtext`(191)), -- 768 / 4 (https://dev.mysql.com/doc/refman/8.0/en/innodb-limits.html)
ADD KEY `idx_fulltext` (`fulltext`(191)); -- 768 / 4
