ALTER TABLE `#__blog`
MODIFY COLUMN `extra1` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.' AFTER `sources_blob`,
MODIFY COLUMN `extra2` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.' AFTER `extra1`,
MODIFY COLUMN `extra3` varchar(200) NOT NULL DEFAULT '' COMMENT 'Placeholder for additional custom field.' AFTER `extra2`,
MODIFY COLUMN `attribs` text NOT NULL AFTER `urls`,
DROP KEY `idx_sources`;
