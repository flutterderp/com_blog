ALTER TABLE `#__blog`
ADD COLUMN `sources_blob` text NOT NULL AFTER `sources`,
ADD KEY `idx_sources_blob` (`sources_blob`(250));
