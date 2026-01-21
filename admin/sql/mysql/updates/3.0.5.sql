ALTER TABLE `#__blog`
ADD COLUMN `gallery` text NOT NULL COMMENT 'A Subform-driven pseudo image gallery.' AFTER `secondary_categories`;
