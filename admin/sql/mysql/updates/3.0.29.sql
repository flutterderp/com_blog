ALTER TABLE `#__blog`
MODIFY COLUMN `created` datetime NOT NULL DEFAULT current_timestamp AFTER `catid`,
MODIFY COLUMN `modified` datetime AFTER `created_by_alias`,
MODIFY COLUMN `checked_out_time` datetime AFTER `checked_out`,
MODIFY COLUMN `publish_up` datetime NOT NULL DEFAULT current_timestamp AFTER `checked_out_time`,
MODIFY COLUMN `publish_down` datetime AFTER `publish_up`;
