ALTER TABLE `#__blog`
ADD COLUMN `note` varchar(255) NOT NULL DEFAULT '' AFTER `xreference`;

ALTER TABLE `#__blog_frontpage`
CHANGE `blog_id` `content_id` int(11) NOT NULL DEFAULT 0;

ALTER TABLE `#__blog_rating`
CHANGE `blog_id` `content_id` int(11) NOT NULL DEFAULT 0;

-- Convert tables to utf8mb4
ALTER TABLE `#__blog` CONVERT TO CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__blog_frontpage` CONVERT TO CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__blog_rating` CONVERT TO CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
