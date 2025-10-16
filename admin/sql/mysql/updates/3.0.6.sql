ALTER TABLE `#__blog`
ADD COLUMN `video_uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'A YouTube/Vimeo URI.' AFTER `gallery`,
ADD COLUMN `sources` text NOT NULL AFTER `video_uri`,
ADD KEY `idx_sources` (`sources`(250)),
ADD KEY `idx_secondary_categories` (`secondary_categories`(128));
